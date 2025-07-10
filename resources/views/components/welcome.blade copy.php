<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SVR - Consejo de Aseguramiento de la Calidad de la Educación Superior</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles (Vite) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-100">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen">
            
            {{-- ENLACES DE LOGIN/REGISTER EN LA ESQUINA SUPERIOR DERECHA --}}
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    {{-- Si el usuario ya está logueado, muestra un enlace al dashboard --}}
                    <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-700 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Dashboard</a>
                @else
                    {{-- Si es un invitado, muestra los enlaces de Login y Register --}}
                    <a href="{{ route('login') }}" class="font-semibold text-gray-700 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Iniciar Sesión</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-700 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">Registrarse</a>
                    @endif
                @endauth
            </div>

            {{-- CONTENIDO CENTRAL DE LA PÁGINA DE BIENVENIDA --}}
            <div class="max-w-3xl mx-auto p-6 lg:p-8 text-center">
                
                <!-- Logo de CACES -->
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/caces-logo.png') }}" alt="Logo CACES" class="h-24 w-auto">
                </div>

                <!-- Título y Subtítulo -->
                <h1 class="text-4xl font-bold text-gray-800 tracking-tight sm:text-5xl">
                    Sistema de Validación de Reactivos (SVR)
                </h1>
                <p class="mt-4 text-lg text-gray-600 leading-8">
                    Una plataforma centralizada para la creación, gestión y validación por pares de preguntas de opción múltiple, asegurando la calidad y consistencia en las evaluaciones de educación superior.
                </p>

                <!-- Botones de Acción Principal -->
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Comenzar como Autor
                    </a>
                    <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-gray-900">
                        Ya tengo una cuenta <span aria-hidden="true">→</span>
                    </a>
                </div>

                <!-- Footer o Información Adicional -->
                <div class="mt-16 border-t pt-8">
                     <p class="text-sm text-gray-500">
                        Desarrollado para el Consejo de Aseguramiento de la Calidad de la Educación Superior.
                        Serechos Reservados CACES 
                        &copy; {{ date('Y') }}.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>