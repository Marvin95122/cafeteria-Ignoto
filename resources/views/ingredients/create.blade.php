@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('ingredients.index') }}" class="text-stone-400 hover:text-amber-700 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="font-serif text-2xl font-bold text-stone-800">Nuevo Ingrediente</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-stone-100 p-8">
        <form action="{{ route('ingredients.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-bold text-stone-600 mb-2">Nombre del Insumo</label>
                <input type="text" name="name" class="w-full rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500" placeholder="Ej: Grano de Café, Leche Entera, Azúcar..." required>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-stone-600 mb-2">Cantidad Inicial</label>
                    <input type="number" step="0.01" name="current_quantity" class="w-full rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500" placeholder="0.00" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-stone-600 mb-2">Unidad de Medida</label>
                    <select name="unit" class="w-full rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500">
                        <option value="g">Gramos (g)</option>
                        <option value="ml">Mililitros (ml)</option>
                        <option value="kg">Kilogramos (kg)</option>
                        <option value="l">Litros (L)</option>
                        <option value="pza">Piezas (pza)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full bg-amber-600 text-white font-bold py-3 rounded-lg hover:bg-amber-700 transition shadow-md">
                Guardar Ingrediente
            </button>
        </form>
    </div>
</div>
@endsection