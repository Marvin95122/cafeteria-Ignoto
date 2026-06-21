@extends('layouts.admin')

@section('content')
<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    editForm: { id: '', name: '', phone: '', points: 0, active: true },
    openEdit(customer) {
        this.editForm.id = customer.id;
        this.editForm.name = customer.name;
        this.editForm.phone = customer.phone;
        this.editForm.points = customer.points;
        this.editForm.active = Boolean(customer.active);
        this.showEditModal = true;
    }
}" class="w-full max-w-[1500px] mx-auto space-y-4 sm:space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] sm:text-xs font-bold mb-2 sm:mb-3">
                👑 Programa de lealtad
            </div>

            <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-amber-900 leading-tight">
                Clientes VIP
            </h1>

            <p class="text-sm sm:text-base text-stone-500 mt-1 leading-snug">
                Administra clientes frecuentes, puntos acumulados, estado de cuenta y reglas de canje.
            </p>
        </div>

        <button type="button"
                @click="showCreateModal = true"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-amber-800 text-white px-5 sm:px-6 py-3 rounded-full shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-1 font-bold">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                </path>
            </svg>
            Nuevo Cliente VIP
        </button>
    </div>

    {{-- ERRORES --}}
    @if($errors->any())
        <div class="p-4 bg-red-50 border-l-4 border-red-600 text-red-800 rounded-r-xl text-sm shadow-sm">
            <p class="font-bold mb-2">Corrige los siguientes errores:</p>

            <ul class="list-disc pl-4 space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TARJETAS RESUMEN --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 xl:gap-5">
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Clientes registrados
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-stone-800 mt-1">
                        {{ $totalCustomers }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    👑
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Clientes activos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-green-600 mt-1">
                        {{ $activeCustomers }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-green-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    ✅
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Clientes inactivos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-stone-500 mt-1">
                        {{ $inactiveCustomers }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-stone-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    ⏸️
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-4 sm:p-5 rounded-2xl shadow-md border border-amber-800 relative overflow-hidden text-white">
            <div class="absolute right-[-6px] top-[-8px] text-5xl sm:text-6xl opacity-20">
                🎁
            </div>

            <p class="text-xs sm:text-sm font-bold text-amber-200 leading-tight">
                Puntos acumulados
            </p>

            <h3 class="text-2xl sm:text-3xl font-black mt-1">
                {{ number_format($totalPoints) }}
            </h3>

            <p class="text-[10px] sm:text-xs text-amber-200 mt-1 leading-tight">
                Valor estimado: ${{ number_format($estimatedPointsValue, 2) }}
            </p>
        </div>
    </div>

    {{-- PANEL DE CONFIGURACIÓN GLOBAL DE PUNTOS --}}
    <div class="bg-white p-3 sm:p-5 lg:p-6 rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-5 border-b border-stone-100 pb-4">
            <div class="flex items-start sm:items-center gap-3 min-w-0">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    ⚙️
                </div>

                <div class="min-w-0">
                    <h3 class="font-bold text-base sm:text-lg text-stone-800 leading-tight">
                        Reglas de acumulación y canje
                    </h3>
                    <p class="text-xs text-stone-400 leading-snug">
                        Configura cuántos puntos gana el cliente y cuánto valor tienen al pagar.
                    </p>
                </div>
            </div>

            @if(Auth::user()->role !== 'admin')
                <span class="w-full sm:w-auto text-center inline-flex justify-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-100">
                    Solo lectura para gerente
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('vip.settings.update') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 items-end">
            @csrf

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1.5 leading-snug">
                    Dinero requerido para ganar 1 punto ($)
                </label>

                <input type="number"
                       name="money_for_point"
                       value="{{ $moneyForOnePoint }}"
                       step="0.5"
                       min="1"
                       class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500"
                       {{ Auth::user()->role !== 'admin' ? 'readonly' : '' }}>

                <span class="text-[11px] text-stone-400 block mt-1 leading-snug">
                    Ej: por cada ${{ $moneyForOnePoint }} pesos gastados, se otorga 1 punto.
                </span>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-600 mb-1.5 leading-snug">
                    Valor monetario de 1 punto al pagar ($)
                </label>

                <input type="number"
                       name="point_value"
                       value="{{ $pointValue }}"
                       step="0.5"
                       min="0.5"
                       class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500"
                       {{ Auth::user()->role !== 'admin' ? 'readonly' : '' }}>

                <span class="text-[11px] text-stone-400 block mt-1 leading-snug">
                    Ej: 1 punto equivale a ${{ $pointValue }} pesos de descuento.
                </span>
            </div>

            <div>
                @if(Auth::user()->role === 'admin')
                    <button type="submit"
                            class="w-full bg-stone-800 hover:bg-stone-900 text-white font-bold py-2.5 px-4 rounded-lg text-sm transition shadow">
                        Guardar ajustes
                    </button>
                @else
                    <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-[11px] text-amber-800 text-center font-medium leading-snug">
                        🔒 Solo el Administrador puede cambiar la tasa de puntos.
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- SECCIÓN DE LISTADO Y BÚSQUEDA --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="p-3 sm:p-5 lg:p-6 border-b border-stone-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0">
                <h3 class="font-bold text-base sm:text-lg text-stone-800 flex items-center gap-2">
                    📇 Directorio de clientes VIP
                </h3>
                <p class="text-xs sm:text-sm text-stone-500 mt-1 leading-snug">
                    Consulta, filtra y administra cuentas del programa de lealtad.
                </p>
            </div>

            <form method="GET"
                  action="{{ route('vip.index') }}"
                  class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_12rem_11rem_auto] gap-3 w-full lg:max-w-4xl">

                <div class="relative w-full">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre o teléfono..."
                           class="w-full text-sm rounded-xl border-stone-300 pl-9 pr-4 py-2.5 sm:py-3 focus:ring-amber-500 focus:border-amber-500 bg-stone-50">

                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm">
                        🔍
                    </span>
                </div>

                <select name="status"
                        onchange="this.form.submit()"
                        class="w-full text-sm rounded-xl border-stone-300 px-3 py-2.5 sm:py-3 focus:ring-amber-500 focus:border-amber-500 bg-stone-50">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>

                <select name="per_page"
                        onchange="this.form.submit()"
                        class="w-full text-sm rounded-xl border-stone-300 px-3 py-2.5 sm:py-3 focus:ring-amber-500 focus:border-amber-500 bg-stone-50">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 por página</option>
                    <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20 por página</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 por página</option>
                    <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 por página</option>
                </select>

                <button type="submit"
                        class="w-full px-5 py-2.5 sm:py-3 rounded-xl bg-amber-800 text-white text-sm font-bold hover:bg-amber-900 transition">
                    Buscar
                </button>
            </form>
        </div>

        @if(request('search') || request('status') || request('per_page'))
            <div class="px-4 sm:px-6 py-3 bg-stone-50 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs sm:text-sm text-stone-500">
                    Mostrando clientes filtrados.
                </p>

                <a href="{{ route('vip.index') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 text-sm font-bold hover:bg-stone-100 transition">
                    Limpiar filtros
                </a>
            </div>
        @endif

        {{-- TABLA EN TABLET/PC --}}
        <div class="hidden md:block overflow-x-auto max-h-[560px] overflow-y-auto">
            <table class="w-full text-left border-collapse text-sm min-w-[980px]">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-stone-50 text-stone-500 text-xs uppercase border-b border-stone-100 font-bold shadow-sm">
                        <th class="py-3 px-6">Cliente</th>
                        <th class="py-3 px-6">Teléfono</th>
                        <th class="py-3 px-6 text-center">Puntos</th>
                        <th class="py-3 px-6 text-center">Compras</th>
                        <th class="py-3 px-6">Registro</th>
                        <th class="py-3 px-6 text-center">Estado</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100 text-stone-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-amber-50/30 transition {{ !$customer->active ? 'opacity-75 bg-stone-50/60' : '' }}">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="h-10 w-10 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-black shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </span>

                                    <div class="min-w-0">
                                        <p class="font-bold text-stone-900 truncate">
                                            {{ $customer->name }}
                                        </p>

                                        <p class="text-xs text-stone-400">
                                            Cliente #{{ $customer->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-6 font-mono text-stone-600 whitespace-nowrap">
                                📱 {{ $customer->phone }}
                            </td>

                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $customer->points > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-stone-100 text-stone-500' }}">
                                    🎁 {{ number_format($customer->points) }} pts
                                </span>

                                <p class="text-[10px] text-stone-400 mt-1">
                                    ≈ ${{ number_format($customer->points * (float) $pointValue, 2) }}
                                </p>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $customer->orders_count }} ticket(s)
                                </span>
                            </td>

                            <td class="py-4 px-6 text-xs text-stone-400 whitespace-nowrap">
                                {{ $customer->created_at->format('d/m/Y') }}
                            </td>

                            <td class="py-4 px-6 text-center">
                                @if($customer->active)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-stone-200 text-stone-600">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-6 text-center">
                                <div class="flex justify-center gap-2">
                                    <button type="button"
                                            @click='openEdit(@json($customer))'
                                            class="px-3 py-1.5 text-xs bg-stone-100 hover:bg-amber-100 hover:text-amber-900 text-stone-600 rounded-lg font-bold transition">
                                        Editar
                                    </button>

                                    @if($customer->active)
                                        <form method="POST"
                                              action="{{ route('vip.customer.destroy', $customer) }}"
                                              onsubmit="return confirm('¿Dar de baja a este cliente VIP? Sus puntos e historial se conservarán.')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="px-3 py-1.5 text-xs bg-stone-100 hover:bg-orange-100 hover:text-orange-700 text-stone-600 rounded-lg font-bold transition">
                                                Baja
                                            </button>
                                        </form>
                                    @else
                                        <span class="px-3 py-1.5 text-xs bg-stone-100 text-stone-400 rounded-lg font-bold">
                                            Dado de baja
                                        </span>
                                    @endif

                                    <form method="POST"
                                          action="{{ route('vip.customer.force-delete', $customer) }}"
                                          onsubmit="return confirm('¿Eliminar definitivamente este cliente? Solo se permitirá si no tiene ventas asociadas.')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="px-3 py-1.5 text-xs bg-stone-100 hover:bg-red-100 hover:text-red-700 text-stone-600 rounded-lg font-bold transition">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-14 text-stone-400">
                                <span class="text-5xl block mb-3">👑</span>

                                <p class="text-lg font-bold text-stone-600">
                                    No se encontraron clientes VIP.
                                </p>

                                <p class="text-sm text-stone-400 mt-1">
                                    Prueba limpiar filtros o registra un nuevo cliente.
                                </p>

                                <a href="{{ route('vip.index') }}"
                                   class="inline-flex mt-4 px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                                    Limpiar filtros
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TARJETAS EN CELULAR --}}
        <div class="md:hidden p-3 space-y-3 bg-stone-50/50">
            @forelse($customers as $customer)
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 relative overflow-hidden {{ !$customer->active ? 'opacity-75 bg-stone-50' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="h-12 w-12 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-black shrink-0">
                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                        </span>

                        <div class="flex-1 min-w-0 pr-3">
                            <h3 class="font-bold text-stone-900 leading-tight break-words">
                                {{ $customer->name }}
                            </h3>

                            <p class="text-xs text-stone-400 mt-1">
                                Cliente #{{ $customer->id }} · {{ $customer->created_at->format('d/m/Y') }}
                            </p>

                            <p class="text-xs font-mono text-stone-600 mt-1 break-all">
                                📱 {{ $customer->phone }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $customer->points > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-stone-100 text-stone-500' }}">
                            🎁 {{ number_format($customer->points) }} pts
                        </span>

                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $customer->orders_count }} ticket(s)
                        </span>

                        @if($customer->active)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                Activo
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-stone-200 text-stone-600">
                                Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 rounded-xl bg-amber-50 border border-amber-100 p-3 text-xs text-amber-800">
                        <span class="font-black block">Valor estimado</span>
                        ≈ ${{ number_format($customer->points * (float) $pointValue, 2) }} disponibles para descuento.
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <button type="button"
                                @click='openEdit(@json($customer))'
                                class="px-3 py-2 text-xs bg-stone-100 hover:bg-amber-100 hover:text-amber-900 text-stone-600 rounded-xl font-bold transition">
                            Editar
                        </button>

                        @if($customer->active)
                            <form method="POST"
                                  action="{{ route('vip.customer.destroy', $customer) }}"
                                  onsubmit="return confirm('¿Dar de baja a este cliente VIP? Sus puntos e historial se conservarán.')">
                                @csrf
                                @method('DELETE')

                                <button class="w-full px-3 py-2 text-xs bg-stone-100 hover:bg-orange-100 hover:text-orange-700 text-stone-600 rounded-xl font-bold transition">
                                    Baja
                                </button>
                            </form>
                        @else
                            <span class="w-full inline-flex justify-center px-3 py-2 text-xs bg-stone-100 text-stone-400 rounded-xl font-bold">
                                Baja
                            </span>
                        @endif

                        <form method="POST"
                              action="{{ route('vip.customer.force-delete', $customer) }}"
                              onsubmit="return confirm('¿Eliminar definitivamente este cliente? Solo se permitirá si no tiene ventas asociadas.')">
                            @csrf
                            @method('DELETE')

                            <button class="w-full px-3 py-2 text-xs bg-red-50 hover:bg-red-100 text-red-700 rounded-xl font-bold transition">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 bg-white rounded-2xl border border-dashed border-stone-300">
                    <span class="text-5xl block mb-3">👑</span>

                    <p class="text-lg font-bold text-stone-600">
                        No se encontraron clientes VIP.
                    </p>

                    <p class="text-sm text-stone-400 mt-1">
                        Prueba limpiar filtros o registra un nuevo cliente.
                    </p>

                    <a href="{{ route('vip.index') }}"
                       class="inline-flex mt-4 px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                        Limpiar filtros
                    </a>
                </div>
            @endforelse
        </div>

        {{-- PAGINACIÓN --}}
        <div class="p-3 sm:p-4 pb-6 sm:pb-4 border-t border-stone-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4 overflow-x-auto">
            <p class="text-xs sm:text-sm text-stone-500">
                Mostrando {{ $customers->firstItem() ?? 0 }} a {{ $customers->lastItem() ?? 0 }}
                de {{ $customers->total() }} cliente(s).
            </p>

            <div>
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL: NUEVO CLIENTE --}}
    <div x-show="showCreateModal"
         class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50"
         x-cloak>
        <div @click.away="showCreateModal = false"
             class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-xl border border-stone-100 transform transition-all">
            <div class="flex justify-between items-start gap-3 mb-4 border-b border-stone-100 pb-3">
                <div>
                    <h3 class="font-bold text-lg text-amber-900 font-serif leading-tight">
                        ✨ Registrar Nuevo VIP
                    </h3>
                    <p class="text-xs text-stone-400 mt-1">
                        Captura los datos iniciales del cliente.
                    </p>
                </div>

                <button type="button"
                        @click="showCreateModal = false"
                        class="text-stone-400 hover:text-stone-600 text-lg font-bold shrink-0">
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('vip.customer.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Nombre Completo
                    </label>

                    <input type="text"
                           name="name"
                           required
                           placeholder="Ej: Alejandra Gómez"
                           class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Teléfono Celular (10 dígitos)
                    </label>

                    <input type="tel"
                           name="phone"
                           required
                           placeholder="Ej: 9511234567"
                           class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Saldo Inicial de Puntos
                    </label>

                    <input type="number"
                           name="points"
                           value="0"
                           min="0"
                           required
                           class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500">

                    <span class="text-[10px] text-stone-400 mt-1 block leading-snug">
                        Por defecto inicia en 0. Puedes otorgar puntos de bienvenida.
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-3 border-t border-stone-50">
                    <button type="button"
                            @click="showCreateModal = false"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-stone-500 hover:bg-stone-100 rounded-lg transition">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold bg-amber-800 hover:bg-amber-900 text-white rounded-lg transition shadow">
                        Registrar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDITAR CLIENTE --}}
    <div x-show="showEditModal"
         class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 z-50"
         x-cloak>
        <div @click.away="showEditModal = false"
             class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 shadow-xl border border-stone-100 transform transition-all">
            <div class="flex justify-between items-start gap-3 mb-4 border-b border-stone-100 pb-3">
                <div>
                    <h3 class="font-bold text-lg text-amber-900 font-serif leading-tight">
                        ✏️ Modificar Cuenta VIP
                    </h3>
                    <p class="text-xs text-stone-400 mt-1">
                        Actualiza datos, puntos y estado del cliente.
                    </p>
                </div>

                <button type="button"
                        @click="showEditModal = false"
                        class="text-stone-400 hover:text-stone-600 text-lg font-bold shrink-0">
                    ×
                </button>
            </div>

            <form method="POST" :action="'{{ url('vip-management/customer') }}/' + editForm.id" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Nombre Completo
                    </label>

                    <input type="text"
                           name="name"
                           x-model="editForm.name"
                           required
                           class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Teléfono Celular
                    </label>

                    <input type="tel"
                           name="phone"
                           x-model="editForm.phone"
                           required
                           class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1">
                        Ajustar Saldo de Puntos
                    </label>

                    <input type="number"
                           name="points"
                           x-model="editForm.points"
                           min="0"
                           required
                           class="w-full rounded-lg border-stone-300 text-sm py-2.5 focus:ring-amber-500 focus:border-amber-500 font-bold text-amber-800">

                    <span class="text-[10px] text-amber-700 mt-1 block leading-snug">
                        ⚠️ Modificar el saldo directamente afectará el poder de compra del cliente.
                    </span>
                </div>

                <label class="flex items-start gap-2 bg-stone-50 border border-stone-100 rounded-lg px-3 py-2 cursor-pointer">
                    <input type="checkbox"
                           name="active"
                           value="1"
                           x-model="editForm.active"
                           class="mt-0.5 rounded border-stone-300 text-amber-700 focus:ring-amber-600 shrink-0">

                    <span class="text-xs font-bold text-stone-700 leading-snug">
                        Cliente VIP activo
                    </span>
                </label>

                <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-3 border-t border-stone-50">
                    <button type="button"
                            @click="showEditModal = false"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-stone-500 hover:bg-stone-100 rounded-lg transition">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold bg-stone-800 hover:bg-stone-900 text-white rounded-lg transition shadow">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
