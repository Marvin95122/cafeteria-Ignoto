@extends('layouts.admin')

@section('content')

<div class="flex items-center gap-4 mb-8">
    <a href="{{ route('categories.index') }}" class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Nueva Categoría</h1>
        <p class="text-stone-500">Crea una sección para organizar tu menú.</p>
    </div>
</div>

<div class="max-w-xl mx-auto">

    {{-- ALERTAS --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
            <strong class="font-bold">Corrige los siguientes errores:</strong>
            <ul class="list-disc list-inside mt-2 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        
        <div class="h-2 bg-amber-800 w-full"></div>

        <div class="p-8">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block font-bold text-stone-700 mb-2">Nombre de la Categoría</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4 text-lg"
                               placeholder="Ej. Bebidas Calientes"
                               required autofocus>
                        <p class="text-xs text-stone-400 mt-2">Este nombre será visible en el menú principal.</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-stone-100 flex justify-end gap-4">
                    <a href="{{ route('categories.index') }}" 
                       class="px-6 py-3 rounded-lg text-stone-600 font-medium hover:bg-stone-100 transition">
                        Cancelar
                    </a>

                    <button type="submit" 
                            class="bg-amber-800 text-white px-8 py-3 rounded-lg shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-0.5 font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection