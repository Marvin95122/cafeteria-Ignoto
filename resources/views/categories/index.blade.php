@extends('layouts.admin')

@section('content')

<div class="w-full max-w-[1500px] mx-auto space-y-4 sm:space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] sm:text-xs font-bold mb-2 sm:mb-3">
                🗂️ Organización del menú
            </div>

            <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-amber-900 leading-tight">
                Categorías del Menú
            </h1>

            <p class="text-sm sm:text-base text-stone-500 mt-1 leading-snug">
                Organiza productos por secciones para facilitar la administración y la venta en el POS.
            </p>
        </div>

        <a href="{{ route('categories.create') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-amber-800 text-white px-5 sm:px-6 py-3 rounded-full shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-1 font-bold">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            Nueva Categoría
        </a>
    </div>

    {{-- TARJETAS RESUMEN --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 xl:gap-5">
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">Total categorías</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-stone-800 mt-1">
                        {{ $totalCategories }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-2xl">
                    🗂️
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">Activas</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-green-600 mt-1">
                        {{ $activeCategories }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-2xl">
                    ✅
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">Inactivas</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-stone-500 mt-1">
                        {{ $inactiveCategories }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-2xl">
                    ⏸️
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">Con productos</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-amber-700 mt-1">
                        {{ $categoriesWithProducts }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-2xl">
                    ☕
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <form method="GET" action="{{ route('categories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_13rem_12rem_auto] gap-3 sm:gap-4 items-center">

                {{-- Buscador --}}
                <div class="relative w-full sm:col-span-2 xl:col-span-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        🔍
                    </span>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar categoría por nombre..."
                           class="w-full pl-10 pr-10 py-2.5 sm:py-3 rounded-xl border-stone-200 bg-stone-50 text-sm focus:border-amber-500 focus:ring-amber-200 transition">
                </div>

                {{-- Estado --}}
                <select name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 text-sm text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-2.5 sm:py-3">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activas</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivas</option>
                </select>

                {{-- Por página --}}
                <select name="per_page"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 text-sm text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-2.5 sm:py-3">
                    <option value="8" {{ request('per_page', 12) == 8 ? 'selected' : '' }}>8 por página</option>
                    <option value="12" {{ request('per_page', 12) == 12 ? 'selected' : '' }}>12 por página</option>
                    <option value="24" {{ request('per_page', 12) == 24 ? 'selected' : '' }}>24 por página</option>
                    <option value="48" {{ request('per_page', 12) == 48 ? 'selected' : '' }}>48 por página</option>
                </select>

                <button type="submit"
                        class="w-full px-5 py-2.5 sm:py-3 rounded-xl bg-amber-800 text-white text-sm font-bold hover:bg-amber-900 transition">
                    Buscar
                </button>
            </div>

            @if(request('search') || request('status') || request('per_page'))
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-stone-100">
                    <p class="text-xs sm:text-sm text-stone-500">
                        Mostrando categorías filtradas.
                    </p>

                    <a href="{{ route('categories.index') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- TARJETAS DE CATEGORÍAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4 xl:gap-5">
        @forelse($categories as $category)
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-stone-100 hover:border-amber-200 hover:shadow-md transition flex flex-col items-center text-center relative group min-w-0 {{ !$category->active ? 'opacity-75 bg-stone-50' : '' }}">
                
                <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-full flex items-center justify-center text-2xl sm:text-3xl mb-3 sm:mb-4 transition duration-300
                    {{ $category->active ? 'bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white' : 'bg-stone-100 text-stone-400' }}">
                    🏷️
                </div>

                <h3 class="font-bold text-lg sm:text-xl text-stone-800 mb-1 break-words max-w-full">
                    {{ $category->name }}
                </h3>

                <p class="text-xs text-stone-400 mb-3 sm:mb-4">
                    {{ $category->products_count }} producto(s) asociado(s)
                </p>
                
                <div class="flex flex-wrap justify-center gap-2 mb-4 sm:mb-6">
                    @if($category->active)
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                            Activa
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-stone-200 text-stone-600">
                            Inactiva
                        </span>
                    @endif

                    @if($category->products_count > 0)
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                            En uso
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-stone-100 text-stone-500">
                            Sin productos
                        </span>
                    @endif
                </div>

                <div class="flex flex-col gap-2 w-full justify-center pt-3 sm:pt-4 border-t border-stone-100 mt-auto">
                    <a href="{{ route('categories.edit', $category) }}" 
                       class="w-full py-2 text-sm text-stone-600 hover:text-amber-700 hover:bg-amber-50 rounded-xl transition font-bold">
                        Editar
                    </a>

                    @if($category->active)
                        <form method="POST"
                              action="{{ route('categories.destroy', $category) }}"
                              onsubmit="return confirm('¿Desactivar esta categoría? Los productos asociados se conservarán.')">
                            @csrf
                            @method('DELETE')

                            <button class="w-full py-2 text-sm text-stone-600 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition font-bold">
                                Desactivar
                            </button>
                        </form>
                    @else
                        <div class="w-full py-2 text-sm text-stone-400 bg-stone-50 rounded-xl font-bold">
                            Categoría inactiva
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('categories.force-delete', $category) }}"
                          onsubmit="return confirm('¿Eliminar esta categoría? Solo se permitirá si no tiene productos asociados.')">
                        @csrf
                        @method('DELETE')

                        <button class="w-full py-2 text-sm text-stone-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition font-bold">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="2xl:col-span-4 xl:col-span-3 sm:col-span-2 col-span-1 text-center py-12 sm:py-14 px-4 bg-white rounded-2xl border border-dashed border-stone-300">
                <span class="text-5xl block mb-3">🗂️</span>

                <p class="text-lg font-bold text-stone-600">
                    No se encontraron categorías.
                </p>

                <p class="text-sm text-stone-400 mt-1">
                    Prueba limpiar filtros o crea una nueva categoría.
                </p>

                <a href="{{ route('categories.index') }}"
                   class="inline-flex mt-4 px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                    Limpiar filtros
                </a>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-3 sm:p-4 pb-6 sm:pb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4 overflow-x-auto">
        <p class="text-xs sm:text-sm text-stone-500">
            Mostrando {{ $categories->firstItem() ?? 0 }} a {{ $categories->lastItem() ?? 0 }}
            de {{ $categories->total() }} categoría(s).
        </p>

        <div>
            {{ $categories->links() }}
        </div>
    </div>

</div>

@endsection