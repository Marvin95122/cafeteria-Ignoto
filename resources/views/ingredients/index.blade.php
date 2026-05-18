@extends('layouts.admin')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📦 Control de almacén
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Materia Prima
            </h1>

            <p class="text-stone-500 mt-1">
                Administra insumos, unidades de medida, cantidades disponibles y estado de uso.
            </p>
        </div>

        <a href="{{ route('ingredients.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-amber-800 text-white px-6 py-3 rounded-full hover:bg-amber-900 transition shadow-lg font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4">
                </path>
            </svg>
            Registrar Insumo
        </a>
    </div>

    {{-- TARJETAS RESUMEN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Total de insumos</p>
                    <h3 class="text-3xl font-black text-stone-800 mt-1">
                        {{ $totalIngredients }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-2xl">
                    📦
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Activos</p>
                    <h3 class="text-3xl font-black text-green-600 mt-1">
                        {{ $activeIngredients }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-2xl">
                    ✅
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Inactivos</p>
                    <h3 class="text-3xl font-black text-stone-500 mt-1">
                        {{ $inactiveIngredients }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-2xl">
                    ⏸️
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Stock bajo</p>
                    <h3 class="text-3xl font-black {{ $lowStockIngredients > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        {{ $lowStockIngredients }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl {{ $lowStockIngredients > 0 ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center text-2xl">
                    {{ $lowStockIngredients > 0 ? '⚠️' : '🟢' }}
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100">
        <form id="ingredientFilterForm"
              method="GET"
              action="{{ route('ingredients.index') }}"
              class="space-y-4">

            <div class="flex flex-col lg:flex-row gap-4 items-center">
                {{-- Buscador --}}
                <div class="relative w-full lg:flex-1">
                    <input 
                        type="text" 
                        name="search" 
                        id="ingredientSearchInput"
                        value="{{ request('search') }}" 
                        placeholder="Buscar ingrediente por nombre..." 
                        autocomplete="off"
                        class="w-full pl-10 pr-10 py-3 rounded-xl border-stone-200 bg-stone-50 focus:border-amber-500 focus:ring-amber-200 transition"
                    >

                    <div class="absolute left-3 top-3 text-stone-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                            </path>
                        </svg>
                    </div>
                    
                    @if(request('search'))
                        <a href="{{ route('ingredients.index', ['status' => request('status')]) }}"
                           class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-red-500 transition cursor-pointer"
                           title="Borrar búsqueda">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                      clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                </div>

                {{-- Estado --}}
                <div class="w-full lg:w-64">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full py-3 rounded-xl border-stone-200 bg-stone-50 focus:border-amber-500 focus:ring-amber-200 transition">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

                <div id="loadingSpinner" class="hidden text-amber-600">
                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>

            @if(request('search') || request('status'))
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-stone-100">
                    <p class="text-sm text-stone-500">
                        Mostrando resultados filtrados.
                    </p>

                    <a href="{{ route('ingredients.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- TABLA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-amber-50 text-amber-900 font-bold uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Ingrediente</th>
                        <th class="px-6 py-4">Stock Actual</th>
                        <th class="px-6 py-4">Uso</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($ingredients as $ingredient)
                        <tr class="hover:bg-stone-50 transition {{ !$ingredient->active ? 'opacity-75 bg-stone-50/60' : '' }}">
                            {{-- Ingrediente --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center text-xl">
                                        📦
                                    </div>

                                    <div>
                                        <p class="font-bold text-stone-800">
                                            {{ $ingredient->name }}
                                        </p>

                                        <p class="text-xs text-stone-400">
                                            Unidad base: {{ strtoupper($ingredient->unit) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Stock --}}
                            <td class="px-6 py-4">
                                @php
                                    $isLow = $ingredient->active && $ingredient->current_quantity < 500;
                                @endphp

                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit px-3 py-1 rounded-full text-sm font-bold
                                        {{ $isLow ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $ingredient->full_quantity }}
                                    </span>

                                    @if($isLow)
                                        <span class="text-xs text-red-500 font-bold">
                                            Stock bajo
                                        </span>
                                    @else
                                        <span class="text-xs text-stone-400">
                                            Nivel estable
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Uso --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 text-xs">
                                    <span class="inline-flex w-fit px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 font-bold">
                                        {{ $ingredient->products_count }} producto(s)
                                    </span>

                                    <span class="inline-flex w-fit px-2 py-1 rounded-full bg-stone-50 text-stone-600 border border-stone-100 font-bold">
                                        {{ $ingredient->inventory_movements_count }} movimiento(s)
                                    </span>
                                </div>
                            </td>

                            {{-- Estado --}}
                            <td class="px-6 py-4">
                                @if($ingredient->active)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-stone-200 text-stone-600">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('ingredients.edit', $ingredient) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-amber-50 text-amber-700 text-sm font-bold hover:bg-amber-100 transition">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center text-stone-500">
                                <div class="flex flex-col items-center">
                                    <span class="text-5xl mb-3">📦</span>

                                    @if(request('search') || request('status'))
                                        <p class="font-bold text-stone-700">
                                            No hay insumos que coincidan con los filtros.
                                        </p>
                                        <a href="{{ route('ingredients.index') }}"
                                           class="mt-4 inline-flex px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                                            Limpiar filtros
                                        </a>
                                    @else
                                        <p class="font-bold text-stone-700">
                                            No hay ingredientes registrados aún.
                                        </p>
                                        <a href="{{ route('ingredients.create') }}"
                                           class="mt-4 inline-flex px-4 py-2 rounded-xl bg-amber-800 text-white text-sm font-bold hover:bg-amber-900 transition">
                                            Registrar primer insumo
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-stone-100">
            {{ $ingredients->appends(request()->query())->links() }} 
        </div>
    </div>
</div>

<script>
    let timeout = null;
    const form = document.getElementById('ingredientFilterForm');
    const input = document.getElementById('ingredientSearchInput');
    const spinner = document.getElementById('loadingSpinner');

    if(input) {
        input.addEventListener('keyup', function() {
            clearTimeout(timeout);

            if(spinner) {
                spinner.classList.remove('hidden');
            }

            timeout = setTimeout(function() {
                form.submit();
            }, 500);
        });
    }
</script>
@endsection