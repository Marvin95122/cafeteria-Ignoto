@extends('layouts.admin')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">
    
    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📋 Control de movimientos
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Bitácora de Inventario
            </h1>

            <p class="text-stone-500 mt-1">
                Consulta entradas, compras, ventas POS, devoluciones y mermas registradas en almacén.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="openAdjustmentModal()"
                    class="inline-flex items-center justify-center gap-2 bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 hover:border-amber-300 font-bold py-3 px-5 rounded-xl transition shadow-sm">
                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                    </path>
                </svg>
                Ajuste Rápido
            </button>

            <a href="{{ route('inventory_movements.create_bulk') }}"
               class="inline-flex items-center justify-center gap-2 bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-5 rounded-xl transition shadow-md">
                <span class="text-xl leading-none">🛒</span>
                Ingresar Compra
            </a>
        </div>
    </div>

    {{-- TARJETAS RESUMEN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Total movimientos</p>
                    <h3 class="text-3xl font-black text-stone-800 mt-1">
                        {{ $totalMovements }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-2xl">
                    📋
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Entradas</p>
                    <h3 class="text-3xl font-black text-green-600 mt-1">
                        {{ $entryMovements }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-2xl">
                    ➕
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Ventas POS</p>
                    <h3 class="text-3xl font-black text-blue-600 mt-1">
                        {{ $saleMovements }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl">
                    🧾
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Mermas</p>
                    <h3 class="text-3xl font-black text-red-600 mt-1">
                        {{ $lossMovements }}
                    </h3>
                </div>

                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-2xl">
                    ⚠️
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">

        {{-- PESTAÑAS --}}
        <div class="px-4 pt-4">
            @php
                $currentType = request('type', 'all');
            @endphp

            <div class="flex flex-wrap gap-2 bg-stone-100 p-1 rounded-xl w-full">
                <a href="{{ route('inventory_movements.index', array_merge(request()->except('page'), ['type' => 'all'])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-bold transition
                   {{ $currentType === 'all' ? 'bg-white text-stone-800 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">
                    Todos
                </a>

                <a href="{{ route('inventory_movements.index', array_merge(request()->except('page'), ['type' => 'entrada'])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-bold transition
                   {{ $currentType === 'entrada' ? 'bg-white text-green-700 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">
                    Entradas
                </a>

                <a href="{{ route('inventory_movements.index', array_merge(request()->except('page'), ['type' => 'merma'])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-bold transition
                   {{ $currentType === 'merma' ? 'bg-white text-red-700 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">
                    Mermas
                </a>

                <a href="{{ route('inventory_movements.index', array_merge(request()->except('page'), ['type' => 'venta'])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-bold transition
                   {{ $currentType === 'venta' ? 'bg-white text-blue-700 shadow-sm' : 'text-stone-500 hover:text-stone-700' }}">
                    Ventas POS
                </a>
            </div>
        </div>

        {{-- BUSCADOR Y FILTROS SECUNDARIOS --}}
        <form method="GET"
              action="{{ route('inventory_movements.index') }}"
              class="p-4 flex flex-col xl:flex-row gap-4 items-center border-b border-stone-100">

            <input type="hidden" name="type" value="{{ request('type', 'all') }}">

            <div class="relative w-full xl:flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                    🔍
                </span>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar por insumo, motivo o usuario..."
                       class="w-full pl-10 border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-200 py-3 bg-stone-50">
            </div>

            <select name="period"
                    onchange="this.form.submit()"
                    class="w-full xl:w-52 border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-200 py-3 bg-stone-50">
                <option value="">Todos los periodos</option>
                <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Hoy</option>
                <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Esta semana</option>
                <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Este mes</option>
            </select>

            <select name="per_page"
                    onchange="this.form.submit()"
                    class="w-full xl:w-44 border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-200 py-3 bg-stone-50">
                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10 por página</option>
                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 por página</option>
                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 por página</option>
                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 por página</option>
            </select>

            <button type="submit"
                    class="w-full xl:w-auto px-5 py-3 rounded-xl bg-amber-800 text-white text-sm font-bold hover:bg-amber-900 transition">
                Buscar
            </button>
        </form>

        @if(request('search') || request('period') || (request('type') && request('type') !== 'all') || request('per_page'))
            <div class="px-4 py-3 bg-stone-50 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-stone-500">
                    Mostrando movimientos filtrados.
                </p>

                <a href="{{ route('inventory_movements.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 text-sm font-bold hover:bg-stone-100 transition">
                    Limpiar filtros
                </a>
            </div>
        @endif

        {{-- TABLA --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-amber-50 text-amber-900 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-6 py-4 font-bold">Fecha / Hora</th>
                        <th class="px-6 py-4 font-bold">Insumo</th>
                        <th class="px-6 py-4 font-bold text-center">Tipo</th>
                        <th class="px-6 py-4 font-bold text-right">Cantidad</th>
                        <th class="px-6 py-4 font-bold">Motivo</th>
                        <th class="px-6 py-4 font-bold">Usuario</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($movements as $movement)
                        @php
                            $ingredientName = $movement->ingredient->name ?? 'Insumo no disponible';
                            $ingredientUnit = $movement->ingredient->unit ?? '';
                            $userName = $movement->user->name ?? 'Usuario no disponible';
                        @endphp

                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-stone-800">
                                    {{ $movement->created_at->format('d M Y') }}
                                </div>

                                <div class="text-xs text-stone-400">
                                    {{ $movement->created_at->format('h:i A') }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-stone-100 flex items-center justify-center text-sm shadow-inner shrink-0">
                                        📦
                                    </div>

                                    <div>
                                        <span class="text-sm font-bold text-stone-800 block">
                                            {{ $ingredientName }}
                                        </span>

                                        <span class="text-xs text-stone-400 uppercase">
                                            {{ $ingredientUnit }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($movement->type === 'entrada')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                        Entrada
                                    </span>
                                @elseif($movement->type === 'venta')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                        Venta POS
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                        Merma
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black {{ $movement->type === 'entrada' ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $movement->type === 'entrada' ? '+' : '-' }}{{ floatval($movement->quantity) }}
                                    <span class="text-xs font-normal text-stone-500">
                                        {{ $ingredientUnit }}
                                    </span>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="max-w-xs">
                                    <p class="text-sm text-stone-700 truncate" title="{{ $movement->reason }}">
                                        {{ $movement->reason }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ substr($userName, 0, 1) }}
                                    </div>

                                    <span class="text-sm text-stone-600">
                                        {{ explode(' ', $userName)[0] }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-stone-400">
                                <span class="text-5xl block mb-3">📋</span>

                                <p class="text-lg font-bold text-stone-600">
                                    No se encontraron movimientos.
                                </p>

                                <p class="text-sm text-stone-400 mt-1">
                                    Prueba limpiar filtros o registrar un nuevo ajuste.
                                </p>

                                <a href="{{ route('inventory_movements.index') }}"
                                   class="inline-flex mt-4 px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                                    Limpiar filtros
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="p-4 border-t border-stone-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <p class="text-sm text-stone-500">
                Mostrando {{ $movements->firstItem() ?? 0 }} a {{ $movements->lastItem() ?? 0 }}
                de {{ $movements->total() }} movimiento(s).
            </p>

            <div>
                {{ $movements->links() }}
            </div>
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

                <button type="button"
                        onclick="openIngredientModal()"
                        id="quick-ingredient-btn"
                        class="w-full text-left bg-white border border-stone-300 rounded-xl px-4 py-3 text-sm text-stone-500 hover:bg-amber-50 hover:border-amber-400 transition flex justify-between items-center group">
                    <span class="flex items-center gap-2">🔍 Buscar insumo...</span>
                    <svg class="w-4 h-4 text-stone-400 group-hover:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">Tipo de Ajuste</label>

                    <select name="type"
                            id="adjust-type"
                            required
                            onchange="updateReasonPlaceholder()"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 font-bold">
                        <option value="entrada" class="text-green-600">➕ Entrada (+)</option>
                        <option value="merma" class="text-red-600">➖ Merma (-)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">Cantidad</label>

                    <input type="number"
                        name="quantity"
                        required
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        class="w-full rounded-xl border-stone-300 focus:border-amber-500 font-bold text-lg">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-stone-700 mb-2">Motivo / Justificación</label>

                <input type="text"
                    name="reason"
                    id="adjust-reason"
                    required
                    placeholder="Ej: Ajuste por conteo físico"
                    class="w-full rounded-xl border-stone-300 focus:border-amber-500 text-sm">
            </div>

            {{-- GASTO EN CAJA --}}
            <div id="expense-option-section" class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
                @if($activeRegister)
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox"
                            name="register_expense"
                            value="1"
                            id="register-expense-checkbox"
                            onchange="toggleExpenseInputs()"
                            class="mt-1 rounded border-stone-300 text-amber-700 focus:ring-amber-600">

                        <div>
                            <span class="block text-sm font-bold text-amber-900">
                                Registrar también como gasto en caja
                            </span>

                            <span class="block text-xs text-amber-700 mt-1">
                                Úsalo cuando esta entrada corresponde a una compra pagada desde la caja abierta.
                            </span>
                        </div>
                    </label>

                    <div id="expense-details" class="hidden mt-4 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">
                                Monto del gasto ($)
                            </label>

                            <input type="number"
                                name="expense_amount"
                                id="expense-amount"
                                step="0.01"
                                min="0.01"
                                disabled
                                class="expense-input w-full rounded-xl border-stone-300 focus:border-amber-500 text-sm"
                                placeholder="Ej. 150.00">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1">
                                Descripción del gasto
                            </label>

                            <input type="text"
                                name="expense_description"
                                id="expense-description"
                                disabled
                                class="expense-input w-full rounded-xl border-stone-300 focus:border-amber-500 text-sm"
                                placeholder="Ej. Compra rápida de leche o café">
                        </div>
                    </div>
                @else
                    <div class="text-sm text-amber-800">
                        <p class="font-bold">No hay caja abierta.</p>
                        <p class="text-xs mt-1">
                            Puedes registrar el movimiento de inventario, pero no se podrá cargar como gasto hasta abrir caja.
                        </p>
                    </div>
                @endif
            </div>

            <div class="pt-4 border-t border-stone-100 flex gap-3">
                <button type="button"
                        onclick="closeAdjustmentModal()"
                        class="flex-1 py-3 text-stone-600 font-bold rounded-xl hover:bg-stone-200 transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="flex-1 py-3 bg-stone-800 text-white font-bold rounded-xl hover:bg-black transition shadow-md">
                    Guardar Ajuste
                </button>
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

        reasonInput.placeholder = type === 'merma'
            ? 'Ej: Caducidad, producto derramado, error de preparación, etc.'
            : 'Ej: Ingreso individual, devolución, compra rápida, ajuste por conteo físico, etc.';

        toggleExpenseSection();
    }

    function toggleExpenseSection() {
        const type = document.getElementById('adjust-type').value;
        const section = document.getElementById('expense-option-section');
        const checkbox = document.getElementById('register-expense-checkbox');

        if (!section) {
            return;
        }

        if (type === 'entrada') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');

            if (checkbox) {
                checkbox.checked = false;
            }

            toggleExpenseInputs();
        }
    }

    function toggleExpenseInputs() {
        const checkbox = document.getElementById('register-expense-checkbox');
        const details = document.getElementById('expense-details');
        const inputs = document.querySelectorAll('.expense-input');

        if (!checkbox || !details) {
            return;
        }

        if (checkbox.checked) {
            details.classList.remove('hidden');

            inputs.forEach(input => {
                input.disabled = false;
            });
        } else {
            details.classList.add('hidden');

            inputs.forEach(input => {
                input.disabled = true;
                input.value = '';
            });
        }
    }

    document.getElementById('quick-adjust-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('quick-ingredient-id').value;

        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Falta insumo',
                text: 'Por favor usa el buscador para seleccionar la materia prima.',
                confirmButtonColor: '#b45309'
            });
            return;
        }

        const tipo = document.getElementById('adjust-type').value;
        const registerExpense = document.getElementById('register-expense-checkbox');
        const expenseAmount = document.getElementById('expense-amount');

        if (registerExpense && registerExpense.checked) {
            if (!expenseAmount || !expenseAmount.value || parseFloat(expenseAmount.value) <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Monto requerido',
                    text: 'Ingresa el monto del gasto para registrarlo en caja.',
                    confirmButtonColor: '#b45309'
                });
                return;
            }
        }

        let mensaje = tipo === 'merma'
            ? 'Se descontará esta cantidad del stock como merma.'
            : 'Se sumará esta cantidad al stock del insumo.';

        if (registerExpense && registerExpense.checked) {
            mensaje += ' Además, se registrará un gasto en caja.';
        }

        Swal.fire({
            title: 'Confirmar ajuste',
            text: mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#92400e',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, guardar ajuste',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        toggleExpenseSection();
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