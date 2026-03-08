<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Extra;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; 

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'ingredients'])->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('active', true)->get();
        $ingredients = Ingredient::where('active', true)->orderBy('name')->get();
        return view('products.create', compact('categories', 'ingredients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'extras' => 'nullable|array',
            'ingredients' => 'nullable|array',
            'ingredients.*.quantity' => 'numeric|min:0.01',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 1. Crear Producto
        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'image' => $imagePath,
            'active' => $request->has('active'),
            'use_dynamic_stock' => $request->has('use_dynamic_stock'),
        ]);

        // 2. Guardar Extras
        if ($request->has('extras')) {
            foreach ($request->extras as $extraData) {
                if (!empty($extraData['name']) && isset($extraData['price'])) {
                    $extra = Extra::create([
                        'name' => $extraData['name'],
                        'price' => $extraData['price'],
                        'active' => true,
                    ]);
                    
                    // Asociar al producto
                    $product->extras()->attach($extra->id, [
                        'price' => $extraData['price'],
                        'active' => true
                    ]);

                    if (!empty($extraData['ingredient_id']) && !empty($extraData['ingredient_qty'])) {
                        $extra->ingredients()->attach($extraData['ingredient_id'], [
                            'quantity' => $extraData['ingredient_qty']
                        ]);
                    }
                }
            }
        }

        // 3. Ingredientes
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingData) {
                if (!empty($ingData['id']) && !empty($ingData['quantity'])) {
                    $product->ingredients()->attach($ingData['id'], [
                        'quantity' => $ingData['quantity']
                    ]);
                }
            }
        }

        // Si es dinámico, calculamos el stock real y lo guardamos en la BD para que coincida
        if ($product->use_dynamic_stock) {
            // Recargamos los ingredientes recién guardados
            $product->refresh(); 
            // Usamos el accesor del modelo para obtener el cálculo
            $realStock = $product->calculated_stock; 
            // Actualizamos la columna 'stock' en la base de datos
            $product->update(['stock' => $realStock]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('active', true)->get();
        $ingredients = Ingredient::where('active', true)->orderBy('name')->get();
        $product->load(['extras', 'ingredients']); 
        return view('products.edit', compact('product', 'categories', 'ingredients'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'extras' => 'nullable|array',
            'new_extras' => 'nullable|array',
            'ingredients' => 'nullable|array',
            'ingredients.*.quantity' => 'numeric|min:0.01',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        // 1. Actualizar Datos Básicos
        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'active' => $request->has('active'),
            'use_dynamic_stock' => $request->input('use_dynamic_stock') == '1',
        ]);

        // 2. Extras
        // Crear Extras Nuevos
        $idsToKeep = [];
        if ($request->has('extras')) {
            foreach ($request->extras as $id => $data) {
                $extra = Extra::find($id);
                if ($extra) {
                    //Actualizar datos básicos
                    $extra->update([
                        'name' => $data['name'], 
                        'price' => $data['price'], 
                        'active' => isset($data['active'])
                    ]);
                    
                    $product->extras()->updateExistingPivot($id, [
                        'price' => $data['price'], 
                        'active' => isset($data['active'])
                    ]);

                    if (!empty($data['ingredient_id']) && !empty($data['ingredient_qty'])) {
                        $extra->ingredients()->sync([
                            $data['ingredient_id'] => ['quantity' => $data['ingredient_qty']]
                        ]);
                    } else {
                        $extra->ingredients()->detach();
                    }

                    $idsToKeep[] = $id;
                }
            }
        }

        // 3. Ingredientes
        $ingredientsToSync = [];
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingData) {
                if (!empty($ingData['id']) && !empty($ingData['quantity'])) {
                    $ingredientsToSync[$ingData['id']] = ['quantity' => $ingData['quantity']];
                }
            }
        }
        $product->ingredients()->sync($ingredientsToSync);

        //SINCRONIZACIÓN 
        // Volvemos a sincronizar el stock de la BD con el cálculo real
        if ($product->use_dynamic_stock) {
            $product->refresh();
            $realStock = $product->calculated_stock;
            $product->update(['stock' => $realStock]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->extras()->detach();
        $product->ingredients()->detach();
        $product->delete();
        
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente');
    }
}