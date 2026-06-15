@extends('layouts.admin')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📂 Detalle de corte
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Corte #{{ str_pad($cashRegister->id, 5, '0', STR_PAD_LEFT) }}
            </h1>

            <p class="text-stone-500 mt-1">
                Revisión completa de ventas, gastos, cancelaciones y efectivo del turno.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('cash_registers.index') }}"
               class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white border border-stone-300 text-stone-700 font-bold hover:bg-stone-50 transition">
                ← Volver a caja
            </a>

            @if(auth()->user()->role === 'admin')
                <span class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-stone-900 text-white font-bold">
                    Modo administrador
                </span>
            @endif
        </div>
    </div>

    {{-- ESTADO DEL CORTE --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="p-5 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $cashRegister->status === 'abierta' ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-700' }} flex items-center justify-center text-2xl">
                    {{ $cashRegister->status === 'abierta' ? '💰' : '🔒' }}
                </div>

                <div>
                    <h2 class="font-black text-stone-800 text-lg">
                        {{ $cashRegister->status === 'abierta' ? 'Caja abierta' : 'Caja cerrada' }}
                    </h2>

                    <p class="text-sm text-stone-500">
                        Apertura:
                        <span class="font-bold">{{ $cashRegister->opened_at->format('d/m/Y h:i A') }}</span>
                        por
                        <span class="font-bold">{{ $cashRegister->user->name ?? 'Usuario no disponible' }}</span>
                    </p>

                    <p class="text-sm text-stone-500">
                        Cierre:
                        @if($cashRegister->closed_at)
                            <span class="font-bold">{{ $cashRegister->closed_at->format('d/m/Y h:i A') }}</span>
                            por
                            <span class="font-bold">{{ $cashRegister->closedBy->name ?? 'No registrado' }}</span>
                        @else
                            <span class="font-bold text-green-700">Aún no se ha cerrado</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-100 rounded-2xl px-4 py-3">
                <p class="text-xs font-black text-amber-700 uppercase tracking-wide">
                    Periodo revisado
                </p>

                <p class="text-sm font-bold text-amber-900">
                    {{ $start->format('d/m/Y h:i A') }}
                    -
                    {{ $cashRegister->closed_at ? $end->format('d/m/Y h:i A') : 'Actualidad' }}
                </p>
            </div>
        </div>
    </div>

    {{-- RESUMEN FINANCIERO --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-5">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Fondo inicial</p>
            <h3 class="text-2xl font-black text-stone-800 mt-1">
                ${{ number_format($cashRegister->opening_amount, 2) }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Ventas efectivo</p>
            <h3 class="text-2xl font-black text-green-600 mt-1">
                ${{ number_format($stats['sales_cash'], 2) }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Tarjeta / puntos</p>
            <h3 class="text-2xl font-black text-blue-600 mt-1">
                ${{ number_format($stats['sales_card'] + $stats['sales_points'], 2) }}
            </h3>

            <p class="text-xs text-stone-400 mt-1">
                Tarjeta ${{ number_format($stats['sales_card'], 2) }}
                · Puntos ${{ number_format($stats['sales_points'], 2) }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Gastos activos</p>
            <h3 class="text-2xl font-black text-red-600 mt-1">
                ${{ number_format($stats['total_expenses'], 2) }}
            </h3>
        </div>

        <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-5 rounded-2xl shadow-md border border-amber-800 text-white">
            <p class="text-sm font-bold text-amber-200">Efectivo esperado</p>
            <h3 class="text-2xl font-black mt-1">
                ${{ number_format($stats['expected_cash'], 2) }}
            </h3>

            <p class="text-xs text-amber-200 mt-1">
                Fondo + efectivo - gastos
            </p>
        </div>
    </div>

    {{-- AUDITORÍA DE CIERRE --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Efectivo contado</p>

            <h3 class="text-2xl font-black text-blue-700 mt-1">
                {{ $stats['actual_cash'] !== null ? '$' . number_format($stats['actual_cash'], 2) : 'Pendiente' }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Diferencia</p>

            @if($stats['difference'] === null)
                <h3 class="text-2xl font-black text-stone-500 mt-1">
                    Pendiente
                </h3>
            @elseif($stats['difference'] == 0)
                <h3 class="text-2xl font-black text-green-600 mt-1">
                    Cuadró exacto
                </h3>
            @elseif($stats['difference'] > 0)
                <h3 class="text-2xl font-black text-blue-600 mt-1">
                    Sobraron ${{ number_format($stats['difference'], 2) }}
                </h3>
            @else
                <h3 class="text-2xl font-black text-red-600 mt-1">
                    Faltaron ${{ number_format(abs($stats['difference']), 2) }}
                </h3>
            @endif
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Tickets del corte</p>

            <h3 class="text-2xl font-black text-stone-800 mt-1">
                {{ $stats['total_tickets'] }}
            </h3>

            <p class="text-xs text-stone-400 mt-1">
                {{ $stats['orders_count'] }} activos · {{ $stats['cancelled_orders_count'] }} cancelados
            </p>
        </div>
    </div>

    {{-- NOTAS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
        <h3 class="font-bold text-stone-800 mb-2">
            Notas del cierre
        </h3>

        <p class="text-sm text-stone-500">
            {{ $cashRegister->notes ?: 'Sin notas registradas en este corte.' }}
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- GASTOS DEL CORTE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-red-900 flex items-center gap-2">
                        💸 Gastos del corte
                    </h3>

                    <p class="text-xs text-red-700 mt-1">
                        Salidas registradas durante este turno.
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full bg-white border border-red-100 text-red-700 text-xs font-bold">
                    {{ $stats['expenses_count'] }} activo(s)
                </span>
            </div>

            <div class="overflow-x-auto max-h-[560px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white text-stone-500 uppercase text-xs sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3">Hora</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3">Categoría</th>
                            <th class="px-4 py-3 text-right">Monto</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-stone-50 {{ $expense->status === 'cancelado' ? 'bg-red-50/40' : '' }}">
                                <td class="px-4 py-3 text-stone-500">
                                    {{ $expense->created_at->format('h:i A') }}
                                </td>

                                <td class="px-4 py-3">
                                    <p class="font-bold {{ $expense->status === 'cancelado' ? 'text-red-700 line-through' : 'text-stone-800' }}">
                                        {{ $expense->description }}
                                    </p>

                                    <p class="text-xs text-stone-400">
                                        Registrado por {{ $expense->user->name ?? 'Usuario' }}
                                    </p>

                                    @if($expense->status === 'cancelado')
                                        <div class="mt-2 text-xs text-red-700 bg-red-100 p-2 rounded border border-red-200">
                                            <p>
                                                <strong>Anulado por:</strong>
                                                {{ $expense->canceller->name ?? 'Usuario no disponible' }}
                                            </p>

                                            <p>
                                                <strong>Motivo:</strong>
                                                {{ $expense->cancellation_reason }}
                                            </p>

                                            @if($expense->cancelled_at)
                                                <p>
                                                    <strong>Fecha:</strong>
                                                    {{ $expense->cancelled_at->format('d/m/Y h:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-stone-100 text-stone-600">
                                        {{ $expense->category ?? 'Sin categoría' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right font-bold {{ $expense->status === 'cancelado' ? 'text-stone-400 line-through' : 'text-red-600' }}">
                                    ${{ number_format($expense->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-stone-400 italic">
                                    No hubo gastos en este corte.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- VENTAS DEL CORTE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-green-900 flex items-center gap-2">
                        🧾 Tickets del corte
                    </h3>

                    <p class="text-xs text-green-700 mt-1">
                        Ventas cobradas, reimpresiones y cancelaciones del turno.
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full bg-white border border-green-100 text-green-700 text-xs font-bold">
                    {{ $stats['orders_count'] }} activo(s)
                </span>
            </div>

            <div class="overflow-x-auto max-h-[560px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white text-stone-500 uppercase text-xs sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3">Ticket</th>
                            <th class="px-4 py-3">Productos</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Acción</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-stone-50 {{ $order->status === 'cancelado' ? 'bg-red-50/40' : '' }}">
                                <td class="px-4 py-3">
                                    <p class="font-bold {{ $order->status === 'cancelado' ? 'text-red-600 line-through' : 'text-stone-800' }}">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </p>

                                    <p class="text-xs text-stone-400">
                                        {{ $order->created_at->format('h:i A') }}
                                    </p>

                                    <p class="text-xs text-stone-400">
                                        Cobró: {{ $order->user->name ?? 'Caja' }}
                                    </p>

                                    @if($order->customer)
                                        <p class="text-xs text-amber-700 mt-1">
                                            👑 {{ $order->customer->name }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <ul class="list-disc list-inside text-xs space-y-1">
                                        @foreach($order->items as $item)
                                            <li>
                                                {{ $item->quantity }}x
                                                {{ $item->product->name ?? 'Producto borrado' }}
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if($order->status === 'cancelado')
                                        <div class="mt-2 text-xs text-red-700 bg-red-100 p-2 rounded border border-red-200 space-y-1">
                                            <div>
                                                <strong>Motivo:</strong>
                                                {{ $order->cancellation_reason }}
                                            </div>

                                            <div>
                                                <strong>Canceló:</strong>
                                                {{ $order->canceller->name ?? 'Usuario no disponible' }}
                                            </div>

                                            @if($order->cancelled_at)
                                                <div>
                                                    <strong>Fecha:</strong>
                                                    {{ $order->cancelled_at->format('d/m/Y h:i A') }}
                                                </div>
                                            @endif

                                            @if($order->cancellation_action === 'devolver')
                                                <span class="inline-flex mt-1 items-center px-2 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">
                                                    Insumos devueltos
                                                </span>
                                            @elseif($order->cancellation_action === 'merma')
                                                <span class="inline-flex mt-1 items-center px-2 py-1 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 border border-orange-200">
                                                    Registrado como merma
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <span class="block font-black {{ $order->status === 'cancelado' ? 'text-red-600 line-through' : 'text-stone-800' }}">
                                        ${{ number_format($order->total, 2) }}
                                    </span>

                                    <span class="text-[10px] font-bold uppercase {{ $order->payment_method === 'efectivo' ? 'text-green-600' : 'text-blue-600' }}">
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
                                    <a href="{{ route('pos.ticket', $order) }}?reprint=1"
                                       target="_blank"
                                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold hover:bg-amber-100 border border-amber-100 transition">
                                        Reimprimir
                                    </a>

                                    <div class="mt-2">
                                        @if($order->status === 'completado')
                                            <span class="text-[10px] px-2 py-1 rounded-full font-bold bg-green-100 text-green-700">
                                                Completado
                                            </span>
                                        @else
                                            <span class="text-[10px] px-2 py-1 rounded-full font-bold bg-red-100 text-red-700">
                                                Cancelado
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-stone-400 italic">
                                    No hubo ventas en este corte.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CORRECCIONES ADMINISTRATIVAS --}}
    @if(auth()->user()->role === 'admin')
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                <h3 class="font-black text-blue-900 flex items-center gap-2">
                    🛠️ Corrección administrativa
                </h3>

                <p class="text-xs text-blue-700 mt-1">
                    Solo administradores pueden modificar estos datos. Cada cambio queda guardado en auditoría.
                </p>
            </div>

            <form method="POST" action="{{ route('cash_registers.adjust', $cashRegister) }}" class="p-6 space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                            Fondo inicial
                        </label>

                        <input type="number"
                            step="0.01"
                            min="0"
                            name="opening_amount"
                            value="{{ old('opening_amount', $cashRegister->opening_amount) }}"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                            Efectivo contado
                        </label>

                        <input type="number"
                            step="0.01"
                            min="0"
                            name="actual_amount"
                            value="{{ old('actual_amount', $cashRegister->actual_amount) }}"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500"
                            placeholder="Pendiente">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                            Contraseña administrador
                        </label>

                        <input type="password"
                            name="admin_password"
                            class="w-full rounded-xl border-stone-300 focus:border-red-500 focus:ring-red-500"
                            required
                            placeholder="Confirma tu contraseña">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Notas del cierre
                    </label>

                    <textarea name="notes"
                            rows="3"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500"
                            placeholder="Notas, observaciones o comentarios del cierre">{{ old('notes', $cashRegister->notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Motivo de la corrección
                    </label>

                    <textarea name="adjustment_reason"
                            rows="3"
                            class="w-full rounded-xl border-stone-300 focus:border-red-500 focus:ring-red-500"
                            required
                            placeholder="Explica por qué se realiza este ajuste administrativo">{{ old('adjustment_reason') }}</textarea>

                    <p class="text-xs text-stone-400 mt-1">
                        Este motivo quedará registrado en la auditoría del corte.
                    </p>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-sm text-amber-800">
                    <p class="font-black mb-1">
                        Importante
                    </p>

                    <p>
                        Al cambiar el fondo inicial, el sistema recalculará automáticamente el efectivo esperado y la diferencia usando las ventas en efectivo y los gastos activos de este corte.
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            onclick="return confirm('¿Confirmas guardar esta corrección administrativa? El cambio quedará auditado.')"
                            class="inline-flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-black px-6 py-3 rounded-xl shadow-md transition">
                        Guardar corrección
                    </button>
                </div>
            </form>
        </div>

        {{-- HISTORIAL DE AUDITORÍA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-stone-900 px-6 py-4 border-b border-stone-800 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-black text-white flex items-center gap-2">
                        🧾 Auditoría de correcciones
                    </h3>

                    <p class="text-xs text-stone-300 mt-1">
                        Registro de cambios realizados por administradores en este corte.
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full bg-white/10 text-white text-xs font-bold border border-white/10">
                    {{ $adjustments->count() }} cambio(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-stone-50 border-b border-stone-200">
                        <tr class="text-xs text-stone-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Administrador</th>
                            <th class="px-5 py-3">Campo</th>
                            <th class="px-5 py-3">Valor anterior</th>
                            <th class="px-5 py-3">Valor nuevo</th>
                            <th class="px-5 py-3">Motivo</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100">
                        @forelse($adjustments as $adjustment)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="px-5 py-4 text-stone-500 whitespace-nowrap">
                                    {{ $adjustment->created_at->format('d/m/Y h:i A') }}
                                </td>

                                <td class="px-5 py-4 font-bold text-stone-800">
                                    {{ $adjustment->user->name ?? 'Usuario no disponible' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-black">
                                        {{ $adjustment->field_name }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-red-700 text-xs">
                                    {{ $adjustment->old_value !== null && $adjustment->old_value !== '' ? $adjustment->old_value : 'Sin dato' }}
                                </td>

                                <td class="px-5 py-4 text-green-700 text-xs font-bold">
                                    {{ $adjustment->new_value !== null && $adjustment->new_value !== '' ? $adjustment->new_value : 'Sin dato' }}
                                </td>

                                <td class="px-5 py-4 text-stone-600 text-xs max-w-xs">
                                    {{ $adjustment->reason }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-stone-400 italic">
                                    Este corte aún no tiene correcciones administrativas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

@endsection