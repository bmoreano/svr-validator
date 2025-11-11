<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Preguntas Pendientes de Revisión
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notificaciones Flash 
            {{ 'INDEX' }}
            {{ $pendingQuestions }}--}}
            @if(session('status')) <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ session('status') }}</div> @endif
            
            {{-- Aquí cargamos nuestro nuevo componente dinámico de Livewire --}}
            @livewire('validator-dashboard',['pendingQuestions' => $pendingQuestions])
            
        </div>
    </div>
</x-app-layout>