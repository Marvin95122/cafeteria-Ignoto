<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\CashRegister;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Métricas financieras
        |--------------------------------------------------------------------------
        */
        $salesToday = Order::where('status', 'completado')
            ->whereDate('created_at', $today)
            ->sum('total');

        $salesWeek = Order::where('status', 'completado')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('total');

        $salesMonth = Order::where('status', 'completado')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total');

        $expensesToday = Expense::where('status', 'activo')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $expensesMonth = Expense::where('status', 'activo')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $netProfitMonth = $salesMonth - $expensesMonth;

        $ordersToday = Order::where('status', 'completado')
            ->whereDate('created_at', $today)
            ->count();

        $cancelledOrdersToday = Order::where('status', 'cancelado')
            ->whereDate('created_at', $today)
            ->count();

        $averageTicketToday = $ordersToday > 0
            ? $salesToday / $ordersToday
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Caja activa
        |--------------------------------------------------------------------------
        */
        $activeRegister = CashRegister::with('user')
            ->where('status', 'abierta')
            ->latest()
            ->first();

        $expectedCash = 0;

        if ($activeRegister) {
            $cashSales = Order::where('status', 'completado')
                ->where('payment_method', 'efectivo')
                ->where('created_at', '>=', $activeRegister->opened_at)
                ->sum('total');

            $activeExpenses = Expense::where('status', 'activo')
                ->where('cash_register_id', $activeRegister->id)
                ->sum('amount');

            $expectedCash = $activeRegister->opening_amount + $cashSales - $activeExpenses;
        }

        /*
        |--------------------------------------------------------------------------
        | Inventario bajo
        |--------------------------------------------------------------------------
        */
        $manualLowStockProducts = Product::with('category')
            ->where('active', true)
            ->where('use_dynamic_stock', false)
            ->where('stock', '<=', 10)
            ->get();

        $dynamicLowStockProducts = Product::with(['category', 'ingredients'])
            ->where('active', true)
            ->where('use_dynamic_stock', true)
            ->get()
            ->filter(function ($product) {
                return $product->calculated_stock <= 10;
            });

        $lowStockProducts = $manualLowStockProducts
            ->merge($dynamicLowStockProducts)
            ->sortBy('calculated_stock')
            ->take(8);

        $lowStockIngredients = Ingredient::where('active', true)
            ->get()
            ->filter(function ($ingredient) {
                $unit = strtolower(trim($ingredient->unit));
                $qty = (float) $ingredient->current_quantity;

                if (in_array($unit, ['g', 'gr', 'gramos', 'ml', 'mililitros'])) {
                    return $qty <= 1000;
                }

                if (in_array($unit, ['kg', 'kilo', 'kilos', 'l', 'litro', 'litros'])) {
                    return $qty <= 1.5;
                }

                return $qty <= 15;
            })
            ->take(8);

        $totalInventoryAlerts = $lowStockProducts->count() + $lowStockIngredients->count();

        /*
        |--------------------------------------------------------------------------
        | Productos más vendidos
        |--------------------------------------------------------------------------
        */
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->where('orders.status', 'completado')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Tickets del día
        |--------------------------------------------------------------------------
        */
        $todayOrders = Order::with(['user', 'customer'])
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $todayTicketsCount = $todayOrders->count();

        /*
        |--------------------------------------------------------------------------
        | Clientes VIP
        |--------------------------------------------------------------------------
        */
        $totalCustomers = Customer::count();
        $totalVipPoints = Customer::sum('points');

        /*
        |--------------------------------------------------------------------------
        | Gráfica últimos 7 días
        |--------------------------------------------------------------------------
        */
        $chartDates = [];
        $chartSales = [];
        $chartExpenses = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $chartDates[] = $date->format('d M');

            $chartSales[] = Order::where('status', 'completado')
                ->whereDate('created_at', $date)
                ->sum('total');

            $chartExpenses[] = Expense::where('status', 'activo')
                ->whereDate('created_at', $date)
                ->sum('amount');
        }

        return view('dashboard', compact(
            'salesToday',
            'salesWeek',
            'salesMonth',
            'expensesToday',
            'expensesMonth',
            'netProfitMonth',
            'ordersToday',
            'cancelledOrdersToday',
            'averageTicketToday',
            'activeRegister',
            'expectedCash',
            'lowStockProducts',
            'lowStockIngredients',
            'totalInventoryAlerts',
            'topProducts',
            'todayOrders',
            'todayTicketsCount',
            'totalCustomers',
            'totalVipPoints',
            'chartDates',
            'chartSales',
            'chartExpenses'
        ));
    }
}