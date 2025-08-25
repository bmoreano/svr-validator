<div>
    <x-app-layout>
        {{-- ========================================================== --}}
        {{-- ENCABEZADO DE LA PÁGINA --}}
        {{-- ========================================================== --}}
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard de Carrera no Disponible
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 md:p-8 text-center">

                        {{-- Icono de Advertencia --}}
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        {{-- Título del Mensaje --}}
                        <h3 class="mt-5 text-lg leading-6 font-medium text-gray-900">
                            Perfil no Asociado a una Carrera
                        </h3>

                        {{-- Explicación del Problema y Solución --}}
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500">
                                No puedes acceder a este panel de reportería porque tu cuenta de usuario no está
                                asociada a ninguna carrera.
                            </p>
                            <p class="mt-2 text-sm text-gray-500">
                                Por favor, contacta a un administrador del sistema para que asigne la carrera
                                correspondiente a tu perfil de usuario.
                            </p>
                        </div>

                        {{-- Botón de Acción para Volver --}}
                        <div class="mt-5">
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver al Dashboard Principal
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>
