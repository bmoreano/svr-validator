<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ** Editar Pregunta #{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <div class="max-w-4xl mx-auto">
                    <x-validation-errors class="mb-4" />
                    <form action="{{ route('questions.update', $question) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Incluimos el formulario parcial, pasándole la variable 'question' --}}
                        @include('admin.questions.partials._form', ['question' => $question])

                        {{-- ========================================================== --}}
                        {{-- SECCIÓN DE BOTONES DE ACCIÓN (RESTAURADA) --}}
                        {{-- ========================================================== --}}
                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('questions.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-button>
                                Actualizar Pregunta
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
