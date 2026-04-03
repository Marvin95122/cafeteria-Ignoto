@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="font-serif text-3xl font-bold text-amber-900">Ingresar Compra</h1>
            <p class="text-stone-500 text-sm">Agrega varios insumos al mismo tiempo.</p>
        </div>
        <a href="{{ route('inventory_movements.index') }}" class="text-stone-500 hover:text-stone-700 transition font-bold text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Volver
        </a>
    </div>

    <form action="{{ route('inventory_movements.store_bulk') }}" method="POST" id="bulk-form" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-amber-50/50 p-4 border-b border-stone-100 flex justify-between items-center">
                <h2 class="font-bold text-stone-800 flex items-center gap-2">
                    <span class="text-xl">🛒</span> Lista de Insumos Comprados
                </h2>
                <button type="button" onclick="addRow()" class="bg-amber-800 hover:bg-amber-900 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm">
                    + Agregar Fila
                </button>
            </div>
            
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="items-table">
                        <thead>
                            <tr class="text-xs text-stone-500 uppercase tracking-wider border-b border-stone-200">
                                <th class="pb-3 font-bold w-4/12">Materia Prima</th>
                                <th class="pb-3 font-bold w-4/12">Cantidad y Unidad de Compra</th>
                                <th class="pb-3 font-bold w-3/12">Costo Total ($)</th>
                                <th class="pb-3 font-bold w-1/12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            {{-- Fila Inicial --}}
                            <tr class="item-row border-b border-stone-100 last:border-0" data-index="0">
                                <td class="py-3 pr-2">
                                    {{-- Agregamos onchange="updateUnitOptions(this)" para detectar cuando elijas el producto --}}
                                    <select name="items[0][ingredient_id]" required onchange="updateUnitOptions(this)" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-500 focus:ring-amber-200">
                                        <option value="">Selecciona insumo...</option>
                                        @foreach($ingredients as $ingredient)
                                            <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit_measure }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-3 pr-2">
                                    <div class="flex gap-1">
                                        <input type="number" name="items[0][quantity]" required step="0.01" min="0.01" placeholder="Ej: 2.5" class="w-1/2 rounded-lg border-stone-300 text-sm focus:border-amber-500 focus:ring-amber-200">
                                        {{-- Agregamos la clase 'unit-select' para que JS la encuentre y la modifique --}}
                                        <select name="items[0][input_unit]" class="unit-select w-1/2 rounded-lg border-stone-300 text-sm bg-stone-50 text-stone-600 focus:border-amber-500 cursor-pointer">
                                            <option value="base">Normal</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="py-3 pr-2">
                                    <input type="number" name="items[0][cost]" step="0.01" min="0" placeholder="0.00" oninput="calculateTotal()" class="cost-input w-full rounded-lg border-stone-300 text-sm focus:border-amber-500 focus:ring-amber-200">
                                </td>
                                <td class="py-3 text-center">
                                    <button type="button" onclick="removeRow(this)" class="text-stone-400 hover:text-red-500 transition" title="Eliminar Fila">
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                
                <div class="flex-1">
                    @if($activeRegister)
                        <label class="flex items-start gap-3 cursor-pointer group p-3 border border-stone-200 rounded-xl hover:bg-stone-50 transition">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="checkbox" name="register_expense" id="register_expense" value="1" onchange="toggleExpenseDesc()" class="w-5 h-5 text-amber-600 rounded border-stone-300 focus:ring-amber-500 cursor-pointer">
                            </div>
                            <div>
                                <span class="block font-bold text-stone-800 text-sm">Registrar como Gasto en Caja</span>
                                <span class="block text-xs text-stone-500 mt-0.5">El total de esta compra se restará del dinero de la caja actual.</span>
                            </div>
                        </label>

                        <div id="expense-desc-container" class="mt-3 hidden">
                            <input type="text" name="expense_description" placeholder="Ej: Compra Chedrahui Factura #1023" class="w-full text-sm rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-200">
                        </div>
                    @else
                        <div class="p-3 bg-red-50 border border-red-100 rounded-xl">
                            <span class="block font-bold text-red-800 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Caja Cerrada
                            </span>
                            <span class="block text-xs text-red-600 mt-1">No puedes registrar esto como un gasto automático porque no hay un turno abierto.</span>
                        </div>
                    @endif
                </div>

                <div class="text-right bg-stone-50 px-6 py-4 rounded-xl border border-stone-200 w-full md:w-auto">
                    <p class="text-xs font-bold text-stone-500 uppercase tracking-widest mb-1">Costo Total</p>
                    <p class="text-3xl font-black text-amber-900" id="total-cost-display">$0.00</p>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-amber-800 hover:bg-amber-900 text-white font-bold text-lg py-3 px-8 rounded-xl shadow-lg transition">
                Guardar Compra
            </button>
        </div>
    </form>
</div>

<script>
    const ingredients = {!! json_encode($ingredients->map(function($i) { return ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit_measure]; })) !!};
    let rowCount = 1;

    function updateUnitOptions(selectElement) {
        const row = selectElement.closest('tr');
        const unitSelect = row.querySelector('.unit-select');
        const ingredientId = selectElement.value;

        if (!ingredientId) {
            unitSelect.innerHTML = '<option value="base">Normal</option>';
            return;
        }

        const ingredient = ingredients.find(i => i.id == ingredientId);
        if (!ingredient) return;

        let baseUnit = (ingredient.unit || '').toLowerCase().trim();
        baseUnit = baseUnit.replace(/[^a-z]/g, ''); 

        let optionsHtml = '';

        const isWeight = ['g', 'gr', 'gramos', 'gramo', 'kg', 'kilo', 'kilos'].includes(baseUnit);
        const isVolume = ['ml', 'mililitro', 'mililitros', 'l', 'litro', 'litros'].includes(baseUnit);

        if (isWeight) {
            const isKg = ['kg', 'kilo', 'kilos'].includes(baseUnit);
            optionsHtml += `<option value="g" ${!isKg ? 'selected' : ''}>Gramos (g)</option>`;
            optionsHtml += `<option value="kg" ${isKg ? 'selected' : ''}>Kilos (Kg)</option>`;
        }
        else if (isVolume) {
            const isL = ['l', 'litro', 'litros'].includes(baseUnit);
            optionsHtml += `<option value="ml" ${!isL ? 'selected' : ''}>Mililitros (ml)</option>`;
            optionsHtml += `<option value="l" ${isL ? 'selected' : ''}>Litros (L)</option>`;
        }
        else {
            const nombreUnidad = ingredient.unit ? ingredient.unit : 'Piezas';
            optionsHtml += `<option value="${baseUnit}" selected>${nombreUnidad}</option>`;
        }

        unitSelect.innerHTML = optionsHtml;
    }

    function addRow() {
        const tbody = document.getElementById('items-body');
        
        let optionsHtml = '<option value="">Selecciona insumo...</option>';
        ingredients.forEach(i => {
            optionsHtml += `<option value="${i.id}">${i.name} (${i.unit || 'Pz'})</option>`;
        });

        const newRow = document.createElement('tr');
        newRow.className = 'item-row border-b border-stone-100 last:border-0';
        newRow.dataset.index = rowCount;
        
        newRow.innerHTML = `
            <td class="py-3 pr-2">
                <select name="items[${rowCount}][ingredient_id]" required onchange="updateUnitOptions(this)" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-500 focus:ring-amber-200">
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-3 pr-2">
                <div class="flex gap-1">
                    <input type="number" name="items[${rowCount}][quantity]" required step="0.01" min="0.01" placeholder="Ej: 2.5" class="w-1/2 rounded-lg border-stone-300 text-sm focus:border-amber-500 focus:ring-amber-200">
                    <select name="items[${rowCount}][input_unit]" class="unit-select w-1/2 rounded-lg border-stone-300 text-sm bg-stone-50 text-stone-600 focus:border-amber-500 cursor-pointer">
                        <option value="base">Normal</option>
                    </select>
                </div>
            </td>
            <td class="py-3 pr-2">
                <input type="number" name="items[${rowCount}][cost]" step="0.01" min="0" placeholder="0.00" oninput="calculateTotal()" class="cost-input w-full rounded-lg border-stone-300 text-sm focus:border-amber-500 focus:ring-amber-200">
            </td>
            <td class="py-3 text-center">
                <button type="button" onclick="removeRow(this)" class="text-stone-400 hover:text-red-500 transition" title="Eliminar Fila">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        rowCount++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('tr').remove();
            calculateTotal();
        } else {
            Swal.fire('Atención', 'Debes ingresar al menos un producto.', 'warning');
        }
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.cost-input').forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) total += val;
        });
        document.getElementById('total-cost-display').innerText = '$' + total.toFixed(2);
    }

    function toggleExpenseDesc() {
        const checkbox = document.getElementById('register_expense');
        const container = document.getElementById('expense-desc-container');
        if (checkbox.checked) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    document.getElementById('bulk-form').addEventListener('submit', function(e) {
        const checkbox = document.getElementById('register_expense');
        let title = '¿Guardar Inventario?';
        let text = 'Se agregarán las cantidades indicadas a tu stock.';
        
        if(checkbox && checkbox.checked) {
            title = '¿Guardar Inventario y Gasto?';
            text = 'Se actualizará el stock y se descontará el dinero de la caja automáticamente.';
        }
        confirmarAccion(e, 'bulk-form', text);
    });
</script>
@endsection