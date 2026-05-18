@extends('layouts.admin')

@section('content')
{{-- Chart.js para las gráficas --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO DEL MÓDULO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📊 Panel administrativo
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Resumen Financiero y Operativo
            </h1>

            <p class="text-stone-500 mt-1">
                Consulta ventas, gastos, ganancia neta, productos destacados y alertas de inventario.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('pos.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-amber-800 text-white font-bold shadow-sm hover:bg-amber-900 hover:shadow-md transition">
                🧾 Ir al POS
            </a>

            <a href="{{ route('cash_registers.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white border border-stone-200 text-stone-700 font-bold shadow-sm hover:bg-stone-50 hover:border-amber-300 transition">
                💰 Corte de caja
            </a>
        </div>
    </div>

    {{-- RESUMEN PRINCIPAL --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        
        {{-- Ventas hoy --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute right-4 top-4 text-4xl opacity-10">💵</div>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl bg-green-100 text-green-700 flex items-center justify-center text-xl">
                    $
                </div>
                <div>
                    <p class="text-sm font-bold text-stone-500">Ventas de hoy</p>
                    <p class="text-xs text-stone-400">{{ now()->format('d/m/Y') }}</p>
                </div>
            </div>

            <h3 class="text-3xl font-black text-green-600">
                ${{ number_format($salesToday, 2) }}
            </h3>

            <div class="mt-4 bg-stone-50 rounded-xl px-3 py-2 text-xs text-stone-500">
                Gastos del día:
                <span class="font-bold text-red-600">
                    ${{ number_format($expensesToday, 2) }}
                </span>
            </div>
        </div>

        {{-- Ventas semana --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute right-4 top-4 text-4xl opacity-10">📅</div>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl">
                    📈
                </div>
                <div>
                    <p class="text-sm font-bold text-stone-500">Ventas de la semana</p>
                    <p class="text-xs text-stone-400">Desde inicio de semana</p>
                </div>
            </div>

            <h3 class="text-3xl font-black text-blue-600">
                ${{ number_format($salesWeek, 2) }}
            </h3>

            <p class="mt-4 text-xs text-stone-400">
                Acumulado semanal de ventas completadas.
            </p>
        </div>

        {{-- Ventas mes --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute right-4 top-4 text-4xl opacity-10">🗓️</div>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-xl">
                    ☕
                </div>
                <div>
                    <p class="text-sm font-bold text-stone-500">Ventas del mes</p>
                    <p class="text-xs text-stone-400">{{ now()->translatedFormat('F Y') }}</p>
                </div>
            </div>

            <h3 class="text-3xl font-black text-amber-700">
                ${{ number_format($salesMonth, 2) }}
            </h3>

            <div class="mt-4 bg-stone-50 rounded-xl px-3 py-2 text-xs text-stone-500">
                Gastos del mes:
                <span class="font-bold text-red-600">
                    ${{ number_format($expensesMonth, 2) }}
                </span>
            </div>
        </div>

        {{-- Ganancia neta --}}
        <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-6 rounded-2xl shadow-md border border-amber-800 relative overflow-hidden text-white hover:shadow-lg transition">
            <div class="absolute right-[-8px] top-[-8px] opacity-20 text-7xl">📊</div>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl bg-white/10 text-amber-200 flex items-center justify-center text-xl">
                    ★
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-200">Ganancia neta mensual</p>
                    <p class="text-xs text-amber-300">Ventas - gastos</p>
                </div>
            </div>

            <h3 class="text-3xl font-black">
                ${{ number_format($netProfitMonth, 2) }}
            </h3>

            <p class="mt-4 text-xs text-amber-200">
                Estimación basada en ventas completadas y gastos activos.
            </p>
        </div>
    </div>

    {{-- GRÁFICA + TOP PRODUCTOS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        {{-- Gráfica --}}
        <div class="xl:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div>
                    <h3 class="font-bold text-stone-800 text-lg">
                        Flujo de dinero
                    </h3>
                    <p class="text-sm text-stone-500">
                        Comparación de ingresos y egresos de los últimos 7 días.
                    </p>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-100 text-stone-500 text-xs font-bold">
                    Últimos 7 días
                </span>
            </div>

            <div class="relative h-80 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Top ventas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-stone-100 bg-amber-50">
                <h3 class="font-bold text-amber-900 text-lg flex items-center gap-2">
                    🏆 Productos más vendidos
                </h3>
                <p class="text-sm text-amber-700 mt-1">
                    Ranking del mes actual.
                </p>
            </div>

            <div class="p-6">
                <ul class="space-y-3">
                    @forelse($topProducts as $index => $top)
                        <li class="flex items-center justify-between gap-3 p-3 rounded-xl border border-stone-100 hover:bg-stone-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black
                                    {{ $index == 0 ? 'bg-amber-200 text-amber-900' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $index + 1 }}
                                </div>

                                <div>
                                    <p class="font-bold text-stone-800 leading-tight">
                                        {{ $top->name }}
                                    </p>
                                    <p class="text-xs text-stone-400">
                                        Producto vendido este mes
                                    </p>
                                </div>
                            </div>

                            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap">
                                {{ $top->total_sold }} vendidos
                            </span>
                        </li>
                    @empty
                        <li class="py-8 text-center text-stone-400 italic text-sm">
                            Aún no hay ventas registradas este mes.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- ALERTAS DE INVENTARIO --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
            {{ $lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty() ? 'bg-green-50' : 'bg-red-50' }}">
            
            <div>
                <h3 class="font-bold text-lg flex items-center gap-2
                    {{ $lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty() ? 'text-green-800' : 'text-red-800' }}">
                    @if($lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty())
                        ✅ Inventario saludable
                    @else
                        ⚠️ Alertas de inventario bajo
                    @endif
                </h3>

                <p class="text-sm mt-1
                    {{ $lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty() ? 'text-green-700' : 'text-red-700' }}">
                    @if($lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty())
                        No hay productos ni materia prima en nivel crítico.
                    @else
                        Revisa los productos o insumos que necesitan atención.
                    @endif
                </p>
            </div>

            <a href="{{ route('ingredients.index') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 text-sm font-bold hover:bg-stone-50 transition">
                📦 Ver materia prima
            </a>
        </div>
        
        <div class="p-6">
            @if($lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty())
                <div class="text-center text-green-700 font-bold py-10 flex flex-col items-center">
                    <span class="text-5xl mb-3">✅</span>
                    Todo tu inventario está en niveles saludables.
                    <span class="text-sm font-normal text-green-600 mt-1">
                        No hay alertas críticas por el momento.
                    </span>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Materia Prima --}}
                    <div class="rounded-2xl border border-stone-100 overflow-hidden">
                        <div class="bg-stone-50 px-5 py-4 border-b border-stone-100">
                            <h4 class="font-bold text-stone-800">
                                Materia prima crítica
                            </h4>
                        </div>

                        <div class="p-5">
                            @if($lowStockIngredients->count() > 0)
                                <ul class="space-y-3">
                                    @foreach($lowStockIngredients as $ing)
                                        <li class="flex justify-between items-center bg-red-50 p-3 rounded-xl border border-red-100">
                                            <div>
                                                <p class="font-bold text-stone-800">
                                                    {{ $ing->name }}
                                                </p>
                                                <p class="text-xs text-stone-400">
                                                    Insumo en nivel bajo
                                                </p>
                                            </div>

                                            <span class="text-red-600 font-bold text-sm">
                                                {{ floatval($ing->current_quantity) }} {{ $ing->unit }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-8 text-stone-400 text-sm">
                                    No hay materia prima crítica.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Productos Directos --}}
                    <div class="rounded-2xl border border-stone-100 overflow-hidden">
                        <div class="bg-stone-50 px-5 py-4 border-b border-stone-100">
                            <h4 class="font-bold text-stone-800">
                                Productos críticos
                            </h4>
                        </div>

                        <div class="p-5">
                            @if($lowStockProducts->count() > 0)
                                <ul class="space-y-3">
                                    @foreach($lowStockProducts as $prod)
                                        <li class="flex justify-between items-center bg-red-50 p-3 rounded-xl border border-red-100">
                                            <div>
                                                <p class="font-bold text-stone-800">
                                                    {{ $prod->name }}
                                                </p>
                                                <p class="text-xs text-stone-400">
                                                    Producto con stock bajo
                                                </p>
                                            </div>

                                            <span class="text-red-600 font-bold text-sm">
                                                {{ $prod->stock }} pz
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-8 text-stone-400 text-sm">
                                    No hay productos críticos.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- SCRIPT PARA LA GRÁFICA --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartCanvas = document.getElementById('salesChart');

        if (!chartCanvas) {
            return;
        }

        const ctx = chartCanvas.getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartDates) !!},
                datasets: [
                    {
                        label: 'Ingresos (Ventas)',
                        data: {!! json_encode($chartSales) !!},
                        backgroundColor: '#16a34a',
                        borderRadius: 8,
                    },
                    {
                        label: 'Egresos (Gastos)',
                        data: {!! json_encode($chartExpenses) !!},
                        backgroundColor: '#dc2626',
                        borderRadius: 8,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';

                                if (label) {
                                    label += ': ';
                                }

                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('es-MX', {
                                        style: 'currency',
                                        currency: 'MXN'
                                    }).format(context.parsed.y);
                                }

                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
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