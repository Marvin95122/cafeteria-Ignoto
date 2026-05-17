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

            <div class="mb-6 flex items-center gap-2 bg-stone-50 border border-stone-100 rounded-lg px-4 py-3">
                <input type="checkbox"
                    name="active"
                    value="1"
                    id="active"
                    {{ old('active', $ingredient->active) ? 'checked' : '' }}
                    class="rounded border-stone-300 text-amber-700 focus:ring-amber-600">

                <label for="active" class="text-sm font-bold text-stone-700">
                    Ingrediente activo
                </label>

                <span class="text-xs text-stone-400">
                    Si está inactivo, no se usará para nuevas configuraciones.
                </span>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-amber-600 text-white font-bold py-3 rounded-lg hover:bg-amber-700 transition shadow-md">
                    Actualizar Inventario
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-stone-100">
            <h3 class="text-sm font-bold text-stone-700 mb-3">
                Acciones de estado
            </h3>

            <div class="flex flex-col sm:flex-row gap-3">
                @if($ingredient->active)
                    <form action="{{ route('ingredients.destroy', $ingredient) }}"
                        method="POST"
                        onsubmit="return confirm('¿Desactivar este ingrediente? No se eliminará su historial ni sus relaciones.')">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-orange-100 text-orange-700 text-sm font-bold hover:bg-orange-200 transition">
                            Desactivar insumo
                        </button>
                    </form>
                @else
                    <div class="px-4 py-2 rounded-lg bg-stone-100 text-stone-500 text-sm font-bold">
                        Este insumo ya está desactivado
                    </div>
                @endif

                <form action="{{ route('ingredients.force-delete', $ingredient) }}"
                    method="POST"
                    onsubmit="return confirm('¿Eliminar definitivamente este ingrediente? Solo se permitirá si no tiene productos, extras ni movimientos asociados.')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-100 text-red-700 text-sm font-bold hover:bg-red-200 transition">
                        Eliminar definitivamente
                    </button>
                </form>
            </div>

            <p class="text-xs text-stone-400 mt-3">
                Recomendación: usa “Desactivar” cuando el insumo ya fue usado en productos, extras, inventario o ventas. La eliminación definitiva solo es segura cuando no existe historial.
            </p>
        </div>
    </div>
</div>
@endsection