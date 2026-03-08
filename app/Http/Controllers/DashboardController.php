<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. TARJETAS DE RESUMEN (Ingresos y Gastos)
        $salesToday = Order::where('status', 'completado')->whereDate('created_at', $today)->sum('total');
        $salesWeek = Order::where('status', 'completado')->where('created_at', '>=', $startOfWeek)->sum('total');
        $salesMonth = Order::where('status', 'completado')->where('created_at', '>=', $startOfMonth)->sum('total');

        $expensesToday = Expense::whereDate('created_at', $today)->sum('amount');
        $expensesMonth = Expense::where('created_at', '>=', $startOfMonth)->sum('amount');

        $netProfitMonth = $salesMonth - $expensesMonth;

        // 2. ALERTAS DE INVENTARIO BAJO
        // Productos directos
        $lowStockProducts = Product::where('use_dynamic_stock', false)
                                   ->where('stock', '<=', 10)
                                   ->get();

        // Filtro Inteligente para Materia Prima
        $lowStockIngredients = Ingredient::all()->filter(function ($ingredient) {
            // Convertimos la unidad a minúsculas para que no importe cómo la escribiste
            $unit = strtolower(trim($ingredient->unit_measure));
            $qty = $ingredient->current_quantity;
            
            // Si la unidad es gramos o mililitros, avisar si hay 1000 o menos (1 Kilo/Litro)
            if (in_array($unit, ['g', 'gr', 'ml', 'gramos', 'mililitros'])) {
                return $qty <= 1000;
            }
            // Si la unidad es Kilos o Litros, avisar si hay 1.5 o menos
            elseif (in_array($unit, ['kg', 'kilo', 'kilos', 'l', 'litro', 'litros'])) {
                return $qty <= 1.5;
            }
            // Para piezas, rebanadas, o cualquier otra cosa, avisar si hay 15 o menos
            else {
                return $qty <= 15;
            }
        });

        // Ingredientes con menos de 1000 gramos/ml (1 kilo/litro) o 10 piezas
        $lowStockIngredients = Ingredient::where('active', true)
                                         ->where(function($query) {
                                             $query->where('current_quantity', '<=', 1000)
                                                   ->whereIn('unit_measure', ['g', 'ml']);
                                         })->orWhere(function($query) {
                                             $query->where('current_quantity', '<=', 15)
                                                   ->where('unit_measure', 'pz');
                                         })->get();

        // 3. PRODUCTOS MÁS VENDIDOS DEL MES
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->where('orders.status', 'completado')
            ->where('orders.created_at', '>=', $startOfMonth)
            ->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 4. DATOS PARA LA GRÁFICA (Últimos 7 días)
        $chartDates = [];
        $chartSales = [];
        $chartExpenses = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartDates[] = $date->format('d M'); // Ej: "08 Mar"
            
            $chartSales[] = Order::where('status', 'completado')->whereDate('created_at', $date)->sum('total');
            $chartExpenses[] = Expense::whereDate('created_at', $date)->sum('amount');
        }

        return view('dashboard', compact(
            'salesToday', 'salesWeek', 'salesMonth', 'expensesToday', 'expensesMonth', 'netProfitMonth',
            'lowStockProducts', 'lowStockIngredients', 'topProducts',
            'chartDates', 'chartSales', 'chartExpenses'
        ));
    }
}