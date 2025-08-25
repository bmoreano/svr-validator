<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Prompt #{{ $prompt->id }}: {{ $prompt->name }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <x-validation-errors class="mb-4" />

                    <form action="{{ route('admin.prompts.update', $prompt) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nombre del Prompt -->
                        <div class="mb-4">
                            <x-label for="name" value="Nombre del Prompt" />
                            <x-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $prompt->name)" required />
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <x-label for="description" value="Descripción" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $prompt->description) }}</textarea>
                        </div>

                        <!-- Motor de IA -->
                        <div class="mb-4">
                            <x-label for="ai_engine" value="Motor de IA:" />
                            <select id="ai_engine" name="ai_engine" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="chatgpt" @selected(old('ai_engine', $prompt->ai_engine) == 'chatgpt')>ChatGPT</option>
                                <option value="gemini" @selected(old('ai_engine', $prompt->ai_engine) == 'gemini')>Gemini</option>
                            </select>
                        </div>

                        <!-- Contenido del Prompt -->
                        <div class="mb-4">
                            <x-label for="content" value="Contenido del Prompt" />
                            <p class="text-xs text-gray-500 mb-2">Variables disponibles: <code class="bg-gray-200 px-1 rounded">@{{PREGUNTA_JSON}}</code>, <code class="bg-gray-200 px-1 rounded">@{{CRITERIOS}}</code></p>
                            <textarea id="content" name="content" rows="15" class="mt-1 block w-full font-mono text-sm border-gray-300 rounded-md shadow-sm" required>{{ old('content', $prompt->content) }}</textarea>
                        </div>

                        <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Estado del Prompt -->
                            <div class="mb-4">
                                <x-label for="status" value="Estado de Revisión" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="pending_review" @selected(old('status', $prompt->status) == 'pending_review')>Pendiente de Revisión</option>
                                    <option value="active" @selected(old('status', $prompt->status) == 'active')>Activo</option>
                                    <option value="rejected" @selected(old('status', $prompt->status) == 'rejected')>Rechazado</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Si lo pones en "Pendiente", se re-enviará a la meta-validación.</p>
                            </div>

                            <!-- Activo/Inactivo -->
                            <div class="mb-4">
                                <x-label for="is_active" value="¿Está Activo?" />
                                <label for="is_active" class="flex items-center mt-2">
                                    <x-checkbox id="is_active" name="is_active" :checked="old('is_active', $prompt->is_active)" />
                                    <span class="ms-2 text-sm text-gray-600">Visible para los usuarios</span>
                                </label>
                            </div>
                        </div>

                        <!-- Feedback de Revisión -->
                        <div class="mb-4">
                            <x-label for="review_feedback" value="Feedback de la Revisión (solo lectura)" />
                            <textarea id="review_feedback" name="review_feedback" rows="4" class="mt-1 block w-full font-mono text-sm bg-gray-100 border-gray-300 rounded-md shadow-sm" readonly>{{ old('review_feedback', $prompt->review_feedback) }}</textarea>
                        </div>


                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('admin.prompts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-button>
                                Actualizar Prompt
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>