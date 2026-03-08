<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Extra;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            // Cargamos ventas y a quién las canceló (si están canceladas)
            $orders = Order::with(['user', 'canceller', 'items.product'])
                           ->where('created_at', '>=', $activeRegister->opened_at)
                           ->latest()
                           ->get();

            // Solo sumamos el dinero de los tickets "completados", ignoramos los cancelados
            $salesCash = $orders->where('status', 'completado')->where('payment_method', 'efectivo')->sum('total');
            $salesCard = $orders->where('status', 'completado')->where('payment_method', 'tarjeta')->sum('total');
            $totalSales = $salesCash + $salesCard;

            $expectedCash = $activeRegister->opening_amount + $salesCash - $totalExpenses;

            $stats = [
                'total_sales' => $totalSales,
                'sales_cash' => $salesCash,
                'sales_card' => $salesCard,
                'total_expenses' => $totalExpenses,
                'expected_cash' => $expectedCash,
                'orders_count' => $orders->where('status', 'completado')->count(),
            ];
        }

        $history = CashRegister::with('user')->where('status', 'cerrada')->latest()->take(10)->get();

        return view('cash_registers.index', compact('activeRegister', 'stats', 'expenses', 'history', 'orders'));
    }

    public function open(Request $request) { /* Mismo código que ya tienes */
        $request->validate(['opening_amount' => 'required|numeric|min:0']);
        if (CashRegister::where('status', 'abierta')->exists()) return back()->with('error', 'Ya existe una caja abierta.');
        CashRegister::create(['user_id' => Auth::id(),'opening_amount' => $request->opening_amount,'status' => 'abierta','opened_at' => Carbon::now()]);
        return back()->with('success', 'Caja abierta correctamente.');
    }

    public function storeExpense(Request $request) { /* Mismo código que ya tienes */
        $request->validate(['description' => 'required|string|max:255','amount' => 'required|numeric|min:1','category' => 'required|string']);
        $activeRegister = CashRegister::where('status', 'abierta')->first();
        if (!$activeRegister) return back()->with('error', 'Caja cerrada.');
        Expense::create(['user_id' => Auth::id(),'cash_register_id' => $activeRegister->id,'description' => $request->description,'amount' => $request->amount,'category' => $request->category]);
        return back()->with('success', 'Gasto registrado.');
    }

    public function close(Request $request) { /* Mismo código que ya tienes */
        $request->validate(['actual_amount' => 'required|numeric|min:0','expected_amount' => 'required|numeric','notes' => 'nullable|string']);
        $activeRegister = CashRegister::where('status', 'abierta')->first();
        if (!$activeRegister) return back()->with('error', 'No hay caja abierta.');
        $activeRegister->update(['expected_amount' => $request->expected_amount,'actual_amount' => $request->actual_amount,'notes' => $request->notes,'status' => 'cerrada','closed_at' => Carbon::now()]);
        return back()->with('success', '¡Corte de caja realizado con éxito!');
    }

    // --- CANCELACIÓN ---
    public function cancelOrder(Request $request, Order $order)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
        ]);

        if ($order->status === 'cancelado') {
            return back()->with('error', 'Este ticket ya había sido cancelado.');
        }

        DB::beginTransaction();

        try {
            // 1. Cambiamos el estado del ticket
            $order->update([
                'status' => 'cancelado',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            // 2. Devolvemos el inventario producto por producto
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantity = $item->quantity;

                // Devolver materia prima base
                if ($product->use_dynamic_stock) {
                    foreach ($product->ingredients as $ingredient) {
                        $needed = $ingredient->pivot->quantity * $quantity;
                        $ingredient->increment('current_quantity', $needed);
                        
                        // Registramos en la bitácora
                        InventoryMovement::create([
                            'ingredient_id' => $ingredient->id,
                            'user_id' => Auth::id(),
                            'type' => 'entrada',
                            'quantity' => $needed,
                            'reason' => "Devolución por Ticket Cancelado #{$order->id}",
                        ]);
                    }
                } else {
                    $product->increment('stock', $quantity);
                }

                // Devolver materia prima de los Extras
                if (!empty($item->extras)) {
                    foreach ($item->extras as $extraData) {
                        $extraModel = Extra::with('ingredients')->find($extraData['id']);
                        if ($extraModel) {
                            foreach ($extraModel->ingredients as $extraIng) {
                                $needed = $extraIng->pivot->quantity * $quantity;
                                $extraIng->increment('current_quantity', $needed);
                                
                                InventoryMovement::create([
                                    'ingredient_id' => $extraIng->id,
                                    'user_id' => Auth::id(),
                                    'type' => 'entrada',
                                    'quantity' => $needed,
                                    'reason' => "Devolución de Extra por Ticket Cancelado #{$order->id}",
                                ]);
                            }
                        }
                    }
                }

                // Actualizar stock visual del producto
                if ($product->use_dynamic_stock) {
                    $product->refresh();
                    $product->update(['stock' => $product->calculated_stock]);
                }
            }

            DB::commit();
            return back()->with('success', 'Ticket cancelado. El dinero se restó de la caja y los ingredientes regresaron al inventario.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar: ' . $e->getMessage());
        }
    }
}