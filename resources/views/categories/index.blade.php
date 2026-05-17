@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Categorías del Menú</h1>
        <p class="text-stone-500 mt-1">Organiza cómo se muestran tus productos.</p>
    </div>

    <a href="{{ route('categories.create') }}"
       class="bg-amber-800 text-white px-6 py-3 rounded-full shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Nueva Categoría
    </a>
</div>

<div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 mb-8">
    <form method="GET" action="{{ route('categories.index') }}" class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <div>
            <p class="text-sm font-bold text-stone-700">Filtrar categorías</p>
            <p class="text-xs text-stone-400">Visualiza categorías activas, inactivas o todas.</p>
        </div>

        <select name="status"
                onchange="this.form.submit()"
                class="w-full md:w-64 rounded-xl border-stone-200 bg-stone-50 text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition">
            <option value="">Todos los estados</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activas</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivas</option>
        </select>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @forelse($categories as $category)
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-100 hover:border-amber-200 hover:shadow-md transition flex flex-col items-center text-center relative group">
            
            <div class="h-16 w-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center text-3xl mb-4 group-hover:bg-amber-600 group-hover:text-white transition duration-300">
                🏷️
            </div>

            <h3 class="font-bold text-xl text-stone-800 mb-1">{{ $category->name }}</h3>
            <p class="text-xs text-stone-400 mb-3">
                {{ $category->products_count }} producto(s) asociado(s)
            </p>
            
            <div class="mb-6">
                @if($category->active)
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">Activa</span>
                @else
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-stone-200 text-stone-500">Inactiva</span>
                @endif
            </div>

            <div class="flex flex-col gap-2 w-full justify-center pt-4 border-t border-stone-50">
                <a href="{{ route('categories.edit', $category) }}" 
                class="w-full py-2 text-sm text-stone-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition font-medium">
                Editar
                </a>

                @if($category->active)
                    <form method="POST"
                        action="{{ route('categories.destroy', $category) }}"
                        onsubmit="return confirm('¿Desactivar esta categoría? Los productos asociados se conservarán.')">
                        @csrf
                        @method('DELETE')

                        <button class="w-full py-2 text-sm text-stone-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition font-medium">
                            Desactivar
                        </button>
                    </form>
                @else
                    <div class="w-full py-2 text-sm text-stone-400 bg-stone-50 rounded-lg font-medium">
                        Categoría inactiva
                    </div>
                @endif

                <form method="POST"
                    action="{{ route('categories.force-delete', $category) }}"
                    onsubmit="return confirm('¿Eliminar definitivamente esta categoría? Solo se permitirá si no tiene productos asociados.')">
                    @csrf
                    @method('DELETE')

                    <button class="w-full py-2 text-sm text-stone-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition font-medium">
                        Eliminar definitivamente
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-4 text-center py-12 bg-white rounded-xl border border-dashed border-stone-300">
            <p class="text-stone-500">No has creado categorías. ¡Empieza ahora!</p>
        </div>
    @endforelse
</div>
@endsection