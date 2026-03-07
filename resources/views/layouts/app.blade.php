<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cafetería Ignoto') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .font-serif { font-family: 'Playfair Display', serif; }
            .font-sans { font-family: 'Lato', sans-serif; }
            
            /* Scrollbar personalizada color café */
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: #f5f5f4; } /* stone-100 */
            ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
        </style>
    </head>
    <body class="font-sans antialiased bg-stone-50 text-stone-800">
        <div class="min-h-screen flex flex-col">
            
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow-sm border-b border-stone-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="text-2xl font-serif font-bold text-amber-900">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>