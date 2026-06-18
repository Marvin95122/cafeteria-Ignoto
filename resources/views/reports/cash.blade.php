@extends('layouts.admin')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                💰 Reporte de caja
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Reporte de caja
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

            <a href="{{ route('reports.cash.excel', request()->query()) }}"
                class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-green-700 text-white font-bold hover:bg-green-800 transition">
                📊 Exportar Excel
            </a>

            <a href="{{ route('reports.cash.pdf', request()->query()) }}"
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
                Consulta cortes por periodo, estado, agrupación y usuario relacionado.
            </p>
        </div>

        <form method="GET" action="{{ route('reports.cash') }}" class="p-5 bg-stone-50">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Periodo rápido
                    </label>

                    <select name="period" class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
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

                    <select name="group_by" class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
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
                        Estado
                    </label>

                    <select name="status" class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        <option value="todas" {{ $statusFilter === 'todas' ? 'selected' : '' }}>Todas</option>
                        <option value="abierta" {{ $statusFilter === 'abierta' ? 'selected' : '' }}>Abierta</option>
                        <option value="cerrada" {{ $statusFilter === 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-stone-500 uppercase tracking-wide mb-1">
                        Buscar
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Corte, abrió, cerró o notas..."
                           class="w-full rounded-xl border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                </div>

                <div class="xl:col-span-6 flex justify-end">
                    <button type="submit"
                            class="bg-amber-800 hover:bg-amber-900 text-white font-black px-8 py-2.5 rounded-xl transition">
                        Generar reporte
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

    {{-- RESUMEN PRINCIPAL --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Cortes registrados</p>
            <h3 class="text-2xl font-black text-amber-700 mt-1">{{ $totalCuts }}</h3>
            <p class="text-xs text-stone-400 mt-1">{{ $closedCuts }} cerrados · {{ $openCuts }} abiertos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Efectivo esperado</p>
            <h3 class="text-2xl font-black text-blue-700 mt-1">${{ number_format($expectedTotal, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">Fondo + ventas efectivo - gastos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Efectivo contado</p>
            <h3 class="text-2xl font-black text-green-700 mt-1">${{ number_format($actualTotal, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-1">Suma de cierres capturados</p>
        </div>

        <div class="rounded-2xl shadow-md border p-5 text-white {{ $differenceTotal < 0 ? 'bg-gradient-to-br from-red-800 to-stone-900 border-red-800' : 'bg-gradient-to-br from-amber-900 to-stone-900 border-amber-800' }}">
            <p class="text-xs font-black uppercase text-white/80">Diferencia acumulada</p>
            <h3 class="text-2xl font-black mt-1">${{ number_format($differenceTotal, 2) }}</h3>
            <p class="text-xs text-white/70 mt-1">Contado - esperado</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-xs font-black uppercase text-stone-400">Correcciones</p>
            <h3 class="text-2xl font-black text-purple-700 mt-1">{{ $adjustmentsCount }}</h3>
            <p class="text-xs text-stone-400 mt-1">Ajustes administrativos</p>
        </div>
    </div>

    {{-- RESUMEN SECUNDARIO --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Ventas en efectivo</p>
            <h3 class="text-2xl font-black text-green-700 mt-1">${{ number_format($salesCashTotal, 2) }}</h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Gastos activos</p>
            <h3 class="text-2xl font-black text-red-600 mt-1">${{ number_format($expensesTotal, 2) }}</h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Flujo de efectivo</p>
            <h3 class="text-2xl font-black {{ $cashFlow < 0 ? 'text-red-600' : 'text-blue-700' }} mt-1">
                ${{ number_format($cashFlow, 2) }}
            </h3>
            <p class="text-xs text-stone-400 mt-1">Ventas efectivo - gastos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
            <p class="text-sm font-bold text-stone-500">Tickets</p>
            <h3 class="text-2xl font-black text-stone-800 mt-1">{{ $completedOrdersCount }}</h3>
            <p class="text-xs text-stone-400 mt-1">{{ $cancelledOrdersCount }} cancelado(s)</p>
        </div>
    </div>

    {{-- GRÁFICAS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800">
                Caja por periodo
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Efectivo esperado, contado y diferencias agrupadas por {{ $groupBy }}.
            </p>

            <div id="cash-period-chart" class="min-h-[320px]"></div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800">
                Estado de cortes
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Cortes abiertos y cerrados en el periodo.
            </p>

            <div id="cash-status-chart" class="min-h-[280px]"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800">
                Mayores diferencias
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Cortes con mayor diferencia absoluta entre efectivo contado y esperado.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-stone-50 text-stone-500 uppercase text-xs border-b border-stone-200">
                        <tr>
                            <th class="px-4 py-3">Corte</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3 text-right">Esperado</th>
                            <th class="px-4 py-3 text-right">Contado</th>
                            <th class="px-4 py-3 text-right">Diferencia</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100">
                        @forelse($topDifferences as $row)
                            <tr class="hover:bg-stone-50">
                                <td class="px-4 py-3 font-black text-stone-800">#{{ $row['id'] }}</td>
                                <td class="px-4 py-3 text-stone-500">{{ $row['opened_at']->format('d/m/Y h:i A') }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($row['expected_cash'], 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    {{ $row['actual_cash'] !== null ? '$' . number_format($row['actual_cash'], 2) : 'Pendiente' }}
                                </td>
                                <td class="px-4 py-3 text-right font-black {{ $row['difference'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                                    ${{ number_format($row['difference'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-stone-400 italic">
                                    No hay diferencias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h3 class="font-serif text-xl font-black text-stone-800">
                Gastos por categoría
            </h3>

            <p class="text-sm text-stone-500 mb-4">
                Distribución de gastos activos asociados a los cortes consultados.
            </p>

            <div id="cash-expenses-chart" class="min-h-[300px]"></div>
        </div>
    </div>

    {{-- AUDITORÍA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="bg-purple-900 px-6 py-4 border-b border-purple-800">
            <h3 class="font-bold text-white flex items-center gap-2">
                🛠️ Correcciones administrativas recientes
            </h3>

            <p class="text-xs text-purple-100 mt-1">
                Últimos cambios registrados en los cortes dentro del periodo consultado.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white text-stone-500 uppercase text-xs border-b border-stone-200">
                    <tr>
                        <th class="px-5 py-4">Fecha</th>
                        <th class="px-5 py-4">Corte</th>
                        <th class="px-5 py-4">Administrador</th>
                        <th class="px-5 py-4">Campo</th>
                        <th class="px-5 py-4">Anterior</th>
                        <th class="px-5 py-4">Nuevo</th>
                        <th class="px-5 py-4">Motivo</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($recentAdjustments as $adjustment)
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-4 text-stone-500 whitespace-nowrap">
                                {{ $adjustment->created_at->format('d/m/Y h:i A') }}
                            </td>
                            <td class="px-5 py-4 font-black text-stone-800">
                                #{{ $adjustment->cash_register_id }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $adjustment->user->name ?? 'Usuario no disponible' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-black">
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
                            <td colspan="7" class="px-5 py-10 text-center text-stone-400 italic">
                                No hay correcciones administrativas en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLA DE CORTES --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="bg-stone-900 px-6 py-4 border-b border-stone-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="font-bold text-white flex items-center gap-2">
                    🧾 Cortes encontrados
                </h3>

                <p class="text-xs text-stone-300 mt-1">
                    Resultado según filtros. Puedes abrir el detalle completo del corte.
                </p>
            </div>

            <span class="px-3 py-1 rounded-full bg-white/10 text-white text-xs font-bold border border-white/10">
                {{ $cashRegisters->total() }} resultado(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white text-stone-500 uppercase text-xs border-b border-stone-200">
                    <tr>
                        <th class="px-5 py-4">Corte</th>
                        <th class="px-5 py-4">Apertura / Cierre</th>
                        <th class="px-5 py-4">Usuarios</th>
                        <th class="px-5 py-4 text-right">Esperado</th>
                        <th class="px-5 py-4 text-right">Contado</th>
                        <th class="px-5 py-4 text-right">Diferencia</th>
                        <th class="px-5 py-4 text-center">Estado</th>
                        <th class="px-5 py-4 text-center">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse($paginatedCutRows as $row)
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-4">
                                <p class="font-black text-stone-800">
                                    #{{ $row['id'] }}
                                </p>

                                <p class="text-xs text-stone-400 mt-1">
                                    {{ $row['completed_orders_count'] }} ticket(s) · {{ $row['active_expenses_count'] }} gasto(s)
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-bold text-stone-700">
                                    {{ $row['opened_at']->format('d/m/Y h:i A') }}
                                </p>

                                <p class="text-xs text-stone-400">
                                    Cierre:
                                    {{ $row['closed_at'] ? $row['closed_at']->format('d/m/Y h:i A') : 'Caja abierta' }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <p class="text-sm text-stone-700">
                                    Abrió: <span class="font-bold">{{ $row['opened_by'] }}</span>
                                </p>
                                <p class="text-xs text-stone-400">
                                    Cerró: {{ $row['closed_by'] }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-right font-black text-blue-700">
                                ${{ number_format($row['expected_cash'], 2) }}
                            </td>

                            <td class="px-5 py-4 text-right font-black text-green-700">
                                {{ $row['actual_cash'] !== null ? '$' . number_format($row['actual_cash'], 2) : 'Pendiente' }}
                            </td>

                            <td class="px-5 py-4 text-right font-black {{ $row['difference'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                                ${{ number_format($row['difference'], 2) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($row['status'] === 'abierta')
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-black">
                                        Abierta
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-black">
                                        Cerrada
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('cash_registers.show', $row['id']) }}"
                                   class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold hover:bg-amber-100 border border-amber-100 transition">
                                    Ver corte
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-stone-400 italic">
                                No se encontraron cortes con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashRegisters->hasPages())
            <div class="px-6 py-4 border-t border-stone-100 bg-stone-50">
                {{ $cashRegisters->links() }}
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

        const cashByPeriod = @json($cashByPeriod);
        const statusBreakdown = @json($cashStatusBreakdown);
        const expensesByCategory = @json($expensesByCategory);

        new ApexCharts(document.querySelector("#cash-period-chart"), {
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: true },
                fontFamily: 'inherit'
            },
            series: [
                {
                    name: 'Esperado',
                    data: cashByPeriod.map(item => Number(item.expected || 0))
                },
                {
                    name: 'Contado',
                    data: cashByPeriod.map(item => Number(item.actual || 0))
                },
                {
                    name: 'Diferencia',
                    data: cashByPeriod.map(item => Number(item.difference || 0))
                }
            ],
            xaxis: {
                categories: cashByPeriod.map(item => item.label)
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#2563eb', '#16a34a', '#dc2626'],
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return moneyFormatter.format(value);
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return '$' + Math.round(value);
                    }
                }
            }
        }).render();

        new ApexCharts(document.querySelector("#cash-status-chart"), {
            chart: {
                type: 'donut',
                height: 280,
                fontFamily: 'inherit'
            },
            labels: statusBreakdown.map(item => item.label),
            series: statusBreakdown.map(item => Number(item.count || 0)),
            colors: ['#2563eb', '#16a34a'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                formatter: function(value) {
                    return value.toFixed(1) + '%';
                }
            }
        }).render();

        new ApexCharts(document.querySelector("#cash-expenses-chart"), {
            chart: {
                type: 'bar',
                height: 300,
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