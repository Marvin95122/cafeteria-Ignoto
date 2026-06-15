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
use Illuminate\Support\Facades\Hash;
use App\Models\CashRegisterAdjustment;
use Illuminate\Validation\ValidationException;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {
        $activeRegister = CashRegister::with('user')
            ->where('status', 'abierta')
            ->latest()
            ->first();

        $stats = [];
        $expenses = collect();
        $orders = collect();
        $salesPoints = 0;

        if ($activeRegister) {
            $expenses = Expense::with(['user', 'canceller'])
                ->where('cash_register_id', $activeRegister->id)
                ->latest()
                ->get();

            $activeExpenses = $expenses->where('status', 'activo');
            $cancelledExpenses = $expenses->where('status', 'cancelado');

            $totalExpenses = $activeExpenses->sum('amount');

            $orders = Order::with(['user', 'canceller', 'items.product', 'customer'])
                ->where('created_at', '>=', $activeRegister->opened_at)
                ->latest()
                ->get();

            $completedOrders = $orders->where('status', 'completado');
            $cancelledOrders = $orders->where('status', 'cancelado');

            $salesCash = $completedOrders->where('payment_method', 'efectivo')->sum('total');
            $salesCard = $completedOrders->where('payment_method', 'tarjeta')->sum('total');
            $salesPoints = $completedOrders->where('payment_method', 'puntos')->sum('total');

            $totalSales = $salesCash + $salesCard;
            $expectedCash = $activeRegister->opening_amount + $salesCash - $totalExpenses;

            $stats = [
                'total_sales' => $totalSales,
                'sales_cash' => $salesCash,
                'sales_card' => $salesCard,
                'sales_points' => $salesPoints,
                'total_expenses' => $totalExpenses,
                'expected_cash' => $expectedCash,
                'orders_count' => $completedOrders->count(),
                'cancelled_orders_count' => $cancelledOrders->count(),
                'expenses_count' => $activeExpenses->count(),
                'cancelled_expenses_count' => $cancelledExpenses->count(),
                'cash_flow' => $salesCash - $totalExpenses,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Historial completo con filtros
        |--------------------------------------------------------------------------
        */
        $statusFilter = $request->input('status', 'cerrada');
        $from = $request->input('from');
        $to = $request->input('to');

        $historyQuery = CashRegister::with(['user', 'closedBy'])
            ->orderByDesc('opened_at');

        if (in_array($statusFilter, ['abierta', 'cerrada'])) {
            $historyQuery->where('status', $statusFilter);
        }

        if ($from) {
            $historyQuery->whereDate('opened_at', '>=', $from);
        }

        if ($to) {
            $historyQuery->whereDate('opened_at', '<=', $to);
        }

        $history = $historyQuery
            ->paginate(10)
            ->withQueryString();

        return view('cash_registers.index', compact(
            'activeRegister',
            'stats',
            'expenses',
            'history',
            'orders',
            'salesPoints',
            'statusFilter',
            'from',
            'to'
        ));
    }

    public function show(CashRegister $cashRegister)
    {
        $cashRegister->load(['user', 'closedBy', 'adjustments.user']);

        $start = $cashRegister->opened_at;
        $end = $cashRegister->closed_at ?? now();

        $expenses = Expense::with(['user', 'canceller'])
            ->where('cash_register_id', $cashRegister->id)
            ->latest()
            ->get();

        $activeExpenses = $expenses->where('status', 'activo');
        $cancelledExpenses = $expenses->where('status', 'cancelado');

        $orders = Order::with(['user', 'canceller', 'items.product', 'customer'])
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->latest()
            ->get();

        $completedOrders = $orders->where('status', 'completado');
        $cancelledOrders = $orders->where('status', 'cancelado');

        $salesCash = $completedOrders->where('payment_method', 'efectivo')->sum('total');
        $salesCard = $completedOrders->where('payment_method', 'tarjeta')->sum('total');
        $salesPoints = $completedOrders->where('payment_method', 'puntos')->sum('total');

        $totalExpenses = $activeExpenses->sum('amount');
        $totalSales = $salesCash + $salesCard;

        $calculatedExpectedCash = $cashRegister->opening_amount + $salesCash - $totalExpenses;

        $expectedCash = $cashRegister->expected_amount ?? $calculatedExpectedCash;
        $actualCash = $cashRegister->actual_amount;
        $difference = $cashRegister->difference_amount;

        if ($difference === null && $actualCash !== null) {
            $difference = $actualCash - $expectedCash;
        }

        $stats = [
            'sales_cash' => $salesCash,
            'sales_card' => $salesCard,
            'sales_points' => $salesPoints,
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'expected_cash' => $expectedCash,
            'calculated_expected_cash' => $calculatedExpectedCash,
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'orders_count' => $completedOrders->count(),
            'cancelled_orders_count' => $cancelledOrders->count(),
            'expenses_count' => $activeExpenses->count(),
            'cancelled_expenses_count' => $cancelledExpenses->count(),
            'total_tickets' => $orders->count(),
        ];
        
        $adjustments = $cashRegister->adjustments()
        ->with('user')
        ->latest()
        ->get();

        return view('cash_registers.show', compact(
            'cashRegister',
            'expenses',
            'orders',
            'stats',
            'start',
            'end',
            'adjustments'
        ));
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0'
        ]);

        if (CashRegister::where('status', 'abierta')->exists()) {
            return back()->with('error', 'Ya existe una caja abierta.');
        }

        CashRegister::create([
            'user_id' => Auth::id(),
            'opening_amount' => $request->opening_amount,
            'status' => 'abierta',
            'opened_at' => Carbon::now()
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

        if (!$activeRegister) {
            return back()->with('error', 'Caja cerrada.');
        }

        Expense::create([
            'user_id' => Auth::id(),
            'cash_register_id' => $activeRegister->id,
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category
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

        if (!$activeRegister) {
            return back()->with('error', 'No hay caja abierta.');
        }

        $difference = $request->actual_amount - $request->expected_amount;

        $activeRegister->update([
            'expected_amount' => $request->expected_amount,
            'actual_amount' => $request->actual_amount,
            'difference_amount' => $difference,
            'closed_by' => Auth::id(),
            'notes' => $request->notes,
            'status' => 'cerrada',
            'closed_at' => Carbon::now()
        ]);

        return back()->with('success', '¡Corte de caja realizado con éxito! La auditoría del cierre fue registrada.');
    }

    // --- CANCELACIÓN INTELIGENTE ---
    public function cancelOrder(Request $request, Order $order)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'action_type' => 'required|in:devolver,merma'
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
                'cancellation_action' => $request->action_type,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            // 2. Procesamos el inventario según la decisión del usuario
            foreach ($order->items as $item) {
                $product = $item->product;
                $quantity = $item->quantity;

                // A. Materia Prima Base
                if ($product->use_dynamic_stock) {
                    foreach ($product->ingredients as $ingredient) {
                        $needed = $ingredient->pivot->quantity * $quantity;
                        
                        if ($request->action_type === 'devolver') {
                            $ingredient->increment('current_quantity', $needed);
                            InventoryMovement::create([
                                'ingredient_id' => $ingredient->id,
                                'user_id' => Auth::id(),
                                'type' => 'entrada',
                                'quantity' => $needed,
                                'reason' => "Devolución por Ticket Cancelado #{$order->id}",
                            ]);
                        } else {
                            InventoryMovement::create([
                                'ingredient_id' => $ingredient->id,
                                'user_id' => Auth::id(),
                                'type' => 'merma',
                                'quantity' => $needed,
                                'reason' => "Merma por Ticket Cancelado #{$order->id} ({$request->cancellation_reason})",
                            ]);
                        }
                    }
                } else {
                    if ($request->action_type === 'devolver') {
                        $product->increment('stock', $quantity);
                    }
                }

                // B. Materia Prima de los Extras
                if (!empty($item->extras)) {
                    foreach ($item->extras as $extraData) {
                        $extraModel = Extra::with('ingredients')->find($extraData['id']);
                        if ($extraModel) {
                            foreach ($extraModel->ingredients as $extraIng) {
                                $needed = $extraIng->pivot->quantity * $quantity;
                                
                                if ($request->action_type === 'devolver') {
                                    $extraIng->increment('current_quantity', $needed);
                                    InventoryMovement::create([
                                        'ingredient_id' => $extraIng->id,
                                        'user_id' => Auth::id(),
                                        'type' => 'entrada',
                                        'quantity' => $needed,
                                        'reason' => "Devolución Extra por Ticket Cancelado #{$order->id}",
                                    ]);
                                } else {
                                    InventoryMovement::create([
                                        'ingredient_id' => $extraIng->id,
                                        'user_id' => Auth::id(),
                                        'type' => 'merma',
                                        'quantity' => $needed,
                                        'reason' => "Merma Extra por Ticket Cancelado #{$order->id}",
                                    ]);
                                }
                            }
                        }
                    }
                }

                // C. Actualizar stock visual del producto (Solo si devolvimos)
                if ($product->use_dynamic_stock && $request->action_type === 'devolver') {
                    $product->refresh();
                    $product->update(['stock' => $product->calculated_stock]);
                }
            }

            DB::commit();
            
            $mensaje = $request->action_type === 'devolver' 
                ? 'Ticket cancelado correctamente. La venta dejó de contar en caja y los insumos regresaron al inventario.' 
                : 'Ticket cancelado correctamente. La venta dejó de contar en caja y los insumos fueron registrados como merma.';
            
            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar: ' . $e->getMessage());
        }
    }

    // SEGURIDAD: CANCELAR GASTOS CON CONTRASEÑA Y MOTIVO
    public function destroyExpense(Request $request, Expense $expense)
    {
        $request->validate([
            'admin_password' => 'required|string',
            'cancellation_reason' => 'required|string|max:255' // AHORA EXIGIMOS MOTIVO
        ]);

        $user = Auth::user();

        // Verificamos que sea Admin/Gerente y contraseña correcta
        if (!in_array($user->role, ['admin', 'gerente']) || !Hash::check($request->admin_password, $user->password)) {
            return back()->with('error', '⛔ Contraseña incorrecta o no tienes permisos suficientes.');
        }

        if ($expense->status === 'cancelado') {
            return back()->with('error', 'Este gasto ya estaba cancelado.');
        }

        DB::beginTransaction();

        try {
            $expense->update([
                'status' => 'cancelado',
                'cancelled_by' => $user->id,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
            ]);

            DB::commit();
            if ($expense->cashRegister) {
                $this->recalculateCashRegisterTotals($expense->cashRegister);
            }
            return back()->with('success', 'Gasto anulado. El dinero vuelve a estar disponible en caja.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hubo un error al anular el gasto: ' . $e->getMessage());
        }
    }

    public function adjust(Request $request, CashRegister $cashRegister)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            return back()->with('error', 'Solo un administrador puede realizar correcciones en cortes de caja.');
        }

        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'admin_password' => 'required|string',
            'adjustment_reason' => 'required|string|min:5|max:1000',
        ], [
            'admin_password.required' => 'Debes escribir tu contraseña de administrador para guardar la corrección.',
            'adjustment_reason.required' => 'Debes escribir el motivo de la corrección.',
            'adjustment_reason.min' => 'El motivo de la corrección debe tener al menos 5 caracteres.',
            'opening_amount.required' => 'El fondo inicial es obligatorio.',
        ]);

        if (!Hash::check($request->admin_password, $user->password)) {
            return back()
                ->withErrors(['admin_password' => 'La contraseña del administrador no es correcta. No se guardaron cambios.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $oldValues = [
                'opening_amount' => $cashRegister->opening_amount,
                'expected_amount' => $cashRegister->expected_amount,
                'actual_amount' => $cashRegister->actual_amount,
                'difference_amount' => $cashRegister->difference_amount,
                'notes' => $cashRegister->notes,
            ];

            $start = $cashRegister->opened_at;
            $end = $cashRegister->closed_at ?? now();

            $salesCash = Order::where('status', 'completado')
                ->where('payment_method', 'efectivo')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->sum('total');

            $totalExpenses = Expense::where('cash_register_id', $cashRegister->id)
                ->where('status', 'activo')
                ->sum('amount');

            $newOpeningAmount = (float) $request->opening_amount;
            $newExpectedAmount = $newOpeningAmount + $salesCash - $totalExpenses;

            $newActualAmount = $request->actual_amount !== null && $request->actual_amount !== ''
                ? (float) $request->actual_amount
                : null;

            $newDifferenceAmount = $newActualAmount !== null
                ? $newActualAmount - $newExpectedAmount
                : null;

            $newValues = [
                'opening_amount' => $newOpeningAmount,
                'expected_amount' => $newExpectedAmount,
                'actual_amount' => $newActualAmount,
                'difference_amount' => $newDifferenceAmount,
                'notes' => $request->notes,
            ];

            $labels = [
                'opening_amount' => 'Fondo inicial',
                'expected_amount' => 'Efectivo esperado',
                'actual_amount' => 'Efectivo contado',
                'difference_amount' => 'Diferencia',
                'notes' => 'Notas del cierre',
            ];

            $changes = 0;

            foreach ($newValues as $field => $newValue) {
                $oldValue = $oldValues[$field];

                $oldComparable = is_numeric($oldValue) ? number_format((float) $oldValue, 2, '.', '') : trim((string) $oldValue);
                $newComparable = is_numeric($newValue) ? number_format((float) $newValue, 2, '.', '') : trim((string) $newValue);

                if ($oldComparable !== $newComparable) {
                    CashRegisterAdjustment::create([
                        'cash_register_id' => $cashRegister->id,
                        'user_id' => $user->id,
                        'field_name' => $labels[$field] ?? $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'reason' => $request->adjustment_reason,
                    ]);

                    $changes++;
                }
            }

            if ($changes === 0) {
                DB::rollBack();

                return back()->with('info', 'No se detectaron cambios para guardar.');
            }

            $cashRegister->update($newValues);

            DB::commit();

            return redirect()
                ->route('cash_registers.show', $cashRegister)
                ->with('success', 'Corrección administrativa guardada correctamente. Los datos del corte fueron recalculados.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'No se pudo guardar la corrección: ' . $e->getMessage());
        }
    }

    private function validateAdminPassword(Request $request, string $reasonField = 'adjustment_reason'): void
    {
        $request->validate([
            'admin_password' => 'required|string',
            $reasonField => 'required|string|min:5|max:1000',
        ], [
            'admin_password.required' => 'Debes escribir tu contraseña de administrador.',
            $reasonField . '.required' => 'Debes escribir el motivo del cambio.',
            $reasonField . '.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            throw ValidationException::withMessages([
                'permission' => 'Solo un administrador puede realizar cambios en cortes de caja.',
            ]);
        }

        if (!Hash::check($request->admin_password, $user->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'La contraseña del administrador no es correcta.',
            ]);
        }
    }

    private function orderBelongsToCashRegister(CashRegister $cashRegister, Order $order): bool
    {
        $start = $cashRegister->opened_at;
        $end = $cashRegister->closed_at ?? now();

        return $order->created_at >= $start && $order->created_at <= $end;
    }

    private function recalculateCashRegisterTotals(CashRegister $cashRegister): void
    {
        $start = $cashRegister->opened_at;
        $end = $cashRegister->closed_at ?? now();

        $salesCash = Order::where('status', 'completado')
            ->where('payment_method', 'efectivo')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->sum('total');

        $totalExpenses = Expense::where('cash_register_id', $cashRegister->id)
            ->where('status', 'activo')
            ->sum('amount');

        $expectedAmount = $cashRegister->opening_amount + $salesCash - $totalExpenses;

        $differenceAmount = $cashRegister->actual_amount !== null
            ? $cashRegister->actual_amount - $expectedAmount
            : null;

        $cashRegister->update([
            'expected_amount' => $expectedAmount,
            'difference_amount' => $differenceAmount,
        ]);
    }

    private function returnOrderInventory(Order $order, string $reason): void
    {
        $order->loadMissing(['items.product.ingredients']);

        foreach ($order->items as $item) {
            $product = $item->product;
            $quantity = $item->quantity;

            if (!$product) {
                continue;
            }

            if ($product->use_dynamic_stock) {
                foreach ($product->ingredients as $ingredient) {
                    $needed = $ingredient->pivot->quantity * $quantity;

                    if ($needed > 0) {
                        $ingredient->increment('current_quantity', $needed);

                        InventoryMovement::create([
                            'ingredient_id' => $ingredient->id,
                            'user_id' => Auth::id(),
                            'type' => 'entrada',
                            'quantity' => $needed,
                            'reason' => $reason,
                        ]);
                    }
                }
            } else {
                $product->increment('stock', $quantity);
            }

            if (!empty($item->extras)) {
                foreach ($item->extras as $extraData) {
                    $extraModel = Extra::with('ingredients')->find($extraData['id'] ?? null);

                    if (!$extraModel) {
                        continue;
                    }

                    foreach ($extraModel->ingredients as $extraIng) {
                        $needed = $extraIng->pivot->quantity * $quantity;

                        if ($needed > 0) {
                            $extraIng->increment('current_quantity', $needed);

                            InventoryMovement::create([
                                'ingredient_id' => $extraIng->id,
                                'user_id' => Auth::id(),
                                'type' => 'entrada',
                                'quantity' => $needed,
                                'reason' => $reason,
                            ]);
                        }
                    }
                }
            }

            if ($product->use_dynamic_stock) {
                $product->refresh();

                if ($product->calculated_stock !== null) {
                    $product->update(['stock' => $product->calculated_stock]);
                }
            }
        }
    }

    private function consumeOrderInventoryAgain(Order $order, string $reason): void
    {
        $order->loadMissing(['items.product.ingredients']);

        foreach ($order->items as $item) {
            $product = $item->product;
            $quantity = $item->quantity;

            if (!$product) {
                continue;
            }

            if ($product->use_dynamic_stock) {
                foreach ($product->ingredients as $ingredient) {
                    $needed = $ingredient->pivot->quantity * $quantity;

                    if ($needed > 0 && $ingredient->current_quantity < $needed) {
                        throw new \Exception("No hay suficiente inventario de {$ingredient->name} para habilitar nuevamente el ticket.");
                    }
                }

                foreach ($product->ingredients as $ingredient) {
                    $needed = $ingredient->pivot->quantity * $quantity;

                    if ($needed > 0) {
                        $ingredient->decrement('current_quantity', $needed);

                        InventoryMovement::create([
                            'ingredient_id' => $ingredient->id,
                            'user_id' => Auth::id(),
                            'type' => 'venta',
                            'quantity' => $needed,
                            'reason' => $reason,
                        ]);
                    }
                }
            } else {
                if ($product->stock < $quantity) {
                    throw new \Exception("No hay stock suficiente de {$product->name} para habilitar nuevamente el ticket.");
                }

                $product->decrement('stock', $quantity);
            }

            if (!empty($item->extras)) {
                foreach ($item->extras as $extraData) {
                    $extraModel = Extra::with('ingredients')->find($extraData['id'] ?? null);

                    if (!$extraModel) {
                        continue;
                    }

                    foreach ($extraModel->ingredients as $extraIng) {
                        $needed = $extraIng->pivot->quantity * $quantity;

                        if ($needed > 0 && $extraIng->current_quantity < $needed) {
                            throw new \Exception("No hay suficiente inventario de {$extraIng->name} para habilitar nuevamente el extra {$extraModel->name}.");
                        }
                    }

                    foreach ($extraModel->ingredients as $extraIng) {
                        $needed = $extraIng->pivot->quantity * $quantity;

                        if ($needed > 0) {
                            $extraIng->decrement('current_quantity', $needed);

                            InventoryMovement::create([
                                'ingredient_id' => $extraIng->id,
                                'user_id' => Auth::id(),
                                'type' => 'venta',
                                'quantity' => $needed,
                                'reason' => $reason,
                            ]);
                        }
                    }
                }
            }

            if ($product->use_dynamic_stock) {
                $product->refresh();

                if ($product->calculated_stock !== null) {
                    $product->update(['stock' => $product->calculated_stock]);
                }
            }
        }
    }

    public function cancelOrderFromCut(Request $request, CashRegister $cashRegister, Order $order)
    {
        $request->validate([
            'action_type' => 'required|in:devolver,merma',
        ], [
            'action_type.required' => 'Debes indicar si los insumos se devuelven o se registran como merma.',
        ]);

        try {
            $this->validateAdminPassword($request, 'cancellation_reason');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if (!$this->orderBelongsToCashRegister($cashRegister, $order)) {
            return back()->with('error', 'Este ticket no pertenece al corte seleccionado.');
        }

        if ($order->status === 'cancelado') {
            return back()->with('error', 'Este ticket ya está cancelado.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $order->status;

            $order->update([
                'status' => 'cancelado',
                'cancellation_reason' => $request->cancellation_reason,
                'cancellation_action' => $request->action_type,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            if ($request->action_type === 'devolver') {
                $this->returnOrderInventory(
                    $order,
                    "Devolución administrativa por cancelación de Ticket #{$order->id}"
                );
            } else {
                $order->loadMissing(['items.product.ingredients']);

                foreach ($order->items as $item) {
                    $product = $item->product;

                    if (!$product || !$product->use_dynamic_stock) {
                        continue;
                    }

                    foreach ($product->ingredients as $ingredient) {
                        $needed = $ingredient->pivot->quantity * $item->quantity;

                        if ($needed > 0) {
                            InventoryMovement::create([
                                'ingredient_id' => $ingredient->id,
                                'user_id' => Auth::id(),
                                'type' => 'merma',
                                'quantity' => $needed,
                                'reason' => "Merma administrativa por cancelación de Ticket #{$order->id}: {$request->cancellation_reason}",
                            ]);
                        }
                    }
                }
            }

            $this->recalculateCashRegisterTotals($cashRegister);

            CashRegisterAdjustment::create([
                'cash_register_id' => $cashRegister->id,
                'user_id' => Auth::id(),
                'field_name' => 'Ticket cancelado',
                'old_value' => "Ticket #{$order->id} | Estado: {$oldStatus} | Total: $" . number_format($order->total, 2),
                'new_value' => "Cancelado | Acción: {$request->action_type}",
                'reason' => $request->cancellation_reason,
            ]);

            DB::commit();

            return redirect()
                ->route('cash_registers.show', $cashRegister)
                ->with('success', 'Ticket cancelado correctamente y corte recalculado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'No se pudo cancelar el ticket: ' . $e->getMessage());
        }
    }

    public function restoreOrderFromCut(Request $request, CashRegister $cashRegister, Order $order)
    {
        try {
            $this->validateAdminPassword($request, 'restore_reason');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if (!$this->orderBelongsToCashRegister($cashRegister, $order)) {
            return back()->with('error', 'Este ticket no pertenece al corte seleccionado.');
        }

        if ($order->status === 'completado') {
            return back()->with('error', 'Este ticket ya está habilitado.');
        }

        DB::beginTransaction();

        try {
            $oldValue = "Ticket #{$order->id} cancelado | Acción anterior: {$order->cancellation_action}";

            if ($order->cancellation_action === 'devolver') {
                $this->consumeOrderInventoryAgain(
                    $order,
                    "Reactivación administrativa de Ticket #{$order->id}"
                );
            }

            $order->update([
                'status' => 'completado',
                'cancellation_reason' => null,
                'cancellation_action' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
            ]);

            $this->recalculateCashRegisterTotals($cashRegister);

            CashRegisterAdjustment::create([
                'cash_register_id' => $cashRegister->id,
                'user_id' => Auth::id(),
                'field_name' => 'Ticket habilitado',
                'old_value' => $oldValue,
                'new_value' => "Ticket #{$order->id} completado nuevamente | Total: $" . number_format($order->total, 2),
                'reason' => $request->restore_reason,
            ]);

            DB::commit();

            return redirect()
                ->route('cash_registers.show', $cashRegister)
                ->with('success', 'Ticket habilitado correctamente y corte recalculado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'No se pudo habilitar el ticket: ' . $e->getMessage());
        }
    }

    public function cancelExpenseFromCut(Request $request, CashRegister $cashRegister, Expense $expense)
    {
        try {
            $this->validateAdminPassword($request, 'cancellation_reason');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if ($expense->cash_register_id !== $cashRegister->id) {
            return back()->with('error', 'Este gasto no pertenece al corte seleccionado.');
        }

        if ($expense->status === 'cancelado') {
            return back()->with('error', 'Este gasto ya está cancelado.');
        }

        DB::beginTransaction();

        try {
            $expense->update([
                'status' => 'cancelado',
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
            ]);

            $this->recalculateCashRegisterTotals($cashRegister);

            CashRegisterAdjustment::create([
                'cash_register_id' => $cashRegister->id,
                'user_id' => Auth::id(),
                'field_name' => 'Gasto cancelado',
                'old_value' => "Activo | {$expense->description} | $" . number_format($expense->amount, 2),
                'new_value' => "Cancelado",
                'reason' => $request->cancellation_reason,
            ]);

            DB::commit();

            return redirect()
                ->route('cash_registers.show', $cashRegister)
                ->with('success', 'Gasto cancelado correctamente y corte recalculado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'No se pudo cancelar el gasto: ' . $e->getMessage());
        }
    }

    public function restoreExpenseFromCut(Request $request, CashRegister $cashRegister, Expense $expense)
    {
        try {
            $this->validateAdminPassword($request, 'restore_reason');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if ($expense->cash_register_id !== $cashRegister->id) {
            return back()->with('error', 'Este gasto no pertenece al corte seleccionado.');
        }

        if ($expense->status === 'activo') {
            return back()->with('error', 'Este gasto ya está habilitado.');
        }

        DB::beginTransaction();

        try {
            $expense->update([
                'status' => 'activo',
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'cancelled_at' => null,
            ]);

            $this->recalculateCashRegisterTotals($cashRegister);

            CashRegisterAdjustment::create([
                'cash_register_id' => $cashRegister->id,
                'user_id' => Auth::id(),
                'field_name' => 'Gasto habilitado',
                'old_value' => "Cancelado | {$expense->description} | $" . number_format($expense->amount, 2),
                'new_value' => "Activo",
                'reason' => $request->restore_reason,
            ]);

            DB::commit();

            return redirect()
                ->route('cash_registers.show', $cashRegister)
                ->with('success', 'Gasto habilitado correctamente y corte recalculado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'No se pudo habilitar el gasto: ' . $e->getMessage());
        }
    }
}