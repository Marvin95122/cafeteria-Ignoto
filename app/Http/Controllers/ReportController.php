<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $paymentFilter = $request->input('payment_method', 'todos');
        $statusFilter = $request->input('status', 'todos');
        $search = trim($request->input('search', ''));

        $startDate = Carbon::parse($from)->startOfDay();
        $endDate = Carbon::parse($to)->endOfDay();

        if ($startDate->gt($endDate)) {
            $endDate = $startDate->copy()->endOfDay();
            $to = $startDate->toDateString();
        }

        /*
        |--------------------------------------------------------------------------
        | Consulta base para tickets de la tabla
        |--------------------------------------------------------------------------
        */
        $ordersQuery = Order::with(['user', 'customer', 'items.product'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($statusFilter !== 'todos') {
            $ordersQuery->where('status', $statusFilter);
        }

        if ($paymentFilter !== 'todos') {
            $ordersQuery->where('payment_method', $paymentFilter);
        }

        if ($search !== '') {
            $this->applyTicketSearch($ordersQuery, $search);
        }

        $orders = $ordersQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Métricas principales
        |--------------------------------------------------------------------------
        */
        $completedQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completado');

        $cancelledQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelado');

        if ($paymentFilter !== 'todos') {
            $completedQuery->where('payment_method', $paymentFilter);
            $cancelledQuery->where('payment_method', $paymentFilter);
        }

        $salesCash = (clone $completedQuery)
            ->where('payment_method', 'efectivo')
            ->sum('total');

        $salesCard = (clone $completedQuery)
            ->where('payment_method', 'tarjeta')
            ->sum('total');

        $salesPoints = (clone $completedQuery)
            ->where('payment_method', 'puntos')
            ->sum('total');

        $cashOrdersCount = (clone $completedQuery)
            ->where('payment_method', 'efectivo')
            ->count();

        $cardOrdersCount = (clone $completedQuery)
            ->where('payment_method', 'tarjeta')
            ->count();

        $pointsOrdersCount = (clone $completedQuery)
            ->where('payment_method', 'puntos')
            ->count();

        $totalOperatedSales = $salesCash + $salesCard + $salesPoints;
        $realIncome = $salesCash + $salesCard;

        $completedOrdersCount = (clone $completedQuery)->count();
        $averageTicket = $completedOrdersCount > 0
            ? $totalOperatedSales / $completedOrdersCount
            : 0;

        $cancelledOrdersCount = (clone $cancelledQuery)->count();
        $cancelledOrdersAmount = (clone $cancelledQuery)->sum('total');

        $cashReceived = (clone $completedQuery)
            ->where('payment_method', 'efectivo')
            ->sum('cash_received');

        $cashChange = (clone $completedQuery)
            ->where('payment_method', 'efectivo')
            ->sum('cash_change');

        $pointsEarned = (clone $completedQuery)->sum('points_earned');
        $pointsUsed = (clone $completedQuery)->sum('points_used');

        /*
        |--------------------------------------------------------------------------
        | Gastos del periodo
        |--------------------------------------------------------------------------
        */
        $expensesTotal = Expense::where('status', 'activo')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $expensesCount = Expense::where('status', 'activo')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $cancelledExpensesCount = Expense::where('status', 'cancelado')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $operatingProfit = $realIncome - $expensesTotal;

        /*
        |--------------------------------------------------------------------------
        | Productos más vendidos
        |--------------------------------------------------------------------------
        */
        $topProductsQuery = DB::table('order_items')
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
            $topProductsQuery->where('orders.payment_method', $paymentFilter);
        }

        $topProducts = $topProductsQuery
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Gráfica diaria
        |--------------------------------------------------------------------------
        */
        $dailyQuery = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total) as total_sales')
            )
            ->where('status', 'completado')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($paymentFilter !== 'todos') {
            $dailyQuery->where('payment_method', $paymentFilter);
        }

        $dailySalesRaw = $dailyQuery
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sale_date')
            ->get()
            ->pluck('total_sales', 'sale_date');

        $chartLabels = [];
        $chartSales = [];

        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();

            $chartLabels[] = $date->format('d/m');
            $chartSales[] = (float) ($dailySalesRaw[$key] ?? 0);
        }

        $paymentBreakdown = [
            [
                'label' => 'Efectivo',
                'amount' => $salesCash,
                'count' => $cashOrdersCount,
                'emoji' => '💵',
                'class' => 'text-green-700 bg-green-50 border-green-100',
            ],
            [
                'label' => 'Tarjeta',
                'amount' => $salesCard,
                'count' => $cardOrdersCount,
                'emoji' => '💳',
                'class' => 'text-blue-700 bg-blue-50 border-blue-100',
            ],
            [
                'label' => 'Puntos',
                'amount' => $salesPoints,
                'count' => $pointsOrdersCount,
                'emoji' => '👑',
                'class' => 'text-purple-700 bg-purple-50 border-purple-100',
            ],
        ];

        $periodLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');

        return view('reports.sales', compact(
            'from',
            'to',
            'paymentFilter',
            'statusFilter',
            'search',
            'orders',
            'salesCash',
            'salesCard',
            'salesPoints',
            'cashOrdersCount',
            'cardOrdersCount',
            'pointsOrdersCount',
            'totalOperatedSales',
            'realIncome',
            'completedOrdersCount',
            'averageTicket',
            'cancelledOrdersCount',
            'cancelledOrdersAmount',
            'cashReceived',
            'cashChange',
            'pointsEarned',
            'pointsUsed',
            'expensesTotal',
            'expensesCount',
            'cancelledExpensesCount',
            'operatingProfit',
            'topProducts',
            'chartLabels',
            'chartSales',
            'paymentBreakdown',
            'periodLabel'
        ));
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
}