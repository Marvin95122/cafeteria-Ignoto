<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::withCount(['products', 'inventoryMovements'])
            ->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        $ingredients = $query->paginate(10)->withQueryString();

        $totalIngredients = Ingredient::count();
        $activeIngredients = Ingredient::where('active', true)->count();
        $inactiveIngredients = Ingredient::where('active', false)->count();
        $lowStockIngredients = Ingredient::where('active', true)
            ->where('current_quantity', '<', 500)
            ->count();

        return view('ingredients.index', compact(
            'ingredients',
            'totalIngredients',
            'activeIngredients',
            'inactiveIngredients',
            'lowStockIngredients'
        ));
    }

    public function create()
    {
        return view('ingredients.create');
    }

    public function store(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'unit' => 'required|string|in:g,ml,kg,l,pza',
        'current_quantity' => 'required|numeric|min:0',
    ]);

    $data = $request->all();

    if ($data['unit'] == 'kg') {
        $data['current_quantity'] = $data['current_quantity'] * 1000;
        $data['unit'] = 'g';
    }
    
    if ($data['unit'] == 'l') {
        $data['current_quantity'] = $data['current_quantity'] * 1000;
        $data['unit'] = 'ml';
    }

    Ingredient::create($data);

    return redirect()->route('ingredients.index')
        ->with('success', 'Ingrediente registrado correctamente (Convertido a unidad base).');
    }

    public function edit(Ingredient $ingredient)
    {
        return view('ingredients.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|in:g,ml,kg,l,pza',
            'current_quantity' => 'required|numeric|min:0',
        ]);

        $data = $request->only(['name', 'unit', 'current_quantity']);

        if ($data['unit'] == 'kg') {
            $data['current_quantity'] = $data['current_quantity'] * 1000;
            $data['unit'] = 'g';
        }
        
        if ($data['unit'] == 'l') {
            $data['current_quantity'] = $data['current_quantity'] * 1000;
            $data['unit'] = 'ml';
        }

        $data['active'] = $request->has('active');

        $ingredient->update($data);

        foreach ($ingredient->products as $product) {
            if ($product->use_dynamic_stock) {
                $nuevoStock = $product->calculated_stock;

                $product->update([
                    'stock' => $nuevoStock
                ]);
            }
        }

        return redirect()->route('ingredients.index')
            ->with('success', 'Inventario actualizado y productos recalculados correctamente.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->update([
            'active' => false,
        ]);

        return redirect()
            ->route('ingredients.index')
            ->with('success', 'Ingrediente desactivado correctamente. Su historial y relaciones se conservaron.');
    }

    public function forceDelete(Ingredient $ingredient)
    {
        if ($ingredient->products()->exists()) {
            return redirect()
                ->route('ingredients.index')
                ->with('error', 'No puedes eliminar definitivamente este ingrediente porque está asociado a uno o más productos.');
        }

        if ($ingredient->extras()->exists()) {
            return redirect()
                ->route('ingredients.index')
                ->with('error', 'No puedes eliminar definitivamente este ingrediente porque está asociado a uno o más extras.');
        }

        if ($ingredient->inventoryMovements()->exists()) {
            return redirect()
                ->route('ingredients.index')
                ->with('error', 'No puedes eliminar definitivamente este ingrediente porque tiene movimientos de inventario registrados.');
        }

        $ingredient->delete();

        return redirect()
            ->route('ingredients.index')
            ->with('success', 'Ingrediente eliminado definitivamente porque no tenía productos, extras ni movimientos asociados.');
    }
    
}