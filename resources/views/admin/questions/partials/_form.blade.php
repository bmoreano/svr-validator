<div>
    {{-- Este formulario recibe una variable opcional $question --}}
    @props(['question' => null])

    <div class="space-y-6">
        <!-- Enunciado -->
        <div>
            <x-label for="stem" class="font-bold">Enunciado de la Pregunta <span
                    class="text-red-500">*</span></x-label>
            <textarea id="stem" name="stem" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                required>{{ old('stem', $question?->stem) }}</textarea>
        </div>

        <!-- Opciones de Respuesta -->
        <div>
            <h3 class="text-lg font-bold text-gray-900">Opciones de Respuesta <span class="text-red-500">*</span></h3>
            <p class="text-sm text-gray-600">Rellena las 4 opciones, su argumentación y marca la respuesta correcta.</p>

            <div class="mt-4 space-y-6">
                @for ($i = 0; $i < 4; $i++)
                    @php
                        $option = $question?->options->get($i);
                        $correctIndex = $question ? $question->options->search(fn($opt) => $opt->is_correct) : -1;
                    @endphp
                    <div class="p-4 border rounded-lg bg-gray-50">
                        <input type="hidden" name="options[{{ $i }}][id]" value="{{ $option?->id }}">
                        <div class="flex items-center space-x-3">
                            <input type="radio" name="correct_option" value="{{ $i }}"
                                @if (old('correct_option', $correctIndex) == $i) checked @endif
                                class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300">
                            <div class="w-full">
                                <x-label :for="'option_text_' . $i" :value="'Texto de la Opción ' . ($i + 1)" />
                                <x-input :id="'option_text_' . $i" name="options[{{ $i }}][text]" type="text"
                                    class="mt-1 block w-full" :value="old('options.' . $i . '.text', $option?->option_text)" required />
                            </div>
                        </div>
                        <div class="mt-4">
                            <x-label :for="'argumentation_' . $i" value="Argumentación" />
                            <textarea :id="'argumentation_'.$i" name="options[{{ $i }}][argumentation]" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('options.' . $i . '.argumentation', $option?->argumentation) }}</textarea>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Bibliografía -->
        <div>
            <x-label for="bibliography" class="font-bold">Bibliografía <span class="text-red-500">*</span></x-label>
            <textarea id="bibliography" name="bibliography" rows="3"
                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('bibliography', $question?->bibliography) }}</textarea>
        </div>
        {{--  Selector de Carrera --}}
        <div>
            <x-label for="career_id" value="Carrera Asociada" />
            <select id="career_id" name="career_id" class="block mt-1 w-full ..." :disabled="$disabled" required>
                <option value="">-- Seleccionar Carrera --</option>
                @foreach (\App\Models\Career::where('is_active', true)->orderBy('name')->get() as $career)
                    <option value="{{ $career->id }}" @selected(old('career_id', $question?->career_id) == $career->id)>
                        {{ $career->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <!-- Campos Psicométricos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-label for="grado_dificultad" value="Grado de Dificultad" />
                <select id="grado_dificultad" name="grado_dificultad"
                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">-- Seleccionar Nivel --</option>
                    <option value="muy_facil" @selected(old('grado_dificultad', $question?->grado_dificultad) == 'muy_facil')>Muy Fácil</option>
                    <option value="facil" @selected(old('grado_dificultad', $question?->grado_dificultad) == 'facil')>Fácil</option>
                    <option value="dificil" @selected(old('grado_dificultad', $question?->grado_dificultad) == 'dificil')>Difícil</option>
                    <option value="muy_dificil" @selected(old('grado_dificultad', $question?->grado_dificultad) == 'muy_dificil')>Muy Difícil</option>
                </select>
            </div>
            <div>
                <x-label for="poder_discriminacion" value="Poder de Discriminación" />
                <select id="poder_discriminacion" name="poder_discriminacion"
                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">-- Seleccionar Poder --</option>
                    <option value="muy_alto" @selected(old('poder_discriminacion', $question?->poder_discriminacion) == 'muy_alto')>Muy Alto</option>
                    <option value="alto" @selected(old('poder_discriminacion', $question?->poder_discriminacion) == 'alto')>Alto</option>
                    <option value="moderado" @selected(old('poder_discriminacion', $question?->poder_discriminacion) == 'moderado')>Moderado</option>
                    <option value="bajo" @selected(old('poder_discriminacion', $question?->poder_discriminacion) == 'bajo')>Bajo</option>
                    <option value="muy_bajo" @selected(old('poder_discriminacion', $question?->poder_discriminacion) == 'muy_bajo')>Muy Bajo</option>
                </select>
            </div>
        </div>
    </div>
</div>
