@extends('layouts.admin')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                👤 Cuenta de usuario
            </div>

            <h1 class="font-serif text-3xl md:text-4xl font-bold text-amber-900">
                Mi Perfil
            </h1>

            <p class="text-stone-500 mt-1">
                Actualiza tus datos personales asociados al acceso del sistema.
            </p>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl px-4 py-3 shadow-sm text-sm text-stone-600">
            <span class="font-bold text-amber-800">Sesión actual:</span>
            {{ auth()->user()->name }}
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- TARJETA DE INFORMACIÓN DEL USUARIO --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden lg:col-span-1">
            <div class="bg-gradient-to-br from-amber-900 to-stone-900 p-6 text-white text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-white/15 border border-white/20 flex items-center justify-center text-3xl font-black mb-4">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <h2 class="font-black text-xl">
                    {{ auth()->user()->name }}
                </h2>

                <p class="text-amber-200 text-sm mt-1">
                    {{ auth()->user()->email }}
                </p>
            </div>

            <div class="p-5 space-y-4">
                <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100">
                    <p class="text-xs text-stone-400 font-bold uppercase tracking-wide">
                        Rol
                    </p>

                    <p class="font-black text-stone-800 mt-1">
                        @if(auth()->user()->role === 'admin')
                            🛡️ Administrador
                        @elseif(auth()->user()->role === 'gerente')
                            📋 Gerente
                        @else
                            ☕ Empleado
                        @endif
                    </p>
                </div>

                <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100">
                    <p class="text-xs text-stone-400 font-bold uppercase tracking-wide">
                        Estado
                    </p>

                    @if(auth()->user()->active)
                        <p class="font-black text-green-700 mt-1">
                            Activo
                        </p>
                    @else
                        <p class="font-black text-red-700 mt-1">
                            Inactivo
                        </p>
                    @endif
                </div>

                <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100">
                    <p class="text-xs text-stone-400 font-bold uppercase tracking-wide">
                        Usuario registrado
                    </p>

                    <p class="font-black text-stone-800 mt-1">
                        {{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : 'Sin fecha' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- FORMULARIO --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden lg:col-span-2">
            <div class="px-6 py-5 border-b border-stone-100 bg-amber-50">
                <h2 class="font-bold text-amber-900 text-lg flex items-center gap-2">
                    🧾 Información personal
                </h2>

                <p class="text-sm text-amber-700 mt-1">
                    Modifica tu nombre y correo electrónico de acceso.
                </p>
            </div>

            <form method="POST" action="{{ route('profile.update.admin') }}" class="p-6 md:p-8 space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-stone-700 mb-2">
                        Nombre completo
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ old('name', auth()->user()->name) }}"
                           class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
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
                           value="{{ old('email', auth()->user()->email) }}"
                           class="w-full rounded-xl border-stone-200 bg-stone-50 focus:ring-amber-500 focus:border-amber-500 py-3 px-4 transition"
                           required>

                    <p class="text-xs text-stone-400 mt-1">
                        Este correo se usa para iniciar sesión en el sistema.
                    </p>
                </div>

                {{-- AVISO --}}
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-800">
                    <p class="font-bold mb-1">
                        Seguridad de cuenta
                    </p>

                    <p>
                        Si necesitas cambiar contraseña o permisos, realiza el cambio desde el módulo de empleados o solicita apoyo a un administrador.
                    </p>
                </div>

                {{-- BOTONES --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-stone-100">
                    <a href="{{ route('dashboard') }}"
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
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection