<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nueva Pregunta</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <div class="max-w-4xl mx-auto">
                    <x-validation-errors class="mb-4" />
                    {{-- ========================================================== --}}
                    {{-- LÍNEA CORREGIDA: La acción apunta a 'questions.store' --}}
                    {{-- ========================================================== --}}
                    <form action="{{ route('questions.store') }}" method="POST">
                        @csrf
                        
                        @include('admin.questions.partials._form')

                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('questions.index') }}" class="...">Cancelar</a>
                            <x-button>Guardar Borrador</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>