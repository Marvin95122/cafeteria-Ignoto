@extends('layouts.admin')

@section('content')

<div class="w-full max-w-[1500px] mx-auto space-y-4 sm:space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] sm:text-xs font-bold mb-2 sm:mb-3">
                📦 Control de almacén
            </div>

            <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-amber-900 leading-tight">
                Materia Prima
            </h1>

            <p class="text-sm sm:text-base text-stone-500 mt-1 leading-snug">
                Administra insumos, unidades de medida, cantidades disponibles y estado de uso.
            </p>
        </div>

        <a href="{{ route('ingredients.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-amber-800 text-white px-5 sm:px-6 py-3 rounded-full hover:bg-amber-900 transition shadow-lg font-bold">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4">
                </path>
            </svg>
            Registrar Insumo
        </a>
    </div>

    {{-- TARJETAS RESUMEN --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 xl:gap-5">
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Total de insumos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-stone-800 mt-1">
                        {{ $totalIngredients }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-stone-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    📦
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Activos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-green-600 mt-1">
                        {{ $activeIngredients }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-green-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    ✅
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Inactivos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-stone-500 mt-1">
                        {{ $inactiveIngredients }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-stone-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    ⏸️
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Stock bajo
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black {{ $lowStockIngredients > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        {{ $lowStockIngredients }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl {{ $lowStockIngredients > 0 ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    {{ $lowStockIngredients > 0 ? '⚠️' : '🟢' }}
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <form id="ingredientFilterForm"
              method="GET"
              action="{{ route('ingredients.index') }}"
              class="space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_13rem_auto] gap-3 sm:gap-4 items-center">

                {{-- Buscador --}}
                <div class="relative w-full">
                    <input 
                        type="text" 
                        name="search" 
                        id="ingredientSearchInput"
                        value="{{ request('search') }}" 
                        placeholder="Buscar ingrediente por nombre..." 
                        autocomplete="off"
                        class="w-full pl-10 pr-10 py-2.5 sm:py-3 rounded-xl border-stone-200 bg-stone-50 text-sm focus:border-amber-500 focus:ring-amber-200 transition"
                    >

                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">
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
                <select name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 text-sm text-stone-700 focus:border-amber-500 focus:ring-amber-200 transition py-2.5 sm:py-3">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>

                <div id="loadingSpinner" class="hidden text-amber-600 justify-self-center">
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
                    <p class="text-xs sm:text-sm text-stone-500">
                        Mostrando resultados filtrados.
                    </p>

                    <a href="{{ route('ingredients.index') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </form>
    </div>

    @if($ingredients->count())

        {{-- TABLA EN TABLET/PC --}}
        <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="bg-amber-50 text-amber-900 font-bold uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Ingrediente</th>
                            <th class="px-5 py-4">Stock Actual</th>
                            <th class="px-5 py-4">Uso</th>
                            <th class="px-5 py-4">Estado</th>
                            <th class="px-5 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100">
                        @foreach($ingredients as $ingredient)
                            @php
                                $isLow = $ingredient->active && $ingredient->current_quantity < 500;
                            @endphp

                            <tr class="hover:bg-stone-50 transition {{ !$ingredient->active ? 'opacity-75 bg-stone-50/60' : '' }}">
                                {{-- Ingrediente --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center text-xl shrink-0">
                                            📦
                                        </div>

                                        <div class="min-w-0">
                                            <p class="font-bold text-stone-800 truncate">
                                                {{ $ingredient->name }}
                                            </p>

                                            <p class="text-xs text-stone-400">
                                                Unidad base: {{ strtoupper($ingredient->unit) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Stock --}}
                                <td class="px-5 py-4">
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
                                <td class="px-5 py-4">
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
                                <td class="px-5 py-4">
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
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('ingredients.edit', $ingredient) }}"
                                       class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-amber-50 text-amber-700 text-sm font-bold hover:bg-amber-100 transition">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-stone-100 overflow-x-auto">
                {{ $ingredients->appends(request()->query())->links() }} 
            </div>
        </div>

        {{-- TARJETAS EN CELULAR --}}
        <div class="md:hidden space-y-3">
            @foreach($ingredients as $ingredient)
                @php
                    $isLow = $ingredient->active && $ingredient->current_quantity < 500;
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 relative overflow-hidden {{ !$ingredient->active ? 'opacity-75 bg-stone-50' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-xl shrink-0">
                            📦
                        </div>

                        <div class="flex-1 min-w-0 pr-16">
                            <h3 class="font-bold text-stone-800 text-base leading-tight break-words">
                                {{ $ingredient->name }}
                            </h3>

                            <p class="text-xs text-stone-400 mt-1">
                                Unidad base: {{ strtoupper($ingredient->unit) }}
                            </p>
                        </div>

                        <a href="{{ route('ingredients.edit', $ingredient) }}"
                           class="absolute top-3 right-3 p-2 text-stone-400 hover:text-amber-600 hover:bg-amber-50 rounded-full transition"
                           title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                </path>
                            </svg>
                        </a>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                            {{ $isLow ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $ingredient->full_quantity }}
                        </span>

                        @if($isLow)
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                Stock bajo
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-stone-50 text-stone-500 border border-stone-100">
                                Nivel estable
                            </span>
                        @endif

                        @if($ingredient->active)
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                Activo
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-stone-200 text-stone-600 border border-stone-300">
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-blue-50 text-blue-700 border border-blue-100 p-3">
                            <span class="font-black block">
                                {{ $ingredient->products_count }}
                            </span>
                            producto(s)
                        </div>

                        <div class="rounded-xl bg-stone-50 text-stone-600 border border-stone-100 p-3">
                            <span class="font-black block">
                                {{ $ingredient->inventory_movements_count }}
                            </span>
                            movimiento(s)
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-3 pb-6 overflow-x-auto">
                {{ $ingredients->appends(request()->query())->links() }} 
            </div>
        </div>

    @else
        {{-- ESTADO VACÍO --}}
        <div class="text-center py-12 sm:py-14 px-4 bg-white rounded-2xl border border-dashed border-stone-300">
            <span class="text-5xl block mb-3">📦</span>

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
    @endif

</div>

<script>
    let timeout = null;
    const form = document.getElementById('ingredientFilterForm');
    const input = document.getElementById('ingredientSearchInput');
    const spinner = document.getElementById('loadingSpinner');

    if (input) {
        input.addEventListener('keyup', function() {
            clearTimeout(timeout);

            if (spinner) {
                spinner.classList.remove('hidden');
            }

            timeout = setTimeout(function() {
                form.submit();
            }, 500);
        });
    }
</script>

@endsection