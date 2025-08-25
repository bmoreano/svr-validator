{{-- Usamos el layout principal de la aplicación para mantener la cabecera y la navegación. --}}
<x-app-layout>
    
    {{-- Definimos un encabezado para esta página de error --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ERROR
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col items-center justify-center text-center p-12">
                    
                    {{-- Ícono grande para dar énfasis visual --}}
                    <svg class="w-16 h-16 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    
                    {{-- Título del Error --}}
                    <h1 class="text-4xl font-bold text-gray-800">403</h1>
                    <h2 class="text-2xl font-semibold text-gray-700 mt-2">Acceso No Autorizado</h2>

                    {{-- Mensaje Explicativo --}}
                    <p class="mt-4 max-w-2xl text-gray-600">
                        {{-- Laravel pasa automáticamente una variable $exception con el mensaje del error. --}}
                        {{-- Podemos mostrarlo para dar más contexto si está disponible. --}}
                        {{ $exception->getMessage() ?: 'Lo sentimos, no tienes los permisos necesarios para acceder a esta página. Esto puede deberse a que tu rol no permite esta acción.' }}
                    </p>

                    {{-- Botón de Acción para Regresar --}}
                    <div class="mt-8">
                        <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 mr-4">
                            ← Volver a la Página Anterior
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Ir al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>