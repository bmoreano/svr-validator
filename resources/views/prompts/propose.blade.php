<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Proponer un Nuevo Prompt
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8"> {{-- Ancho ampliado --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <p class="text-gray-600 mb-6">Usa este formulario para enviar un nuevo prompt a nuestro sistema de revisión automática. La IA evaluará su claridad, objetividad y seguridad. Si es aprobado, estará disponible para su uso.</p>

                <x-validation-errors class="mb-4" />

                @php
                    $preguntaJsonEjemplo = "{\n    \"enunciado\": \"¿Cuál es la capital de Francia?\",\n    \"opciones\": [\n        {\"texto\": \"Berlín\", \"es_correcta\": false},\n        {\"texto\": \"París\", \"es_correcta\": true}\n    ]\n}";
                    $criteriosEjemplo = "- ID: 1, Criterio: El enunciado es claro y conciso.\n- ID: 2, Criterio: Existe una única respuesta correcta.";
                    $placeholderText = "Ejemplo de un buen prompt:\n\n"
                        . "ROL Y OBJETIVO:\n"
                        . "Eres un evaluador académico experto...\n\n"
                        . "INSTRUCCIONES:\n"
                        . "1. Analiza la pregunta en {{PREGUNTA_JSON}}.\n"
                        . "   Ejemplo de {{PREGUNTA_JSON}}:\n"
                        . "   ```json\n"
                        . "   " . $preguntaJsonEjemplo . "\n"
                        . "   ```\n\n"
                        . "2. Evalúa contra los criterios en {{CRITERIOS}}.\n"
                        . "   Ejemplo de {{CRITERIOS}}:\n"
                        . "   ```\n"
                        . "   " . $criteriosEjemplo . "\n"
                        . "   ```\n\n"
                        . "FORMATO DE RESPUESTA JSON REQUERIDO:\n"
                        . "[{\"criterion_id\": 1, \"response\": \"si\", \"comment\": \"...\"}]";
                @endphp

                <form action="{{ route('prompts.propose.store') }}" method="POST">
                    @csrf

                    <!-- Nombre del Prompt -->
                    <div class="mb-4">
                        <x-label for="name" value="Nombre del Prompt (ej. 'Validación de Objetividad v3')" />
                        <x-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <x-label for="description" value="Descripción (¿Cuál es su propósito?)" />
                        <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description') }}</textarea>
                    </div>

                    <!-- Motor de IA -->
                     <div class="mb-4">
                        <x-label for="ai_engine" value="Diseñado para el motor de IA:" />
                        <select id="ai_engine" name="ai_engine" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="chatgpt" @selected(old('ai_engine') == 'chatgpt')>ChatGPT</option>
                            <option value="gemini" @selected(old('ai_engine') == 'gemini')>Gemini</option>
                        </select>
                    </div>

                    <!-- Contenido del Prompt -->
                    <div class="mb-4">
                        <x-label for="content" value="Contenido completo del Prompt" />
                        <p class="text-xs text-gray-500 mb-2">
                            Recuerda usar las variables <code class="bg-gray-200 text-red-600 px-1 rounded">@{{PREGUNTA_JSON}}</code> y <code class="bg-gray-200 text-red-600 px-1 rounded">@{{CRITERIOS}}</code> que serán reemplazadas por el sistema.
                        </p>
                        
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="20"
                            class="mt-1 block w-full font-mono text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                            required
                            placeholder="{{ $placeholderText }}"
                        >{{ old('content') }}</textarea>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- SECCIÓN DE BOTONES MODIFICADA --}}
                    {{-- ========================================================== --}}
                    <div class="flex items-center justify-between mt-8 border-t pt-5">
                        
                        {{-- Botón Cancelar (ahora a la izquierda) --}}
                        <div>
                            <a href="{{ route('dashboard') }}" 
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                        </div>
                        
                        {{-- Botón Enviar a Revisión (ahora a la derecha) --}}
                        <div>
                            <x-button>
                                Enviar a Revisión
                            </x-button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>