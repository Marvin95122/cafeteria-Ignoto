@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold text-amber-900">Equipo de Trabajo</h1>
        <p class="text-stone-500 mt-1">Gestiona el acceso y roles de tu personal.</p>
    </div>

    <a href="{{ route('employees.create') }}"
       class="bg-blue-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-blue-700 hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        Nuevo Empleado
    </a>
</div>

{{-- ALERTAS DE ERROR --}}
@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r shadow-sm flex items-center gap-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-r shadow-sm">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($employees as $employee)
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6 flex items-start gap-4 hover:shadow-md transition relative group">
            
            <div class="h-16 w-16 rounded-full flex items-center justify-center text-2xl font-bold shadow-inner
                {{ $employee->role === 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                {{ substr($employee->name, 0, 1) }}
            </div>

            <div class="flex-1">
                <h3 class="font-bold text-lg text-stone-800 leading-tight">{{ $employee->name }}</h3>
                <p class="text-sm text-stone-500 mb-2">{{ $employee->email }}</p>
                
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $employee->role === 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                    {{ $employee->role === 'admin' ? '🛡️ Administrador' : '☕ Empleado' }}
                </span>
            </div>

            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="flex gap-2 items-center">
                    
                    <a href="{{ route('employees.edit', $employee) }}" class="p-2 text-stone-400 hover:text-amber-600 hover:bg-amber-50 rounded-full transition" title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                    
                    @if(auth()->id() !== $employee->id && $employee->id !== 1)
                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('¿Eliminar a {{ $employee->name }}?')">
                            @csrf @method('DELETE')
                            <button class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-full transition" title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    @else
                        <div class="p-2 text-stone-300 cursor-not-allowed" title="Usuario Protegido">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-12 bg-white rounded-xl border border-dashed border-stone-300">
            <p class="text-stone-500">No hay empleados registrados aún.</p>
        </div>
    @endforelse
</div>
@endsection