@extends('layouts.admin')

@section('content')

<div class="w-full max-w-5xl mx-auto space-y-4 sm:space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-4">
        <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0">
            <a href="{{ route('employees.index') }}"
               class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition shrink-0"
               title="Volver a empleados">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>

            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] sm:text-xs font-bold mb-2">
                    ✏️ Edición de usuario
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-amber-900 leading-tight">
                    Editar Empleado
                </h1>

                <p class="text-sm sm:text-base text-stone-500 mt-1 leading-snug">
                    Actualiza datos, rol, estado y credenciales de
                    <span class="font-bold text-stone-700 break-words">{{ $user->name }}</span>.
                </p>
            </div>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-xs sm:text-sm text-stone-600 w-full lg:w-auto">
            <span class="font-bold text-amber-800">Estado actual:</span>

            @if($user->active)
                <span class="text-green-700 font-bold">Activo</span>
            @else
                <span class="text-stone-500 font-bold">Inactivo</span>
            @endif
        </div>
    </div>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r p-4 shadow-sm text-sm">
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
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-stone-100 bg-amber-50">
            <h2 class="font-bold text-amber-900 text-base sm:text-lg flex items-center gap-2">
                🧾 Información del usuario
            </h2>

            <p class="text-xs sm:text-sm text-amber-700 mt-1 leading-snug">
                Modifica los datos del usuario y conserva la trazabilidad de sus operaciones.
            </p>
        </div>

        <form method="POST" action="{{ route('employees.update', $user) }}" class="p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6 xl:gap-8">

                {{-- INFORMACIÓN GENERAL --}}
                <div class="space-y-4 sm:space-y-6 min-w-0">
                    <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                        <h3 class="font-bold text-stone-800 text-base sm:text-lg flex items-center gap-2 mb-4">
                            👤 Información general
                        </h3>

                        <div class="space-y-4 sm:space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Nombre
                                </label>

                                <input type="text"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                                       required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Correo electrónico
                                </label>

                                @if($user->id == 1 && auth()->id() != 1)
                                    <div class="relative">
                                        <input type="email"
                                               name="email"
                                               value="{{ $user->email }}"
                                               class="w-full rounded-xl border-stone-200 bg-stone-100 text-stone-500 py-2.5 sm:py-3 px-3 sm:px-4 pl-10 text-sm sm:text-base cursor-not-allowed"
                                               readonly>

                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            🔒
                                        </div>
                                    </div>

                                    <p class="text-xs text-stone-400 mt-1 leading-snug">
                                        Este correo pertenece al Super Administrador y está protegido.
                                    </p>
                                @else
                                    <input type="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                                           required>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ROL Y ESTADO --}}
                    <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                        <h3 class="font-bold text-stone-800 text-base sm:text-lg flex items-center gap-2 mb-4">
                            🛡️ Rol y estado
                        </h3>

                        <div class="space-y-4 sm:space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Rol del usuario
                                </label>

                                @if($user->id == 1)
                                    <div class="w-full rounded-xl border border-stone-200 bg-stone-100 text-stone-500 px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base cursor-not-allowed flex items-center gap-2">
                                        <span>🛡️ Administrador principal</span>
                                        <input type="hidden" name="role" value="admin">
                                    </div>

                                    <p class="text-xs text-stone-400 mt-1 leading-snug">
                                        El rol del Super Administrador no se puede modificar.
                                    </p>
                                @else
                                    <select name="role"
                                            class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                                            required>
                                        <option value="empleado" {{ old('role', $user->role) === 'empleado' ? 'selected' : '' }}>
                                            ☕ Empleado - Punto de venta
                                        </option>

                                        <option value="gerente" {{ old('role', $user->role) === 'gerente' ? 'selected' : '' }}>
                                            📋 Gerente - Operación, caja e inventario
                                        </option>

                                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                                            🛡️ Administrador - Acceso total
                                        </option>
                                    </select>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-2">
                                    Estado del usuario
                                </label>

                                @if($user->id == 1)
                                    <div class="w-full rounded-xl border border-stone-200 bg-stone-100 text-stone-500 px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base cursor-not-allowed flex items-center gap-2">
                                        <span>🟢 Super Administrador activo</span>
                                        <input type="hidden" name="active" value="1">
                                    </div>

                                    <p class="text-xs text-stone-400 mt-1 leading-snug">
                                        El Super Administrador no puede darse de baja.
                                    </p>
                                @elseif(auth()->id() == $user->id)
                                    <div class="w-full rounded-xl border border-stone-200 bg-stone-100 text-stone-500 px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base cursor-not-allowed flex items-center gap-2">
                                        <span>🟢 Tu cuenta está activa</span>
                                        <input type="hidden" name="active" value="1">
                                    </div>

                                    <p class="text-xs text-stone-400 mt-1 leading-snug">
                                        No puedes desactivar tu propia cuenta mientras estás conectado.
                                    </p>
                                @else
                                    <label class="flex items-start gap-3 rounded-2xl border p-4 cursor-pointer
                                        {{ old('active', $user->active) ? 'bg-green-50 border-green-100' : 'bg-stone-50 border-stone-200' }}">
                                        <input type="checkbox"
                                               name="active"
                                               value="1"
                                               {{ old('active', $user->active) ? 'checked' : '' }}
                                               class="w-5 h-5 mt-1 rounded border-stone-300 text-green-600 focus:ring-green-500 shrink-0">

                                        <div>
                                            <span class="font-bold text-stone-700 text-sm sm:text-base">
                                                Usuario activo
                                            </span>

                                            <p class="text-xs text-stone-500 mt-1 leading-snug">
                                                Si se desactiva, este usuario no podrá iniciar sesión.
                                            </p>
                                        </div>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEGURIDAD --}}
                <div class="space-y-4 sm:space-y-6 min-w-0">
                    <div class="bg-stone-50 border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                        <h3 class="font-bold text-stone-800 text-base sm:text-lg flex items-center gap-2 mb-4">
                            🔒 Seguridad
                        </h3>

                        @if($user->id == 1 && auth()->id() != 1)
                            <div class="text-center py-6 sm:py-8">
                                <div class="bg-amber-100 text-amber-600 h-14 w-14 sm:h-16 sm:w-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                </div>

                                <h4 class="font-bold text-stone-800">
                                    Acceso restringido
                                </h4>

                                <p class="text-xs sm:text-sm text-stone-500 mt-2 leading-snug">
                                    Solo el Super Administrador puede cambiar sus propias credenciales.
                                </p>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-5 text-xs sm:text-sm text-blue-800">
                                <p class="font-bold">Cambio de contraseña opcional</p>
                                <p class="text-xs mt-1 leading-snug">
                                    Si no deseas cambiar la contraseña, deja ambos campos vacíos.
                                </p>
                            </div>

                            <div class="space-y-4 sm:space-y-5">
                                <div>
                                    <label class="block text-sm font-bold text-stone-700 mb-2">
                                        Nueva contraseña
                                    </label>

                                    <input type="password"
                                           name="password"
                                           class="w-full rounded-xl border-stone-200 bg-white focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                                           placeholder="Nueva contraseña">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-stone-700 mb-2">
                                        Confirmar nueva contraseña
                                    </label>

                                    <input type="password"
                                           name="password_confirmation"
                                           class="w-full rounded-xl border-stone-200 bg-white focus:ring-amber-500 focus:border-amber-500 py-2.5 sm:py-3 px-3 sm:px-4 text-sm sm:text-base transition"
                                           placeholder="Repite la nueva contraseña">
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- INFORMACIÓN DE AUDITORÍA --}}
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 sm:p-5">
                        <h3 class="font-bold text-amber-900 mb-3 text-sm sm:text-base">
                            Información de auditoría
                        </h3>

                        <div class="space-y-3 text-xs sm:text-sm text-amber-800">
                            <div class="bg-white/70 rounded-xl p-3 border border-amber-100">
                                <span class="font-bold block">Fecha de registro</span>
                                {{ $user->created_at ? $user->created_at->format('d/m/Y h:i A') : 'Sin fecha registrada' }}
                            </div>

                            <div class="bg-white/70 rounded-xl p-3 border border-amber-100">
                                <span class="font-bold block">Última actualización</span>
                                {{ $user->updated_at ? $user->updated_at->format('d/m/Y h:i A') : 'Sin fecha registrada' }}
                            </div>

                            <div class="bg-white/70 rounded-xl p-3 border border-amber-100">
                                <span class="font-bold block">Identificador interno</span>
                                Usuario #{{ $user->id }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-5 sm:pt-6 border-t border-stone-100">
                <a href="{{ route('employees.index') }}"
                   class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 rounded-xl border border-stone-200 text-stone-600 font-bold hover:bg-stone-50 transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-8 rounded-xl shadow-md transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

@endsection