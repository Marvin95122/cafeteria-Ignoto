@extends('layouts.admin')

@section('content')

<div class="w-full max-w-4xl mx-auto space-y-4 sm:space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-4">
        <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0">
            <a href="{{ route('ingredients.index') }}"
               class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition shrink-0"
               title="Volver a materia prima">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>

            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] sm:text-xs font-bold mb-2">
                    ✏️ Edición de insumo
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl font-bold text-amber-900 leading-tight">
                    Editar Materia Prima
                </h1>

                <p class="text-sm sm:text-base text-stone-500 mt-1 leading-snug">
                    Actualiza cantidades, unidad de medida y estado del insumo
                    <span class="font-bold text-stone-700 break-words">{{ $ingredient->name }}</span>.
                </p>
            </div>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-xs sm:text-sm text-stone-600 w-full lg:w-auto">
            <span class="font-bold text-amber-800">Estado actual:</span>

            @if($ingredient->active)
                <span class="text-green-700 font-bold">Activo</span>
            @else
                <span class="text-stone-500 font-bold">Inactivo</span>
            @endif
        </div>
    </div>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r p-4 shadow-sm text-sm">
            <p class="font-bold mb-2">Corrige los siguientes errores:</p>

            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULARIO --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">

        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-stone-100 bg-amber-50">
            <h2 class="font-bold text-amber-900 text-base sm:text-lg flex items-center gap-2">
                🧾 Información del insumo
            </h2>

            <p class="text-xs sm:text-sm text-amber-700 mt-1 leading-snug">
                Modifica el insumo con cuidado, ya que puede afectar productos calculados por receta.
            </p>
        </div>

        <form action="{{ route('ingredients.update', $ingredient) }}" method="POST" class="p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">
                    Nombre del insumo
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $ingredient->name) }}"
                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                       required>

                <p class="text-xs text-stone-400 mt-1 leading-snug">
                    Cambia el nombre solo si deseas actualizar cómo aparece en inventario y recetas.
                </p>
            </div>

            {{-- Cantidad y unidad --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">
                        Cantidad actual
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="current_quantity"
                           value="{{ old('current_quantity', $ingredient->current_quantity) }}"
                           class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                           required>

                    <p class="text-xs text-stone-400 mt-1 leading-snug">
                        Esta cantidad actualizará el stock disponible del insumo.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">
                        Unidad de medida
                    </label>

                    <select name="unit"
                            class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                            required>
                        <option value="g" {{ old('unit', $ingredient->unit) == 'g' ? 'selected' : '' }}>Gramos (g)</option>
                        <option value="ml" {{ old('unit', $ingredient->unit) == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="kg" {{ old('unit', $ingredient->unit) == 'kg' ? 'selected' : '' }}>Kilogramos (kg)</option>
                        <option value="l" {{ old('unit', $ingredient->unit) == 'l' ? 'selected' : '' }}>Litros (L)</option>
                        <option value="pza" {{ old('unit', $ingredient->unit) == 'pza' ? 'selected' : '' }}>Piezas (pza)</option>
                    </select>

                    <p class="text-xs text-stone-400 mt-1 leading-snug">
                        Si eliges kg o L, el sistema volverá a convertirlo a unidad base.
                    </p>
                </div>
            </div>

            {{-- Estado --}}
            <div class="flex items-start gap-3 rounded-2xl border p-4
                {{ old('active', $ingredient->active) ? 'bg-green-50 border-green-100' : 'bg-stone-50 border-stone-200' }}">

                <input type="checkbox"
                       name="active"
                       value="1"
                       id="active"
                       {{ old('active', $ingredient->active) ? 'checked' : '' }}
                       class="w-5 h-5 mt-1 rounded border-stone-300 text-green-600 focus:ring-green-500 shrink-0">

                <div>
                    <label for="active"
                           class="font-bold cursor-pointer text-sm sm:text-base {{ old('active', $ingredient->active) ? 'text-green-800' : 'text-stone-700' }}">
                        Insumo activo
                    </label>

                    <p class="text-xs mt-1 leading-snug {{ old('active', $ingredient->active) ? 'text-green-700' : 'text-stone-500' }}">
                        Si está inactivo, se conserva su historial, pero queda marcado como fuera de uso.
                    </p>
                </div>
            </div>

            {{-- Advertencia --}}
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-xs sm:text-sm text-blue-800">
                <p class="font-bold mb-1">
                    Recalculo automático
                </p>

                <p class="leading-snug">
                    Al modificar la cantidad de un insumo, los productos configurados con stock por receta podrán recalcular su disponibilidad.
                </p>
            </div>

            {{-- BOTONES --}}
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-5 sm:pt-6 border-t border-stone-100">
                <a href="{{ route('ingredients.index') }}"
                   class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-3 rounded-xl border border-stone-200 text-stone-600 font-bold hover:bg-stone-50 transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 rounded-xl bg-amber-800 text-white font-bold hover:bg-amber-900 transition shadow-md">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    {{-- ACCIONES DE ESTADO --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-stone-100 bg-stone-50">
            <h2 class="font-bold text-stone-800 text-base sm:text-lg flex items-center gap-2">
                ⚙️ Acciones avanzadas
            </h2>

            <p class="text-xs sm:text-sm text-stone-500 mt-1 leading-snug">
                Usa estas acciones con cuidado para mantener historial e integridad del inventario.
            </p>
        </div>

        <div class="p-4 sm:p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                {{-- Desactivar --}}
                <div class="border border-orange-100 bg-orange-50 rounded-2xl p-4 sm:p-5">
                    <h3 class="font-bold text-orange-800 mb-2">
                        Desactivar insumo
                    </h3>

                    <p class="text-xs sm:text-sm text-orange-700 mb-4 leading-snug">
                        Conserva historial, recetas y movimientos. Es la opción recomendada cuando el insumo ya fue utilizado.
                    </p>

                    @if($ingredient->active)
                        <form action="{{ route('ingredients.destroy', $ingredient) }}"
                              method="POST"
                              onsubmit="return confirm('¿Desactivar este ingrediente? No se eliminará su historial ni sus relaciones.')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="w-full px-4 py-3 rounded-xl bg-orange-100 text-orange-700 text-sm font-bold hover:bg-orange-200 transition">
                                Desactivar insumo
                            </button>
                        </form>
                    @else
                        <div class="w-full px-4 py-3 rounded-xl bg-stone-100 text-stone-500 text-sm font-bold text-center">
                            Este insumo ya está desactivado
                        </div>
                    @endif
                </div>

                {{-- Eliminar definitivamente --}}
                <div class="border border-red-100 bg-red-50 rounded-2xl p-4 sm:p-5">
                    <h3 class="font-bold text-red-800 mb-2">
                        Eliminar definitivamente
                    </h3>

                    <p class="text-xs sm:text-sm text-red-700 mb-4 leading-snug">
                        Solo se permitirá si no está asociado a productos, extras ni movimientos de inventario.
                    </p>

                    <form action="{{ route('ingredients.force-delete', $ingredient) }}"
                          method="POST"
                          onsubmit="return confirm('¿Eliminar definitivamente este ingrediente? Solo se permitirá si no tiene productos, extras ni movimientos asociados.')">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="w-full px-4 py-3 rounded-xl bg-red-100 text-red-700 text-sm font-bold hover:bg-red-200 transition">
                            Eliminar definitivamente
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-xs text-stone-400 mt-5 leading-snug">
                Recomendación: utiliza “Desactivar” para insumos con historial. La eliminación definitiva debe usarse solo para registros creados por error y sin relaciones.
            </p>
        </div>
    </div>
</div>

@endsection