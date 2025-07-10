<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    {{-- El logo puede apuntar al dashboard si está logueado, o a la home si no --}}
                    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                {{-- ============================================================= --}}
                {{--         INICIA LA SECCIÓN PROTEGIDA POR AUTENTICACIÓN         --}}
                {{-- ============================================================= --}}
                @auth
                    <!-- Navigation Links para usuarios logueados -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        {{-- Enlace condicional para el rol 'autor' --}}
                        @if(Auth::user()->role === 'autor')
                            <x-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">
                                {{ __('Mis Preguntas') }}
                            </x-nav-link>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Menús de la derecha -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    {{-- Si el usuario está logueado, muestra los menús de equipos y perfil --}}
                    
                    <!-- Teams Dropdown -->
                    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <div class="ms-3 relative">
                            <x-dropdown align="right" width="60">
                                {{-- ... (código del dropdown de equipos con su slot trigger/content) ... --}}
                            </x-dropdown>
                        </div>
                    @endif

                    <!-- Settings Dropdown -->
                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                    </button>
                                @else
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                            {{ Auth::user()->name }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4" ...></svg>
                                        </button>
                                    </span>
                                @endif
                            </x-slot>

                            <x-slot name="content">
                                {{-- ... (contenido del dropdown, incluyendo el formulario de Log Out) ... --}}
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    {{-- Si el usuario NO está logueado, muestra los enlaces de Login y Register --}}
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900">Log in</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ms-4 font-semibold text-gray-600 hover:text-gray-900">Register</a>
                    @endif
                @endguest
            </div>

            <!-- Hamburger (Menú para móviles) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 ...">
                    <svg class="h-6 w-6" ...></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        @auth
            {{-- Menú móvil para usuarios logueados --}}
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            </div>
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="flex items-center px-4">
                    {{-- ... info del perfil ... --}}
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            {{-- Menú móvil para invitados (opcional, si el layout se usara en páginas públicas) --}}
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="{{ route('login') }}">
                    {{ __('Log in') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('register') }}">
                    {{ __('Register') }}
                </x-responsive-nav-link>
            </div>
        @endauth
    </div>
</nav>