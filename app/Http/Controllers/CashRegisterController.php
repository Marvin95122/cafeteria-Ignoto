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
    // Mostrar la pantalla principal de la Caja
    public function index()
    {
        //Buscamos si hay una caja abierta actualmente
        $activeRegister = CashRegister::where('status', 'abierta')->latest()->first();

        $stats = [];
        $expenses = collect();

        //si hay una caja abierta, calculamos todo lo que ha pasado en ese turno
        if ($activeRegister) {
            
            // Traemos todos los gastos de este turno
            $expenses = Expense::where('cash_register_id', $activeRegister->id)->latest()->get();
            $totalExpenses = $expenses->sum('amount');

            // Traemos todas las ventas desde que se abrió la caja hasta ahorita
            $orders = Order::where('created_at', '>=', $activeRegister->opened_at)->get();

            // Sumamos las ventas por método de pago
            $salesCash = $orders->where('payment_method', 'efectivo')->sum('total');
            $salesCard = $orders->where('payment_method', 'tarjeta')->sum('total');
            $totalSales = $salesCash + $salesCard;

            //¿Cuánto dinero físico debe haber en el cajón?
            // (Fondo Inicial) + (Ventas en Efectivo) - (Gastos que salieron de la caja)
            $expectedCash = $activeRegister->opening_amount + $salesCash - $totalExpenses;

            // Guardamos todo en un arreglo para mandarlo a la vista
            $stats = [
                'total_sales' => $totalSales,
                'sales_cash' => $salesCash,
                'sales_card' => $salesCard,
                'total_expenses' => $totalExpenses,
                'expected_cash' => $expectedCash,
                'orders_count' => $orders->count(),
            ];
        }

        // Traemos el historial de cajas pasadas (para que el gerente vea días anteriores)
        $history = CashRegister::where('status', 'cerrada')->latest()->take(10)->get();

        return view('cash_registers.index', compact('activeRegister', 'stats', 'expenses', 'history'));
    }

    // Abrir una nueva caja
    public function open(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0'
        ]);

        // Verificamos que no haya ya una caja abierta
        if (CashRegister::where('status', 'abierta')->exists()) {
            return back()->with('error', 'Ya existe una caja abierta. Ciérrala primero.');
        }

        CashRegister::create([
            'user_id' => Auth::id(),
            'opening_amount' => $request->opening_amount,
            'status' => 'abierta',
            'opened_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Caja abierta correctamente. ¡Buen turno!');
    }

    // Registrar un gasto (Ej: Comprar agua)
    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string'
        ]);

        $activeRegister = CashRegister::where('status', 'abierta')->first();

        if (!$activeRegister) {
            return back()->with('error', 'No puedes registrar gastos si la caja está cerrada.');
        }

        Expense::create([
            'user_id' => Auth::id(),
            'cash_register_id' => $activeRegister->id,
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
        ]);

        return back()->with('success', 'Gasto registrado. Se ha restado del total esperado en caja.');
    }

    // Cerrar la caja (Corte Final)
    public function close(Request $request)
    {
        $request->validate([
            'actual_amount' => 'required|numeric|min:0', // Lo que el gerente contó físicamente
            'expected_amount' => 'required|numeric',     // Lo que el sistema dice que debería haber
            'notes' => 'nullable|string'
        ]);

        $activeRegister = CashRegister::where('status', 'abierta')->first();

        if (!$activeRegister) {
            return back()->with('error', 'No hay ninguna caja abierta.');
        }

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