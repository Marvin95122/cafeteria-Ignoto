<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cafetería Ignoto - Panel de Control</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Lato', sans-serif; }
        h1, h2, h3, .font-serif { font-family: 'Playfair Display', serif; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f5f5f4; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
    </style>
</head>

<body class="bg-stone-50 text-stone-800">

    <div class="flex min-h-screen">

        <aside class="w-64 bg-amber-900 text-amber-50 shadow-2xl flex flex-col transition-all duration-300">
            
            <div class="p-6 flex items-center gap-3 border-b border-amber-800">
                <div class="bg-white p-1 rounded-full h-10 w-10 flex items-center justify-center">
                    <img src="{{ asset('img/logo-cafeteria.png') }}" class="h-8 w-8 object-contain" alt="Logo" onerror="this.src='{{ asset('images/logo-cafeteria.png') }}'">
                </div>
                <span class="font-serif font-bold text-xl tracking-wide text-white">Ignoto Café</span>
            </div>

            <nav class="flex-1 p-4 space-y-2 mt-2">

                {{-- Solo Admin y Gerente --}}
                @if(in_array(Auth::user()->role, ['admin', 'gerente']))
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                       {{ request()->routeIs('dashboard') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl">📊</span>
                        <span class="font-medium">Dashboard</span>
                    </a>
                @endif

                {{-- Solo Admin --}}
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('employees.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                       {{ request()->routeIs('employees.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl">👥</span>
                        <span class="font-medium">Empleados</span>
                    </a>
                @endif

                {{-- Solo Admin y Gerente --}}
                @if(in_array(Auth::user()->role, ['admin', 'gerente']))
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                       {{ request()->routeIs('categories.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl">🗂️</span>
                        <span class="font-medium">Categorías</span>
                    </a>

                    <a href="{{ route('ingredients.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                       {{ request()->routeIs('ingredients.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl">📦</span> <span class="font-medium">Materia Prima</span>
                    </a>
                    
                    <a href="{{ route('products.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                       {{ request()->routeIs('products.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl">☕</span> <span class="font-medium">Productos</span>
                    </a>

                    <a href="{{ route('cash_registers.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                       {{ request()->routeIs('cash_registers.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl">💰</span> <span class="font-medium">Corte de Caja</span>
                    </a>
                @endif

                {{-- Accesible para TODO EL MUNDO (Admin, Gerente, Empleado) --}}
                <a href="{{ route('pos.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                   {{ request()->routeIs('pos.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                    <span class="text-xl">📠</span> <span class="font-medium">Ventas (POS)</span>
                </a>
            </nav>

            <div class="p-4 border-t border-amber-800 text-xs text-amber-400 text-center">
                &copy; {{ date('Y') }} Sistema Ignoto
            </div>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto h-screen">
            
            <div class="flex justify-between items-center mb-8 bg-white p-4 rounded-xl shadow-sm border border-stone-200">
                <div class="flex items-center gap-4">
                    <h2 class="font-serif font-bold text-2xl text-amber-900">
                        @if(request()->routeIs('dashboard')) Panel Principal
                        @elseif(request()->routeIs('products.*')) Gestión de Menú de Productos
                        @elseif(request()->routeIs('employees.*')) Equipo de Trabajo Ignoto
                        @elseif(request()->routeIs('categories.*')) Gestión de Categorías
                        @elseif(request()->routeIs('ingredients.*')) Gestión de Materia Prima
                        @elseif(request()->routeIs('pos.*')) Punto de Venta
                        @else Bienvenido
                        @endif
                    </h2>
                </div>

                <div class="relative group">
                    <button class="flex items-center gap-3 focus:outline-none transition">
                        <div class="text-right hidden sm:block">
                            {{-- Muestra el nombre y el rol real del usuario conectado --}}
                            <p class="text-sm font-bold text-stone-700">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-stone-500 capitalize">{{ Auth::user()->role }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold border border-amber-200">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div class="absolute right-0 mt-2 w-48 bg-white border border-stone-100 rounded-lg shadow-xl py-2 hidden group-hover:block transition-all z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-stone-700 hover:bg-amber-50 hover:text-amber-700">Mi Perfil</a>
                        <div class="border-t border-stone-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-r shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-xl mr-2">✅</span>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r shadow-sm flex items-center">
                    <span class="text-xl mr-2">⚠️</span>
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 min-h-[500px]">
                @yield('content')
            </div>

        </main>

    </div>

    <script>
        const userBtn = document.querySelector('button.flex.items-center.gap-3');
        const userMenu = userBtn.nextElementSibling;

        userBtn.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>