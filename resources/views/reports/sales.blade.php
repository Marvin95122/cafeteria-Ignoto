@extends('layouts.admin')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📈 Reporte de ventas
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Reporte de ventas
            </h1>

            <p class="text-stone-500 mt-1">
                Periodo analizado:
                <span class="font-bold text-stone-700">{{ $periodLabel }}</span>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('reports.index') }}"
               class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-white border border-stone-300 text-stone-700 font-bold hover:bg-stone-50 transition">
                ← Reportes
            </a>

            <a href="{{ route('reports.sales.excel', request()->query()) }}"
                class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-green-700 text-white font-bold hover:bg-green-800 transition">
                📊 Exportar Excel
            </a>

            <a href="{{ route('reports.sales.pdf', request()->query()) }}"
                class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-red-700 text-white font-bold hover:bg-red-800 transition">
                📄 Exportar PDF
            </a>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="bg-stone-900 px-6 py-4">
            <h2 class="font-bold text-white flex items-center gap-2">
                🔎 Filtros del reporte
            </h2>
            <p class="text-xs text-stone-300 mt-1">
                Filtra por periodo rápido, fecha personalizada, agrupación, método de pago, estado y búsqueda.
            </p>
        </div>

        <form method="GET" action="{{ route('reports.sales') }}" class="p-5 bg-stone-50">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Periodo rápido
                    </label>

                    <select name="period"
                            id="period-select"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="hoy" {{ $period === 'hoy' ? 'selected' : '' }}>Hoy</option>
                        <option value="semana" {{ $period === 'semana' ? 'selected' : '' }}>Semana</option>
                        <option value="mes" {{ $period === 'mes' ? 'selected' : '' }}>Mes</option>
                        <option value="bimestre" {{ $period === 'bimestre' ? 'selected' : '' }}>Bimestre</option>
                        <option value="trimestre" {{ $period === 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                        <option value="semestre" {{ $period === 'semestre' ? 'selected' : '' }}>Semestre</option>
                        <option value="anio" {{ $period === 'anio' ? 'selected' : '' }}>Año</option>
                        <option value="personalizado" {{ $period === 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Agrupar por
                    </label>

                    <select name="group_by"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="dia" {{ $groupBy === 'dia' ? 'selected' : '' }}>Día</option>
                        <option value="semana" {{ $groupBy === 'semana' ? 'selected' : '' }}>Semana</option>
                        <option value="mes" {{ $groupBy === 'mes' ? 'selected' : '' }}>Mes</option>
                        <option value="bimestre" {{ $groupBy === 'bimestre' ? 'selected' : '' }}>Bimestre</option>
                        <option value="trimestre" {{ $groupBy === 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                        <option value="semestre" {{ $groupBy === 'semestre' ? 'selected' : '' }}>Semestre</option>
                        <option value="anio" {{ $groupBy === 'anio' ? 'selected' : '' }}>Año</option>
                    </select>
                </div>

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
                        Estado tabla
                    </label>

                    <select name="status"
                            class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="todos" {{ $statusFilter === 'todos' ? 'selected' : '' }}>Todos</option>
                        <option value="completado" {{ $statusFilter === 'completado' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelado" {{ $statusFilter === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="md:col-span-2 xl:col-span-5">
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Buscar
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Ticket, cajero, cliente VIP o teléfono..."
                           class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-amber-800 hover:bg-amber-900 text-white font-black px-5 py-2.5 rounded-xl transition">
                        Generar
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- INTERPRETACIÓN --}}
    <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5">
        <h2 class="font-serif text-xl font-black text-amber-900 mb-2">
            Interpretación del reporte
        </h2>

        <p class="text-sm text-stone-600 leading-relaxed">
            {{ $executiveSummary }}
        </p>
    </div>

    {{-- RESUMEN EJECUTIVO --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Ventas operadas</p>
            <h3 class="text-2xl font-black text-amber-700 mt-1">${{ number_format($totalOperatedSales, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">Efectivo + tarjeta + puntos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Ingresos reales</p>
            <h3 class="text-2xl font-black text-green-600 mt-1">${{ number_format($realIncome, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">Efectivo + tarjeta</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Gastos activos</p>
            <h3 class="text-2xl font-black text-red-600 mt-1">${{ number_format($expensesTotal, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">{{ $expensesCount }} gasto(s)</p>
        </div>

        <div class="bg-gradient-to-br from-amber-900 to-stone-900 rounded-2xl shadow-md border border-amber-800 p-5 text-white">
            <p class="text-xs font-black uppercase text-amber-200">Utilidad operativa</p>
            <h3 class="text-2xl font-black mt-1">${{ number_format($operatingProfit, 2) }}</h3>
            <p class="text-xs text-amber-200 mt-1">Ingresos reales - gastos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Ticket promedio</p>
            <h3 class="text-2xl font-black text-blue-700 mt-1">${{ number_format($averageTicket, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">{{ $completedOrdersCount }} completado(s)</p>
        </div>
    </div>

    {{-- DETALLE DE MÉTRICAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Tickets completados</p>
            <h3 class="text-2xl font-black text-green-700 mt-1">{{ $completedOrdersCount }}</h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Tickets cancelados</p>
            <h3 class="text-2xl font-black text-red-600 mt-1">{{ $cancelledOrdersCount }}</h3>
            <p class="text-xs text-stone-400 mt-1">${{ number_format($cancelledOrdersAmount, 2) }} cancelados</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Efectivo recibido</p>
            <h3 class="text-2xl font-black text-green-700 mt-1">${{ number_format($cashReceived, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">Cambio: ${{ number_format($cashChange, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Puntos VIP</p>
            <h3 class="text-2xl font-black text-purple-700 mt-1">{{ number_format($pointsEarned) }} ganados</h3>
            <p class="text-xs text-stone-400 mt-1">{{ number_format($pointsUsed) }} usados</p>
        </div>
    </div>

    {{-- GRÁFICAS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="font-serif text-xl font-black text-stone-800">
                        Ventas por periodo
                    </h3>

                    <p class="text-sm text-stone-500">
                        Agrupación seleccionada:
                        <span class="font-bold">{{ ucfirst($groupBy) }}</span>
                    </p>
                </div>
            </div>

            <div id="sales-period-chart" class="min-h-[320px]"></div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800 mb-1">
                Métodos de pago
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Distribución de ventas operadas.
            </p>

            <div id="payment-chart" class="min-h-[280px]"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800 mb-1">
                Productos más vendidos
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Ranking por unidades vendidas e ingreso generado.
            </p>

            <div id="top-products-chart" class="min-h-[340px]"></div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800 mb-1">
                Gastos por categoría
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Distribución de gastos activos del periodo.
            </p>

            <div id="expenses-category-chart" class="min-h-[340px]"></div>
        </div>
    </div>

    {{-- MÉTODOS DE PAGO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($paymentBreakdown as $payment)
            <div class="rounded-2xl border p-5 {{ $payment['class'] }}">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-black">
                            {{ $payment['emoji'] }} {{ $payment['label'] }}
                        </p>

                        <p class="text-xs opacity-80 mt-1">
                            {{ $payment['count'] }} ticket(s)
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="font-black text-xl">
                            ${{ number_format($payment['amount'], 2) }}
                        </p>

                        <p class="text-xs font-bold">
                            {{ number_format($payment['percentage'], 1) }}%
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- PRODUCTOS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100">
            <h3 class="font-black text-amber-900">
                🏆 Productos más vendidos
            </h3>
            <p class="text-xs text-amber-700 mt-1">
                Productos con mayor movimiento dentro del periodo analizado.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white text-stone-500 uppercase text-xs border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-4">Producto</th>
                        <th class="px-6 py-4 text-center">Unidades</th>
                        <th class="px-6 py-4 text-right">Ingreso</th>
                        <th class="px-6 py-4 text-right">Participación</th>
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

                            <td class="px-6 py-4 text-right font-bold text-stone-600">
                                {{ number_format($product->percentage, 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-stone-400 italic">
                                No hay productos vendidos en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA DE TICKETS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="bg-stone-900 px-6 py-4 border-b border-stone-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="font-bold text-white flex items-center gap-2">
                    🧾 Tickets encontrados
                </h3>

                <p class="text-xs text-stone-300 mt-1">
                    Resultado según filtros. Puedes reimprimir cualquier ticket.
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
                        <th class="px-5 py-4 text-center">Acción</th>
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
                                    <p class="text-xs text-red-600 mt-1 max-w-xs">
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

                            <td class="px-5 py-4 text-center">
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
            <div class="px-6 py-4 border-t border-stone-100 bg-stone-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const moneyFormatter = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
        });

        const salesByPeriod = @json($salesByPeriod);
        const paymentBreakdown = @json($paymentBreakdown);
        const topProducts = @json($topProducts);
        const expensesByCategory = @json($expensesByCategory);

        const periodLabels = salesByPeriod.map(item => item.label);
        const periodSales = salesByPeriod.map(item => Number(item.sales || 0));
        const periodOrders = salesByPeriod.map(item => Number(item.orders || 0));

        new ApexCharts(document.querySelector("#sales-period-chart"), {
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: true },
                fontFamily: 'inherit'
            },
            series: [
                {
                    name: 'Ventas',
                    data: periodSales
                },
                {
                    name: 'Tickets',
                    data: periodOrders
                }
            ],
            xaxis: {
                categories: periodLabels
            },
            yaxis: [
                {
                    labels: {
                        formatter: function(value) {
                            return '$' + Math.round(value);
                        }
                    }
                },
                {
                    opposite: true,
                    labels: {
                        formatter: function(value) {
                            return Math.round(value);
                        }
                    }
                }
            ],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                opacity: 0.25
            },
            colors: ['#b45309', '#2563eb'],
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: [
                    {
                        formatter: function(value) {
                            return moneyFormatter.format(value);
                        }
                    },
                    {
                        formatter: function(value) {
                            return value + ' ticket(s)';
                        }
                    }
                ]
            }
        }).render();

        new ApexCharts(document.querySelector("#payment-chart"), {
            chart: {
                type: 'donut',
                height: 280,
                fontFamily: 'inherit'
            },
            labels: paymentBreakdown.map(item => item.label),
            series: paymentBreakdown.map(item => Number(item.amount || 0)),
            colors: ['#16a34a', '#2563eb', '#7c3aed'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                formatter: function(value) {
                    return value.toFixed(1) + '%';
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return moneyFormatter.format(value);
                    }
                }
            }
        }).render();

        new ApexCharts(document.querySelector("#top-products-chart"), {
            chart: {
                type: 'bar',
                height: 340,
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            series: [
                {
                    name: 'Unidades',
                    data: topProducts.map(item => Number(item.total_sold || 0))
                }
            ],
            xaxis: {
                categories: topProducts.map(item => item.name)
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6
                }
            },
            colors: ['#b45309'],
            dataLabels: {
                enabled: true
            }
        }).render();

        new ApexCharts(document.querySelector("#expenses-category-chart"), {
            chart: {
                type: 'bar',
                height: 340,
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            series: [
                {
                    name: 'Gastos',
                    data: expensesByCategory.map(item => Number(item.amount || 0))
                }
            ],
            xaxis: {
                categories: expensesByCategory.map(item => item.category)
            },
            plotOptions: {
                bar: {
                    borderRadius: 6
                }
            },
            colors: ['#dc2626'],
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return moneyFormatter.format(value);
                    }
                }
            }
        }).render();
    });
</script>

@endsection