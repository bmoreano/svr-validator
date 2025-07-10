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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            
            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">

                <!-- 1. Logo de CACES -->
                <div class="flex justify-center mb-6">
                    <a href="/">
                        <img src="{{ asset('images/caces-logo.png') }}" alt="Logo CACES" class="w-24 h-auto">
                    </a>
                </div>

                <!-- 2. Título -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 tracking-tight">
                        Sistema de Validación de Reactivos
                    </h1>
                </div>

                {{-- ======================================================= --}}
                {{-- ==         ENLACES CON APARIENCIA DE BOTÓN           == --}}
                {{-- ======================================================= --}}
                <div class="space-y-4">
                    <!-- Botón Primario: Registrarse -->
                    <a href="{{ route('register') }}" class="w-full flex items-center justify-center rounded-md bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition ease-in-out duration-150">
                        Registrarse como Autor
                    </a>
                    
                    <!-- Botón Secundario: Iniciar Sesión -->
                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center rounded-md bg-white px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition ease-in-out duration-150">
                        Iniciar Sesión
                    </a>
                </div>
                
                <!-- Descripción -->
                <div class="mt-8 text-center border-t pt-6">
                    <div class="max-w-xs mx-auto">
                        <p class="text-sm text-gray-600">
                            Una plataforma para la creación, gestión y validación de preguntas, asegurando la calidad en las evaluaciones.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>