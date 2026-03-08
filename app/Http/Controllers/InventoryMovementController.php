<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index()
    {
        // Traemos los ingredientes activos para que aparezcan en la lista desplegable
        $ingredients = Ingredient::where('active', true)->orderBy('name')->get();
        
        // Traemos el historial de movimientos de lo mas nuevo a lo mas viejo
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
            DB::beginTransaction(); // Iniciamos transacción segura

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
                // Evitamos que el stock quede en numeros negativos si hubo un error de captura
                if ($ingredient->current_quantity < 0) {
                    $ingredient->current_quantity = 0;
                }
            }
            
            $ingredient->save();

            DB::commit(); // Confirmamos los cambios en la base de datos

            $mensaje = $request->type === 'entrada' ? 'Entrada registrada y stock sumado.' : 'Merma registrada y stock descontado.';
            return back()->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla, cancelamos
            return back()->with('error', 'Hubo un error al registrar el movimiento.');
        }
    }
}