@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('ingredients.index') }}" class="text-stone-400 hover:text-amber-700 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="font-serif text-2xl font-bold text-stone-800">Editar Inventario</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-stone-100 p-8">
        <form action="{{ route('ingredients.update', $ingredient) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-sm font-bold text-stone-600 mb-2">Nombre del Insumo</label>
                <input type="text" name="name" value="{{ $ingredient->name }}" class="w-full rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500" required>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-stone-600 mb-2">Cantidad Actual</label>
                    <input type="number" step="0.01" name="current_quantity" value="{{ $ingredient->current_quantity }}" class="w-full rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-stone-600 mb-2">Unidad de Medida</label>
                    <select name="unit" class="w-full rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500">
                        <option value="g" {{ $ingredient->unit == 'g' ? 'selected' : '' }}>Gramos (g)</option>
                        <option value="ml" {{ $ingredient->unit == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="kg" {{ $ingredient->unit == 'kg' ? 'selected' : '' }}>Kilogramos (kg)</option>
                        <option value="l" {{ $ingredient->unit == 'l' ? 'selected' : '' }}>Litros (L)</option>
                        <option value="pza" {{ $ingredient->unit == 'pza' ? 'selected' : '' }}>Piezas (pza)</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-amber-600 text-white font-bold py-3 rounded-lg hover:bg-amber-700 transition shadow-md">
                    Actualizar Inventario
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-stone-100">
            <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST" onsubmit="return confirm('¿Eliminar este ingrediente? Si algún producto lo usa en su receta, podría causar errores.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 text-sm font-bold hover:underline">
                    Eliminar este insumo permanentemente
                </button>
            </form>
        </div>
    </div>
</div>
@endsection