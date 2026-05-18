@extends('layouts.admin')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('employees.index') }}"
               class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition"
               title="Volver a empleados">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>

            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-2">
                    👤 Nuevo usuario
                </div>

                <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                    Nuevo Empleado
                </h1>

                <p class="text-stone-500 mt-1">
                    Registra un nuevo miembro del equipo, define su rol y crea sus credenciales de acceso.
                </p>
            </div>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-sm text-stone-600 max-w-md">
            <span class="font-bold text-amber-800">Recomendación:</span>
            asigna el rol de acuerdo con las responsabilidades reales dentro del sistema.
        </div>
    </div>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r p-4 shadow-sm">
            <p class="font-bold mb-2">Corrige los siguientes errores:</p>

            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULARIO --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-stone-100 bg-amber-50">
            <h2 class="font-bold text-amber-900 text-lg flex items-center gap-2">
                🧾 Información del usuario
            </h2>
            <p class="text-sm text-amber-700 mt-1">
                Captura los datos principales y permisos iniciales del nuevo usuario.
            </p>
        </div>

        <form method="POST" action="{{ route('employees.store') }}" class="p-6 md:p-8 space-y-8">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- DATOS PERSONALES --}}
                <div class="space-y-6">
                    <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-sm">
                        <h3 class="font-bold text-stone-800 text-lg flex items-center gap-2 mb-4">
                            👤 Datos personales
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Nombre completo
                                </label>

                                <input type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                                       placeholder="Ej. Juan Pérez"
                                       required>

                                <p class="text-xs text-stone-400 mt-1">
                                    Este nombre aparecerá en ventas, caja, movimientos y auditorías.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Correo electrónico
                                </label>

                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                                       placeholder="empleado@cafeteria.com"
                                       required>

                                <p class="text-xs text-stone-400 mt-1">
                                    El correo será usado para iniciar sesión.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ACCESO Y ROL --}}
                    <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-sm">
                        <h3 class="font-bold text-stone-800 text-lg flex items-center gap-2 mb-4">
                            🛡️ Acceso y rol
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Rol del usuario
                                </label>

                                <select name="role"
                                        class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                                        required>
                                    <option value="empleado" {{ old('role') === 'empleado' ? 'selected' : '' }}>
                                        ☕ Empleado - Punto de venta
                                    </option>

                                    <option value="gerente" {{ old('role') === 'gerente' ? 'selected' : '' }}>
                                        📋 Gerente - Operación, caja e inventario
                                    </option>

                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                        🛡️ Administrador - Acceso total
                                    </option>
                                </select>

                                <p class="text-xs text-stone-400 mt-1">
                                    El rol define qué módulos podrá utilizar dentro del sistema.
                                </p>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl border border-green-100 bg-green-50 p-4">
                                <input type="checkbox"
                                       name="active"
                                       value="1"
                                       id="active"
                                       {{ old('active', true) ? 'checked' : '' }}
                                       class="w-5 h-5 mt-1 rounded border-stone-300 text-green-600 focus:ring-green-500">

                                <div>
                                    <label for="active" class="font-bold text-green-800 cursor-pointer">
                                        Usuario activo
                                    </label>

                                    <p class="text-xs text-green-700 mt-1">
                                        Si está activo, podrá iniciar sesión después de ser creado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEGURIDAD --}}
                <div class="space-y-6">
                    <div class="bg-stone-50 border border-stone-200 rounded-2xl p-5 shadow-sm">
                        <h3 class="font-bold text-stone-800 text-lg flex items-center gap-2 mb-4">
                            🔒 Seguridad
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Contraseña
                                </label>

                                <input type="password"
                                       name="password"
                                       class="w-full rounded-xl border-stone-200 bg-white focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                                       placeholder="Mínimo 6 caracteres"
                                       required>

                                <p class="text-xs text-stone-400 mt-1">
                                    Usa una contraseña segura y fácil de recordar para el usuario.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Confirmar contraseña
                                </label>

                                <input type="password"
                                       name="password_confirmation"
                                       class="w-full rounded-xl border-stone-200 bg-white focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                                       placeholder="Repite la contraseña"
                                       required>
                            </div>
                        </div>
                    </div>

                    {{-- GUÍA DE ROLES --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                        <h3 class="font-bold text-blue-800 mb-3">
                            Guía rápida de roles
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="bg-white/70 rounded-xl p-3 border border-blue-100">
                                <p class="font-bold text-purple-700">🛡️ Administrador</p>
                                <p class="text-blue-700 text-xs mt-1">
                                    Acceso completo: usuarios, productos, inventario, caja, VIP y configuración.
                                </p>
                            </div>

                            <div class="bg-white/70 rounded-xl p-3 border border-blue-100">
                                <p class="font-bold text-sky-700">📋 Gerente</p>
                                <p class="text-blue-700 text-xs mt-1">
                                    Gestión operativa: productos, inventario, caja, ventas y clientes VIP.
                                </p>
                            </div>

                            <div class="bg-white/70 rounded-xl p-3 border border-blue-100">
                                <p class="font-bold text-blue-700">☕ Empleado</p>
                                <p class="text-blue-700 text-xs mt-1">
                                    Acceso principal al punto de venta para registrar ventas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-stone-100">
                <a href="{{ route('employees.index') }}"
                   class="inline-flex justify-center items-center px-6 py-3 rounded-xl border border-stone-200 text-stone-600 font-bold hover:bg-stone-50 transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex justify-center items-center gap-2 bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-8 rounded-xl shadow-md transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Guardar empleado
                </button>
            </div>
        </form>
    </div>
</div>

@endsection