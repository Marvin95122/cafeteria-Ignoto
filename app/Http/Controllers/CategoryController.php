<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
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
        if ($category->products()->count() > 0) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'No puedes eliminar una categoría que tiene productos asociados. Puedes desactivarla en lugar de eliminarla.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
