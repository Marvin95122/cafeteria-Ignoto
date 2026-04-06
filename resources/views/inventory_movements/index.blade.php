@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col h-full">
    
    {{-- ENCABEZADO Y BOTONES DE ACCIÓN --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold text-amber-900">Bitácora de Inventario</h1>
            <p class="text-stone-500 text-sm mt-1">Historial de entradas, compras y mermas.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openAdjustmentModal()" class="bg-white border-2 border-stone-200 text-stone-600 hover:bg-stone-50 hover:border-stone-300 font-bold py-2.5 px-4 rounded-xl transition flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                Ajuste Rápido
            </button>
            <a href="{{ route('inventory_movements.create_bulk') }}" class="bg-amber-800 hover:bg-amber-900 text-white font-bold py-2.5 px-5 rounded-xl transition flex items-center gap-2 shadow-md">
                <span class="text-xl leading-none">🛒</span> Ingresar Compra
            </a>
        </div>
    </div>

    {{-- FILTROS Y BUSCADOR --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex bg-stone-100 p-1 rounded-xl w-full md:w-auto" id="type-filters">
            <button onclick="filterByType('all', this)" class="filter-btn flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-bold bg-white text-stone-800 shadow-sm transition">Todos</button>
            <button onclick="filterByType('entrada', this)" class="filter-btn flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:text-stone-700 transition">Entradas</button>
            <button onclick="filterByType('merma', this)" class="filter-btn flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:text-stone-700 transition">Mermas</button>
            <button onclick="filterByType('venta', this)" class="filter-btn flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:text-stone-700 transition">Ventas POS</button>
        </div>
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">🔍</span>
            <input type="text" id="logSearch" placeholder="Buscar insumo..." onkeyup="filterTable()" class="w-full pl-10 border-stone-200 rounded-xl text-sm focus:border-amber-500 py-2.5 bg-stone-50">
        </div>
    </div>

    {{-- TABLA DE BITÁCORA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden flex-1 flex flex-col">
        <div class="overflow-y-auto custom-scrollbar flex-1">
            <table class="w-full text-left border-collapse">
                <thead class="bg-stone-50 sticky top-0 z-10">
                    <tr class="text-xs text-stone-500 uppercase tracking-wider border-b border-stone-200">
                        <th class="px-6 py-4 font-bold">Fecha / Hora</th>
                        <th class="px-6 py-4 font-bold">Insumo</th>
                        <th class="px-6 py-4 font-bold text-center">Tipo</th>
                        <th class="px-6 py-4 font-bold text-right">Cantidad</th>
                        <th class="px-6 py-4 font-bold">Motivo</th>
                        <th class="px-6 py-4 font-bold">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100" id="log-table-body">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-amber-50/30 transition log-row" data-name="{{ strtolower($movement->ingredient->name) }}" data-type="{{ strtolower($movement->type) }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-stone-800">{{ $movement->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-stone-400">{{ $movement->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center text-sm shadow-inner shrink-0">📦</div>
                                    <span class="text-sm font-bold text-stone-800">{{ $movement->ingredient->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($movement->type === 'entrada') <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">Entrada</span>
                                @elseif($movement->type === 'venta') <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">Venta POS</span>
                                @else <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">Merma</span> @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black {{ $movement->type === 'entrada' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $movement->type === 'entrada' ? '+' : '-' }}{{ floatval($movement->quantity) }} 
                                    <span class="text-xs font-normal text-stone-500">{{ $movement->ingredient->unit }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-stone-600 truncate max-w-[200px]" title="{{ $movement->reason }}">{{ $movement->reason }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold shrink-0">{{ substr($movement->user->name, 0, 1) }}</div>
                                    <span class="text-sm text-stone-600">{{ explode(' ', $movement->user->name)[0] }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-state"><td colspan="6" class="px-6 py-12 text-center text-stone-400"><span class="text-5xl block mb-3">📋</span><p class="text-lg font-medium">No hay movimientos registrados aún.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DE AJUSTE RÁPIDO (Mermas o Entradas chicas) --}}
<div id="adjustment-modal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 ease-out">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 ease-out" id="adjustment-modal-content">
        
        <div class="p-6 border-b border-stone-100 flex justify-between items-center bg-stone-50">
            <h3 class="font-serif font-bold text-2xl text-stone-800">Ajuste de Inventario</h3>
            <button onclick="closeAdjustmentModal()" class="text-stone-400 hover:text-red-500 transition text-3xl leading-none">&times;</button>
        </div>
        
        <form action="{{ route('inventory_movements.store') }}" method="POST" id="quick-adjust-form" class="p-6 space-y-5">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">Materia Prima</label>
                <input type="hidden" name="ingredient_id" id="quick-ingredient-id">
                <button type="button" onclick="openIngredientModal()" id="quick-ingredient-btn" class="w-full text-left bg-white border border-stone-300 rounded-xl px-4 py-3 text-sm text-stone-500 hover:bg-amber-50 hover:border-amber-400 transition flex justify-between items-center group">
                    <span class="flex items-center gap-2">🔍 Buscar insumo...</span>
                    <svg class="w-4 h-4 text-stone-400 group-hover:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">Tipo de Ajuste</label>
                    <select name="type" id="adjust-type" required onchange="updateReasonPlaceholder()" class="w-full rounded-xl border-stone-300 focus:border-amber-500 font-bold">
                        <option value="entrada" class="text-green-600">➕ Entrada (+)</option>
                        <option value="merma" class="text-red-600">➖ Merma (-)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">Cantidad</label>
                    <input type="number" name="quantity" required step="0.01" min="0.01" placeholder="0.00" class="w-full rounded-xl border-stone-300 focus:border-amber-500 font-bold text-lg">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">Motivo / Justificación</label>
                <input type="text" name="reason" id="adjust-reason" required placeholder="Ej: Ajuste por conteo físico" class="w-full rounded-xl border-stone-300 focus:border-amber-500 text-sm">
            </div>

            <div class="pt-4 border-t border-stone-100 flex gap-3">
                <button type="button" onclick="closeAdjustmentModal()" class="flex-1 py-3 text-stone-600 font-bold rounded-xl hover:bg-stone-200 transition">Cancelar</button>
                <button type="submit" class="flex-1 py-3 bg-stone-800 text-white font-bold rounded-xl hover:bg-black transition shadow-md">Guardar Ajuste</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BUSCADOR DE MATERIA PRIMA --}}
<div id="ingredient-modal" class="fixed inset-0 bg-stone-900/70 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300 ease-out">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col h-[70vh] transform scale-95 opacity-0 transition-all duration-300 ease-out" id="ingredient-modal-content">
        <div class="p-5 border-b border-stone-100 flex gap-4 items-center bg-amber-50/50">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-stone-400 text-xl">🔍</span>
                <input type="text" id="ingredient-search" onkeyup="filterIngredients()" placeholder="Buscar materia prima..." class="w-full pl-12 border-stone-200 rounded-xl text-lg focus:border-amber-500 py-3 shadow-sm">
            </div>
            <button type="button" onclick="closeIngredientModal()" class="text-stone-400 hover:text-red-500 transition text-4xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 custom-scrollbar bg-stone-50/50">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="ingredients-grid"></div>
        </div>
    </div>
</div>

<script>
    const ingredients = {!! json_encode($ingredients->map(function($i) { return ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit]; })) !!};

    //ODAL DE AJUSTE RÁPIDO
    function openAdjustmentModal() {
        const modal = document.getElementById('adjustment-modal');
        const content = document.getElementById('adjustment-modal-content');
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }

    function closeAdjustmentModal() {
        const modal = document.getElementById('adjustment-modal');
        const content = document.getElementById('adjustment-modal-content');
        modal.classList.add('opacity-0'); 
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function updateReasonPlaceholder() {
        const type = document.getElementById('adjust-type').value;
        const reasonInput = document.getElementById('adjust-reason');
        reasonInput.placeholder = type === 'merma' ? 'Ej: Caducidad, Producto derramado, etc.' : 'Ej: Ingreso individual, Devolución, etc.';
    }

    document.getElementById('quick-adjust-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('quick-ingredient-id').value;
        if(!id) {
            Swal.fire('Falta Insumo', 'Por favor usa el buscador para seleccionar la materia prima.', 'error');
            return;
        }
        const tipo = document.getElementById('adjust-type').value;
        let mensaje = tipo === 'merma' ? 'Se descontará este producto de tu stock (Merma).' : 'Se sumará este producto a tu stock.';
        confirmarAccion(e, 'quick-adjust-form', mensaje);
    });

    //BUSCADOR DE INSUMOS
    function openIngredientModal() {
        document.getElementById('ingredient-search').value = '';
        
        const modal = document.getElementById('ingredient-modal');
        const content = document.getElementById('ingredient-modal-content');
        
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
        setTimeout(() => {
            renderIngredientsGrid('');
            document.getElementById('ingredient-search').focus();
        }, 50);
    }

    function closeIngredientModal() {
        const modal = document.getElementById('ingredient-modal');
        const content = document.getElementById('ingredient-modal-content');
        modal.classList.add('opacity-0'); 
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function renderIngredientsGrid(search) {
        const grid = document.getElementById('ingredients-grid');
        grid.innerHTML = '';
        
        const term = search.toLowerCase().trim();
        const filtered = ingredients.filter(i => i.name.toLowerCase().includes(term));

        if(filtered.length === 0) {
            grid.innerHTML = '<div class="col-span-full text-center py-8"><span class="text-4xl block mb-2">🤷‍♂️</span><span class="text-stone-500">No se encontró insumo.</span></div>';
            return;
        }

        const fragment = document.createDocumentFragment();

        filtered.forEach(ing => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const safeName = ing.name.replace(/'/g, "\\'");
            btn.setAttribute('onclick', `selectIngredient(${ing.id}, '${safeName}', '${ing.unit || 'Pz'}')`);
            btn.className = 'bg-white border border-stone-200 p-4 rounded-xl hover:border-amber-400 hover:bg-amber-50 transition text-left flex items-center gap-3';
            
            btn.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center text-xl shadow-inner">📦</div>
                <div>
                    <div class="font-bold text-stone-800 text-sm">${ing.name}</div>
                    <div class="text-xs text-stone-400 mt-0.5 font-bold uppercase">${ing.unit || 'Pz'}</div>
                </div>
            `;
            fragment.appendChild(btn);
        });

        grid.appendChild(fragment);
    }

    function filterIngredients() { renderIngredientsGrid(document.getElementById('ingredient-search').value); }

    function selectIngredient(id, name, unit) {
        const btn = document.getElementById('quick-ingredient-btn');
        btn.innerHTML = `<div class="flex items-center gap-2"><span class="text-amber-700 text-lg">📦</span> <span class="font-bold text-stone-800">${name}</span></div><span class="text-xs font-bold text-stone-400 uppercase">${unit}</span>`;
        btn.classList.replace('text-stone-500', 'text-stone-800');
        document.getElementById('quick-ingredient-id').value = id;
        closeIngredientModal();
    }

    //FILTROS EN LA TABLA
    let currentFilterType = 'all';

    function filterByType(type, btnElement) {
        currentFilterType = type;
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-stone-800', 'shadow-sm');
            btn.classList.add('text-stone-500');
        });
        btnElement.classList.remove('text-stone-500');
        btnElement.classList.add('bg-white', 'text-stone-800', 'shadow-sm');
        applyFilters();
    }

    function filterTable() { applyFilters(); }

    function applyFilters() {
        const searchTerm = document.getElementById('logSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.log-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.name;
            const type = row.dataset.type;
            const matchesSearch = name.includes(searchTerm);
            const matchesType = currentFilterType === 'all' || type === currentFilterType;

            if (matchesSearch && matchesType) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyState = document.getElementById('empty-state');
        if(emptyState) {
            if(visibleCount === 0 && rows.length > 0) {
                emptyState.style.display = '';
                emptyState.innerHTML = '<td colspan="6" class="px-6 py-12 text-center text-stone-400"><span class="text-4xl block mb-2">🔍</span><p>No se encontraron movimientos con esos filtros.</p></td>';
            } else if (rows.length > 0) {
                emptyState.style.display = 'none';
            }
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const ingredientModal = document.getElementById('ingredient-modal');
            const adjustmentModal = document.getElementById('adjustment-modal');

            if (ingredientModal && !ingredientModal.classList.contains('hidden')) {
                closeIngredientModal();
            }
            else if (adjustmentModal && !adjustmentModal.classList.contains('hidden')) {
                closeAdjustmentModal();
            }
        }
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 10px; }
</style>
@endsection