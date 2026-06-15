@extends('layouts.admin')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    @media print {
        aside,
        header,
        .no-print {
            display: none !important;
        }

        main,
        body {
            background: white !important;
            overflow: visible !important;
        }

        .print-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .print-break {
            page-break-inside: avoid;
        }
    }
</style>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 print-break">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📑 Reporte de ventas
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Reporte profesional de ventas
            </h1>

            <p class="text-stone-500 mt-1">
                Periodo consultado:
                <span class="font-bold text-stone-700">{{ $periodLabel }}</span>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 no-print">
            <button onclick="window.print()"
                    class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-stone-900 text-white font-bold hover:bg-black transition">
                🖨️ Imprimir reporte
            </button>

            <a href="{{ route('reports.sales') }}"
               class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-white border border-stone-300 text-stone-700 font-bold hover:bg-stone-50 transition">
                Limpiar filtros
            </a>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden no-print">
        <div class="bg-stone-800 px-6 py-4">
            <h2 class="font-bold text-white flex items-center gap-2">
                🔎 Filtros del reporte
            </h2>
            <p class="text-xs text-stone-300 mt-1">
                Consulta ventas por fecha, método de pago, estado o datos del ticket.
            </p>
        </div>

        <form method="GET" action="{{ route('reports.sales') }}" class="p-5 bg-stone-50">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Desde
                    </label>

                    <input type="date"
                           name="from"
                           value="{{ $from }}"
                           class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Hasta
                    </label>

                    <input type="date"
                           name="to"
                           value="{{ $to }}"
                           class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Método
                    </label>

                    <select name="payment_method"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="todos" {{ $paymentFilter === 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="efectivo" {{ $paymentFilter === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="tarjeta" {{ $paymentFilter === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                        <option value="puntos" {{ $paymentFilter === 'puntos' ? 'selected' : '' }}>Puntos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Estado
                    </label>

                    <select name="status"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="todos" {{ $statusFilter === 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="completado" {{ $statusFilter === 'completado' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelado" {{ $statusFilter === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Buscar ticket, cajero o cliente
                    </label>

                    <div class="flex gap-2">
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Ej. 25, Marvin, cliente..."
                               class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">

                        <button type="submit"
                                class="bg-amber-800 hover:bg-amber-900 text-white font-bold px-5 rounded-xl transition">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- RESUMEN PRINCIPAL --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 print-break">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Total operado</p>
            <h3 class="text-3xl font-black text-amber-700 mt-1">
                ${{ number_format($totalOperatedSales, 2) }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                Efectivo + tarjeta + puntos.
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Ingresos reales</p>
            <h3 class="text-3xl font-black text-green-600 mt-1">
                ${{ number_format($realIncome, 2) }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                Efectivo + tarjeta.
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Gastos activos</p>
            <h3 class="text-3xl font-black text-red-600 mt-1">
                ${{ number_format($expensesTotal, 2) }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                {{ $expensesCount }} gasto(s) activo(s).
            </p>
        </div>

        <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-5 rounded-2xl shadow-md border border-amber-800 text-white print-card">
            <p class="text-sm font-bold text-amber-200">Utilidad operativa</p>
            <h3 class="text-3xl font-black mt-1">
                ${{ number_format($operatingProfit, 2) }}
            </h3>
            <p class="text-xs text-amber-200 mt-1">
                Ingresos reales - gastos.
            </p>
        </div>
    </div>

    {{-- TARJETAS SECUNDARIAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 print-break">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Tickets completados</p>
            <h3 class="text-2xl font-black text-stone-800 mt-1">
                {{ $completedOrdersCount }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                Promedio: ${{ number_format($averageTicket, 2) }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Tickets cancelados</p>
            <h3 class="text-2xl font-black text-red-600 mt-1">
                {{ $cancelledOrdersCount }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                Monto cancelado: ${{ number_format($cancelledOrdersAmount, 2) }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Efectivo recibido</p>
            <h3 class="text-2xl font-black text-green-700 mt-1">
                ${{ number_format($cashReceived, 2) }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                Cambio entregado: ${{ number_format($cashChange, 2) }}
            </p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 print-card">
            <p class="text-sm font-bold text-stone-500">Puntos VIP</p>
            <h3 class="text-2xl font-black text-purple-600 mt-1">
                {{ number_format($pointsEarned) }} ganados
            </h3>
            <p class="text-xs text-stone-400 mt-1">
                {{ number_format($pointsUsed) }} usados.
            </p>
        </div>
    </div>

    {{-- GRÁFICA Y MÉTODOS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 print-break">

        <div class="xl:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-stone-200 print-card">
            <h3 class="font-bold text-stone-800 text-lg">
                Ventas por día
            </h3>
            <p class="text-sm text-stone-500 mb-4">
                Total de tickets completados por fecha.
            </p>

            <div class="relative h-72">
                <canvas id="salesReportChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 print-card">
            <h3 class="font-bold text-stone-800 text-lg mb-4">
                Métodos de pago
            </h3>

            <div class="space-y-3">
                @foreach($paymentBreakdown as $payment)
                    <div class="border rounded-2xl p-4 {{ $payment['class'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-black">
                                    {{ $payment['emoji'] }} {{ $payment['label'] }}
                                </p>

                                <p class="text-xs opacity-80 mt-1">
                                    {{ $payment['count'] }} ticket(s)
                                </p>
                            </div>

                            <p class="font-black text-xl">
                                ${{ number_format($payment['amount'], 2) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 bg-stone-50 border border-stone-100 rounded-2xl p-4 text-xs text-stone-500">
                Las ventas con puntos se muestran como valor canjeado, no como ingreso en efectivo.
            </div>
        </div>
    </div>

    {{-- PRODUCTOS MÁS VENDIDOS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden print-break print-card">
        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100">
            <h3 class="font-bold text-amber-900 flex items-center gap-2">
                🏆 Productos más vendidos
            </h3>

            <p class="text-xs text-amber-700 mt-1">
                Ranking de productos vendidos dentro del periodo consultado.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white text-stone-500 uppercase text-xs border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-4">Producto</th>
                        <th class="px-6 py-4 text-center">Unidades</th>
                        <th class="px-6 py-4 text-right">Ingreso generado</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($topProducts as $product)
                        <tr class="hover:bg-stone-50">
                            <td class="px-6 py-4 font-bold text-stone-800">
                                {{ $product->name }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-black">
                                    {{ $product->total_sold }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right font-black text-green-700">
                                ${{ number_format($product->total_revenue, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-stone-400 italic">
                                No hay productos vendidos en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA DE TICKETS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden print-card">
        <div class="bg-stone-900 px-6 py-4 border-b border-stone-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="font-bold text-white flex items-center gap-2">
                    🧾 Tickets encontrados
                </h3>

                <p class="text-xs text-stone-300 mt-1">
                    Resultado según los filtros seleccionados. Puedes reimprimir cualquier ticket.
                </p>
            </div>

            <span class="px-3 py-1 rounded-full bg-white/10 text-white text-xs font-bold border border-white/10">
                {{ $orders->total() }} resultado(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white text-stone-500 uppercase text-xs border-b border-stone-200">
                    <tr>
                        <th class="px-5 py-4">Ticket</th>
                        <th class="px-5 py-4">Fecha / Cajero</th>
                        <th class="px-5 py-4">Cliente</th>
                        <th class="px-5 py-4">Método</th>
                        <th class="px-5 py-4 text-right">Total</th>
                        <th class="px-5 py-4 text-center">Estado</th>
                        <th class="px-5 py-4 text-center no-print">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-stone-50 {{ $order->status === 'cancelado' ? 'bg-red-50/40' : '' }}">
                            <td class="px-5 py-4">
                                <p class="font-black {{ $order->status === 'cancelado' ? 'text-red-600 line-through' : 'text-stone-800' }}">
                                    #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </p>

                                @if($order->status === 'cancelado' && $order->cancellation_reason)
                                    <p class="text-xs text-red-600 mt-1">
                                        {{ $order->cancellation_reason }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-bold text-stone-700">
                                    {{ $order->created_at->format('d/m/Y h:i A') }}
                                </p>

                                <p class="text-xs text-stone-400">
                                    {{ $order->user->name ?? 'Caja' }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                @if($order->customer)
                                    <span class="text-amber-700 font-bold">
                                        👑 {{ $order->customer->name }}
                                    </span>
                                @else
                                    <span class="text-stone-400">
                                        Público general
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase
                                    {{ $order->payment_method === 'efectivo' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->payment_method === 'tarjeta' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->payment_method === 'puntos' ? 'bg-purple-100 text-purple-700' : '' }}">
                                    {{ $order->payment_method }}
                                </span>

                                @if($order->payment_method === 'efectivo')
                                    <p class="text-[10px] text-stone-400 mt-2 leading-tight">
                                        Recibido: ${{ number_format($order->cash_received ?? $order->total, 2) }}<br>
                                        Cambio: ${{ number_format($order->cash_change ?? 0, 2) }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right font-black {{ $order->status === 'cancelado' ? 'text-red-600 line-through' : 'text-stone-800' }}">
                                ${{ number_format($order->total, 2) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($order->status === 'completado')
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-black">
                                        Completado
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-black">
                                        Cancelado
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center no-print">
                                <a href="{{ route('pos.ticket', $order) }}?reprint=1"
                                   target="_blank"
                                   class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold hover:bg-amber-100 border border-amber-100 transition">
                                    Reimprimir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-stone-400 italic">
                                No se encontraron tickets con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-stone-100 bg-stone-50 no-print">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('salesReportChart');

        if (!canvas) {
            return;
        }

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Ventas completadas',
                        data: {!! json_encode($chartSales) !!},
                        borderColor: '#b45309',
                        backgroundColor: 'rgba(180, 83, 9, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('es-MX', {
                                    style: 'currency',
                                    currency: 'MXN'
                                }).format(context.parsed.y);
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

@endsection