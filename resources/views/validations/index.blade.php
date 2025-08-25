<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Preguntas Pendientes de Revisión
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notificaciones Flash --}}
            @if(session('status')) <div class="mb-6 ...">{{ session('status') }}</div> @endif
            
            {{-- Aquí cargamos nuestro nuevo componente dinámico de Livewire --}}
            @livewire('validator-dashboard')
        </div>
    </div>
</x-app-layout>