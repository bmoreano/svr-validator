<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nueva Pregunta</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('questions.store') }}" method="POST" x-data="questionForm()" class="bg-white shadow-xl sm:rounded-lg">
                @csrf
                <div class="p-6 sm:p-8 space-y-8">
                    <x-validation-errors class="mb-4" />

                    <div>
                        <x-label for="stem" class="text-lg font-semibold">Enunciado <span class="text-red-500">*</span></x-label>
                        <p class="text-sm text-gray-500 mb-2">Escribe el cuerpo principal de la pregunta o el caso clínico.</p>
                        <textarea id="stem" name="stem" rows="5" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('stem') }}</textarea>
                    </div>

                    <div class="p-6 border rounded-lg bg-gray-50">
                        <h3 class="text-lg font-semibold">Opciones de Respuesta <span class="text-red-500">*</span></h3>
                        <p class="text-sm text-gray-500 mt-1 mb-4">Rellena las 4 opciones con su respectiva argumentación y marca la respuesta correcta.</p>
                        
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

                    <div>
                        <x-label for="bibliography" class="font-bold text-lg">Bibliografía <span class="text-red-500">*</span></x-label>
                        <p class="text-sm text-gray-500 mb-2">Cita las fuentes utilizadas.</p>
                        <textarea id="bibliography" name="bibliography" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('bibliography') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 px-8 py-4 bg-gray-50 border-t border-gray-200 sm:rounded-b-lg">
                    <a href="{{ route('questions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Cancelar</a>
                    <x-button>{{ __('Guardar Borrador') }}</x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function questionForm(data = {}) {
            return {
                options: data.options || [
                    { text: '{{ old("options.0.text", "") }}', argumentation: '{{ old("options.0.argumentation", "") }}' },
                    { text: '{{ old("options.1.text", "") }}', argumentation: '{{ old("options.1.argumentation", "") }}' },
                    { text: '{{ old("options.2.text", "") }}', argumentation: '{{ old("options.2.argumentation", "") }}' },
                    { text: '{{ old("options.3.text", "") }}', argumentation: '{{ old("options.3.argumentation", "") }}' }
                ],
                correctOptionIndex: '{{ old("correct_option", "0") }}',
            }
        }
    </script>
</x-app-layout>