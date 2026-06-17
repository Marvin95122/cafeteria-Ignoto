@extends('layouts.admin')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                📑 Módulo de reportes
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Reportes
            </h1>

            <p class="text-stone-500 mt-1">
                Consulta información ejecutiva del sistema para analizar ventas, caja, inventario, productos y clientes.
            </p>
        </div>
    </div>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <a href="{{ route('reports.sales') }}"
           class="group bg-white rounded-2xl shadow-sm border border-stone-200 p-6 hover:shadow-lg hover:border-amber-200 transition">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-3xl mb-4 group-hover:scale-105 transition">
                📈
            </div>

            <h2 class="font-serif text-2xl font-black text-amber-900">
                Reporte de ventas
            </h2>

            <p class="text-sm text-stone-500 mt-2">
                Analiza ventas por periodo, método de pago, productos vendidos, cancelaciones, gastos y utilidad operativa.
            </p>

            <div class="mt-5 inline-flex items-center gap-2 text-amber-800 font-black text-sm">
                Abrir reporte →
            </div>
        </a>

        <a href="{{ route('reports.cash') }}"
            class="group bg-white rounded-2xl shadow-sm border border-stone-200 p-6 hover:shadow-lg hover:border-amber-200 transition">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-3xl mb-4 group-hover:scale-105 transition">
                💰
            </div>

            <h2 class="font-serif text-2xl font-black text-amber-900">
                Reporte de caja
            </h2>

            <p class="text-sm text-stone-500 mt-2">
                Analiza cortes, efectivo esperado, efectivo contado, diferencias, gastos, ventas en efectivo y auditoría.
            </p>

            <div class="mt-5 inline-flex items-center gap-2 text-amber-800 font-black text-sm">
                Abrir reporte →
            </div>
        </a>

        <div class="bg-white/70 rounded-2xl shadow-sm border border-stone-200 p-6 opacity-75">
            <div class="w-14 h-14 rounded-2xl bg-stone-100 text-stone-500 flex items-center justify-center text-3xl mb-4">
                📦
            </div>

            <h2 class="font-serif text-2xl font-black text-stone-700">
                Reporte de inventario
            </h2>

            <p class="text-sm text-stone-500 mt-2">
                Entradas, salidas, mermas, materia prima crítica y movimientos de inventario.
            </p>

            <span class="mt-5 inline-flex px-3 py-1 rounded-full bg-stone-100 text-stone-500 text-xs font-black">
                Próximamente
            </span>
        </div>

        <div class="bg-white/70 rounded-2xl shadow-sm border border-stone-200 p-6 opacity-75">
            <div class="w-14 h-14 rounded-2xl bg-stone-100 text-stone-500 flex items-center justify-center text-3xl mb-4">
                ☕
            </div>

            <h2 class="font-serif text-2xl font-black text-stone-700">
                Reporte de productos
            </h2>

            <p class="text-sm text-stone-500 mt-2">
                Productos activos, stock bajo, productos más vendidos y rendimiento por categoría.
            </p>

            <span class="mt-5 inline-flex px-3 py-1 rounded-full bg-stone-100 text-stone-500 text-xs font-black">
                Próximamente
            </span>
        </div>

        <div class="bg-white/70 rounded-2xl shadow-sm border border-stone-200 p-6 opacity-75">
            <div class="w-14 h-14 rounded-2xl bg-stone-100 text-stone-500 flex items-center justify-center text-3xl mb-4">
                👑
            </div>

            <h2 class="font-serif text-2xl font-black text-stone-700">
                Reporte de clientes VIP
            </h2>

            <p class="text-sm text-stone-500 mt-2">
                Clientes frecuentes, puntos acumulados, puntos usados y compras por cliente.
            </p>

            <span class="mt-5 inline-flex px-3 py-1 rounded-full bg-stone-100 text-stone-500 text-xs font-black">
                Próximamente
            </span>
        </div>

        <div class="bg-white/70 rounded-2xl shadow-sm border border-stone-200 p-6 opacity-75">
            <div class="w-14 h-14 rounded-2xl bg-stone-100 text-stone-500 flex items-center justify-center text-3xl mb-4">
                🧾
            </div>

            <h2 class="font-serif text-2xl font-black text-stone-700">
                Reporte general
            </h2>

            <p class="text-sm text-stone-500 mt-2">
                Vista ejecutiva general de ventas, caja, inventario y clientes.
            </p>

            <span class="mt-5 inline-flex px-3 py-1 rounded-full bg-stone-100 text-stone-500 text-xs font-black">
                Próximamente
            </span>
        </div>
    </div>

</div>

@endsection