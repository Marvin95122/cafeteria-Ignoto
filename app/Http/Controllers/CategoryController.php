<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products')
            ->orderBy('name');

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

        $perPage = $request->integer('per_page', 12);

        if (! in_array($perPage, [8, 12, 24, 48])) {
            $perPage = 12;
        }

        $categories = $query->paginate($perPage)->withQueryString();

        $totalCategories = Category::count();
        $activeCategories = Category::where('active', true)->count();
        $inactiveCategories = Category::where('active', false)->count();
        $categoriesWithProducts = Category::has('products')->count();

        return view('categories.index', compact(
            'categories',
            'totalCategories',
            'activeCategories',
            'inactiveCategories',
            'categoriesWithProducts'
        ));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
            'active' => $request->has('active'),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'active' => $request->has('active'),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
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
