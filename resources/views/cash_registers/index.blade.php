@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                💰 Administración de caja
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Control de Caja
            </h1>

            <p class="text-stone-500 mt-1">
                Administra apertura, gastos, ventas del turno, cancelaciones y cierre de caja.
            </p>
        </div>

        @if($activeRegister)
            <div class="bg-green-50 text-green-800 px-5 py-4 rounded-2xl font-bold flex items-center gap-3 border border-green-200 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse shrink-0"></span>

                <div>
                    <p class="text-sm">
                        Caja abierta
                    </p>
                    <p class="text-xs text-green-700 font-normal">
                        Apertura por {{ $activeRegister->user->name ?? 'Usuario' }}
                        · {{ $activeRegister->opened_at->format('d/m/Y h:i A') }}
                    </p>
                </div>
            </div>
        @else
            <div class="bg-red-50 text-red-800 px-5 py-4 rounded-2xl font-bold flex items-center gap-3 border border-red-200 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-red-500 shrink-0"></span>

                <div>
                    <p class="text-sm">
                        Caja cerrada
                    </p>
                    <p class="text-xs text-red-700 font-normal">
                        Abre un turno para comenzar a registrar ventas y gastos.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- SI LA CAJA ESTÁ CERRADA --}}
    @if(!$activeRegister)
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-stone-200 max-w-xl mx-auto text-center mt-10 mb-10">
            <div class="w-24 h-24 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <span class="text-5xl">💰</span>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold mb-4">
                Caja sin turno activo
            </div>

            <h2 class="text-3xl font-serif font-bold text-amber-900 mb-2">
                Abrir Turno de Caja
            </h2>

            <p class="text-stone-500 mb-6 text-sm max-w-md mx-auto">
                Ingresa el fondo inicial con el que comenzará el turno. Este monto servirá para calcular el efectivo esperado al cerrar caja.
            </p>
            
            <form action="{{ route('cash_registers.open') }}" method="POST" class="space-y-5">
                @csrf

                <div class="relative text-left">
                    <label class="block text-sm font-bold text-stone-700 mb-2">
                        Fondo inicial
                    </label>

                    <span class="absolute left-4 top-[42px] flex items-center text-stone-500 font-bold text-xl">$</span>

                    <input type="number"
                        name="opening_amount"
                        step="0.01"
                        min="0"
                        required
                        placeholder="0.00"
                        class="w-full pl-10 pr-4 py-4 border-stone-300 rounded-2xl text-2xl font-black focus:border-amber-500 focus:ring-amber-200 bg-stone-50">
                </div>

                <button type="submit"
                        class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold py-4 rounded-2xl shadow-md transition">
                    Abrir caja registradora
                </button>
            </form>

            <p class="text-xs text-stone-400 mt-5">
                Solo debe existir una caja abierta a la vez para mantener el control del turno.
            </p>
        </div>
    
    {{-- SI LA CAJA ESTÁ ABIERTA --}}
    @else
        
        {{-- ESTADO Y RESUMEN DEL TURNO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-stone-500">Fondo inicial</p>
                        <h3 class="text-2xl font-black text-stone-800 mt-1">
                            ${{ number_format($activeRegister->opening_amount, 2) }}
                        </h3>
                        <p class="text-xs text-stone-400 mt-1">
                            {{ $activeRegister->opened_at->format('h:i A') }}
                        </p>
                    </div>

                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl">
                        🏦
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-stone-500">Ventas efectivo</p>
                        <h3 class="text-2xl font-black text-green-600 mt-1">
                            +${{ number_format($stats['sales_cash'], 2) }}
                        </h3>
                        <p class="text-xs text-stone-400 mt-1">
                            {{ $stats['orders_count'] }} venta(s) activas
                        </p>
                    </div>

                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center text-2xl">
                        💵
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-stone-500">Pagos no efectivos</p>
                        <h3 class="text-2xl font-black text-blue-600 mt-1">
                            ${{ number_format($stats['sales_card'] + $stats['sales_points'], 2) }}
                        </h3>
                        <p class="text-xs text-stone-400 mt-1">
                            Tarjeta: ${{ number_format($stats['sales_card'], 2) }} · Puntos: ${{ number_format($stats['sales_points'], 2) }}
                        </p>
                    </div>

                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl">
                        💳
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-stone-500">Gastos activos</p>
                        <h3 class="text-2xl font-black text-red-600 mt-1">
                            -${{ number_format($stats['total_expenses'], 2) }}
                        </h3>
                        <p class="text-xs text-stone-400 mt-1">
                            {{ $stats['expenses_count'] }} activo(s), {{ $stats['cancelled_expenses_count'] }} anulado(s)
                        </p>
                    </div>

                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-700 flex items-center justify-center text-2xl">
                        💸
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-5 rounded-2xl shadow-md border border-amber-800 relative overflow-hidden text-white hover:shadow-lg transition">
                <div class="absolute right-[-6px] top-[-8px] text-6xl opacity-20">
                    💰
                </div>

                <p class="text-sm font-bold text-amber-200">
                    Efectivo esperado
                </p>

                <h3 class="text-3xl font-black mt-1">
                    ${{ number_format($stats['expected_cash'], 2) }}
                </h3>

                <p class="text-xs text-amber-200 mt-1">
                    Fondo + efectivo - gastos
                </p>
            </div>
        </div>

        {{-- ACCIONES PRINCIPALES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h3 class="font-bold text-stone-800 text-lg">
                    Acciones del turno
                </h3>
                <p class="text-sm text-stone-500 mt-1">
                    Registra gastos o realiza el corte cuando finalice el turno.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="document.getElementById('modal-expense').classList.remove('hidden')" 
                        class="inline-flex items-center justify-center gap-2 bg-white border border-stone-300 text-stone-700 hover:bg-stone-50 font-bold py-3 px-6 rounded-xl shadow-sm transition">
                    <span>💸</span>
                    Registrar gasto
                </button>
                
                <button onclick="document.getElementById('modal-close').classList.remove('hidden')" 
                        class="inline-flex items-center justify-center gap-2 bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-6 rounded-xl shadow-md transition">
                    <span>🔐</span>
                    Hacer corte de caja
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {{-- SECCIÓN: GASTOS DEL TURNO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-red-900 flex items-center gap-2">
                            💸 Gastos registrados
                        </h3>
                        <p class="text-xs text-red-700 mt-1">
                            Salidas de dinero durante el turno actual.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full bg-white border border-red-100 text-red-700 text-xs font-bold">
                            {{ $stats['expenses_count'] }} activo(s)
                        </span>

                        <span class="px-3 py-1 rounded-full bg-white border border-stone-200 text-stone-600 text-xs font-bold">
                            {{ $stats['cancelled_expenses_count'] }} anulado(s)
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-stone-50 border-b border-stone-200">
                            <tr class="text-xs text-stone-500 uppercase tracking-wider">
                                <th class="px-4 py-3 font-bold">Hora</th>
                                <th class="px-4 py-3 font-bold">Descripción</th>
                                <th class="px-4 py-3 font-bold">Categoría</th>
                                <th class="px-4 py-3 font-bold text-right">Monto</th>
                                <th class="px-4 py-3 font-bold text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($expenses as $expense)
                                <tr class="hover:bg-stone-50 transition {{ $expense->status === 'cancelado' ? 'bg-red-50/30' : '' }}">
                                    <td class="px-4 py-3 text-sm text-stone-500">{{ $expense->created_at->format('h:i A') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-bold {{ $expense->status === 'cancelado' ? 'text-red-700 line-through' : 'text-stone-800' }}">
                                            {{ $expense->description }}
                                        </div>
                                        
                                        {{-- NUEVO: Quién registró el gasto originalmente --}}
                                        <div class="text-xs text-stone-500 mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Registrado por: <span class="font-bold">{{ $expense->user->name ?? 'Usuario' }}</span>
                                        </div>
                                        
                                        {{-- NUEVO: Motivo de cancelación visible y elegante abajo --}}
                                        @if($expense->status === 'cancelado')
                                            <div class="mt-2 bg-red-100/50 p-2 rounded-lg border border-red-100 inline-block w-full">
                                                <div class="text-xs text-red-800 font-bold mb-0.5">
                                                    ⚠️ Anulado por {{ $expense->canceller->name ?? 'Admin' }}
                                                </div>

                                                <div class="text-xs text-red-600 italic">
                                                    "{{ $expense->cancellation_reason }}"
                                                </div>

                                                @if($expense->cancelled_at)
                                                    <div class="text-[10px] text-red-500 mt-1">
                                                        Fecha de anulación: {{ $expense->cancelled_at->format('d/m/Y h:i A') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-stone-100 text-stone-600">{{ $expense->category ?? 'Sin categoría' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-right {{ $expense->status === 'cancelado' ? 'text-stone-400 line-through' : 'text-red-600' }}">
                                        -${{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($expense->status === 'cancelado')
                                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">Cancelado</span>
                                        @else
                                            {{-- NUEVO: Botón "Cancelar" idéntico al de la tabla de ventas --}}
                                            <button onclick="deleteExpenseSecure({{ $expense->id }})" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 border border-red-200 transition shadow-sm">
                                                Cancelar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-stone-400 text-sm">No hay gastos registrados en este turno.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SECCION: VENTAS DEL TURNO --}}
            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
                <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-green-900 flex items-center gap-2">
                            🧾 Ventas registradas
                        </h3>
                        <p class="text-xs text-green-700 mt-1">
                            Tickets cobrados, cancelados y auditados durante el turno.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full bg-white border border-green-100 text-green-700 text-xs font-bold">
                            {{ $stats['orders_count'] }} activa(s)
                        </span>

                        <span class="px-3 py-1 rounded-full bg-white border border-red-100 text-red-700 text-xs font-bold">
                            {{ $stats['cancelled_orders_count'] }} cancelada(s)
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-[520px] overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white text-stone-500 uppercase text-xs sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3">Hora/Cajero</th>
                                <th class="px-4 py-3">Productos Vendidos</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200">
                            @forelse($orders as $order)
                                <tr class="hover:bg-stone-50 {{ $order->status === 'cancelado' ? 'bg-red-50/50 opacity-75' : '' }}">
                                    <td class="px-4 py-3">
                                        <span class="block font-bold {{ $order->status === 'cancelado' ? 'line-through text-red-500' : 'text-stone-800' }}">#{{ $order->id }} - {{ $order->created_at->format('h:i A') }}</span>
                                        <span class="block text-xs text-stone-400">Cobró: {{ $order->user->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <ul class="list-disc list-inside text-xs space-y-1 mb-1">
                                            @foreach($order->items as $item)
                                                <li>{{ $item->quantity }}x {{ $item->product->name ?? 'Producto borrado' }}</li>
                                            @endforeach
                                        </ul>
                                        @if($order->status === 'cancelado')
                                            <div class="mt-2 text-xs text-red-700 bg-red-100 p-2 rounded border border-red-200 space-y-1">
                                                <div>
                                                    <strong>Motivo:</strong> {{ $order->cancellation_reason }}
                                                </div>

                                                <div>
                                                    <strong>Canceló:</strong> {{ $order->canceller->name ?? 'Usuario no disponible' }}
                                                </div>

                                                @if($order->cancelled_at)
                                                    <div>
                                                        <strong>Fecha:</strong> {{ $order->cancelled_at->format('d/m/Y h:i A') }}
                                                    </div>
                                                @endif

                                                <div class="mt-2">
                                                    @if($order->cancellation_action === 'devolver')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">
                                                            Insumos devueltos al inventario
                                                        </span>
                                                    @elseif($order->cancellation_action === 'merma')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 border border-orange-200">
                                                            Insumos registrados como merma
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-stone-100 text-stone-600 border border-stone-200">
                                                            Acción no registrada
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="block font-bold text-stone-800">
                                            ${{ number_format($order->total, 2) }}
                                        </span>

                                        <span class="text-[10px] font-bold uppercase {{ $order->payment_method == 'efectivo' ? 'text-green-600' : 'text-blue-600' }}">
                                            {{ $order->payment_method }}
                                        </span>

                                        @if($order->payment_method === 'efectivo')
                                            <div class="text-[10px] text-stone-400 mt-1 leading-tight">
                                                Recibido: ${{ number_format($order->cash_received ?? $order->total, 2) }}<br>
                                                Cambio: ${{ number_format($order->cash_change ?? 0, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col sm:flex-row justify-center items-center gap-2">
                                            <a href="{{ route('pos.ticket', $order) }}?reprint=1"
                                            target="_blank"
                                            class="text-xs bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold py-1 px-3 rounded transition border border-amber-200">
                                                Reimprimir
                                            </a>

                                            @if($order->status === 'completado')
                                                <button onclick="openCancelModal({{ $order->id }})"
                                                        class="text-xs bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold py-1 px-3 rounded transition border border-red-200 hover:border-red-600">
                                                    Cancelar
                                                </button>
                                            @else
                                                <span class="text-xs font-bold text-red-500">
                                                    CANCELADO ❌
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-stone-400 italic">No hay ventas en este turno.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MODALES DE GASTO Y CIERRE --}}
        <div id="modal-expense" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
                    <h3 class="font-bold text-lg text-stone-800">Registrar Salida de Dinero</h3>
                    <button onclick="document.getElementById('modal-expense').classList.add('hidden')" class="text-stone-400 hover:text-red-500">✖</button>
                </div>
                <form action="{{ route('cash_registers.expense') }}" method="POST" class="p-5">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-stone-700 mb-1">Descripción</label>
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
                            <option value="Servicios">Pago de Servicios</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-stone-800 hover:bg-stone-900 text-white font-bold py-3 rounded-xl">Registrar Gasto</button>
                </form>
            </div>
        </div>

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
                            <input type="number" name="actual_amount" step="0.01" min="0" required class="w-full pl-8 border-stone-300 rounded-lg focus:ring-amber-500 text-xl font-bold">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm text-stone-700 mb-1">Notas (Opcional - ¿Sobra o falta dinero?)</label>
                        <textarea name="notes" rows="2" class="w-full border-stone-300 rounded-lg focus:ring-amber-500"></textarea>
                    </div>
                    <button type="submit" onclick="return confirm('¿Seguro de cerrar la caja?')" class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 rounded-xl">Confirmar y Cerrar Caja</button>
                </form>
            </div>
        </div>
    @endif

    {{-- HISTORIAL DE CORTES PASADOS (Siempre visible para el Gerente) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden mt-8">
        <div class="bg-stone-800 px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-white text-lg flex items-center gap-2">
                <span>📂</span> Historial de Cortes de Caja (Últimos 10)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-stone-600">
                <thead class="bg-stone-100 text-stone-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Fecha / Responsables</th>
                        <th class="px-6 py-4">Fondo Inicial</th>
                        <th class="px-6 py-4">Esperado</th>
                        <th class="px-6 py-4">Físico</th>
                        <th class="px-6 py-4">Diferencia</th>
                        <th class="px-6 py-4">Notas</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-200">
                    @forelse($history as $reg)
                        @php
                            $diferencia = $reg->difference_amount ?? ($reg->actual_amount - $reg->expected_amount);
                        @endphp

                        <tr class="hover:bg-stone-50">
                            <td class="px-6 py-4">
                                <span class="block font-bold text-stone-800">
                                    {{ $reg->opened_at->format('d M, Y') }}
                                </span>

                                <span class="block text-xs text-stone-500">
                                    🟢 Abrió: {{ $reg->user->name ?? 'Usuario no disponible' }}
                                </span>

                                <span class="block text-xs text-stone-500">
                                    🔒 Cerró: {{ $reg->closedBy->name ?? 'No registrado' }}
                                </span>

                                <span class="block text-xs text-stone-400 mt-1">
                                    {{ $reg->opened_at->format('h:i A') }}
                                    -
                                    {{ $reg->closed_at ? $reg->closed_at->format('h:i A') : 'Sin cierre' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-stone-500">
                                ${{ number_format($reg->opening_amount, 2) }}
                            </td>

                            <td class="px-6 py-4 font-bold text-stone-800">
                                ${{ number_format($reg->expected_amount, 2) }}
                            </td>

                            <td class="px-6 py-4 font-bold text-blue-700">
                                ${{ number_format($reg->actual_amount, 2) }}
                            </td>

                            <td class="px-6 py-4">
                                @if($diferencia == 0)
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">
                                        Cuadró Exacto
                                    </span>
                                @elseif($diferencia > 0)
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">
                                        Sobró +${{ number_format($diferencia, 2) }}
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">
                                        Faltó -${{ number_format(abs($diferencia), 2) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs italic text-stone-500 max-w-xs">
                                {{ $reg->notes ?? 'Sin novedades' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-stone-400 italic">
                                No hay historial de cajas cerradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CANCELAR TICKET --}}
        <div id="modal-cancel-order" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border-2 border-red-500">
                <div class="p-5 border-b border-red-100 flex justify-between items-center bg-red-50">
                    <h3 class="font-bold text-lg text-red-900 flex items-center gap-2"><span>⚠️</span> Cancelar Ticket #<span id="cancel-order-id-display"></span></h3>
                    <button onclick="document.getElementById('modal-cancel-order').classList.add('hidden')" class="text-stone-400 hover:text-red-500">✖</button>
                </div>
                <form id="cancel-order-form" method="POST" class="p-5">
                    @csrf
                    <p class="text-sm text-stone-600 mb-4 bg-stone-50 p-3 rounded-lg border border-stone-200">
                        Al cancelar este ticket, <strong>la venta dejará de contar en la caja</strong>. Selecciona si los insumos regresarán al inventario o si quedarán registrados como merma:
                    </p>

                    {{-- Opciones de Decisión (Devolver o Mermar) --}}
                    <div class="mb-5 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer p-3 border border-stone-200 rounded-xl hover:bg-stone-50 transition">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="radio" name="action_type" value="devolver" required class="w-5 h-5 text-amber-600 focus:ring-amber-500 cursor-pointer">
                            </div>
                            <div>
                                <span class="block font-bold text-stone-800 text-sm">Error de Cobro (Devolver)</span>
                                <span class="block text-xs text-stone-500 mt-0.5">La bebida no se preparó. Los insumos regresarán al inventario.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer p-3 border border-stone-200 rounded-xl hover:bg-red-50 transition">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="radio" name="action_type" value="merma" required class="w-5 h-5 text-red-600 focus:ring-red-500 cursor-pointer">
                            </div>
                            <div>
                                <span class="block font-bold text-red-800 text-sm">Bebida Desperdiciada (Merma)</span>
                                <span class="block text-xs text-red-600 mt-0.5">El café se preparó. No regresa al stock y se anota como merma en la bitácora.</span>
                            </div>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-stone-700 mb-2">Motivo de cancelación (Obligatorio)</label>
                        <textarea name="cancellation_reason" rows="2" required placeholder="Ej: Se cobró mal / El cliente no quiso esperar" class="w-full border-stone-300 rounded-lg focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-cancel-order').classList.add('hidden')" class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold py-3 rounded-xl transition">Volver</button>
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl shadow-md transition">Confirmar Anulación</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openCancelModal(orderId) {
                document.getElementById('cancel-order-id-display').innerText = orderId;
                // Le indicamos al formulario a qué ruta (con qué ID) debe mandar los datos
                document.getElementById('cancel-order-form').action = `/caja/venta/${orderId}/cancelar`;
                document.getElementById('modal-cancel-order').classList.remove('hidden');
            }
        </script>
        
        <script>
            function deleteExpenseSecure(expenseId) {
                Swal.fire({
                    title: '🔐 Anular Gasto',
                    html: `
                        <p class="text-sm text-stone-500 mb-4">Ingresa tu contraseña de Administrador y el motivo para anular este gasto de la caja.</p>
                        <input type="password" id="swal-password" class="w-full border-stone-300 rounded-lg mb-3 focus:border-red-500 focus:ring-red-200" placeholder="Contraseña de autorización...">
                        <textarea id="swal-reason" rows="2" class="w-full border-stone-300 rounded-lg focus:border-red-500 focus:ring-red-200" placeholder="Motivo de la anulación..."></textarea>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Confirmar y Anular',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const password = document.getElementById('swal-password').value;
                        const reason = document.getElementById('swal-reason').value;
                        if (!password || !reason) {
                            Swal.showValidationMessage('La contraseña y el motivo son obligatorios');
                        }
                        return { password: password, reason: reason };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/caja/gasto/${expenseId}`;

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        form.innerHTML = `
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="admin_password" value="${result.value.password}">
                            <input type="hidden" name="cancellation_reason" value="${result.value.reason}">
                        `;

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        </script>
</div>
@endsection