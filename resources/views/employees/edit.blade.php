@extends('layouts.admin')

@section('content')

<div class="flex items-center gap-4 mb-8">
    <a href="{{ route('employees.index') }}" class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Editar Empleado</h1>
        <p class="text-stone-500">Actualizando datos de: <span class="font-semibold">{{ $user->name }}</span></p>
    </div>
</div>

<div class="max-w-4xl mx-auto">

    {{-- ALERTAS DE ERROR --}}
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r shadow-sm font-bold flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
            <strong class="font-bold">Corrige los siguientes errores:</strong>
            <ul class="list-disc list-inside mt-2 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        
        <div class="h-2 bg-amber-800 w-full"></div>

        <div class="p-8">
            <form method="POST" action="{{ route('employees.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-stone-700 border-b border-stone-100 pb-2 mb-4">
                            👤 Información General
                        </h3>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Nombre</label>
                            <input type="text" name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                   required>
                        </div>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Email</label>
                            
                            @if($user->id == 1 && auth()->id() != 1)
                                <div class="relative">
                                    <input type="email" name="email" 
                                           value="{{ $user->email }}" 
                                           class="w-full border-stone-200 bg-stone-100 text-stone-500 rounded-lg shadow-sm cursor-not-allowed py-3 px-4 pl-10"
                                           readonly>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        🔒
                                    </div>
                                </div>
                                <p class="text-xs text-stone-400 mt-1">Este correo está protegido.</p>
                            @else
                                <input type="email" name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                       required>
                            @endif
                        </div>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Rol del Usuario</label>
                            
                            @if($user->id == 1)
                                <div class="w-full border-stone-200 bg-stone-100 text-stone-500 rounded-lg px-4 py-3 border cursor-not-allowed flex items-center gap-2">
                                    <span>🛡️ Administrador Principal</span>
                                    <input type="hidden" name="role" value="admin">
                                </div>
                                <p class="text-xs text-stone-400 mt-1">Este rol no se puede modificar.</p>
                            @else
                                <select name="role" class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 transition py-3 px-4 bg-white">
                                    <option value="empleado" {{ $user->role === 'empleado' ? 'selected' : '' }}>☕ Empleado (Estándar)</option>
                                    <option value="gerente" {{ $user->role === 'gerente' ? 'selected' : '' }}>📋 Gerente (Operación e Inventario)</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>🛡️ Administrador (Acceso Total)</option>
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-6 bg-stone-50 p-6 rounded-xl border border-stone-100">
                        <h3 class="text-lg font-bold text-stone-700 border-b border-stone-200 pb-2 mb-4 flex items-center gap-2">
                            🔒 Seguridad
                        </h3>

                        @if($user->id == 1 && auth()->id() != 1)
                            
                            <div class="text-center py-6">
                                <div class="bg-amber-100 text-amber-600 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <h4 class="font-bold text-stone-800">Acceso Restringido</h4>
                                <p class="text-sm text-stone-500 mt-2">
                                    Solo el Super Administrador puede cambiar sus credenciales.
                                </p>
                            </div>

                        @else
                            <div class="text-sm text-stone-500 mb-4 italic">
                                Si no deseas cambiar la contraseña, deja estos campos vacíos.
                            </div>

                            <div>
                                <label class="block font-medium text-stone-600 mb-2">Nueva Contraseña</label>
                                <input type="password" name="password" 
                                       class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4 bg-white"
                                       placeholder="Nueva contraseña">
                            </div>

                            <div>
                                <label class="block font-medium text-stone-600 mb-2">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" 
                                       class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4 bg-white"
                                       placeholder="Confirmar contraseña">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-stone-100 flex justify-end gap-4">
                    <a href="{{ route('employees.index') }}" 
                       class="px-6 py-3 rounded-lg text-stone-600 font-medium hover:bg-stone-100 transition">
                        Cancelar
                    </a>

                    <button type="submit" 
                            class="bg-amber-800 text-white px-8 py-3 rounded-lg shadow-lg hover:bg-amber-900 hover:shadow-xl transition transform hover:-translate-y-0.5 font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection