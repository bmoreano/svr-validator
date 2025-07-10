<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <x-application-mark class="h-12 w-auto" />
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Corrección de Pregunta por Administrador</h2>
                <p class="text-sm text-gray-500">Supervisando la pregunta #{{ $question->id }} creada por {{ $question->author->name ?? 'N/A' }}.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.questions.saveCorrection', $question) }}" method="POST" x-data="questionForm({
                    options: {{ $question->options->map(function($opt) { return ['text' => $opt->option_text, 'argumentation' => $opt->argumentation]; })->toJson() }},
                    correct: {{ $question->options->search(fn($opt) => $opt->is_correct) }}
                })">
                @csrf
                @method('PUT')

                <div class="bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8 space-y-8">
                        <x-validation-errors class="mb-4" />

                        <!-- Información Original (Solo Lectura) -->
                        <div class="p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Información Original</h3>
                            <div class="space-y-4 text-sm">
                                <div><h4 class="font-bold text-gray-600">Enunciado Original:</h4><p class="mt-1 text-gray-800 pl-4 border-l-2">{!! nl2br(e($question->stem)) !!}</p></div>
                                <div>
                                    <h4 class="font-bold text-gray-600">Opciones y Argumentación Originales:</h4>
                                    <ul class="list-disc list-inside mt-1 pl-4 space-y-3">
                                        @foreach($question->options as $option)
                                            <li class="{{ $option->is_correct ? 'text-green-700' : 'text-gray-800' }}">
                                                <span class="{{ $option->is_correct ? 'font-semibold' : '' }}">{{ $option->option_text }}</span> @if($option->is_correct)<span class="font-bold">(Correcta)</span>@endif
                                                @if($option->argumentation)<p class="text-xs text-gray-600 italic pl-5 border-l-2 ml-2 mt-1">Argumento: {{ $option->argumentation }}</p>@endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div><h4 class="font-bold text-gray-600">Bibliografía Original:</h4><p class="mt-1 text-gray-800 pl-4 border-l-2">{!! nl2br(e($question->bibliography)) !!}</p></div>
                            </div>
                        </div>

                        <!-- Formulario de Edición -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Formulario de Corrección</h3>
                            <div class="mb-6"><x-label for="stem" class="font-semibold">Enunciado (Editable) <span class="text-red-500">*</span></x-label><textarea id="stem" name="stem" rows="5" class="block mt-1 w-full" required>{{ old('stem', $question->stem) }}</textarea></div>
                            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                                <div class="flex justify-between items-center"><h4 class="font-semibold">Opciones y Argumentación (Mín. 2, Máx. 4) <span class="text-red-500">*</span></h4><button type="button" @click="addOption()" x-show="options.length < 4" class="text-sm px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 shadow-sm">+ Añadir Opción</button></div>
                                <div class="mt-4 space-y-4">
                                    <template x-for="(option, index) in options" :key="index">
                                        <div class="p-3 border rounded-md bg-white">
                                            <div class="flex items-center space-x-3"><input type="radio" name="correct_option" :value="index" x-model="correctOptionIndex"><input type="text" :name="`options[${index}][text]`" x-model="option.text" class="w-full" required><button type="button" @click="removeOption(index)" x-show="options.length > 2" class="p-1 text-gray-400 hover:text-red-600 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></div>
                                            <div class="mt-2"><textarea :name="`options[${index}][argumentation]`" x-model="option.argumentation" rows="2" class="w-full text-sm" placeholder="Argumentación (opcional)"></textarea></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="mb-6"><x-label for="bibliography" class="font-semibold">Bibliografía (Editable) <span class="text-red-500">*</span></x-label><textarea id="bibliography" name="bibliography" rows="3" class="block mt-1 w-full" required>{{ old('bibliography', $question->bibliography) }}</textarea></div>
                        </div>

                        <!-- Campo de Comentario del Admin -->
                        <div class="p-6 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                            <x-label for="comentario_administrador" class="text-lg font-bold text-red-800">Justificación de la Corrección <span class="text-red-500">*</span></x-label>
                            <p class="text-sm text-red-700 mt-1 mb-2">Este campo es obligatorio. Explica el motivo del cambio.</p>
                            <textarea id="comentario_administrador" name="comentario_administrador" rows="4" class="block mt-1 w-full border-red-300 focus:border-red-500 focus:ring-red-500" required>{{ old('comentario_administrador') }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 px-8 py-4 bg-gray-50 border-t border-gray-200 sm:rounded-b-lg">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase">Cancelar</a>
                        <x-button>Guardar Corrección</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function questionForm(data = {}) {
            return {
                options: data.options || [{ text: '', argumentation: '' }, { text: '', argumentation: '' }],
                correctOptionIndex: (data.correct !== null && data.correct !== undefined) ? String(data.correct) : '0',
                addOption() { if (this.options.length < 4) { this.options.push({ text: '', argumentation: '' }); } },
                removeOption(index) {
                    if (this.options.length > 2) {
                        this.options.splice(index, 1);
                        if (this.correctOptionIndex == index) { this.correctOptionIndex = '0'; }
                        else if (this.correctOptionIndex > index) { this.correctOptionIndex = (parseInt(this.correctOptionIndex) - 1).toString(); }
                    }
                }
            }
        }
    </script>
</x-app-layout>