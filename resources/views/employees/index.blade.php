@extends('layouts.admin')

@section('content')

<div class="w-full max-w-[1500px] mx-auto space-y-4 sm:space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] sm:text-xs font-bold mb-2 sm:mb-3">
                👥 Administración de personal
            </div>

            <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-amber-900 leading-tight">
                Equipo de Trabajo
            </h1>

            <p class="text-sm sm:text-base text-stone-500 mt-1 leading-snug">
                Gestiona usuarios, roles, estado de acceso y seguridad del personal.
            </p>
        </div>

        <a href="{{ route('employees.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-amber-800 text-white px-5 sm:px-6 py-3 rounded-full shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-1 font-bold">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                </path>
            </svg>
            Nuevo Empleado
        </a>
    </div>

    {{-- TARJETAS RESUMEN --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 xl:gap-5">
        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Usuarios registrados
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-stone-800 mt-1">
                        {{ $totalUsers }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-stone-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    👥
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Activos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-green-600 mt-1">
                        {{ $activeUsers }}
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
                        Administración
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-purple-600 mt-1">
                        {{ $adminUsers + $managerUsers }}
                    </h3>

                    <p class="text-[10px] sm:text-xs text-stone-400 mt-1 leading-tight">
                        {{ $adminUsers }} admin · {{ $managerUsers }} gerente(s)
                    </p>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    🛡️
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-stone-200 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-bold text-stone-500 leading-tight">
                        Inactivos
                    </p>

                    <h3 class="text-2xl sm:text-3xl font-black text-stone-500 mt-1">
                        {{ $inactiveUsers }}
                    </h3>
                </div>

                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-stone-100 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                    ⏸️
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <form method="GET" action="{{ route('employees.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_13rem_13rem_12rem_auto] gap-3 sm:gap-4 items-center">

                {{-- Buscador --}}
                <div class="relative w-full sm:col-span-2 xl:col-span-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        🔍
                    </span>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre o correo..."
                           class="w-full pl-10 pr-10 py-2.5 sm:py-3 rounded-xl border-stone-200 bg-stone-50 text-sm focus:border-amber-500 focus:ring-amber-200 transition">
                </div>

                {{-- Estado --}}
                <select name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 text-sm text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-2.5 sm:py-3">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>

                {{-- Rol --}}
                <select name="role"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 text-sm text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-2.5 sm:py-3">
                    <option value="">Todos los roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administradores</option>
                    <option value="gerente" {{ request('role') === 'gerente' ? 'selected' : '' }}>Gerentes</option>
                    <option value="empleado" {{ request('role') === 'empleado' ? 'selected' : '' }}>Empleados</option>
                </select>

                {{-- Por página --}}
                <select name="per_page"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border-stone-200 bg-stone-50 text-sm text-stone-700 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-2.5 sm:py-3">
                    <option value="6" {{ request('per_page', 12) == 6 ? 'selected' : '' }}>6 por página</option>
                    <option value="12" {{ request('per_page', 12) == 12 ? 'selected' : '' }}>12 por página</option>
                    <option value="24" {{ request('per_page', 12) == 24 ? 'selected' : '' }}>24 por página</option>
                    <option value="48" {{ request('per_page', 12) == 48 ? 'selected' : '' }}>48 por página</option>
                </select>

                <button type="submit"
                        class="w-full px-5 py-2.5 sm:py-3 rounded-xl bg-amber-800 text-white text-sm font-bold hover:bg-amber-900 transition">
                    Buscar
                </button>
            </div>

            @if(request('search') || request('status') || request('role') || request('per_page'))
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-stone-100">
                    <p class="text-xs sm:text-sm text-stone-500">
                        Mostrando empleados filtrados.
                    </p>

                    <a href="{{ route('employees.index') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- TARJETAS DE EMPLEADOS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-3 sm:gap-4 xl:gap-5">
        @forelse($employees as $employee)
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 sm:p-6 flex items-start gap-3 sm:gap-4 hover:shadow-md transition relative group min-w-0
                {{ !$employee->active ? 'opacity-75 bg-stone-50' : '' }}">

                <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-full flex items-center justify-center text-xl sm:text-2xl font-black shadow-inner shrink-0
                    {{ $employee->role === 'admin' ? 'bg-purple-100 text-purple-600' : ($employee->role === 'gerente' ? 'bg-sky-100 text-sky-600' : 'bg-blue-100 text-blue-600') }}">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0 pr-24 sm:pr-20">
                    <h3 class="font-bold text-base sm:text-lg text-stone-800 leading-tight truncate">
                        {{ $employee->name }}
                    </h3>

                    <p class="text-xs sm:text-sm text-stone-500 mb-3 truncate">
                        {{ $employee->email }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold
                            {{ $employee->role === 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-100' : ($employee->role === 'gerente' ? 'bg-sky-50 text-sky-700 border border-sky-100' : 'bg-blue-50 text-blue-700 border border-blue-100') }}">
                            {{ $employee->role === 'admin' ? '🛡️ Administrador' : ($employee->role === 'gerente' ? '📋 Gerente' : '☕ Empleado') }}
                        </span>

                        @if($employee->active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-stone-200 text-stone-600 border border-stone-300">
                                Inactivo
                            </span>
                        @endif

                        @if($employee->id === 1)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                Super Admin
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-stone-400 mt-3 sm:mt-4">
                        Registrado: {{ $employee->created_at ? $employee->created_at->format('d/m/Y') : 'Sin fecha' }}
                    </p>
                </div>

                {{-- Acciones --}}
                <div class="absolute top-3 right-3 sm:top-4 sm:right-4 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                    <div class="flex gap-1 sm:gap-2 items-center">

                        <a href="{{ route('employees.edit', $employee) }}"
                        class="p-2 text-stone-400 hover:text-amber-600 hover:bg-amber-50 rounded-full transition"
                        title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                </path>
                            </svg>
                        </a>

                        @if(auth()->id() !== $employee->id && $employee->id !== 1)
                            @if($employee->active)
                                <form method="POST"
                                    action="{{ route('employees.destroy', $employee) }}"
                                    onsubmit="return confirm('¿Dar de baja a {{ $employee->name }}? No se eliminará su historial.')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="p-2 text-stone-400 hover:text-orange-600 hover:bg-orange-50 rounded-full transition"
                                            title="Dar de baja">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 5.636l-12.728 12.728M6.343 6.343l11.314 11.314M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <div class="p-2 text-stone-300 cursor-not-allowed" title="Usuario dado de baja">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18.364 5.636l-12.728 12.728M6.343 6.343l11.314 11.314M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                                    </svg>
                                </div>
                            @endif

                            <form method="POST"
                                action="{{ route('employees.force-delete', $employee) }}"
                                onsubmit="return confirm('¿Eliminar definitivamente a {{ $employee->name }}? Solo se permitirá si no tiene historial asociado.')">
                                @csrf
                                @method('DELETE')

                                <button class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-full transition"
                                        title="Eliminar definitivamente">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <div class="p-2 text-stone-300 cursor-not-allowed" title="Usuario protegido">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="2xl:col-span-3 md:col-span-2 col-span-1 text-center py-12 sm:py-14 px-4 bg-white rounded-2xl border border-dashed border-stone-300">
                <span class="text-5xl block mb-3">👥</span>

                <p class="text-lg font-bold text-stone-600">
                    No se encontraron empleados.
                </p>

                <p class="text-sm text-stone-400 mt-1">
                    Prueba limpiar filtros o registra un nuevo empleado.
                </p>

                <a href="{{ route('employees.index') }}"
                class="inline-flex mt-4 px-4 py-2 rounded-xl bg-stone-100 text-stone-700 text-sm font-bold hover:bg-stone-200 transition">
                    Limpiar filtros
                </a>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-3 sm:p-4 pb-6 sm:pb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4 overflow-x-auto">
        <p class="text-xs sm:text-sm text-stone-500">
            Mostrando {{ $employees->firstItem() ?? 0 }} a {{ $employees->lastItem() ?? 0 }}
            de {{ $employees->total() }} usuario(s).
        </p>

        <div>
            {{ $employees->links() }}
        </div>
    </div>

</div>

@endsection