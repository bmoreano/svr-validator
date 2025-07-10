<div>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editando la Pregunta #{{ $question->id }}
            </h2>
             <a href="{{ route('questions.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Cancelar y Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <x-validation-errors class="mb-6" />

                    {{-- Pasamos los datos existentes de la pregunta a nuestro componente Alpine.js --}}
                    <form action="{{ route('questions.update', $question) }}" method="POST" x-data="questionForm({
                        options: {{ $question->options->map(fn($opt) => ['text' => $opt->option_text])->toJson() }},
                        correct: {{ $question->options->search(fn($opt) => $opt->is_correct) }}
                    })">
                        @csrf
                        @method('PUT') {{-- Importante: Usar el método PUT para la actualización --}}

                        <!-- Enunciado -->
                        <div class="mb-6">
                            <x-label for="stem" class="font-bold">Enunciado <span class="text-red-500">*</span></x-label>
                            <textarea id="stem" name="stem" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('stem', $question->stem) }}</textarea>
                        </div>

                        <!-- Opciones Dinámicas -->
                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <div class="flex justify-between items-center">
                                <x-label value="Opciones de Respuesta (Mín. 2, Máx. 4)" class="font-bold" />
                                <button type="button" @click="addOption()" x-show="options.length < 4" class="text-sm px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                    + Añadir Opción
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Marca la casilla de la respuesta que es correcta.</p>
                            <div class="mt-4 space-y-3">
                                <template x-for="(option, index) in options" :key="index">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" :name="'correct_option'" :value="index" x-model="correctOptionIndex" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <input type="text" :name="`options[${index}][text]`" x-model="option.text" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                        <button type="button" @click="removeOption(index)" x-show="options.length > 2" class="p-1 text-gray-400 hover:text-red-600 rounded-full focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Bibliografía -->
                        <div class="mt-6">
                            <x-label for="bibliography" class="font-bold">Bibliografía <span class="text-red-500">*</span></x-label>
                            <textarea id="bibliography" name="bibliography" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('bibliography', $question->bibliography) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <x-button>
                                Actualizar Pregunta
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Reutilizamos el mismo script de Alpine.js de la vista de creación --}}
    <script>
        function questionForm(data = {}) {
            return {
                options: data.options || [{ text: '' }, { text: '' }],
                correctOptionIndex: (data.correct !== null && data.correct !== undefined) ? String(data.correct) : '0',
                addOption() {
                    if (this.options.length < 4) {
                        this.options.push({ text: '' });
                    }
                },
                removeOption(index) {
                    if (this.options.length > 2) {
                        this.options.splice(index, 1);
                        if (this.correctOptionIndex == index) {
                            this.correctOptionIndex = '0';
                        } else if (this.correctOptionIndex > index) {
                            this.correctOptionIndex = (parseInt(this.correctOptionIndex) - 1).toString();
                        }
                    }
                }
            }
        }
    </script>
</x-app-layout>
</div>
