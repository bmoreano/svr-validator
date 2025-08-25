<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
        <title>{{ config('app.name', 'SVR - CACES') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100 flex flex-col">
            @auth
                @livewire('navigation-menu');
                {{--<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
                    <!-- Contenido de la Navegación Primaria -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <div class="shrink-0 flex items-center">
                                    <a href="{{ route('dashboard') }}">
                                        <x-application-mark class="block h-9 w-auto" />
                                    </a>
                                </div>
                                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                                        {{ __('Dashboard***app//-') }}
                                    </x-nav-link>
                                    @if(in_array(Auth::user()->role, ['autor', 'administrador']))
                                        <x-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*') && 
                                        !request()->routeIs('questions.batch.create')">
                                            {{ __('Mis Preguntas***app//-') }}
                                        </x-nav-link>
                                        <x-nav-link href="{{ route('questions.batch.create') }}" :active="request()->routeIs('questions.batch.create')">
                                            {{ __('Validación Masiva***app//-') }}
                                        </x-nav-link>
                                    @endif
                                </div>
                            </div>
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <div class="ms-3 relative">
                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700">
                                                    {{ Auth::user()->name }}
                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                                </button>
                                            </span>
                                        </x-slot>
                                        <x-slot name="content">
                                            <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Manage Account') }}</div>
                                            <x-dropdown-link href="{{ route('profile.show') }}">{{ __('Profile') }}</x-dropdown-link>
                                            <div class="border-t border-gray-200"></div>
                                            <form method="POST" action="{{ route('logout') }}" x-data>
                                                @csrf
                                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-dropdown-link>
                                            </form>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                            <div class="-me-2 flex items-center sm:hidden">
                                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400"><svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>
                        </div>
                    </div>
                    <!-- Menú de Navegación Responsivo -->
                    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                        <div class="pt-2 pb-3 space-y-1">
                            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>


                            @if(in_array(Auth::user()->role, ['autor', 'administrador']))
                                <x-responsive-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*') && !request()->routeIs('questions.batch.create')">{{ __('Mis Preguntas') }}</x-responsive-nav-link>
                                <x-responsive-nav-link href="{{ route('questions.batch.create') }}" :active="request()->routeIs('questions.batch.create')">{{ __('Validación Masiva***app//--') }}</x-responsive-nav-link>
                            @endif
                            {{ --@can('viewAny', App\Models\Question::class)<x-responsive-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">{{ __('Mis Preguntas') }}</x-responsive-nav-link>@endcan-- }}
                        </div>
                        <div class="pt-4 pb-1 border-t border-gray-200">
                            <div class="flex items-center px-4">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <div class="shrink-0 me-3"><img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" /></div>
                                @endif
                                <div><div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div><div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div></div>
                            </div>
                            <div class="mt-3 space-y-1">
                                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">{{ __('Profile') }}</x-responsive-nav-link>
                                <form method="POST" action="{{ route('logout') }}" x-data>@csrf<x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-responsive-nav-link></form>
                            </div>
                        </div>
                    </div>
                </nav>--}}
            @endauth

            <!-- Contenido Principal -->
            <main class="flex-grow">
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
                    </header>
                @endif
                {{ $slot }}
            </main>

            <!-- Footer Global -->
            <footer class="w-full bg-white border-t border-gray-200 shadow-inner mt-auto">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <p class="text-center text-sm text-gray-500">
                        © Derechos Reservados CACES - 2025 ->
                    </p>
                </div>
            </footer>
        </div>
        @stack('scripts') 
        @stack('modals')
        @livewireScripts
    </body>
</html>