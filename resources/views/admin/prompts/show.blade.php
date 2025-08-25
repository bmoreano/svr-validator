<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle del Prompt: {{ $prompt->name }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8 space-y-6">

                    {{-- Metadata del Prompt --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Motor de IA</h3>
                            <p class="mt-1 text-base font-semibold text-gray-900">{{ ucfirst($prompt->ai_engine) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Estado de Revisión</h3>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($prompt->status)
                                    @case('active') bg-green-100 text-green-800 @break
                                    @case('pending_review') bg-yellow-100 text-yellow-800 @break
                                    @case('rejected') bg-red-100 text-red-800 @break
                                @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $prompt->status)) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Activo para Usuarios</h3>
                            <p class="mt-1 text-base font-semibold {{ $prompt->is_active ? 'text-green-600' : 'text-red-600' }}">
                                {{ $prompt->is_active ? 'Sí' : 'No' }}
                            </p>
                        </div>
                    </div>

                    <!-- Descripción -->
                    @if($prompt->description)
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Descripción</h3>
                        <p class="mt-2 text-gray-600">{{ $prompt->description }}</p>
                    </div>
                    @endif

                    <!-- Contenido del Prompt -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Contenido del Prompt</h3>
                        <pre class="mt-2 p-4 bg-gray-800 text-white rounded-md text-sm font-mono whitespace-pre-wrap break-words"><code>{{ $prompt->content }}</code></pre>
                    </div>

                    <!-- Feedback de Revisión -->
                    @if($prompt->review_feedback)
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Feedback de la Revisión</h3>
                        <div class="mt-2 p-4 bg-yellow-50 border border-yellow-200 rounded-md text-sm text-yellow-800">
                            {{ $prompt->review_feedback }}
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center justify-between mt-8 border-t pt-5">
                        <a href="{{ route('admin.prompts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md ...">Volver a la Lista</a>
                        <a href="{{ route('admin.prompts.edit', $prompt) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white ...">Editar Prompt</a>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>