<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @auth
                        @if(in_array(auth()->user()->role, ['autor', 'administrador']))
                            <x-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">
                                Preguntas
                            </x-nav-link>
                        @endif
                        @if(in_array(auth()->user()->role, ['validador', 'administrador']))
                            <x-nav-link href="{{ route('validations.index') }}" :active="request()->routeIs('validations.*')">
                                Validaciones
                            </x-nav-link>
                        @endif
                        @if(auth()->user()->role === 'administrador')
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <x-dropdown align="left" width="48">
                                    <x-slot name="trigger">
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                                Administración
                                                <svg class="ms-2 -me-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                            </button>
                                        </span>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link href="{{ route('admin.reports.index') }}" :active="request()->routeIs('admin.reports.*')">
                                            Reportería
                                        </x-dropdown-link>
                                        <x-dropdown-link href="{{ route('admin.analytics.index') }}">Analíticas</x-dropdown-link>
                                        <div class="border-t border-gray-200"></div>
                                        <x-dropdown-link href="{{ route('admin.users.index') }}">Gestionar Usuarios</x-dropdown-link>
                                        <x-dropdown-link href="{{ route('admin.prompts.index') }}">Gestionar Prompts</x-dropdown-link>
                                        <x-dropdown-link href="{{ route('admin.careers.index') }}">Gestionar Carreras</x-dropdown-link>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @livewire('notification-bell')
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                             <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" />
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Manage Account') }}</div>
                            <x-dropdown-link href="{{ route('profile.show') }}">{{ __('Profile') }}</x-dropdown-link>
                            <div class="border-t border-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}" inline><@csrf<x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link></form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md ..."><svg class="h-6 w-6" ...></svg></button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        {{-- ... (Lógica de roles replicada para el menú móvil) ... --}}
    </div>
</nav>