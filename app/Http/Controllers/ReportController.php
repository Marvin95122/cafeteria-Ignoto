<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $filters = $this->resolveSalesFilters($request);
        $data = $this->buildSalesReportData($request, $filters, true);

        return view('reports.sales', $data);
    }

    public function salesExcel(Request $request)
    {
        $filters = $this->resolveSalesFilters($request);

        $data = $this->buildSalesReportData($request, $filters, false);

        $data['generatedBy'] = auth()->user()->name ?? 'Sistema';
        $data['generatedAt'] = now()->format('d/m/Y H:i');

        $filename = 'Reporte_Ventas_' . $filters['from'] . '_' . $filters['to'] . '.xlsx';

        return Excel::download(new SalesReportExport($data), $filename);
    }

    public function salesPdf(Request $request)
    {
        $filters = $this->resolveSalesFilters($request);

        $data = $this->buildSalesReportData($request, $filters, false);

        $data['generatedBy'] = auth()->user()->name ?? 'Sistema';
        $data['generatedAt'] = now()->format('d/m/Y H:i');
        $data['logoBase64'] = $this->reportLogoBase64();

        $filename = 'Reporte_Ventas_' . $filters['from'] . '_' . $filters['to'] . '.pdf';

        $pdf = Pdf::loadView('reports.sales_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function resolveSalesFilters(Request $request): array
    {
        $period = $request->input('period', 'mes');
        $groupBy = $request->input('group_by', 'dia');
        $paymentFilter = $request->input('payment_method', 'todos');
        $statusFilter = $request->input('status', 'todos');
        $search = trim($request->input('search', ''));

        $allowedPeriods = ['hoy', 'semana', 'mes', 'bimestre', 'trimestre', 'semestre', 'anio', 'personalizado'];
        $allowedGroups = ['dia', 'semana', 'mes', 'bimestre', 'trimestre', 'semestre', 'anio'];
        $allowedPayments = ['todos', 'efectivo', 'tarjeta', 'puntos'];
        $allowedStatuses = ['todos', 'completado', 'cancelado'];

        if (!in_array($period, $allowedPeriods, true)) {
            $period = 'mes';
        }

        if (!in_array($groupBy, $allowedGroups, true)) {
            $groupBy = 'dia';
        }

        if (!in_array($paymentFilter, $allowedPayments, true)) {
            $paymentFilter = 'todos';
        }

        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'todos';
        }

        $today = Carbon::today();

        if ($period === 'personalizado') {
            $from = $request->input('from', $today->copy()->startOfMonth()->toDateString());
            $to = $request->input('to', $today->toDateString());

            $startDate = Carbon::parse($from)->startOfDay();
            $endDate = Carbon::parse($to)->endOfDay();
        } else {
            [$startDate, $endDate] = $this->datesForQuickPeriod($period, $today);
        }

        if ($startDate->gt($endDate)) {
            $endDate = $startDate->copy()->endOfDay();
        }

        return [
            'period' => $period,
            'groupBy' => $groupBy,
            'paymentFilter' => $paymentFilter,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'from' => $startDate->toDateString(),
            'to' => $endDate->toDateString(),
        ];
    }

    private function datesForQuickPeriod(string $period, Carbon $today): array
    {
        return match ($period) {
            'hoy' => [
                $today->copy()->startOfDay(),
                $today->copy()->endOfDay(),
            ],

            'semana' => [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek(),
            ],

            'bimestre' => [
                $today->copy()->subMonth()->startOfMonth(),
                $today->copy()->endOfMonth(),
            ],

            'trimestre' => [
                $today->copy()->startOfQuarter(),
                $today->copy()->endOfQuarter(),
            ],

            'semestre' => [
                $today->copy()->month <= 6
                    ? $today->copy()->startOfYear()
                    : $today->copy()->month(7)->startOfMonth(),

                $today->copy()->month <= 6
                    ? $today->copy()->month(6)->endOfMonth()
                    : $today->copy()->endOfYear(),
            ],

            'anio' => [
                $today->copy()->startOfYear(),
                $today->copy()->endOfYear(),
            ],

            default => [
                $today->copy()->startOfMonth(),
                $today->copy()->endOfMonth(),
            ],
        };
    }

    private function buildSalesReportData(Request $request, array $filters, bool $paginate = true): array
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $paymentFilter = $filters['paymentFilter'];
        $statusFilter = $filters['statusFilter'];
        $search = $filters['search'];
        $groupBy = $filters['groupBy'];

        /*
        |--------------------------------------------------------------------------
        | Tickets para tabla
        |--------------------------------------------------------------------------
        */
        $ordersTableQuery = Order::with(['user', 'customer', 'items.product', 'canceller'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($statusFilter !== 'todos') {
            $ordersTableQuery->where('status', $statusFilter);
        }

        if ($paymentFilter !== 'todos') {
            $ordersTableQuery->where('payment_method', $paymentFilter);
        }

        if ($search !== '') {
            $this->applyTicketSearch($ordersTableQuery, $search);
        }

        $orders = $paginate
            ? $ordersTableQuery->latest()->paginate(12)->withQueryString()
            : $ordersTableQuery->latest()->get();

        /*
        |--------------------------------------------------------------------------
        | Ventas y cancelaciones
        |--------------------------------------------------------------------------
        */
        $baseCompletedQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completado');

        $baseCancelledQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelado');

        if ($paymentFilter !== 'todos') {
            $baseCompletedQuery->where('payment_method', $paymentFilter);
            $baseCancelledQuery->where('payment_method', $paymentFilter);
        }

        $completedOrders = (clone $baseCompletedQuery)->get();
        $cancelledOrders = (clone $baseCancelledQuery)->with(['user', 'canceller'])->latest()->get();

        $salesCash = (float) $completedOrders->where('payment_method', 'efectivo')->sum('total');
        $salesCard = (float) $completedOrders->where('payment_method', 'tarjeta')->sum('total');
        $salesPoints = (float) $completedOrders->where('payment_method', 'puntos')->sum('total');

        $cashOrdersCount = $completedOrders->where('payment_method', 'efectivo')->count();
        $cardOrdersCount = $completedOrders->where('payment_method', 'tarjeta')->count();
        $pointsOrdersCount = $completedOrders->where('payment_method', 'puntos')->count();

        $totalOperatedSales = $salesCash + $salesCard + $salesPoints;
        $realIncome = $salesCash + $salesCard;

        $completedOrdersCount = $completedOrders->count();

        $averageTicket = $completedOrdersCount > 0
            ? $totalOperatedSales / $completedOrdersCount
            : 0;

        $cancelledOrdersCount = $cancelledOrders->count();
        $cancelledOrdersAmount = (float) $cancelledOrders->sum('total');

        $cashReceived = (float) $completedOrders->where('payment_method', 'efectivo')->sum('cash_received');
        $cashChange = (float) $completedOrders->where('payment_method', 'efectivo')->sum('cash_change');

        $pointsEarned = (int) $completedOrders->sum('points_earned');
        $pointsUsed = (int) $completedOrders->sum('points_used');

        /*
        |--------------------------------------------------------------------------
        | Gastos
        |--------------------------------------------------------------------------
        */
        $expenses = Expense::with(['user', 'canceller'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $activeExpenses = $expenses->where('status', 'activo');
        $cancelledExpenses = $expenses->where('status', 'cancelado');

        $expensesTotal = (float) $activeExpenses->sum('amount');
        $expensesCount = $activeExpenses->count();
        $cancelledExpensesCount = $cancelledExpenses->count();

        $operatingProfit = $realIncome - $expensesTotal;

        /*
        |--------------------------------------------------------------------------
        | Datos para análisis
        |--------------------------------------------------------------------------
        */
        $topProducts = $this->topProducts($startDate, $endDate, $paymentFilter, $totalOperatedSales);
        $salesByPeriod = $this->salesByPeriod($completedOrders, $groupBy, $startDate, $endDate);
        $expensesByCategory = $this->expensesByCategory($activeExpenses, $expensesTotal);
        $cancellationsByAction = $this->cancellationsByAction($cancelledOrders, $cancelledOrdersCount);

        $paymentBreakdown = $this->paymentBreakdown(
            $salesCash,
            $salesCard,
            $salesPoints,
            $cashOrdersCount,
            $cardOrdersCount,
            $pointsOrdersCount,
            $totalOperatedSales
        );

        $bestPayment = collect($paymentBreakdown)->sortByDesc('amount')->first();
        $bestProduct = $topProducts->first();

        $executiveSummary = $this->makeExecutiveSummary(
            $completedOrdersCount,
            $realIncome,
            $bestPayment,
            $bestProduct,
            $cancelledOrdersCount,
            $cancelledOrdersAmount,
            $operatingProfit
        );

        $periodLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');

        return array_merge($filters, [
            'orders' => $orders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'expenses' => $expenses,
            'activeExpenses' => $activeExpenses,
            'cancelledExpenses' => $cancelledExpenses,

            'salesCash' => $salesCash,
            'salesCard' => $salesCard,
            'salesPoints' => $salesPoints,

            'cashOrdersCount' => $cashOrdersCount,
            'cardOrdersCount' => $cardOrdersCount,
            'pointsOrdersCount' => $pointsOrdersCount,

            'totalOperatedSales' => $totalOperatedSales,
            'realIncome' => $realIncome,
            'completedOrdersCount' => $completedOrdersCount,
            'averageTicket' => $averageTicket,

            'cancelledOrdersCount' => $cancelledOrdersCount,
            'cancelledOrdersAmount' => $cancelledOrdersAmount,

            'cashReceived' => $cashReceived,
            'cashChange' => $cashChange,

            'pointsEarned' => $pointsEarned,
            'pointsUsed' => $pointsUsed,

            'expensesTotal' => $expensesTotal,
            'expensesCount' => $expensesCount,
            'cancelledExpensesCount' => $cancelledExpensesCount,
            'operatingProfit' => $operatingProfit,

            'topProducts' => $topProducts,
            'salesByPeriod' => $salesByPeriod,
            'expensesByCategory' => $expensesByCategory,
            'cancellationsByAction' => $cancellationsByAction,
            'paymentBreakdown' => $paymentBreakdown,
            'executiveSummary' => $executiveSummary,
            'periodLabel' => $periodLabel,
        ]);
    }

    private function topProducts(Carbon $startDate, Carbon $endDate, string $paymentFilter, float $totalOperatedSales)
    {
        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->where('orders.status', 'completado')
            ->whereBetween('orders.created_at', [$startDate, $endDate]);

        if ($paymentFilter !== 'todos') {
            $query->where('orders.payment_method', $paymentFilter);
        }

        return $query
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($totalOperatedSales) {
                $item->total_sold = (int) $item->total_sold;
                $item->total_revenue = (float) $item->total_revenue;
                $item->percentage = $totalOperatedSales > 0
                    ? ($item->total_revenue / $totalOperatedSales) * 100
                    : 0;

                return $item;
            });
    }

    private function salesByPeriod($completedOrders, string $groupBy, Carbon $startDate, Carbon $endDate)
    {
        $buckets = [];

        foreach ($this->periodBuckets($startDate, $endDate, $groupBy) as $key => $label) {
            $buckets[$key] = [
                'label' => $label,
                'sales' => 0,
                'orders' => 0,
            ];
        }

        foreach ($completedOrders as $order) {
            $created = Carbon::parse($order->created_at);
            $key = $this->groupKey($created, $groupBy);

            if (!isset($buckets[$key])) {
                $buckets[$key] = [
                    'label' => $this->groupLabel($created, $groupBy),
                    'sales' => 0,
                    'orders' => 0,
                ];
            }

            $buckets[$key]['sales'] += (float) $order->total;
            $buckets[$key]['orders']++;
        }

        return collect($buckets)->values();
    }

    private function periodBuckets(Carbon $startDate, Carbon $endDate, string $groupBy): array
    {
        $buckets = [];
        $cursor = $startDate->copy()->startOfDay();

        while ($cursor->lte($endDate)) {
            $buckets[$this->groupKey($cursor, $groupBy)] = $this->groupLabel($cursor, $groupBy);

            $cursor = match ($groupBy) {
                'semana' => $cursor->copy()->addWeek()->startOfWeek(),
                'mes' => $cursor->copy()->addMonth()->startOfMonth(),
                'bimestre' => $cursor->copy()->addMonths(2)->startOfMonth(),
                'trimestre' => $cursor->copy()->addQuarter()->startOfQuarter(),
                'semestre' => $cursor->copy()->addMonths(6)->startOfMonth(),
                'anio' => $cursor->copy()->addYear()->startOfYear(),
                default => $cursor->copy()->addDay(),
            };
        }

        return $buckets;
    }

    private function groupKey(Carbon $date, string $groupBy): string
    {
        return match ($groupBy) {
            'semana' => $date->format('o') . '-S' . str_pad((string) $date->isoWeek(), 2, '0', STR_PAD_LEFT),
            'mes' => $date->format('Y-m'),
            'bimestre' => $date->format('Y') . '-B' . (int) ceil($date->month / 2),
            'trimestre' => $date->format('Y') . '-T' . $date->quarter,
            'semestre' => $date->format('Y') . '-S' . ($date->month <= 6 ? '1' : '2'),
            'anio' => $date->format('Y'),
            default => $date->toDateString(),
        };
    }

    private function groupLabel(Carbon $date, string $groupBy): string
    {
        return match ($groupBy) {
            'semana' => 'Sem. ' . $date->isoWeek() . ' ' . $date->format('Y'),
            'mes' => ucfirst($date->translatedFormat('M Y')),
            'bimestre' => 'Bim. ' . (int) ceil($date->month / 2) . ' ' . $date->format('Y'),
            'trimestre' => 'Trim. ' . $date->quarter . ' ' . $date->format('Y'),
            'semestre' => 'Semestre ' . ($date->month <= 6 ? '1' : '2') . ' ' . $date->format('Y'),
            'anio' => $date->format('Y'),
            default => $date->format('d/m'),
        };
    }

    private function expensesByCategory($activeExpenses, float $expensesTotal)
    {
        return $activeExpenses
            ->groupBy(fn ($expense) => $expense->category ?: 'Sin categoría')
            ->map(function ($items, $category) use ($expensesTotal) {
                $amount = (float) $items->sum('amount');

                return [
                    'category' => $category,
                    'amount' => $amount,
                    'count' => $items->count(),
                    'percentage' => $expensesTotal > 0 ? ($amount / $expensesTotal) * 100 : 0,
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    private function cancellationsByAction($cancelledOrders, int $cancelledOrdersCount)
    {
        return $cancelledOrders
            ->groupBy(fn ($order) => $order->cancellation_action ?: 'Sin acción')
            ->map(function ($items, $action) use ($cancelledOrdersCount) {
                return [
                    'action' => $action,
                    'count' => $items->count(),
                    'amount' => (float) $items->sum('total'),
                    'percentage' => $cancelledOrdersCount > 0 ? ($items->count() / $cancelledOrdersCount) * 100 : 0,
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    private function paymentBreakdown(
        float $salesCash,
        float $salesCard,
        float $salesPoints,
        int $cashOrdersCount,
        int $cardOrdersCount,
        int $pointsOrdersCount,
        float $totalOperatedSales
    ): array {
        $payments = [
            [
                'key' => 'efectivo',
                'label' => 'Efectivo',
                'amount' => $salesCash,
                'count' => $cashOrdersCount,
                'emoji' => '💵',
                'class' => 'bg-green-50 text-green-700 border-green-100',
            ],
            [
                'key' => 'tarjeta',
                'label' => 'Tarjeta',
                'amount' => $salesCard,
                'count' => $cardOrdersCount,
                'emoji' => '💳',
                'class' => 'bg-blue-50 text-blue-700 border-blue-100',
            ],
            [
                'key' => 'puntos',
                'label' => 'Puntos',
                'amount' => $salesPoints,
                'count' => $pointsOrdersCount,
                'emoji' => '👑',
                'class' => 'bg-purple-50 text-purple-700 border-purple-100',
            ],
        ];

        return collect($payments)
            ->map(function ($payment) use ($totalOperatedSales) {
                $payment['percentage'] = $totalOperatedSales > 0
                    ? ($payment['amount'] / $totalOperatedSales) * 100
                    : 0;

                return $payment;
            })
            ->all();
    }

    private function makeExecutiveSummary(
        int $completedOrdersCount,
        float $realIncome,
        ?array $bestPayment,
        $bestProduct,
        int $cancelledOrdersCount,
        float $cancelledOrdersAmount,
        float $operatingProfit
    ): string {
        $paymentText = $bestPayment && $bestPayment['amount'] > 0
            ? "El método de pago predominante fue {$bestPayment['label']} con $" . number_format($bestPayment['amount'], 2) . "."
            : 'No hay un método de pago predominante en el periodo.';

        $productText = $bestProduct
            ? "El producto con mayor movimiento fue {$bestProduct->name}, con {$bestProduct->total_sold} unidad(es) vendida(s)."
            : 'No hay productos vendidos en el periodo.';

        return "Durante el periodo seleccionado se registraron {$completedOrdersCount} ticket(s) completados, con ingresos reales de $" .
            number_format($realIncome, 2) .
            ". {$paymentText} {$productText} Se registraron {$cancelledOrdersCount} ticket(s) cancelados por un monto de $" .
            number_format($cancelledOrdersAmount, 2) .
            ". La utilidad operativa estimada fue de $" .
            number_format($operatingProfit, 2) .
            " después de descontar gastos activos.";
    }

    private function applyTicketSearch($query, string $search): void
    {
        $query->where(function ($subQuery) use ($search) {
            if (is_numeric($search)) {
                $subQuery->orWhere('id', (int) $search);
            }

            $subQuery->orWhere('customer_name', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
        });
    }

    private function reportLogoBase64(): ?string
    {
        $paths = [
            public_path('img/logo-cafeteria.png'),
            public_path('images/logo-cafeteria.png'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);

                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        return null;
    }
}