<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products')->orderBy('name');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        $categories = $query->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
            'active' => true,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Categoría creada correctamente');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Categoría actualizada');
    }

    public function destroy(Category $category)
    {
        $category->update([
            'active' => false,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría desactivada correctamente. Los productos asociados se conservaron.');
    }

    public function forceDelete(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'No puedes eliminar definitivamente esta categoría porque tiene productos asociados. Puedes mantenerla desactivada.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría eliminada definitivamente porque no tenía productos asociados.');
    }
}
