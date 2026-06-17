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
use App\Models\CashRegister;
use App\Models\CashRegisterAdjustment;
use App\Exports\CashReportExport;

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

    public function cash(Request $request)
    {
        $filters = $this->resolveCashFilters($request);
        $data = $this->buildCashReportData($request, $filters, true);

        return view('reports.cash', $data);
    }

    public function cashExcel(Request $request)
    {
        $filters = $this->resolveCashFilters($request);

        $data = $this->buildCashReportData($request, $filters, false);

        $data['generatedBy'] = auth()->user()->name ?? 'Sistema';
        $data['generatedAt'] = now()->format('d/m/Y H:i');

        $filename = 'Reporte_Caja_' . $filters['from'] . '_' . $filters['to'] . '.xlsx';

        return Excel::download(new CashReportExport($data), $filename);
    }

    private function resolveCashFilters(Request $request): array
    {
        $period = $request->input('period', 'mes');
        $groupBy = $request->input('group_by', 'dia');
        $statusFilter = $request->input('status', 'todas');
        $search = trim($request->input('search', ''));

        $allowedPeriods = ['hoy', 'semana', 'mes', 'bimestre', 'trimestre', 'semestre', 'anio', 'personalizado'];
        $allowedGroups = ['dia', 'semana', 'mes', 'bimestre', 'trimestre', 'semestre', 'anio'];
        $allowedStatuses = ['todas', 'abierta', 'cerrada'];

        if (!in_array($period, $allowedPeriods, true)) {
            $period = 'mes';
        }

        if (!in_array($groupBy, $allowedGroups, true)) {
            $groupBy = 'dia';
        }

        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'todas';
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
            'statusFilter' => $statusFilter,
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'from' => $startDate->toDateString(),
            'to' => $endDate->toDateString(),
        ];
    }

    private function buildCashReportData(Request $request, array $filters, bool $paginate = true): array
    {
        $startDate = $filters['startDate'];
        $endDate = $filters['endDate'];
        $statusFilter = $filters['statusFilter'];
        $search = $filters['search'];
        $groupBy = $filters['groupBy'];

        $cashRegistersQuery = CashRegister::with(['user', 'closedBy', 'expenses', 'adjustments.user'])
            ->whereBetween('opened_at', [$startDate, $endDate])
            ->orderByDesc('opened_at');

        if ($statusFilter !== 'todas') {
            $cashRegistersQuery->where('status', $statusFilter);
        }

        if ($search !== '') {
            $this->applyCashSearch($cashRegistersQuery, $search);
        }

        $cashRegisters = $paginate
            ? (clone $cashRegistersQuery)->paginate(10)->withQueryString()
            : (clone $cashRegistersQuery)->get();

        $allCashRegisters = (clone $cashRegistersQuery)->get();

        $cutRows = $allCashRegisters->map(function ($cashRegister) {
            return $this->cashRegisterReportRow($cashRegister);
        });

        $paginatedCutRows = collect($cashRegisters->items())->map(function ($cashRegister) {
            return $this->cashRegisterReportRow($cashRegister);
        });

        $totalCuts = $cutRows->count();
        $openCuts = $cutRows->where('status', 'abierta')->count();
        $closedCuts = $cutRows->where('status', 'cerrada')->count();

        $openingTotal = (float) $cutRows->sum('opening_amount');
        $expectedTotal = (float) $cutRows->sum('expected_cash');
        $actualTotal = (float) $cutRows->sum('actual_cash');
        $differenceTotal = (float) $cutRows->sum('difference');

        $salesCashTotal = (float) $cutRows->sum('sales_cash');
        $salesCardTotal = (float) $cutRows->sum('sales_card');
        $salesPointsTotal = (float) $cutRows->sum('sales_points');
        $expensesTotal = (float) $cutRows->sum('expenses_total');

        $completedOrdersCount = (int) $cutRows->sum('completed_orders_count');
        $cancelledOrdersCount = (int) $cutRows->sum('cancelled_orders_count');
        $adjustmentsCount = (int) $cutRows->sum('adjustments_count');

        $cutsWithPositiveDifference = $cutRows->filter(fn ($row) => $row['difference'] > 0)->count();
        $cutsWithNegativeDifference = $cutRows->filter(fn ($row) => $row['difference'] < 0)->count();
        $cutsWithoutDifference = $cutRows->filter(fn ($row) => (float) $row['difference'] === 0.0)->count();

        $cashFlow = $salesCashTotal - $expensesTotal;

        $cashByPeriod = $this->cashByPeriod($cutRows, $groupBy, $startDate, $endDate);

        $topDifferences = $cutRows
            ->filter(fn ($row) => $row['actual_cash'] !== null)
            ->sortByDesc(fn ($row) => abs($row['difference']))
            ->take(8)
            ->values();

        $recentAdjustments = CashRegisterAdjustment::with(['cashRegister', 'user'])
            ->whereIn('cash_register_id', $allCashRegisters->pluck('id'))
            ->latest()
            ->limit(12)
            ->get();

        $expensesByCategory = $this->cashExpensesByCategory($allCashRegisters, $expensesTotal);

        $cashStatusBreakdown = [
            [
                'label' => 'Abiertas',
                'count' => $openCuts,
            ],
            [
                'label' => 'Cerradas',
                'count' => $closedCuts,
            ],
        ];

        $executiveSummary = $this->makeCashExecutiveSummary(
            $totalCuts,
            $closedCuts,
            $expectedTotal,
            $actualTotal,
            $differenceTotal,
            $salesCashTotal,
            $expensesTotal,
            $adjustmentsCount
        );

        $periodLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');

        return array_merge($filters, [
            'cashRegisters' => $cashRegisters,
            'cutRows' => $cutRows,
            'paginatedCutRows' => $paginatedCutRows,

            'totalCuts' => $totalCuts,
            'openCuts' => $openCuts,
            'closedCuts' => $closedCuts,

            'openingTotal' => $openingTotal,
            'expectedTotal' => $expectedTotal,
            'actualTotal' => $actualTotal,
            'differenceTotal' => $differenceTotal,

            'salesCashTotal' => $salesCashTotal,
            'salesCardTotal' => $salesCardTotal,
            'salesPointsTotal' => $salesPointsTotal,
            'expensesTotal' => $expensesTotal,

            'completedOrdersCount' => $completedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'adjustmentsCount' => $adjustmentsCount,

            'cutsWithPositiveDifference' => $cutsWithPositiveDifference,
            'cutsWithNegativeDifference' => $cutsWithNegativeDifference,
            'cutsWithoutDifference' => $cutsWithoutDifference,

            'cashFlow' => $cashFlow,
            'cashByPeriod' => $cashByPeriod,
            'topDifferences' => $topDifferences,
            'recentAdjustments' => $recentAdjustments,
            'expensesByCategory' => $expensesByCategory,
            'cashStatusBreakdown' => $cashStatusBreakdown,
            'executiveSummary' => $executiveSummary,
            'periodLabel' => $periodLabel,
        ]);
    }

    private function cashRegisterReportRow(CashRegister $cashRegister): array
    {
        $start = $cashRegister->opened_at;
        $end = $cashRegister->closed_at ?? now();

        $orders = Order::where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->get();

        $completedOrders = $orders->where('status', 'completado');
        $cancelledOrders = $orders->where('status', 'cancelado');

        $salesCash = (float) $completedOrders->where('payment_method', 'efectivo')->sum('total');
        $salesCard = (float) $completedOrders->where('payment_method', 'tarjeta')->sum('total');
        $salesPoints = (float) $completedOrders->where('payment_method', 'puntos')->sum('total');

        $activeExpenses = $cashRegister->expenses->where('status', 'activo');
        $cancelledExpenses = $cashRegister->expenses->where('status', 'cancelado');

        $expensesTotal = (float) $activeExpenses->sum('amount');

        $expectedCash = (float) $cashRegister->opening_amount + $salesCash - $expensesTotal;

        $actualCash = $cashRegister->actual_amount !== null
            ? (float) $cashRegister->actual_amount
            : null;

        $difference = $actualCash !== null
            ? $actualCash - $expectedCash
            : 0;

        return [
            'id' => $cashRegister->id,
            'status' => $cashRegister->status,
            'opened_at' => $cashRegister->opened_at,
            'closed_at' => $cashRegister->closed_at,
            'opened_by' => $cashRegister->user->name ?? 'Usuario no disponible',
            'closed_by' => $cashRegister->closedBy->name ?? 'Sin cierre',
            'opening_amount' => (float) $cashRegister->opening_amount,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'sales_cash' => $salesCash,
            'sales_card' => $salesCard,
            'sales_points' => $salesPoints,
            'expenses_total' => $expensesTotal,
            'completed_orders_count' => $completedOrders->count(),
            'cancelled_orders_count' => $cancelledOrders->count(),
            'active_expenses_count' => $activeExpenses->count(),
            'cancelled_expenses_count' => $cancelledExpenses->count(),
            'adjustments_count' => $cashRegister->adjustments->count(),
            'notes' => $cashRegister->notes,
        ];
    }

    private function cashByPeriod($cutRows, string $groupBy, Carbon $startDate, Carbon $endDate)
    {
        $buckets = [];

        foreach ($this->periodBuckets($startDate, $endDate, $groupBy) as $key => $label) {
            $buckets[$key] = [
                'label' => $label,
                'expected' => 0,
                'actual' => 0,
                'difference' => 0,
                'cuts' => 0,
            ];
        }

        foreach ($cutRows as $row) {
            $openedAt = Carbon::parse($row['opened_at']);
            $key = $this->groupKey($openedAt, $groupBy);

            if (!isset($buckets[$key])) {
                $buckets[$key] = [
                    'label' => $this->groupLabel($openedAt, $groupBy),
                    'expected' => 0,
                    'actual' => 0,
                    'difference' => 0,
                    'cuts' => 0,
                ];
            }

            $buckets[$key]['expected'] += (float) $row['expected_cash'];
            $buckets[$key]['actual'] += (float) ($row['actual_cash'] ?? 0);
            $buckets[$key]['difference'] += (float) $row['difference'];
            $buckets[$key]['cuts']++;
        }

        return collect($buckets)->values();
    }

    private function cashExpensesByCategory($cashRegisters, float $expensesTotal)
    {
        return $cashRegisters
            ->flatMap(fn ($cashRegister) => $cashRegister->expenses)
            ->where('status', 'activo')
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

    private function makeCashExecutiveSummary(
        int $totalCuts,
        int $closedCuts,
        float $expectedTotal,
        float $actualTotal,
        float $differenceTotal,
        float $salesCashTotal,
        float $expensesTotal,
        int $adjustmentsCount
    ): string {
        $differenceText = $differenceTotal > 0
            ? 'sobrante'
            : ($differenceTotal < 0 ? 'faltante' : 'sin diferencia acumulada');

        return "Durante el periodo seleccionado se registraron {$totalCuts} corte(s) de caja, de los cuales {$closedCuts} están cerrados. " .
            "El efectivo esperado acumulado fue de $" . number_format($expectedTotal, 2) .
            " y el efectivo contado acumulado fue de $" . number_format($actualTotal, 2) .
            ". La diferencia acumulada fue de $" . number_format($differenceTotal, 2) .
            " ({$differenceText}). Las ventas en efectivo sumaron $" . number_format($salesCashTotal, 2) .
            " y los gastos activos sumaron $" . number_format($expensesTotal, 2) .
            ". Se registraron {$adjustmentsCount} corrección(es) administrativa(s) en los cortes consultados.";
    }

    private function applyCashSearch($query, string $search): void
    {
        $query->where(function ($subQuery) use ($search) {
            if (is_numeric($search)) {
                $subQuery->orWhere('id', (int) $search);
            }

            $subQuery->orWhere('notes', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('closedBy', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                });
        });
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