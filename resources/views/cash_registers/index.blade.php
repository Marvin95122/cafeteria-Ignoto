@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- CABECERA --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="font-serif text-3xl font-bold text-amber-900">Control de Caja</h1>
            <p class="text-stone-500">Administra el flujo de efectivo y registra gastos operativos.</p>
        </div>
        @if($activeRegister)
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-green-200">
                <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                Caja Abierta
            </div>
        @else
            <div class="bg-red-100 text-red-800 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-red-200">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                Caja Cerrada
            </div>
        @endif
    </div>

    {{-- SI LA CAJA ESTÁ CERRADA: PANTALLA DE APERTURA --}}
    @if(!$activeRegister)
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-stone-200 max-w-md mx-auto text-center mt-10">
            <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-4xl">💰</span>
            </div>
            <h2 class="text-2xl font-bold text-stone-800 mb-2">Abrir Turno</h2>
            <p class="text-stone-500 mb-6 text-sm">Ingresa el fondo de caja (morralla) con el que vas a empezar el día para dar cambio.</p>
            
            <form action="{{ route('cash_registers.open') }}" method="POST">
                @csrf
                <div class="relative mb-6 text-left">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-stone-500 font-bold text-xl">$</span>
                    <input type="number" name="opening_amount" step="0.01" min="0" required placeholder="0.00"
                           class="w-full pl-10 pr-4 py-3 border-stone-300 rounded-xl text-xl font-bold focus:border-amber-500 focus:ring-amber-200">
                </div>
                <button type="submit" class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 rounded-xl shadow-md transition">
                    Abrir Caja Registradora
                </button>
            </form>
        </div>
    
    {{-- SI LA CAJA ESTÁ ABIERTA: DASHBOARD DE CAJA --}}
    @else
        
        {{-- Tarjetas de Resumen --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 border-l-4 border-l-blue-500">
                <p class="text-stone-500 text-sm font-bold">Fondo Inicial</p>
                <h3 class="text-2xl font-black text-stone-800">${{ number_format($activeRegister->opening_amount, 2) }}</h3>
                <p class="text-xs text-stone-400 mt-1">Apertura: {{ $activeRegister->opened_at->format('h:i A') }}</p>
            </div>
            
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 border-l-4 border-l-green-500">
                <p class="text-stone-500 text-sm font-bold">Ventas (Solo Efectivo)</p>
                <h3 class="text-2xl font-black text-green-700">+ ${{ number_format($stats['sales_cash'], 2) }}</h3>
                <p class="text-xs text-stone-400 mt-1">Ventas con Tarjeta: ${{ number_format($stats['sales_card'], 2) }}</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 border-l-4 border-l-red-500">
                <p class="text-stone-500 text-sm font-bold">Gastos (Retiros)</p>
                <h3 class="text-2xl font-black text-red-600">- ${{ number_format($stats['total_expenses'], 2) }}</h3>
                <p class="text-xs text-stone-400 mt-1">{{ $expenses->count() }} gastos registrados</p>
            </div>

            <div class="bg-amber-50 p-5 rounded-2xl shadow-md border border-amber-200 border-l-4 border-l-amber-600 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 text-6xl">💵</div>
                <p class="text-amber-800 text-sm font-bold">Efectivo Esperado en Cajón</p>
                <h3 class="text-3xl font-black text-amber-900 mt-1">${{ number_format($stats['expected_cash'], 2) }}</h3>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="flex gap-4 mb-8">
            <button onclick="document.getElementById('modal-expense').classList.remove('hidden')" 
                    class="bg-white border border-stone-300 text-stone-700 hover:bg-stone-50 font-bold py-3 px-6 rounded-xl shadow-sm transition flex items-center gap-2">
                <span>💸</span> Registrar Gasto
            </button>
            
            <button onclick="document.getElementById('modal-close').classList.remove('hidden')" 
                    class="bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-6 rounded-xl shadow-md transition flex items-center gap-2">
                <span>🔐</span> Hacer Corte de Caja (Cerrar)
            </button>
        </div>

        {{-- Tabla de Gastos del Turno --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden mb-8">
            <div class="bg-stone-50 px-6 py-4 border-b border-stone-200">
                <h3 class="font-bold text-stone-800">Gastos Registrados en este Turno</h3>
            </div>
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Hora</th>
                        <th class="px-6 py-3">Descripción</th>
                        <th class="px-6 py-3">Categoría</th>
                        <th class="px-6 py-3 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-stone-50">
                            <td class="px-6 py-3">{{ $expense->created_at->format('h:i A') }}</td>
                            <td class="px-6 py-3 font-medium text-stone-800">{{ $expense->description }}</td>
                            <td class="px-6 py-3"><span class="bg-stone-100 px-2 py-1 rounded text-xs">{{ $expense->category }}</span></td>
                            <td class="px-6 py-3 text-right text-red-600 font-bold">-${{ number_format($expense->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-stone-400 italic">No hay gastos registrados en este turno.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MODAL REGISTRAR GASTO --}}
        <div id="modal-expense" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
                    <h3 class="font-bold text-lg text-stone-800">Registrar Salida de Dinero</h3>
                    <button onclick="document.getElementById('modal-expense').classList.add('hidden')" class="text-stone-400 hover:text-red-500">✖</button>
                </div>
                <form action="{{ route('cash_registers.expense') }}" method="POST" class="p-5">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-stone-700 mb-1">Descripción (Ej: Compra de hielo)</label>
                        <input type="text" name="description" required class="w-full border-stone-300 rounded-lg focus:ring-amber-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-stone-700 mb-1">Monto retirado</label>
                        <input type="number" name="amount" step="0.01" min="1" required class="w-full border-stone-300 rounded-lg focus:ring-amber-500 text-red-600 font-bold">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-stone-700 mb-1">Categoría</label>
                        <select name="category" class="w-full border-stone-300 rounded-lg focus:ring-amber-500">
                            <option value="Insumos">Insumos urgentes</option>
                            <option value="Servicios">Pago de Servicios (Agua, Gas)</option>
                            <option value="Limpieza">Artículos de Limpieza</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-stone-800 hover:bg-stone-900 text-white font-bold py-3 rounded-xl">Registrar Gasto</button>
                </form>
            </div>
        </div>

        {{-- MODAL CERRAR CAJA --}}
        <div id="modal-close" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-stone-100 flex justify-between items-center bg-amber-50">
                    <h3 class="font-bold text-lg text-amber-900">Corte de Caja Final</h3>
                    <button onclick="document.getElementById('modal-close').classList.add('hidden')" class="text-stone-400 hover:text-red-500">✖</button>
                </div>
                <form action="{{ route('cash_registers.close') }}" method="POST" class="p-5">
                    @csrf
                    <input type="hidden" name="expected_amount" value="{{ $stats['expected_cash'] }}">
                    
                    <div class="bg-stone-100 p-4 rounded-lg text-center mb-6">
                        <p class="text-sm text-stone-500 mb-1">El sistema indica que debe haber:</p>
                        <p class="text-3xl font-black text-stone-800">${{ number_format($stats['expected_cash'], 2) }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-stone-700 mb-1">¿Cuánto dinero contaste FÍSICAMENTE en el cajón?</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-500 font-bold">$</span>
                            <input type="number" name="actual_amount" step="0.01" min="0" required 
                                   class="w-full pl-8 border-stone-300 rounded-lg focus:ring-amber-500 text-xl font-bold">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm text-stone-700 mb-1">Notas (Opcional - ¿Sobra o falta dinero?)</label>
                        <textarea name="notes" rows="2" class="w-full border-stone-300 rounded-lg focus:ring-amber-500"></textarea>
                    </div>
                    <button type="submit" onclick="return confirm('¿Estás seguro de cerrar la caja? Esta acción no se puede deshacer.')" class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 rounded-xl">Confirmar y Cerrar Caja</button>
                </form>
            </div>
        </div>

    @endif

</div>
@endsection