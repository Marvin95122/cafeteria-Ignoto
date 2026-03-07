@extends('layouts.admin')

@section('content')

<div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="font-serif text-4xl font-bold text-amber-900">
            Hola, {{ auth()->user()->name }} 👋
        </h1>
        <p class="text-stone-500 mt-2 text-lg">
            Aquí tienes el resumen de hoy en <span class="font-semibold text-amber-700">Cafetería Ignoto</span>.
        </p>
    </div>
    
    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-stone-200 text-stone-600 text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        {{ now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    <div class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-orange-500 hover:shadow-lg transition-transform transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-stone-400 uppercase tracking-wider">Clientes / Usuarios</p>
                <p class="text-4xl font-bold text-stone-800 mt-1">{{ $totalUsuarios }}</p>
            </div>
            <div class="p-3 bg-orange-100 rounded-full text-orange-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-amber-800 hover:shadow-lg transition-transform transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-stone-400 uppercase tracking-wider">Equipo de Trabajo</p>
                <p class="text-4xl font-bold text-stone-800 mt-1">{{ $totalEmpleados }}</p>
            </div>
            <div class="p-3 bg-amber-100 rounded-full text-amber-800">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-stone-600 hover:shadow-lg transition-transform transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-stone-400 uppercase tracking-wider">Administradores</p>
                <p class="text-4xl font-bold text-stone-800 mt-1">{{ $totalAdmins }}</p>
            </div>
            <div class="p-3 bg-stone-200 rounded-full text-stone-700">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
        </div>
    </div>
</div>

<h2 class="font-serif text-2xl font-bold text-stone-800 mb-6">Accesos Rápidos</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <a href="{{ route('products.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-stone-100 hover:border-amber-300 hover:shadow-md transition flex items-center gap-6">
        <div class="h-14 w-14 bg-amber-50 rounded-full flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition duration-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
        <div>
            <h3 class="font-bold text-lg text-stone-800 group-hover:text-amber-700">Menú de Productos</h3>
            <p class="text-sm text-stone-500">Agregar nuevos cafés, editar precios o revisar stock.</p>
        </div>
        <div class="ml-auto text-stone-300 group-hover:translate-x-1 transition duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </div>
    </a>

    <a href="{{ route('employees.index') }}" class="group bg-white p-6 rounded-xl shadow-sm border border-stone-100 hover:border-blue-300 hover:shadow-md transition flex items-center gap-6">
        <div class="h-14 w-14 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition duration-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div>
            <h3 class="font-bold text-lg text-stone-800 group-hover:text-blue-700">Gestión de Personal</h3>
            <p class="text-sm text-stone-500">Registrar nuevos empleados o actualizar datos.</p>
        </div>
        <div class="ml-auto text-stone-300 group-hover:translate-x-1 transition duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </div>
    </a>
</div>

@endsection