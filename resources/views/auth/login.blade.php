<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso - Cafetería Ignoto</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* Personalización de fuentes para que se vea elegante */
        h1, h2 { font-family: 'Playfair Display', serif; }
        body { font-family: 'Lato', sans-serif; }
    </style>
</head>
<body class="bg-stone-50">

    <div class="min-h-screen flex">
        
        <div class="hidden lg:block relative w-1/2 bg-cover bg-center" 
             style="background-image: url('{{ asset('images/fondo-login.png') }}');">
            <div class="absolute inset-0 bg-black bg-opacity-40"></div> <div class="absolute inset-0 flex flex-col justify-center items-center text-white p-12 text-center">
                <h2 class="text-5xl font-bold mb-6">El aroma que inspira</h2>
                <p class="text-xl font-light">Bienvenido al sistema de gestión de tu cafetería favorita.</p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                
                <div class="text-center">
                    <div class="mx-auto h-24 w-auto flex items-center justify-center mb-6">
                        <img src="{{ asset('images/logo-cafeteria.png') }}" alt="Logo Cafetería" class="h-full object-contain">
                    </div>
                    
                    <h1 class="text-3xl font-bold text-amber-900 tracking-tight">
                        Cafetería Ignoto
                    </h1>
                    <p class="mt-2 text-sm text-stone-500">
                        Ingresa tus credenciales para iniciar sesion
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 text-center">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                    @csrf

                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-stone-700">Correo Electrónico</label>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                value="{{ old('email') }}"
                                class="appearance-none relative block w-full px-3 py-3 border border-stone-300 placeholder-stone-400 text-stone-900 rounded-lg focus:outline-none focus:ring-amber-500 focus:border-amber-500 focus:z-10 sm:text-sm transition ease-in-out duration-150" 
                                placeholder="ejemplo@cafeteria.com">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="password" class="block text-sm font-medium text-stone-700">Contraseña</label>
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                class="appearance-none relative block w-full px-3 py-3 border border-stone-300 placeholder-stone-400 text-stone-900 rounded-lg focus:outline-none focus:ring-amber-500 focus:border-amber-500 focus:z-10 sm:text-sm transition ease-in-out duration-150" 
                                placeholder="••••••••">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-amber-800 hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-amber-600 group-hover:text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>