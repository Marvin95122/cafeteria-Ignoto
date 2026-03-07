@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Editar Perfil</h1>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        <!-- Form for profile fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-medium mb-1">Nombre</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full border rounded-lg px-3 py-2" required>
            </div>

            <div>
                <label class="block font-medium mb-1">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="w-full border rounded-lg px-3 py-2" required>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
            <button type="submit" class="bg-blue-600 text-black px-4 py-2 border rounded">
                Guardar cambios
            </button>
        </div>
    </form>
@endsection
