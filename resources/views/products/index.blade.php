@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Menú de Productos</h1>
        <p class="text-stone-500 mt-1">Administra tu catálogo de cafés y postres.</p>
    </div>

    <a href="{{ route('products.create') }}"
       class="bg-amber-800 text-white px-6 py-3 rounded-full shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Nuevo Producto
    </a>
</div>

<div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 mb-8">
    <form id="filterForm" method="GET" action="{{ route('products.index') }}" class="space-y-4">
        
        <div class="flex flex-col xl:flex-row gap-4 items-center">
            {{-- Buscador --}}
            <div class="w-full xl:flex-1 relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-stone-400 group-focus-within:text-amber-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <input 
                    type="text" 
                    name="search" 
                    id="searchInput"
                    value="{{ request('search') }}" 
                    placeholder="Buscar producto por nombre..." 
                    autocomplete="off"
                    class="pl-10 pr-10 block w-full rounded-xl border-stone-200 bg-stone-50 text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition placeholder-stone-400"
                >

                @if(request('search'))
                    <a href="{{ route('products.index', [
                        'category_id' => request('category_id'),
                        'status' => request('status'),
                        'stock_type' => request('stock_type')
                    ]) }}"
                       class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-red-500 transition cursor-pointer"
                       title="Borrar búsqueda">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Categoría --}}
            <div class="w-full xl:w-64">
                <div class="relative">
                    <select name="category_id" 
                            onchange="this.form.submit()" 
                            class="block w-full rounded-xl border-stone-200 bg-stone-50 text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition cursor-pointer appearance-none pl-4 pr-10">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-stone-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Estado --}}
            <div class="w-full xl:w-52">
                <div class="relative">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border-stone-200 bg-stone-50 text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition cursor-pointer appearance-none pl-4 pr-10">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>

                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-stone-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Tipo de stock --}}
            <div class="w-full xl:w-56">
                <div class="relative">
                    <select name="stock_type"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border-stone-200 bg-stone-50 text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition cursor-pointer appearance-none pl-4 pr-10">
                        <option value="">Todos los stocks</option>
                        <option value="manual" {{ request('stock_type') === 'manual' ? 'selected' : '' }}>Stock manual</option>
                        <option value="dynamic" {{ request('stock_type') === 'dynamic' ? 'selected' : '' }}>Stock por receta</option>
                    </select>

                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-stone-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Spinner --}}
            <div id="loadingSpinner" class="hidden text-amber-600">
                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        {{-- Filtros activos --}}
        @if(request('search') || request('category_id') || request('status') || request('stock_type'))
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-stone-100">
                <p class="text-sm text-stone-500">
                    Mostrando resultados filtrados.
                </p>

                <a href="{{ route('products.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                    Limpiar filtros
                </a>
            </div>
        @endif

    </form>
</div>

@if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            <div class="group bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">
                
                <div class="relative h-48 overflow-hidden bg-stone-100">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500"
                             alt="{{ $product->name }}">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-stone-300">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs font-medium">Sin Imagen</span>
                        </div>
                    @endif

                    <div class="absolute top-3 right-3">
                        @if($product->active)
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 shadow-sm backdrop-blur-md bg-opacity-90">
                                Activo
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-stone-200 text-stone-600 shadow-sm backdrop-blur-md bg-opacity-90">
                                Inactivo
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">
                            {{ $product->category->name ?? 'General' }}
                        </span>
                        
                        {{-- STOCK CALCULADO (Aquí está la corrección) --}}
                        @if($product->calculated_stock <= 5)
                            <span class="text-xs font-bold text-red-500 flex items-center gap-1 bg-red-50 px-2 py-0.5 rounded-full border border-red-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                {{ $product->calculated_stock }}
                            </span>
                        @endif
                    </div>

                    <h3 class="font-bold text-stone-800 text-lg mb-1 leading-tight group-hover:text-amber-700 transition">
                        {{ $product->name }}
                    </h3>

                    <div class="flex flex-wrap gap-2 mt-2">
                        @if($product->use_dynamic_stock)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Stock por receta
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-stone-100 text-stone-600 border border-stone-200">
                                Stock manual
                            </span>
                        @endif

                        @if($product->extras->count() > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                {{ $product->extras->count() }} extra(s)
                            </span>
                        @endif
                    </div>
                                        
                    <p class="text-2xl font-serif font-bold text-stone-900 mt-auto pt-4">
                        ${{ number_format($product->price, 2) }}
                    </p>
                </div>

                <div class="bg-stone-50 px-5 py-3 border-t border-stone-100 flex justify-between items-center">
                    <div class="text-sm text-stone-400 flex items-center gap-1">
                        
                        {{-- INDICADOR DE TIPO DE STOCK --}}
                        @if($product->use_dynamic_stock)
                            <span class="text-amber-600" title="Stock por Receta">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </span>
                        @endif
                        
                        Stock: 
                        {{-- AQUÍ SE MUESTRA EL STOCK REAL --}}
                        <span class="text-stone-600 font-medium">
                            {{ $product->calculated_stock }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('products.edit', $product) }}" 
                           class="p-2 text-stone-500 hover:text-amber-600 hover:bg-amber-50 rounded-full transition" 
                           title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>

                        @if($product->active)
                            <form method="POST"
                                action="{{ route('products.destroy', $product) }}"
                                onsubmit="return confirm('¿Desactivar este producto?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" 
                                        class="p-2 text-stone-500 hover:text-orange-600 hover:bg-orange-50 rounded-full transition" 
                                        title="Desactivar producto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18.364 5.636l-12.728 12.728M6.343 6.343l11.314 11.314M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                                    </svg>
                                </button>
                            </form>
                        @endif

                        <form method="POST"
                            action="{{ route('products.force-delete', $product) }}"
                            onsubmit="return confirm('¿Eliminar definitivamente este producto? Solo se permitirá si no tiene ventas o tickets asociados.')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" 
                                    class="p-2 text-stone-500 hover:text-red-600 hover:bg-red-50 rounded-full transition" 
                                    title="Eliminar definitivamente">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 011 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>

@else
    @if(request('search') || request('category_id'))
        <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-sm border border-stone-200 text-center">
            <div class="bg-stone-100 p-4 rounded-full mb-4">
                <svg class="w-10 h-10 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-stone-800">No se encontraron resultados</h3>
            <p class="text-stone-500 mt-1 max-w-sm mb-6">No hay productos que coincidan con los filtros seleccionados.</p>
            <a href="{{ route('products.index') }}" class="bg-amber-100 text-amber-800 px-4 py-2 rounded-lg font-bold hover:bg-amber-200 transition">
                Limpiar filtros
            </a>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl shadow-sm border border-stone-200 border-dashed text-center">
            <div class="bg-amber-50 p-4 rounded-full mb-4">
                <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-stone-900">No hay productos aún</h3>
            <p class="text-stone-500 mt-1 max-w-sm mb-6">Comienza a agregar deliciosos cafés y postres a tu menú.</p>
            <a href="{{ route('products.create') }}" class="text-amber-700 font-bold hover:underline hover:text-amber-800">
                Crear el primer producto &rarr;
            </a>
        </div>
    @endif
@endif

<script>
    let timeout = null;
    const form = document.getElementById('filterForm');
    const input = document.getElementById('searchInput');
    const spinner = document.getElementById('loadingSpinner');

    input.addEventListener('keyup', function() {
        clearTimeout(timeout);
        if(spinner) spinner.classList.remove('hidden');
        timeout = setTimeout(function() {
            form.submit();
        }, 800);
    });

    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        select.addEventListener('change', () => {
             if(spinner) spinner.classList.remove('hidden');
        });
    });
</script>

@endsection