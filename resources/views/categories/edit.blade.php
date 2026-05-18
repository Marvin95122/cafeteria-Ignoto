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
                    ✏️ Edición de sección
                </div>

                <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                    Editar Categoría
                </h1>

                <p class="text-stone-500 mt-1">
                    Modifica el nombre y estado de
                    <span class="font-bold text-stone-700">{{ $category->name }}</span>.
                </p>
            </div>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-sm text-stone-600">
            <span class="font-bold text-amber-800">Estado actual:</span>

            @if($category->active)
                <span class="text-green-700 font-bold">Activa</span>
            @else
                <span class="text-stone-500 font-bold">Inactiva</span>
            @endif
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
                Cambia los datos de la categoría sin afectar los productos asociados.
            </p>
        </div>

        <form method="POST" action="{{ route('categories.update', $category) }}" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">
                    Nombre de la categoría
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $category->name) }}"
                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 text-lg transition"
                       required>

                <p class="text-xs text-stone-400 mt-1">
                    Los productos asociados conservarán esta categoría aunque cambies el nombre.
                </p>
            </div>

            <div class="flex items-start gap-3 rounded-2xl border p-4
                {{ old('active', $category->active) ? 'bg-green-50 border-green-100' : 'bg-stone-50 border-stone-200' }}">
                <input type="checkbox"
                       id="active"
                       name="active"
                       value="1"
                       {{ old('active', $category->active) ? 'checked' : '' }}
                       class="w-5 h-5 mt-1 rounded border-stone-300 text-green-600 focus:ring-green-500">

                <div>
                    <label for="active" class="font-bold cursor-pointer {{ old('active', $category->active) ? 'text-green-800' : 'text-stone-700' }}">
                        Categoría activa
                    </label>

                    <p class="text-xs mt-1 {{ old('active', $category->active) ? 'text-green-700' : 'text-stone-500' }}">
                        Si está inactiva, se conserva su historial y productos, pero queda marcada fuera de uso.
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-800">
                <p class="font-bold mb-1">Productos asociados</p>
                <p>
                    Esta categoría tiene
                    <span class="font-bold">{{ $category->products()->count() }}</span>
                    producto(s) asociado(s). Al editarla no se eliminan productos.
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
                              d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    {{-- ACCIONES AVANZADAS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-stone-100 bg-stone-50">
            <h2 class="font-bold text-stone-800 text-lg flex items-center gap-2">
                ⚙️ Acciones avanzadas
            </h2>

            <p class="text-sm text-stone-500 mt-1">
                Desactiva o elimina definitivamente la categoría según su uso.
            </p>
        </div>

        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Desactivar --}}
                <div class="border border-orange-100 bg-orange-50 rounded-2xl p-5">
                    <h3 class="font-bold text-orange-800 mb-2">
                        Desactivar categoría
                    </h3>

                    <p class="text-sm text-orange-700 mb-4">
                        Conserva productos e historial. Es la opción recomendada si la categoría ya está en uso.
                    </p>

                    @if($category->active)
                        <form action="{{ route('categories.destroy', $category) }}"
                              method="POST"
                              onsubmit="return confirm('¿Desactivar esta categoría? Los productos asociados se conservarán.')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="w-full px-4 py-3 rounded-xl bg-orange-100 text-orange-700 text-sm font-bold hover:bg-orange-200 transition">
                                Desactivar categoría
                            </button>
                        </form>
                    @else
                        <div class="w-full px-4 py-3 rounded-xl bg-stone-100 text-stone-500 text-sm font-bold text-center">
                            Esta categoría ya está inactiva
                        </div>
                    @endif
                </div>

                {{-- Eliminar definitivamente --}}
                <div class="border border-red-100 bg-red-50 rounded-2xl p-5">
                    <h3 class="font-bold text-red-800 mb-2">
                        Eliminar definitivamente
                    </h3>

                    <p class="text-sm text-red-700 mb-4">
                        Solo se permitirá si no tiene productos asociados.
                    </p>

                    <form action="{{ route('categories.force-delete', $category) }}"
                          method="POST"
                          onsubmit="return confirm('¿Eliminar definitivamente esta categoría? Solo se permitirá si no tiene productos asociados.')">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="w-full px-4 py-3 rounded-xl bg-red-100 text-red-700 text-sm font-bold hover:bg-red-200 transition">
                            Eliminar definitivamente
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-xs text-stone-400 mt-5">
                Recomendación: usa “Desactivar” para categorías con productos. La eliminación definitiva solo debe usarse para registros creados por error.
            </p>
        </div>
    </div>
</div>

@endsection