<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashRegisterController extends Controller
{
    public function index()
    {
        $activeRegister = CashRegister::where('status', 'abierta')->latest()->first();

        $stats = [];
        $expenses = collect();
        $orders = collect();

        if ($activeRegister) {
            $expenses = Expense::where('cash_register_id', $activeRegister->id)->latest()->get();
            $totalExpenses = $expenses->sum('amount');

            //Traemos las ventas de hoy, incluyendo el nombre del cajero y los productos que se llevó el cliente
            $orders = Order::with(['user', 'items.product'])
                           ->where('created_at', '>=', $activeRegister->opened_at)
                           ->latest()
                           ->get();

            $salesCash = $orders->where('payment_method', 'efectivo')->sum('total');
            $salesCard = $orders->where('payment_method', 'tarjeta')->sum('total');
            $totalSales = $salesCash + $salesCard;

            $expectedCash = $activeRegister->opening_amount + $salesCash - $totalExpenses;

            $stats = [
                'total_sales' => $totalSales,
                'sales_cash' => $salesCash,
                'sales_card' => $salesCard,
                'total_expenses' => $totalExpenses,
                'expected_cash' => $expectedCash,
                'orders_count' => $orders->count(),
            ];
        }

        //Traemos las últimas 10 cajas cerradas y quién las cerró
        $history = CashRegister::with('user')->where('status', 'cerrada')->latest()->take(10)->get();

        return view('cash_registers.index', compact('activeRegister', 'stats', 'expenses', 'history', 'orders'));
    }

    public function open(Request $request)
    {
        $request->validate(['opening_amount' => 'required|numeric|min:0']);
        if (CashRegister::where('status', 'abierta')->exists()) {
            return back()->with('error', 'Ya existe una caja abierta.');
        }
        CashRegister::create([
            'user_id' => Auth::id(),
            'opening_amount' => $request->opening_amount,
            'status' => 'abierta',
            'opened_at' => Carbon::now(),
        ]);
        return back()->with('success', 'Caja abierta correctamente.');
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string'
        ]);
        $activeRegister = CashRegister::where('status', 'abierta')->first();
        if (!$activeRegister) return back()->with('error', 'Caja cerrada.');

        Expense::create([
            'user_id' => Auth::id(),
            'cash_register_id' => $activeRegister->id,
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
        ]);
        return back()->with('success', 'Gasto registrado.');
    }

    public function close(Request $request)
    {
        $request->validate([
            'actual_amount' => 'required|numeric|min:0',
            'expected_amount' => 'required|numeric',
            'notes' => 'nullable|string'
        ]);
        $activeRegister = CashRegister::where('status', 'abierta')->first();
        if (!$activeRegister) return back()->with('error', 'No hay caja abierta.');

        $activeRegister->update([
            'expected_amount' => $request->expected_amount,
            'actual_amount' => $request->actual_amount,
            'notes' => $request->notes,
            'status' => 'cerrada',
            'closed_at' => Carbon::now(),
        ]);
        return back()->with('success', '¡Corte de caja realizado con éxito!');
    }
}