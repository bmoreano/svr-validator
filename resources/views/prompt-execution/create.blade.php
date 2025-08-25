<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ejecutar un Prompt Aprobado
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <p class="text-gray-600 mb-6">Selecciona una de tus preguntas en estado "Borrador" y un prompt de la
                        lista de aprobados para ejecutar una validación de IA específica.</p>

                    <x-validation-errors class="mb-4" />

                    <form action="{{ route('prompt-execution.store') }}" method="POST">
                        @csrf

                        {{-- Selector de Pregunta --}}
                        <div class="mb-6">
                            <x-label for="question_id" value="1. Selecciona la Pregunta a Validar" class="font-bold" />
                            <select id="question_id" name="question_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Elige una de tus preguntas --</option>
                                @forelse($questions as $question)
                                    <option value="{{ $question->id }}" @selected(old('question_id') == $question->id)>
                                        {{ $question->code }} - {{ Str::limit($question->stem, 60) }}
                                    </option>
                                @empty
                                    <option value="" disabled>No tienes preguntas en estado 'borrador' o 'necesita
                                        corrección'.</option>
                                @endforelse
                            </select>
                        </div>

                        {{-- Selector de Prompt --}}
                        <div class="mb-6">
                            <x-label for="prompt_id" value="2. Selecciona el Prompt a Ejecutar" class="font-bold" />
                            <select id="prompt_id" name="prompt_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Elige un prompt aprobado --</option>
                                @forelse($prompts as $prompt)
                                    <option value="{{ $prompt->id }}" @selected(old('prompt_id') == $prompt->id)>
                                        {{ $prompt->name }} (para {{ strtoupper($prompt->ai_engine) }})
                                    </option>
                                @empty
                                    <option value="" disabled>No hay prompts activos en el sistema.</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-white ...">Cancelar</a>
                            <x-button>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                    </path>
                                </svg>
                                Ejecutar Validación
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>
