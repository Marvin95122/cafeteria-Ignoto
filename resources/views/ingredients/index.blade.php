@extends('layouts.admin')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Almacén de Insumos</h1>
        <p class="text-stone-500 mt-1">Gestiona tu stock de materia prima (Café, Leche, etc).</p>
    </div>
    <a href="{{ route('ingredients.create') }}" class="bg-amber-800 text-white px-5 py-2.5 rounded-full hover:bg-amber-900 transition flex items-center gap-2 shadow-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Registrar Insumo
    </a>
</div>

<div class="bg-white p-4 rounded-xl shadow-sm border border-stone-100 mb-6">
    <form id="ingredientFilterForm" method="GET" action="{{ route('ingredients.index') }}" class="flex items-center gap-4">
        <div class="relative w-full">
            <input 
                type="text" 
                name="search" 
                id="ingredientSearchInput"
                value="{{ request('search') }}" 
                placeholder="Buscar ingrediente..." 
                autocomplete="off"
                class="w-full pl-10 pr-10 py-2 rounded-lg border-stone-200 focus:border-amber-500 focus:ring-amber-200 transition"
            >
            <div class="absolute left-3 top-2.5 text-stone-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            
            @if(request('search'))
                <a href="{{ route('ingredients.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-red-500 transition cursor-pointer" title="Borrar búsqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif
        </div>

        <div id="loadingSpinner" class="hidden text-amber-600">
            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-amber-50 text-amber-900 font-bold uppercase text-xs tracking-wider">
            <tr>
                <th class="px-6 py-4">Ingrediente</th>
                <th class="px-6 py-4">Stock Actual</th>
                <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($ingredients as $ingredient)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-6 py-4 font-medium text-stone-800">{{ $ingredient->name }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-sm font-bold {{ $ingredient->current_quantity < 500 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{-- Usamos la función inteligente del modelo --}}
                            {{ $ingredient->full_quantity }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('ingredients.edit', $ingredient) }}" class="text-amber-600 hover:text-amber-800 font-medium">Editar</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-10 text-center text-stone-500">
                        @if(request('search'))
                            No se encontraron ingredientes con "{{ request('search') }}".
                        @else
                            No hay ingredientes registrados aún.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4 border-t border-stone-100">
        {{ $ingredients->appends(request()->query())->links() }} 
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

            if(spinner) spinner.classList.remove('hidden');

            timeout = setTimeout(function() {
                form.submit();
            }, 500); // Espera en ms después de dejar de escribir
        });
    }
</script>
@endsection