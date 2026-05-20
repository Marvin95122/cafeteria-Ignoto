@extends('layouts.admin')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📊 Panel general
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Resumen Financiero y Operativo
            </h1>

            <p class="text-stone-500 mt-1">
                Consulta ventas, caja, inventario, clientes VIP y actividad reciente del sistema.
            </p>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-sm text-stone-600">
            <span class="font-bold text-amber-800">Hoy:</span>
            {{ now()->format('d/m/Y h:i A') }}
        </div>
    </div>

    {{-- ESTADO DE CAJA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl {{ $activeRegister ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center text-2xl">
                    {{ $activeRegister ? '💰' : '🔒' }}
                </div>

                <div>
                    <h2 class="font-black text-stone-800 text-lg">
                        {{ $activeRegister ? 'Caja abierta' : 'Caja cerrada' }}
                    </h2>

                    @if($activeRegister)
                        <p class="text-sm text-stone-500">
                            Apertura por {{ $activeRegister->user->name ?? 'Usuario' }}
                            · {{ $activeRegister->opened_at->format('d/m/Y h:i A') }}
                        </p>
                    @else
                        <p class="text-sm text-stone-500">
                            No hay turno de caja activo. Abre caja para registrar ventas.
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                @if($activeRegister)
                    <div class="bg-green-50 border border-green-100 rounded-2xl px-4 py-3">
                        <p class="text-xs font-bold text-green-700 uppercase">
                            Efectivo esperado
                        </p>
                        <p class="text-xl font-black text-green-800">
                            ${{ number_format($expectedCash, 2) }}
                        </p>
                    </div>
                @endif

                <a href="{{ route('cash_registers.index') }}"
                   class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-amber-800 text-white font-bold hover:bg-amber-900 transition">
                    Ir a Corte de Caja
                </a>
            </div>
        </div>
    </div>

    {{-- TARJETAS PRINCIPALES --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Ventas de hoy</p>
                    <h3 class="text-3xl font-black text-green-600 mt-1">
                        ${{ number_format($salesToday, 2) }}
                    </h3>
                    <p class="text-xs text-stone-400 mt-1">
                        {{ $ordersToday }} ticket(s) · Promedio ${{ number_format($averageTicketToday, 2) }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-2xl">
                    💵
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Ventas semana</p>
                    <h3 class="text-3xl font-black text-blue-600 mt-1">
                        ${{ number_format($salesWeek, 2) }}
                    </h3>
                    <p class="text-xs text-stone-400 mt-1">
                        Ingresos acumulados de la semana.
                    </p>
                </div>

                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl">
                    📈
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-stone-500">Gastos del mes</p>
                    <h3 class="text-3xl font-black text-red-600 mt-1">
                        ${{ number_format($expensesMonth, 2) }}
                    </h3>
                    <p class="text-xs text-stone-400 mt-1">
                        Hoy: ${{ number_format($expensesToday, 2) }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-2xl">
                    💸
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-5 rounded-2xl shadow-md border border-amber-800 relative overflow-hidden text-white">
            <div class="absolute right-[-6px] top-[-8px] text-6xl opacity-20">
                📊
            </div>

            <p class="text-sm font-bold text-amber-200">
                Ganancia neta del mes
            </p>

            <h3 class="text-3xl font-black mt-1">
                ${{ number_format($netProfitMonth, 2) }}
            </h3>

            <p class="text-xs text-amber-200 mt-1">
                Ventas del mes - gastos activos.
            </p>
        </div>
    </div>

    {{-- TARJETAS SECUNDARIAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Ventas canceladas hoy</p>
            <h3 class="text-2xl font-black text-orange-600 mt-1">
                {{ $cancelledOrdersToday }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Alertas de inventario</p>
            <h3 class="text-2xl font-black {{ $totalInventoryAlerts > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                {{ $totalInventoryAlerts }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Clientes VIP</p>
            <h3 class="text-2xl font-black text-amber-700 mt-1">
                {{ $totalCustomers }}
            </h3>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-200">
            <p class="text-sm font-bold text-stone-500">Puntos VIP acumulados</p>
            <h3 class="text-2xl font-black text-purple-600 mt-1">
                {{ number_format($totalVipPoints) }}
            </h3>
        </div>
    </div>

    {{-- ACCESOS RÁPIDOS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
            <div>
                <h2 class="font-bold text-stone-800 text-lg">
                    Accesos rápidos
                </h2>
                <p class="text-sm text-stone-500">
                    Atajos a los módulos más usados durante la operación.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
            <a href="{{ route('pos.index') }}"
               class="p-4 rounded-2xl bg-amber-50 text-amber-800 font-bold hover:bg-amber-100 transition text-center">
                🧾<br>
                <span class="text-sm">POS</span>
            </a>

            <a href="{{ route('cash_registers.index') }}"
               class="p-4 rounded-2xl bg-green-50 text-green-700 font-bold hover:bg-green-100 transition text-center">
                💰<br>
                <span class="text-sm">Caja</span>
            </a>

            <a href="{{ route('inventory_movements.index') }}"
               class="p-4 rounded-2xl bg-blue-50 text-blue-700 font-bold hover:bg-blue-100 transition text-center">
                📋<br>
                <span class="text-sm">Inventario</span>
            </a>

            <a href="{{ route('products.index') }}"
               class="p-4 rounded-2xl bg-stone-50 text-stone-700 font-bold hover:bg-stone-100 transition text-center">
                ☕<br>
                <span class="text-sm">Productos</span>
            </a>

            <a href="{{ route('ingredients.index') }}"
               class="p-4 rounded-2xl bg-orange-50 text-orange-700 font-bold hover:bg-orange-100 transition text-center">
                📦<br>
                <span class="text-sm">Materia Prima</span>
            </a>

            <a href="{{ route('vip.index') }}"
               class="p-4 rounded-2xl bg-purple-50 text-purple-700 font-bold hover:bg-purple-100 transition text-center">
                👑<br>
                <span class="text-sm">VIP</span>
            </a>
        </div>
    </div>

    {{-- GRÁFICA Y TOP PRODUCTOS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="font-bold text-stone-800 text-lg">
                        Flujo de dinero
                    </h3>
                    <p class="text-sm text-stone-500">
                        Ingresos y egresos de los últimos 7 días.
                    </p>
                </div>
            </div>

            <div class="relative h-72 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
            <h3 class="font-bold text-stone-800 mb-4 flex items-center gap-2">
                🏆 Top 5 productos del mes
            </h3>

            <ul class="divide-y divide-stone-100">
                @forelse($topProducts as $top)
                    <li class="py-3">
                        <div class="flex justify-between items-center gap-3">
                            <span class="font-bold text-stone-700">
                                {{ $top->name }}
                            </span>

                            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap">
                                {{ $top->total_sold }} vendidos
                            </span>
                        </div>

                        <p class="text-xs text-stone-400 mt-1">
                            Ingreso generado: ${{ number_format($top->total_revenue, 2) }}
                        </p>
                    </li>
                @empty
                    <li class="py-8 text-center text-stone-400 italic text-sm">
                        Aún no hay ventas este mes.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ALERTAS Y VENTAS RECIENTES --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- ALERTAS DE INVENTARIO --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-red-900 flex items-center gap-2">
                        ⚠️ Alertas de inventario
                    </h3>
                    <p class="text-xs text-red-700 mt-1">
                        Productos o insumos que requieren atención.
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full bg-white border border-red-100 text-red-700 text-xs font-bold">
                    {{ $totalInventoryAlerts }} alerta(s)
                </span>
            </div>

            <div class="p-5">
                @if($lowStockProducts->isEmpty() && $lowStockIngredients->isEmpty())
                    <div class="text-center text-green-600 font-bold py-8 flex flex-col items-center">
                        <span class="text-5xl mb-3">✅</span>
                        Todo tu inventario está en niveles saludables.
                    </div>
                @else
                    <div class="space-y-5">
                        @if($lowStockIngredients->count() > 0)
                            <div>
                                <h4 class="font-bold text-stone-700 mb-3">
                                    Materia prima crítica
                                </h4>

                                <ul class="space-y-2">
                                    @foreach($lowStockIngredients as $ingredient)
                                        <li class="flex justify-between items-center bg-red-50 p-3 rounded-xl border border-red-100">
                                            <span class="font-bold text-stone-800">
                                                {{ $ingredient->name }}
                                            </span>

                                            <span class="text-red-600 font-bold text-sm">
                                                {{ $ingredient->full_quantity }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($lowStockProducts->count() > 0)
                            <div>
                                <h4 class="font-bold text-stone-700 mb-3">
                                    Productos críticos
                                </h4>

                                <ul class="space-y-2">
                                    @foreach($lowStockProducts as $product)
                                        <li class="flex justify-between items-center bg-red-50 p-3 rounded-xl border border-red-100">
                                            <div>
                                                <span class="font-bold text-stone-800 block">
                                                    {{ $product->name }}
                                                </span>
                                                <span class="text-xs text-stone-400">
                                                    {{ $product->category->name ?? 'Sin categoría' }}
                                                </span>
                                            </div>

                                            <span class="text-red-600 font-bold text-sm">
                                                {{ $product->calculated_stock }} disp.
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- VENTAS RECIENTES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="bg-stone-50 px-6 py-4 border-b border-stone-100">
                <h3 class="font-bold text-stone-800 flex items-center gap-2">
                    🧾 Ventas recientes
                </h3>
                <p class="text-xs text-stone-500 mt-1">
                    Últimos tickets registrados en el sistema.
                </p>
            </div>

            <div class="divide-y divide-stone-100">
                @forelse($recentOrders as $order)
                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-stone-50 transition">
                        <div>
                            <p class="font-bold text-stone-800">
                                Ticket #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </p>

                            <p class="text-xs text-stone-400">
                                {{ $order->created_at->format('d/m/Y h:i A') }}
                                · {{ $order->user->name ?? 'Caja' }}
                            </p>

                            @if($order->customer)
                                <p class="text-xs text-amber-700 mt-1">
                                    👑 {{ $order->customer->name }}
                                </p>
                            @endif
                        </div>

                        <div class="text-right">
                            <p class="font-black {{ $order->status === 'completado' ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($order->total, 2) }}
                            </p>

                            <span class="text-[10px] px-2 py-1 rounded-full font-bold
                                {{ $order->status === 'completado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-stone-400">
                        No hay ventas recientes.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

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
                        label: 'Ingresos',
                        data: {!! json_encode($chartSales) !!},
                        backgroundColor: '#16a34a',
                        borderRadius: 6,
                    },
                    {
                        label: 'Gastos',
                        data: {!! json_encode($chartExpenses) !!},
                        backgroundColor: '#dc2626',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
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