<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\CashRegister;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $ingredients = Ingredient::where('active', true)
            ->orderBy('name')
            ->get();

        $activeRegister = CashRegister::where('status', 'abierta')->first();

        $query = InventoryMovement::with(['ingredient', 'user'])
            ->latest();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('ingredient', function ($ingredientQuery) use ($request) {
                    $ingredientQuery->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhere('reason', 'like', '%' . $request->search . '%')
                ->orWhereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->filled('period')) {
            if ($request->period === 'today') {
                $query->whereDate('created_at', now()->toDateString());
            }

            if ($request->period === 'week') {
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
            }

            if ($request->period === 'month') {
                $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
            }
        }

        $perPage = $request->integer('per_page', 20);

        if (! in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }

        $movements = $query->paginate($perPage)->withQueryString();

        $totalMovements = InventoryMovement::count();
        $entryMovements = InventoryMovement::where('type', 'entrada')->count();
        $lossMovements = InventoryMovement::where('type', 'merma')->count();
        $saleMovements = InventoryMovement::where('type', 'venta')->count();

        return view('inventory_movements.index', compact(
            'ingredients',
            'activeRegister',
            'movements',
            'totalMovements',
            'entryMovements',
            'lossMovements',
            'saleMovements'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'type' => 'required|in:entrada,merma',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',

            'register_expense' => 'nullable|boolean',
            'expense_amount' => 'required_if:register_expense,1|nullable|numeric|min:0.01',
            'expense_description' => 'nullable|string|max:255',
        ]);

        if ($request->boolean('register_expense') && $request->type !== 'entrada') {
            return back()->with('error', 'Solo puedes registrar gasto en caja cuando el ajuste sea una entrada de inventario.');
        }

        $activeRegister = null;

        if ($request->boolean('register_expense')) {
            $activeRegister = CashRegister::where('status', 'abierta')->first();

            if (!$activeRegister) {
                return back()->with('error', 'No hay caja abierta. No se puede registrar el gasto en caja.');
            }
        }

        try {
            DB::beginTransaction();

            $ingredient = Ingredient::findOrFail($request->ingredient_id);

            InventoryMovement::create([
                'ingredient_id' => $ingredient->id,
                'user_id' => Auth::id(),
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
            ]);

            if ($request->type === 'entrada') {
                $ingredient->current_quantity += $request->quantity;
            } else {
                $ingredient->current_quantity -= $request->quantity;

                if ($ingredient->current_quantity < 0) {
                    $ingredient->current_quantity = 0;
                }
            }

            $ingredient->save();

            if ($request->boolean('register_expense') && $activeRegister) {
                Expense::create([
                    'user_id' => Auth::id(),
                    'cash_register_id' => $activeRegister->id,
                    'description' => $request->expense_description ?: 'Gasto por ajuste rápido de inventario: ' . $ingredient->name,
                    'category' => 'Insumos',
                    'amount' => $request->expense_amount,
                    'status' => 'activo',
                ]);
            }

            DB::commit();

            $mensaje = $request->type === 'entrada'
                ? 'Entrada registrada y stock sumado correctamente.'
                : 'Merma registrada y stock descontado correctamente.';

            if ($request->boolean('register_expense')) {
                $mensaje .= ' También se registró el gasto en caja.';
            }

            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Hubo un error al registrar el movimiento: ' . $e->getMessage());
        }
    }

    public function createBulk()
    {
        $ingredients = Ingredient::where('active', true)->orderBy('name')->get();
        $activeRegister = CashRegister::where('status', 'abierta')->first();
        
        return view('inventory_movements.create_bulk', compact('ingredients', 'activeRegister'));
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.input_unit' => 'required|string',
            'items.*.cost' => 'nullable|numeric|min:0',
            'register_expense' => 'nullable|boolean',
            'expense_description' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        
        try {
            $totalCost = 0;

            foreach ($request->items as $item) {
                $ingredient = Ingredient::find($item['ingredient_id']);
                
                // Limpiamos las unidades para evitar errores
                $baseUnit = strtolower(trim($ingredient->unit));
                $inputUnit = strtolower(trim($item['input_unit']));
                $finalQuantity = floatval($item['quantity']);

                $weightUnits = ['g', 'gr', 'gramos', 'kg', 'kilo', 'kilos'];
                $volumeUnits = ['ml', 'mililitros', 'l', 'litro', 'litros'];

                // Conversión Bidireccional
                if (in_array($baseUnit, $weightUnits) && in_array($inputUnit, $weightUnits)) {
                    $isBaseKg = in_array($baseUnit, ['kg', 'kilo', 'kilos']);
                    $isInputKg = in_array($inputUnit, ['kg', 'kilo', 'kilos']);

                    if ($isBaseKg && !$isInputKg) {
                        $finalQuantity = $finalQuantity / 1000;
                    } elseif (!$isBaseKg && $isInputKg) {
                        $finalQuantity = $finalQuantity * 1000;
                    }
                }
                
                elseif (in_array($baseUnit, $volumeUnits) && in_array($inputUnit, $volumeUnits)) {
                    $isBaseL = in_array($baseUnit, ['l', 'litro', 'litros']);
                    $isInputL = in_array($inputUnit, ['l', 'litro', 'litros']);

                    if ($isBaseL && !$isInputL) {
                        $finalQuantity = $finalQuantity / 1000;
                    } elseif (!$isBaseL && $isInputL) {
                        $finalQuantity = $finalQuantity * 1000;
                    }
                }

                // Guardamos el stock final ya convertido
                $ingredient->current_quantity += $finalQuantity;
                $ingredient->save();

                InventoryMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => Auth::id(),
                    'type' => 'entrada',
                    'quantity' => $finalQuantity,
                    'reason' => 'Carga Masiva de Insumos', 
                ]);

                $totalCost += floatval($item['cost'] ?? 0);
            }

            //Gasto de caja
            if ($request->register_expense && $totalCost > 0) {
                $activeRegister = CashRegister::where('status', 'abierta')->first();
                if ($activeRegister) {
                    Expense::create([
                        'user_id' => Auth::id(),
                        'cash_register_id' => $activeRegister->id,
                        'description' => $request->expense_description ?: 'Compra de insumos en Carga Masiva',
                        'category' => 'Insumos',
                        'amount' => $totalCost,
                    ]);
                    
                    $activeRegister->actual_amount -= $totalCost;
                    $activeRegister->save();
                }
            }

            DB::commit();
            return redirect()->route('inventory_movements.index')->with('success', '¡Compra ingresada correctamente al inventario!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hubo un error al guardar: ' . $e->getMessage());
        }
    }
}