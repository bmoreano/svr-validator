<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Validación Avanzada desde Archivo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <p class="text-gray-600 mb-6">Usa este formulario para validar una de tus preguntas con uno o más prompts personalizados cargados desde archivos de texto (`.txt`).</p>
                
                <form action="{{ route('validation.from-file.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- 1. Selector de Pregunta --}}
                    <div class="mb-4">
                        <x-label for="question_id" value="Selecciona la pregunta a validar:" />
                        <select id="question_id" name="question_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Elige una pregunta --</option>
                            @forelse($questions as $question)
                                <option value="{{ $question->id }}">{{ Str::limit($question->stem, 80) }}</option>
                            @empty
                                <option value="" disabled>No tienes preguntas en estado 'borrador'.</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- 2. Selector de Motor de IA --}}
                    <div class="mb-4">
                        <x-label for="ai_engine" value="Motor de IA a utilizar:" />
                        <select id="ai_engine" name="ai_engine" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="chatgpt">ChatGPT</option>
                            <option value="gemini">Gemini</option>
                        </select>
                    </div>

                    {{-- 3. Carga de un solo archivo --}}
                    <div class="mb-6 border-t pt-4">
                        <x-label for="prompt_file" value="Opción A: Validar con un solo prompt" />
                        <p class="text-xs text-gray-500 mb-2">Sube un archivo `.txt` que contenga el prompt completo.</p>
                        <input type="file" id="prompt_file" name="prompt_file" accept=".txt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                    </div>

                    {{-- 4. Carga de múltiples archivos --}}
                    <div class="mb-6 border-t pt-4">
                        <x-label for="prompt_files" value="Opción B: Validar con múltiples prompts (para comparar)" />
                        <p class="text-xs text-gray-500 mb-2">Selecciona varios archivos `.txt`. Se enviará un job de validación por cada archivo.</p>
                        <input type="file" id="prompt_files" name="prompt_files[]" accept=".txt" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                        <x-button>
                            Enviar a Validación
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>