@extends('layouts.admin')

@section('content')

<div class="flex items-center gap-4 mb-8">
    <a href="{{ route('products.index') }}" class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Nuevo Producto</h1>
        <p class="text-stone-500">Agrega un nuevo café o postre al menú.</p>
    </div>
</div>

{{-- ERRORES --}}
@if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm animate-pulse">
        <strong class="font-bold">¡Ups! Hay un par de detalles:</strong>
        <ul class="list-disc list-inside mt-2 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white p-8 rounded-2xl shadow-sm border border-stone-100 max-w-5xl mx-auto">

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="productForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                
                {{-- Nombre --}}
                <div>
                    <label class="block font-bold text-stone-700 mb-2">Nombre del Producto</label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                           placeholder="Ej. Latte Especial" 
                           required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Categoría --}}
                    <div>
                        <label class="block font-bold text-stone-700 mb-2">Categoría</label>
                        <select name="category_id" 
                                class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                required>
                            <option value="">-- Selecciona --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Precio --}}
                    <div>
                        <label class="block font-bold text-stone-700 mb-2">Precio ($)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-500">$</span>
                            <input type="number" 
                                   name="price" 
                                   step="0.01" 
                                   min="0" 
                                   value="{{ old('price') }}" 
                                   class="w-full border-stone-300 rounded-lg shadow-sm pl-8 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                   placeholder="0.00" 
                                   required>
                        </div>
                    </div>
                </div>

                <hr class="border-stone-100 my-6">

                {{-- SECCIÓN DE INVENTARIO / RECETA --}}
                <div class="bg-amber-50/50 p-5 rounded-xl border border-amber-100" x-data="{ dynamicStock: {{ old('use_dynamic_stock') ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-amber-900 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Control de Inventario
                        </h3>
                        
                        {{-- Switch --}}
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-stone-600" :class="{ 'text-stone-400': dynamicStock }">Manual</span>
                            <button type="button" 
                                    @click="dynamicStock = !dynamicStock" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2" 
                                    :class="{ 'bg-amber-600': dynamicStock, 'bg-stone-200': !dynamicStock }">
                                <span class="sr-only">Usar stock dinámico</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" 
                                      :class="{ 'translate-x-5': dynamicStock, 'translate-x-0': !dynamicStock }"></span>
                            </button>
                            <span class="text-sm font-medium text-stone-600" :class="{ 'text-amber-700 font-bold': dynamicStock }">Por Receta (Dinámico)</span>
                            <input type="hidden" name="use_dynamic_stock" :value="dynamicStock ? 1 : 0">
                        </div>
                    </div>

                    {{-- Opción 1: Stock Manual --}}
                    <div x-show="!dynamicStock" class="transition-opacity duration-300">
                        <label class="block text-sm font-bold text-stone-600 mb-1">Stock Fijo (Cantidad actual)</label>
                        <input type="number" name="stock" value="{{ old('stock') }}" 
                               class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-200"
                               placeholder="Ej. 50">
                        <p class="text-xs text-stone-400 mt-1">Usa esto si NO quieres descontar ingredientes automáticamente.</p>
                    </div>

                    {{-- Opción 2: Receta / Ingredientes --}}
                    <div x-show="dynamicStock" class="transition-opacity duration-300" style="display: none;">
                        <p class="text-sm text-stone-500 mb-3">
                            Selecciona los ingredientes que componen este producto. El stock se calculará automáticamente según lo que tengas en almacén.
                        </p>

                        {{-- Buscador de Ingredientes--}}
                        <div class="relative mb-4">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            {{-- Input --}}
                            <input type="text" id="ingredientSearch" 
                                placeholder="Buscar ingrediente (ej. Leche, Café)..." 
                                class="w-full pl-10 border-stone-300 rounded-lg text-sm focus:border-amber-500 focus:ring-amber-200 py-2.5"
                                autocomplete="off"
                                onkeyup="filterIngredients()">

                            {{-- Lista de Resultados --}}
                            <div id="ingredientList" class="hidden absolute z-50 top-full left-0 right-0 bg-white border border-stone-200 shadow-xl rounded-lg max-h-48 overflow-y-auto mt-1 w-full">
                                @foreach($ingredients as $ingredient)
                                    <div class="ingredient-option p-2.5 hover:bg-amber-50 cursor-pointer flex justify-between items-center text-sm border-b border-stone-50 last:border-0 transition-colors"
                                        onclick="addIngredient({{ $ingredient->id }}, '{{ $ingredient->name }}', '{{ $ingredient->unit }}')">
                                        <span class="font-medium text-stone-700">{{ $ingredient->name }}</span>
                                        <span class="text-xs text-stone-500 bg-stone-100 px-2 py-0.5 rounded border border-stone-200">{{ $ingredient->unit }}</span>
                                    </div>
                                @endforeach
                                
                                {{-- Mensaje si no hay ingredientes en la BD --}}
                                @if($ingredients->isEmpty())
                                    <div class="p-4 text-center text-xs text-stone-400">
                                        No hay ingredientes registrados.<br>
                                        <a href="{{ route('ingredients.create') }}" class="text-amber-600 underline hover:text-amber-800">Crear uno aquí</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tabla de la Receta --}}
                        <div class="bg-white rounded-lg border border-stone-200 overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-stone-50 text-stone-500 font-bold">
                                    <tr>
                                        <th class="p-3">Ingrediente</th>
                                        <th class="p-3 w-32">Cantidad</th>
                                        <th class="p-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="recipe-table-body" class="divide-y divide-stone-100">
                                </tbody>
                            </table>
                            <div id="empty-recipe-msg" class="p-4 text-center text-stone-400 text-xs italic">
                                No has agregado ingredientes a la receta aún.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Checkbox Activo --}}
                <div class="flex items-center gap-3 bg-stone-50 p-4 rounded-lg border border-stone-200">
                    <input type="checkbox" id="active" name="active" value="1" checked 
                           class="w-5 h-5 text-amber-600 border-stone-300 rounded focus:ring-amber-500">
                    <label for="active" class="font-medium text-stone-700 cursor-pointer">Producto disponible para venta</label>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                {{-- Fotografía --}}
                <div>
                    <label class="block font-bold text-stone-700 mb-2">Fotografía</label>
                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-stone-300 border-dashed rounded-xl bg-stone-50 hover:bg-stone-100 transition relative overflow-hidden group">
                        <img id="image-preview" src="#" alt="Vista previa" class="hidden absolute inset-0 w-full h-full object-cover rounded-xl">
                        <div class="space-y-1 text-center relative z-10" id="upload-placeholder">
                            <svg class="mx-auto h-12 w-12 text-stone-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-stone-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-amber-700 hover:text-amber-600 focus-within:outline-none">
                                    <span>Subir archivo</span>
                                    <input id="file-upload" name="image" type="file" accept="image/*" class="sr-only" onchange="previewImage(event)">
                                </label>
                            </div>
                            <p class="text-xs text-stone-500">PNG, JPG hasta 5MB</p>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE EXTRAS --}}
                <div class="bg-stone-50 p-4 rounded-xl border border-stone-200">
                    <h3 class="text-sm font-bold text-stone-700 mb-3 flex items-center gap-2">
                        <span>🍪</span> Extras para el Cliente
                    </h3>
                    <p class="text-xs text-stone-500 mb-3">Opciones que el cliente paga aparte (ej. Jarabe extra).</p>
                    
                    <div id="extras-container" class="space-y-2 mb-3"></div>

                    <button type="button" onclick="addExtra()" 
                            class="text-xs w-full py-2 bg-white border border-stone-300 rounded text-stone-600 font-bold hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 transition">
                        + Agregar Opción Extra
                    </button>
                </div>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="flex justify-end gap-4 mt-10 pt-6 border-t border-stone-100">
            <a href="{{ route('products.index') }}" class="px-6 py-3 rounded-lg text-stone-600 font-medium hover:bg-stone-100 transition">Cancelar</a>
            <button class="bg-amber-800 text-white px-8 py-3 rounded-lg shadow-lg hover:bg-amber-900 font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Guardar Producto
            </button>
        </div>

    </form>
</div>

<script>
    //DATOS INICIALES
    const availableIngredients = @json($ingredients);

    //LÓGICA DE RECETA PRINCIPAL
    let recipeIndex = 0;
    const addedIngredients = new Set(); 

    function filterIngredients() {
        const input = document.getElementById('ingredientSearch');
        const filter = input.value.toUpperCase();
        const list = document.getElementById('ingredientList');
        const items = list.getElementsByClassName('ingredient-option');

        if(filter.length > 0){
            list.classList.remove('hidden');
        } else {
            list.classList.add('hidden');
        }

        for (let i = 0; i < items.length; i++) {
            const txtValue = items[i].textContent || items[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    document.addEventListener('click', function(event) {
        const list = document.getElementById('ingredientList');
        const searchBox = document.getElementById('ingredientSearch');
        if (searchBox && !searchBox.contains(event.target)) {
            list.classList.add('hidden');
        }
    });

    function addIngredient(id, name, unit) {
        if (addedIngredients.has(id)) {
            alert('Este ingrediente ya está en la receta.');
            return;
        }

        const tbody = document.getElementById('recipe-table-body');
        const emptyMsg = document.getElementById('empty-recipe-msg');
        
        if(emptyMsg) emptyMsg.style.display = 'none';
        
        const row = `
            <tr id="recipe-row-${id}" class="group hover:bg-stone-50">
                <td class="p-3 text-stone-700 font-medium">
                    ${name}
                    <input type="hidden" name="ingredients[${recipeIndex}][id]" value="${id}">
                </td>
                <td class="p-3">
                    <div class="relative">
                        <input type="number" step="0.01" name="ingredients[${recipeIndex}][quantity]" 
                               class="w-full border-stone-200 rounded text-sm py-1 px-2 pr-8 focus:ring-amber-500 focus:border-amber-500"
                               placeholder="0.00" required>
                        <span class="absolute right-2 top-1.5 text-xs text-stone-400 font-bold">${unit}</span>
                    </div>
                </td>
                <td class="p-3 text-right">
                    <button type="button" onclick="removeIngredient(${id})" class="text-stone-400 hover:text-red-500 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </td>
            </tr>
        `;
        
        tbody.insertAdjacentHTML('beforeend', row);
        addedIngredients.add(id);
        recipeIndex++;
        
        document.getElementById('ingredientSearch').value = '';
        document.getElementById('ingredientList').classList.add('hidden');
    }

    function removeIngredient(id) {
        document.getElementById(`recipe-row-${id}`).remove();
        addedIngredients.delete(id);
        
        const emptyMsg = document.getElementById('empty-recipe-msg');
        if (addedIngredients.size === 0 && emptyMsg) {
            emptyMsg.style.display = 'block';
        }
    }

    //LÓGICA DE EXTRAS
    let extraIndex = 0;
    
    function addExtra() {
        const container = document.getElementById('extras-container');
        
        // Generar opciones del Select
        let optionsHtml = '<option value="">- Sin ingrediente -</option>';
        if(availableIngredients) {
            availableIngredients.forEach(ing => {
                optionsHtml += `<option value="${ing.id}">${ing.name} (${ing.unit})</option>`;
            });
        }

        const html = `
            <div class="bg-stone-50 p-3 rounded-lg border border-stone-200 animate-fade-in-down mb-3 relative group">
                
                {{-- Fila 1: Nombre y Precio --}}
                <div class="flex gap-3 mb-2">
                    <div class="flex-1">
                        <label class="text-xs font-bold text-stone-500 mb-1 block">Nombre del Extra</label>
                        <input type="text" name="extras[${extraIndex}][name]" placeholder="Ej. Leche Extra" 
                               class="w-full border-stone-300 rounded px-2 py-1 text-sm focus:ring-amber-500" required>
                    </div>
                    <div class="w-24">
                        <label class="text-xs font-bold text-stone-500 mb-1 block">Precio ($)</label>
                        <input type="number" step="0.01" name="extras[${extraIndex}][price]" placeholder="0" 
                               class="w-full border-stone-300 rounded px-2 py-1 text-sm focus:ring-amber-500" required>
                    </div>
                </div>

                {{-- Fila 2: Vinculación con Inventario --}}
                <div class="flex gap-3 items-end bg-white p-2 rounded border border-stone-100">
                    <div class="flex-1">
                        <label class="text-xs text-stone-400 block">Descontar del Inventario (Opcional)</label>
                        <select name="extras[${extraIndex}][ingredient_id]" class="w-full border-stone-200 rounded text-xs py-1 text-stone-600">
                            ${optionsHtml}
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="text-xs text-stone-400 block">Cantidad</label>
                        <input type="number" step="0.01" name="extras[${extraIndex}][ingredient_qty]" placeholder="0" 
                               class="w-full border-stone-200 rounded text-xs py-1">
                    </div>
                </div>

                {{-- Botón Eliminar --}}
                <button type="button" onclick="this.parentElement.remove()" 
                        class="absolute -top-2 -right-2 bg-red-100 text-red-500 rounded-full p-1 hover:bg-red-500 hover:text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        extraIndex++;
    }

    //  IMAGEN
    function previewImage(event) {
        const reader = new FileReader();
        const imageField = document.getElementById("image-preview");
        const placeholder = document.getElementById("upload-placeholder");
        reader.onload = function(){
            if(reader.readyState == 2){
                imageField.src = reader.result;
                imageField.classList.remove("hidden");
                placeholder.classList.add("opacity-0");
            }
        }
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }
</script>

<style>
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-down { animation: fadeInDown 0.2s ease-out; }
    /* Scrollbar fina para la lista de ingredientes */
    #ingredientList::-webkit-scrollbar { width: 6px; }
    #ingredientList::-webkit-scrollbar-track { background: #f1f1f1; }
    #ingredientList::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 3px; }
    #ingredientList::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
</style>

@endsection