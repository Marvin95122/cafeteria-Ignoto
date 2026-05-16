@extends('layouts.admin')

@section('content')
{{-- Chart.js para las gráficas --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- CABECERA --}}
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Resumen Financiero y Operativo</h1>
        <p class="text-stone-500">Métricas clave y estado del inventario en tiempo real.</p>
    </div>

    {{-- 1. TARJETAS DE MÉTRICAS (KPIs) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 flex flex-col justify-center">
            <p class="text-sm font-bold text-stone-500 mb-1">Ventas de Hoy</p>
            <h3 class="text-3xl font-black text-green-600">${{ number_format($salesToday, 2) }}</h3>
            <p class="text-xs text-stone-400 mt-2">Gastos hoy: ${{ number_format($expensesToday, 2) }}</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 flex flex-col justify-center">
            <p class="text-sm font-bold text-stone-500 mb-1">Ventas de la Semana</p>
            <h3 class="text-3xl font-black text-blue-600">${{ number_format($salesWeek, 2) }}</h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200 flex flex-col justify-center">
            <p class="text-sm font-bold text-stone-500 mb-1">Ventas del Mes</p>
            <h3 class="text-3xl font-black text-amber-700">${{ number_format($salesMonth, 2) }}</h3>
        </div>

        <div class="bg-amber-900 p-6 rounded-2xl shadow-md border border-amber-800 flex flex-col justify-center relative overflow-hidden text-white">
            <div class="absolute right-[-10px] top-[-10px] opacity-20 text-7xl">📈</div>
            <p class="text-sm font-bold text-amber-200 mb-1">Ganancia Neta (Mes)</p>
            <h3 class="text-3xl font-black">${{ number_format($netProfitMonth, 2) }}</h3>
            <p class="text-xs text-amber-300 mt-2">Ventas - Gastos</p>
        </div>
    </div>

    {{-- 2. GRÁFICA Y TOP VENTAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Gráfica de 7 Días --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
            <h3 class="font-bold text-stone-800 mb-4">Flujo de Dinero (Últimos 7 días)</h3>
            <div class="relative h-72 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Top Ventas --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
            <h3 class="font-bold text-stone-800 mb-4 flex items-center gap-2"><span>🏆</span> Top 5 Productos (Mes)</h3>
            <ul class="divide-y divide-stone-100">
                @forelse($topProducts as $top)
                    <li class="py-3 flex justify-between items-center">
                        <span class="font-bold text-stone-700">{{ $top->name }}</span>
                        <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold">{{ $top->total_sold }} vendidos</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-stone-400 italic text-sm">Aún no hay ventas este mes.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- 3. ALERTAS DE INVENTARIO --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center gap-2">
            <span class="text-red-500 text-xl animate-pulse">⚠️</span>
            <h3 class="font-bold text-red-800 text-lg">Alertas de Inventario Bajo</h3>
        </div>
        
        <div class="p-6">
            @if($lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty())
                <div class="text-center text-green-600 font-bold py-4 flex flex-col items-center">
                    <span class="text-4xl mb-2">✅</span>
                    Todo tu inventario está en niveles saludables.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Materia Prima --}}
                    @if($lowStockIngredients->count() > 0)
                        <div>
                            <h4 class="font-bold text-stone-700 mb-3 border-b pb-2">Materia Prima Crítica</h4>
                            <ul class="space-y-2">
                                @foreach($lowStockIngredients as $ing)
                                    <li class="flex justify-between items-center bg-red-50 p-2 rounded border border-red-100">
                                        <span class="font-bold text-stone-800">{{ $ing->name }}</span>
                                        <span class="text-red-600 font-bold text-sm">{{ floatval($ing->current_quantity) }} {{ $ing->unit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Productos Directos --}}
                    @if($lowStockProducts->count() > 0)
                        <div>
                            <h4 class="font-bold text-stone-700 mb-3 border-b pb-2">Productos Críticos</h4>
                            <ul class="space-y-2">
                                @foreach($lowStockProducts as $prod)
                                    <li class="flex justify-between items-center bg-red-50 p-2 rounded border border-red-100">
                                        <span class="font-bold text-stone-800">{{ $prod->name }}</span>
                                        <span class="text-red-600 font-bold text-sm">{{ $prod->stock }} pz</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>

{{-- SCRIPT PARA LA GRÁFICA --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartDates) !!}, // Días (Ej: 01 Mar, 02 Mar)
                datasets: [
                    {
                        label: 'Ingresos (Ventas)',
                        data: {!! json_encode($chartSales) !!},
                        backgroundColor: '#16a34a', // Verde
                        borderRadius: 4,
                    },
                    {
                        label: 'Egresos (Gastos)',
                        data: {!! json_encode($chartExpenses) !!},
                        backgroundColor: '#dc2626', // Rojo
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '$' + value; }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection