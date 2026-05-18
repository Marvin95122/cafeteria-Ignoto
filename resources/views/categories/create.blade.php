@extends('layouts.admin')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('categories.index') }}"
               class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition"
               title="Volver a categorías">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>

            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-2">
                    🗂️ Nueva sección
                </div>

                <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                    Nueva Categoría
                </h1>

                <p class="text-stone-500 mt-1">
                    Crea una sección para organizar productos dentro del menú y del punto de venta.
                </p>
            </div>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-sm text-stone-600 max-w-md">
            <span class="font-bold text-amber-800">Ejemplo:</span>
            Bebidas calientes, Bebidas frías, Frappes, Tisanas o Smoothies.
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
                🧾 Información de la categoría
            </h2>

            <p class="text-sm text-amber-700 mt-1">
                Define el nombre y estado inicial de la categoría.
            </p>
        </div>

        <form method="POST" action="{{ route('categories.store') }}" class="p-6 md:p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">
                    Nombre de la categoría
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 text-lg transition"
                       placeholder="Ej. Bebidas Calientes"
                       required
                       autofocus>

                <p class="text-xs text-stone-400 mt-1">
                    Este nombre será visible para organizar productos en administración y POS.
                </p>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border border-green-100 bg-green-50 p-4">
                <input type="checkbox"
                       id="active"
                       name="active"
                       value="1"
                       {{ old('active', true) ? 'checked' : '' }}
                       class="w-5 h-5 mt-1 rounded border-stone-300 text-green-600 focus:ring-green-500">

                <div>
                    <label for="active" class="font-bold text-green-800 cursor-pointer">
                        Categoría activa
                    </label>

                    <p class="text-xs text-green-700 mt-1">
                        Si está activa, podrá usarse para clasificar productos.
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-800">
                <p class="font-bold mb-1">Recomendación</p>
                <p>
                    Usa categorías claras y cortas para que el menú sea fácil de administrar.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-stone-100">
                <a href="{{ route('categories.index') }}"
                   class="inline-flex justify-center items-center px-6 py-3 rounded-xl border border-stone-200 text-stone-600 font-bold hover:bg-stone-50 transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex justify-center items-center gap-2 bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-8 rounded-xl shadow-md transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                        </path>
                    </svg>
                    Guardar categoría
                </button>
            </div>
        </form>
    </div>
</div>

@endsection