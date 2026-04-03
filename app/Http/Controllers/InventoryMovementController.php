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
    public function index()
    {
        $ingredients = Ingredient::where('active', true)->orderBy('name')->get();
        $movements = InventoryMovement::with(['ingredient', 'user'])
                        ->latest()
                        ->get();

        return view('inventory_movements.index', compact('ingredients', 'movements'));
    }

    public function store(Request $request)
    {
        // 1. Validamos los datos
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'type' => 'required|in:entrada,merma',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // 2. Guardamos el movimiento en la bitacora
            InventoryMovement::create([
                'ingredient_id' => $request->ingredient_id,
                'user_id' => Auth::id(),
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
            ]);

            // 3. Actualizamos la cantidad fisica del ingrediente
            $ingredient = Ingredient::findOrFail($request->ingredient_id);
            
            if ($request->type === 'entrada') {
                $ingredient->current_quantity += $request->quantity;
            } else {
                $ingredient->current_quantity -= $request->quantity;
                if ($ingredient->current_quantity < 0) {
                    $ingredient->current_quantity = 0;
                }
            }
            
            $ingredient->save();

            DB::commit();

            $mensaje = $request->type === 'entrada' ? 'Entrada registrada y stock sumado.' : 'Merma registrada y stock descontado.';
            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Hubo un error al registrar el movimiento.');
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
                
                $baseUnit = strtolower(trim($ingredient->unit_measure));
                $inputUnit = strtolower(trim($item['input_unit']));
                $finalQuantity = floatval($item['quantity']);

                $weightUnits = ['g', 'gr', 'gramos', 'kg', 'kilo', 'kilos'];
                $volumeUnits = ['ml', 'mililitros', 'l', 'litro', 'litros'];

                //conversión Bidireccional
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

            // Gasto de caja
            if ($request->register_expense && $totalCost > 0) {
                $activeRegister = CashRegister::where('status', 'abierta')->first();
                if ($activeRegister) {
                    Expense::create([
                        'cash_register_id' => $activeRegister->id,
                        'description' => $request->expense_description ?: 'Compra de insumos en Carga Masiva',
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