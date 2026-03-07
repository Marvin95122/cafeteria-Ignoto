<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $ingredients = $query->paginate(10);
        return view('ingredients.index', compact('ingredients'));
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

        $data = $request->all();

        // --- CONVERSIÓN DE UNIDADES (Kg -> g, L -> ml) ---
        if ($data['unit'] == 'kg') {
            $data['current_quantity'] = $data['current_quantity'] * 1000;
            $data['unit'] = 'g';
        }
        
        if ($data['unit'] == 'l') {
            $data['current_quantity'] = $data['current_quantity'] * 1000;
            $data['unit'] = 'ml';
        }

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
        $ingredient->delete();
        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente eliminado del almacén.');
    }
}