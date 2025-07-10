<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <img src="{{ asset('images/caces-logo.png') }}" alt="Logo CACES" class="h-12">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Editando la Pregunta #{{ $question->id }}
                </h2>
                <p class="text-sm text-gray-500">Realiza los cambios necesarios y actualiza la pregunta.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Pasamos los datos existentes a Alpine.js para inicializar el formulario --}}
            <form action="{{ route('questions.update', $question) }}" method="POST" x-data="questionForm({
                options: {{
                    $question->options->map(function($opt) {
                        return ['text' => $opt->option_text, 'argumentation' => $opt->argumentation];
                    })->toJson()
                }},
                correct: {{ $question->options->search(fn($opt) => $opt->is_correct) ?: 0 }}
            })" class="bg-white shadow-xl sm:rounded-lg">
                
                @csrf
                @method('PUT') {{-- Importante: Usar el método PUT para la actualización --}}

                <div class="p-6 sm:p-8 space-y-8">
                    <x-validation-errors class="mb-4" />

                    <!-- Enunciado -->
                    <div>
                        <x-label for="stem" class="text-lg font-semibold">Enunciado <span class="text-red-500">*</span></x-label>
                        {{-- Usamos old() como fallback, pero el valor principal es el del modelo $question --}}
                        <textarea id="stem" name="stem" rows="5" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('stem', $question->stem) }}</textarea>
                    </div>

                    <!-- Opciones Dinámicas -->
                    <div class="p-6 border rounded-lg bg-gray-50">
                        <h3 class="text-lg font-semibold">Opciones de Respuesta <span class="text-red-500">*</span></h3>
                        <p class="text-sm text-gray-500 mt-1 mb-4">Modifica las 4 opciones y su argumentación. Asegúrate de que una respuesta correcta esté marcada.</p>
                        
                        <div class="space-y-4">
                            <template x-for="(option, index) in options" :key="index">
                                <div class="p-3 border rounded-md bg-white">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" :id="'correct_opt_' + index" name="correct_option" :value="index" x-model="correctOptionIndex" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <input type="text" :id="'option_text_' + index" :name="`options[${index}][text]`" x-model="option.text" class="w-full border-gray-300 rounded-md shadow-sm" :placeholder="'Texto de la opción ' + (index + 1)" required>
                                    </div>
                                    <div class="mt-2 pl-7">
                                        <label :for="'arg_' + index" class="text-xs text-gray-500">Argumentación:</label>
                                        <textarea :id="'arg_' + index" :name="`options[${index}][argumentation]`" x-model="option.argumentation"
                                                  class="w-full text-sm border-gray-200 rounded-md shadow-sm"
                                                  rows="2" placeholder="Justificación de por qué esta opción es correcta o incorrecta..." required></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bibliografía -->
                    <div>
                        <x-label for="bibliography" class="font-bold text-lg">Bibliografía <span class="text-red-500">*</span></x-label>
                        <textarea id="bibliography" name="bibliography" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('bibliography', $question->bibliography) }}</textarea>
                    </div>
                </div>

                {{-- Barra de Acciones Fija --}}
                <div class="flex items-center justify-end space-x-4 px-8 py-4 bg-gray-50 border-t border-gray-200 sm:rounded-b-lg">
                    <a href="{{ route('questions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        Cancelar
                    </a>
                    <x-button>
                        {{ __('Actualizar Pregunta') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script de Alpine.js para manejar los datos del formulario --}}
    <script>
        function questionForm(data = {}) {
            return {
                // Inicializa las opciones con los datos pasados desde el controlador, o un array vacío si no hay datos.
                options: data.options || [],
                // Inicializa la opción correcta con el índice pasado, o 0 por defecto.
                correctOptionIndex: (data.correct !== null && data.correct !== undefined) ? String(data.correct) : '0',

                // Las funciones para añadir/quitar se mantienen por si se decide volver a hacerlas dinámicas,
                // pero en un formulario de 4 opciones fijas, no se usarían.
                addOption() { /* Lógica para añadir si fuera necesario */ },
                removeOption(index) { /* Lógica para quitar si fuera necesario */ }
            }
        }
    </script>
</x-app-layout>