@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('ingredients.index') }}"
               class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition"
               title="Volver a materia prima">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>

            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-2">
                    📦 Nuevo insumo
                </div>

                <h1 class="font-serif text-3xl font-bold text-amber-900">
                    Registrar Materia Prima
                </h1>

                <p class="text-stone-500 mt-1">
                    Agrega un insumo al almacén para controlar existencias, recetas y movimientos de inventario.
                </p>
            </div>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-sm text-stone-600 max-w-md">
            <span class="font-bold text-amber-800">Nota:</span>
            si registras kilogramos o litros, el sistema los convertirá automáticamente a gramos o mililitros.
        </div>
    </div>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r p-4 shadow-sm">
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

        <div class="px-6 py-5 border-b border-stone-100 bg-amber-50">
            <h2 class="font-bold text-amber-900 text-lg flex items-center gap-2">
                🧾 Información del insumo
            </h2>
            <p class="text-sm text-amber-700 mt-1">
                Define el nombre, cantidad inicial y unidad de medida base para el control de inventario.
            </p>
        </div>

        <form action="{{ route('ingredients.store') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">
                    Nombre del insumo
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                       placeholder="Ej. Grano de café, leche entera, azúcar, chocolate..."
                       required>

                <p class="text-xs text-stone-400 mt-1">
                    Usa nombres claros para identificar fácilmente el insumo en recetas e inventario.
                </p>
            </div>

            {{-- Cantidad y unidad --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">
                        Cantidad inicial
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="current_quantity"
                           value="{{ old('current_quantity') }}"
                           class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                           placeholder="0.00"
                           required>

                    <p class="text-xs text-stone-400 mt-1">
                        Esta cantidad será el stock inicial registrado en almacén.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">
                        Unidad de medida
                    </label>

                    <select name="unit"
                            class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                            required>
                        <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Gramos (g)</option>
                        <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogramos (kg)</option>
                        <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>Litros (L)</option>
                        <option value="pza" {{ old('unit') == 'pza' ? 'selected' : '' }}>Piezas (pza)</option>
                    </select>

                    <p class="text-xs text-stone-400 mt-1">
                        Kg se guardará como g y L se guardará como ml para mantener cálculos uniformes.
                    </p>
                </div>
            </div>

            {{-- Ayuda visual --}}
            <div class="bg-stone-50 border border-stone-100 rounded-2xl p-4">
                <h3 class="font-bold text-stone-700 text-sm mb-2">
                    Ejemplos de registro
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-stone-500">
                    <div class="bg-white rounded-xl border border-stone-100 p-3">
                        <span class="font-bold text-stone-700 block">Leche</span>
                        5 litros → 5000 ml
                    </div>

                    <div class="bg-white rounded-xl border border-stone-100 p-3">
                        <span class="font-bold text-stone-700 block">Café</span>
                        2 kg → 2000 g
                    </div>

                    <div class="bg-white rounded-xl border border-stone-100 p-3">
                        <span class="font-bold text-stone-700 block">Vasos</span>
                        100 piezas → 100 pza
                    </div>
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('ingredients.index') }}"
                   class="inline-flex justify-center items-center px-5 py-3 rounded-xl border border-stone-200 text-stone-600 font-bold hover:bg-stone-50 transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex justify-center items-center px-6 py-3 rounded-xl bg-amber-800 text-white font-bold hover:bg-amber-900 transition shadow-md">
                    Guardar insumo
                </button>
            </div>
        </form>
    </div>
</div>

@endsection