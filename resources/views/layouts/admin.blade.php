<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <div class="flex min-h-screen overflow-hidden">

        {{-- MENÚ LATERAL (SIDEBAR) --}}
        <aside id="sidebar" class="w-64 bg-amber-900 text-amber-50 shadow-2xl flex flex-col transition-all duration-300 z-20 shrink-0">
            
            <div class="p-6 flex items-center gap-3 border-b border-amber-800 cursor-pointer hover:bg-amber-800 transition-colors" onclick="toggleSidebar()" title="Colapsar/Expandir Menú">
                <div class="h-12 w-12 rounded-full overflow-hidden bg-white shadow-md border border-white/30 flex items-center justify-center shrink-0">
                    <img src="{{ asset('img/logo-cafeteria.png') }}"
                        class="w-full h-full object-cover object-center"
                        alt="Logo Ignoto Café"
                        onerror="this.src='{{ asset('images/logo-cafeteria.png') }}'">
                </div>

                <span class="font-serif font-bold text-xl tracking-wide text-white sidebar-text whitespace-nowrap overflow-hidden transition-opacity duration-200">
                    Ignoto Café
                </span>
            </div>

            <nav class="flex-1 py-4 space-y-5 overflow-y-auto overflow-x-hidden">

                {{-- OPERACIÓN --}}
                <div class="px-3">
                    <p class="sidebar-text px-3 mb-2 text-[11px] uppercase tracking-widest text-amber-400 font-bold">
                        Operación
                    </p>

                    <a href="{{ route('pos.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                    {{ request()->routeIs('pos.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                        <span class="text-xl shrink-0 flex justify-center w-8">📠</span>
                        <span class="font-medium sidebar-text whitespace-nowrap">Ventas POS</span>
                    </a>

                    @if(in_array(Auth::user()->role, ['admin', 'gerente']))
                        <a href="{{ route('cash_registers.index') }}"
                        class="mt-2 flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('cash_registers.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">💰</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Corte de Caja</span>
                        </a>
                    @endif
                </div>

                @if(in_array(Auth::user()->role, ['admin', 'gerente']))
                    {{-- ADMINISTRACIÓN --}}
                    <div class="px-3">
                        <p class="sidebar-text px-3 mb-2 text-[11px] uppercase tracking-widest text-amber-400 font-bold">
                            Administración
                        </p>

                        <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('dashboard') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">📊</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Dashboard</span>
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('employees.index') }}"
                            class="mt-2 flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                            {{ request()->routeIs('employees.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                                <span class="text-xl shrink-0 flex justify-center w-8">👥</span>
                                <span class="font-medium sidebar-text whitespace-nowrap">Empleados</span>
                            </a>
                        @endif

                        <a href="{{ route('products.index') }}"
                        class="mt-2 flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('products.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">☕</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Productos</span>
                        </a>

                        <a href="{{ route('categories.index') }}"
                        class="mt-2 flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('categories.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">🗂️</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Categorías</span>
                        </a>
                    </div>

                    {{-- INVENTARIO --}}
                    <div class="px-3">
                        <p class="sidebar-text px-3 mb-2 text-[11px] uppercase tracking-widest text-amber-400 font-bold">
                            Inventario
                        </p>

                        <a href="{{ route('ingredients.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('ingredients.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">📦</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Materia Prima</span>
                        </a>

                        <a href="{{ route('inventory_movements.index') }}"
                        class="mt-2 flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('inventory_movements.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">📋</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Bitácora Inventario</span>
                        </a>
                    </div>

                    {{-- CLIENTES --}}
                    <div class="px-3">
                        <p class="sidebar-text px-3 mb-2 text-[11px] uppercase tracking-widest text-amber-400 font-bold">
                            Clientes
                        </p>

                        <a href="{{ route('vip.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('vip.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">👑</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Clientes VIP</span>
                        </a>
                    </div>

                    {{-- REPORTES --}}
                    <div class="px-3">
                        <p class="sidebar-text px-3 mb-2 text-[11px] uppercase tracking-widest text-amber-400 font-bold">
                            Reportes
                        </p>

                        <a href="{{ route('reports.sales') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors duration-200
                        {{ request()->routeIs('reports.*') ? 'bg-amber-800 text-white shadow-md' : 'text-amber-200 hover:bg-amber-800 hover:text-white' }}">
                            <span class="text-xl shrink-0 flex justify-center w-8">📑</span>
                            <span class="font-medium sidebar-text whitespace-nowrap">Ventas</span>
                        </a>
                    </div>
                @endif
            </nav>

            <div class="p-4 border-t border-amber-800 text-xs text-amber-400 text-center sidebar-text whitespace-nowrap">
                &copy; {{ date('Y') }} Sistema Ignoto
            </div>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-stone-100">
            
            {{-- HEADER SUPERIOR --}}
            <header class="flex justify-between items-center bg-white p-4 m-4 mb-0 rounded-xl shadow-sm border border-stone-200 shrink-0">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="text-stone-500 hover:text-amber-900 hover:bg-amber-50 focus:outline-none p-2 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h2 class="font-serif font-bold text-2xl text-amber-900 hidden sm:block">
                        @if(request()->routeIs('dashboard'))
                            Panel Principal
                        @elseif(request()->routeIs('pos.*'))
                            Punto de Venta
                        @elseif(request()->routeIs('cash_registers.*'))
                            Corte de Caja
                        @elseif(request()->routeIs('products.*'))
                            Gestión de Productos
                        @elseif(request()->routeIs('categories.*'))
                            Gestión de Categorías
                        @elseif(request()->routeIs('employees.*'))
                            Equipo de Trabajo
                        @elseif(request()->routeIs('ingredients.*'))
                            Materia Prima
                        @elseif(request()->routeIs('inventory_movements.*'))
                            Bitácora de Inventario
                        @elseif(request()->routeIs('vip.*'))
                            Clientes VIP
                        @elseif(request()->routeIs('reports.*'))
                            Reportes
                        @elseif(request()->routeIs('profile.*'))
                            Mi Perfil
                        @else
                            Sistema Ignoto
                        @endif
                    </h2>
                </div>

                <div class="relative group z-50">
                    <button class="flex items-center gap-3 focus:outline-none transition">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-stone-700">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-stone-500">
                                @if(Auth::user()->role === 'admin')
                                    Administrador
                                @elseif(Auth::user()->role === 'gerente')
                                    Gerente
                                @else
                                    Empleado
                                @endif
                            </p>
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
            </header>

            {{-- ÁREA DE CONTENIDO SCROLLABLE --}}
            <div class="flex-1 p-4 overflow-y-auto relative z-0">
                <div class="max-w-[1600px] mx-auto">
                    @yield('content')
                </div>
            </div>

        </main>
    </div>

    {{-- LIBRERÍA DE ALERTAS PROFESIONALES--}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. Lógica del Menú Lateral

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const texts = document.querySelectorAll('.sidebar-text');
            
            if (sidebar.classList.contains('w-64')) {
                // Lo estamos cerrando
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                texts.forEach(el => el.classList.add('opacity-0', 'hidden'));
                
                // Guardamos en la memoria del navegador que está cerrado
                localStorage.setItem('sidebarState', 'collapsed'); 
            } else {
                // Lo estamos abriendo
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                texts.forEach(el => {
                    el.classList.remove('hidden');
                    setTimeout(() => el.classList.remove('opacity-0'), 50);
                });
                
                // Guardamos en la memoria del navegador que está abierto
                localStorage.setItem('sidebarState', 'expanded'); 
            }
        }
        
        // 2. Apagar autocompletado del navegador
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- MAGIA: RESTAURAR EL MENÚ ANTES DE QUE EL USUARIO LO VEA ---
            const savedState = localStorage.getItem('sidebarState');
            if (savedState === 'collapsed') {
                const sidebar = document.getElementById('sidebar');
                const texts = document.querySelectorAll('.sidebar-text');
                
                // Le quitamos la animación temporalmente para que no se vea cómo se encoge ("brinquito")
                sidebar.classList.remove('transition-all', 'duration-300');
                
                // Lo hacemos pequeño al instante
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                texts.forEach(el => el.classList.add('opacity-0', 'hidden'));
                
                // Le regresamos la animación a los 50 milisegundos por si el usuario lo vuelve a abrir
                setTimeout(() => { sidebar.classList.add('transition-all', 'duration-300'); }, 50);
            }
            // -----------------------------------------------------------------

            // Apagar autocompletado del navegador
            document.querySelectorAll('input, form, textarea').forEach(function(elemento) {
                elemento.setAttribute('autocomplete', 'off');
                elemento.setAttribute('data-lpignore', 'true');
            });

            // 3. TOASTS Y MODALES
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });

            @if(session('success'))
                Toast.fire({
                    icon: "success",
                    title: "{!! session('success') !!}"
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: "error",
                    title: "Atención",
                    text: "{!! session('error') !!}",
                    confirmButtonColor: "#b45309" // amber-700
                });
            @endif

            // 4. LERTAS
            
            // A) Interceptar Formularios con 'onsubmit'
            document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
                let mensajeOriginal = "¿Estás seguro de realizar esta acción?";
                const match = form.getAttribute('onsubmit').match(/confirm\(['"](.*?)['"]\)/);
                if(match && match[1]) { mensajeOriginal = match[1]; }
                
                form.removeAttribute('onsubmit');
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Confirmación',
                        text: mensajeOriginal,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) { form.submit(); }
                    });
                });
            });

            // B) Interceptar Botones o Enlaces con 'onclick'
            document.querySelectorAll('[onclick*="return confirm"]').forEach(elemento => {
                let mensajeOriginal = "¿Estás seguro de realizar esta acción?";
                const match = elemento.getAttribute('onclick').match(/confirm\(['"](.*?)['"]\)/);
                if(match && match[1]) { mensajeOriginal = match[1]; }
                
                elemento.removeAttribute('onclick');
                
                elemento.addEventListener('click', function(e) {
                    e.preventDefault();
                    let targetForm = elemento.closest('form');
                    let targetHref = elemento.getAttribute('href');

                    Swal.fire({
                        title: 'Confirmación',
                        text: mensajeOriginal,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (targetForm) { targetForm.submit(); } 
                            else if (targetHref) { window.location.href = targetHref; }
                        }
                    });
                });
            });

        });
    </script>
</body>
</html>