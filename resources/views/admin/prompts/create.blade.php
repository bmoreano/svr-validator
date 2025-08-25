<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Prompt
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <x-validation-errors class="mb-4" />

                    <form action="{{ route('admin.prompts.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre del Prompt -->
                            <div class="mb-4">
                                <x-label for="name" value="Nombre del Prompt" />
                                <x-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            </div>

                            <!-- Motor de IA -->
                            <div class="mb-4">
                                <x-label for="ai_engine" value="Motor de IA" />
                                <select id="ai_engine" name="ai_engine" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="chatgpt" @selected(old('ai_engine')=='chatgpt' )>ChatGPT</option>
                                    <option value="gemini" @selected(old('ai_engine')=='gemini' )>Gemini</option>
                                </select>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <x-label for="description" value="Descripción (Propósito del prompt)" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        </div>

                        <!-- Contenido del Prompt -->
                        <div class="mb-4">
                            <x-label for="content" value="Contenido completo del Prompt" />
                            <p class="text-xs text-gray-500 mb-2">Variables: <code class="bg-gray-200 px-1 rounded">@{{PREGUNTA_JSON}}</code>, <code class="bg-gray-200 px-1 rounded">@{{CRITERIOS}}</code></p>
                            <textarea id="content" name="content" rows="15" class="mt-1 block w-full font-mono text-sm border-gray-300 rounded-md shadow-sm" required>{{ old('content') }}</textarea>
                        </div>

                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('admin.prompts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-button>
                                Crear y Enviar a Revisión
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>