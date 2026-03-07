@extends('layouts.admin')

@section('content')

<div class="flex items-center gap-4 mb-8">
    <a href="{{ route('employees.index') }}" class="p-2 rounded-full hover:bg-stone-200 text-stone-500 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Nuevo Empleado</h1>
        <p class="text-stone-500">Registra un nuevo miembro en tu equipo de trabajo.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto">
    
    {{-- ALERTAS DE ERROR --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-r shadow-sm">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <strong class="font-bold">Hay algunos problemas:</strong>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 ml-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        
        <div class="h-2 bg-amber-800 w-full"></div>

        <div class="p-8">
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-stone-700 border-b border-stone-100 pb-2 mb-4">
                            👤 Datos Personales
                        </h3>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Nombre Completo</label>
                            <input type="text" name="name" 
                                   value="{{ old('name') }}" 
                                   class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                   placeholder="Ej. Juan Pérez"
                                   required>
                        </div>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Correo Electrónico</label>
                            <input type="email" name="email" 
                                   value="{{ old('email') }}" 
                                   class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                   placeholder="empleado@cafeteria.com"
                                   required>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-stone-700 border-b border-stone-100 pb-2 mb-4">
                            🔒 Seguridad
                        </h3>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Contraseña</label>
                            <input type="password" name="password" 
                                   class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                   placeholder="••••••••"
                                   required>
                        </div>

                        <div>
                            <label class="block font-medium text-stone-600 mb-2">Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full border-stone-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition py-3 px-4"
                                   placeholder="••••••••"
                                   required>
                        </div>
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
                        Guardar Empleado
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection