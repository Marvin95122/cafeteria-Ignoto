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

        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'image' => $imagePath,
            'active' => $request->has('active'),
            'use_dynamic_stock' => $request->has('use_dynamic_stock'),
        ]);

        if ($request->has('extras')) {
            foreach ($request->extras as $extraData) {
                if (!empty($extraData['name']) && isset($extraData['price'])) {
                    $extra = Extra::create([
                        'name' => $extraData['name'],
                        'price' => $extraData['price'],
                        'active' => true,
                    ]);
                    
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

        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingData) {
                if (!empty($ingData['id']) && !empty($ingData['quantity'])) {
                    $product->ingredients()->attach($ingData['id'], [
                        'quantity' => $ingData['quantity']
                    ]);
                }
            }
        }

        if ($product->use_dynamic_stock) {
            $product->refresh(); 
            $realStock = $product->calculated_stock; 
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

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'active' => $request->has('active'),
            'use_dynamic_stock' => $request->input('use_dynamic_stock') == '1',
        ]);

        $idsToKeep = [];

        // A) ACTUALIZAR EXTRAS EXISTENTES
        if ($request->has('extras')) {
            foreach ($request->extras as $id => $data) {
                $extra = Extra::find($id);
                if ($extra) {
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

        // B) NUEVO: GUARDAR EXTRAS NUEVOS AL EDITAR (El bug que detectaste)
        if ($request->has('new_extras')) {
            foreach ($request->new_extras as $newExtra) {
                if (!empty($newExtra['name']) && isset($newExtra['price'])) {
                    $extra = Extra::create([
                        'name' => $newExtra['name'],
                        'price' => $newExtra['price'],
                        'active' => true,
                    ]);
                    
                    $product->extras()->attach($extra->id, [
                        'price' => $newExtra['price'],
                        'active' => true
                    ]);

                    if (!empty($newExtra['ingredient_id']) && !empty($newExtra['ingredient_qty'])) {
                        $extra->ingredients()->attach($newExtra['ingredient_id'], [
                            'quantity' => $newExtra['ingredient_qty']
                        ]);
                    }
                    
                    $idsToKeep[] = $extra->id;
                }
            }
        }

        $currentExtraIds = $product->extras()->pluck('extras.id')->toArray();
        $idsToDelete = array_diff($currentExtraIds, $idsToKeep);
        
        if (!empty($idsToDelete)) {
            Extra::whereIn('id', $idsToDelete)->delete();
        }

        $ingredientsToSync = [];
        if ($request->has('ingredients')) {
            foreach ($request->ingredients as $ingData) {
                if (!empty($ingData['id']) && !empty($ingData['quantity'])) {
                    $ingredientsToSync[$ingData['id']] = ['quantity' => $ingData['quantity']];
                }
            }
        }
        $product->ingredients()->sync($ingredientsToSync);

        if ($product->use_dynamic_stock) {
            $product->refresh();
            $realStock = $product->calculated_stock;
            $product->update(['stock' => $realStock]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto y extras actualizados correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $extraIds = $product->extras()->pluck('extras.id');
        Extra::whereIn('id', $extraIds)->delete();

        $product->ingredients()->detach();
        $product->delete();
        
        return redirect()->route('products.index')
            ->with('success', 'Producto y su historial de extras eliminados correctamente');
    }
}