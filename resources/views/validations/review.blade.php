<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Revisión Humana de la Pregunta #{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- PANEL IZQUIERDO: INFORMACIÓN DE LA PREGUNTA (SOLO LECTURA) --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 space-y-6">
                {{-- ... (código para mostrar enunciado, opciones, etc. - sin cambios) ... --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Enunciado</h3>
                    <p class="mt-2 p-4 bg-gray-50 rounded-md text-gray-700">{{ $question->stem }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Opciones</h3>{{-- Contenedor con altura máxima y scroll vertical --}}
                    <div class="mt-4 space-y-4 max-h-[60vh] overflow-y-auto pr-4">
                        @foreach ($question->options as $option)
                            {{-- Contenedor para cada opción, con resaltado condicional --}}
                            <div
                                class="p-4 rounded-md border {{ $option->is_correct ? 'bg-teal-50 border-teal-300' : 'bg-gray-50 border-gray-200' }}">

                                {{-- Texto de la opción --}}
                                <p class="text-gray-900 font-semibold">
                                    {{ $option->option_text }}
                                    @if ($option->is_correct)
                                        <span class="ml-2 text-xs font-bold text-teal-700">(Respuesta Correcta)</span>
                                    @endif
                                </p>

                                {{-- Argumentación de la opción --}}
                                @if ($option->argumentation)
                                    <div class="mt-2 pl-4 border-l-2 border-gray-300">
                                        <p class="text-xs font-semibold text-gray-500">Argumentación:</p>
                                        <p class="text-sm text-gray-700 italic">"{{ $option->argumentation }}"</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bibliografíade la pregunta --}}
                        <h3 class="text-lg font-bold text-gray-900">Bibliografía:</h3>
                @if ($question->bibliography)
                    <div class="mt-2 pl-4 border-l-2 border-gray-300">
                        <p class="text-sm text-gray-700 italic">"{{ $question->bibliography }}"</p>
                    </div>
                @endif
            </div>

            {{-- PANEL DERECHO: CHECKLIST Y FORMULARIO DE DECISIÓN --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if ($aiValidation)
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Asistente de IA ({{ $aiValidation->ai_engine }})</p>
                        <p>Los siguientes campos han sido pre-llenados. Revisa y corrige si es necesario.</p>
                    </div>
                @else
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Revisión Manual</p>
                        <p>No se encontró una validación de IA previa. Por favor, realiza la evaluación completa.</p>
                    </div>
                @endif

                <form action="{{ route('validations.process_review', $question) }}" method="POST"
                    x-data="{ decision: 'approve' }">
                    @csrf

                    {{-- ========================================================== --}}
                    {{-- SECCIÓN DEL CHECKLIST (CÓDIGO A RESTAURAR/AÑADIR) --}}
                    {{-- ========================================================== --}}
                    <div class="space-y-8 max-h-[60vh] overflow-y-auto pr-4">
                        @foreach ($criteria->groupBy('category') as $category => $items)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2 capitalize border-b pb-2">
                                    {{ $category }}</h3>
                                <div class="space-y-4 mt-4">
                                    @foreach ($items as $criterion)
                                        @php
                                            // Buscamos la respuesta de la IA para este criterio.
                                            $aiResponse = $aiValidation
                                                ? $aiValidation->responses->firstWhere('criterion_id', $criterion->id)
                                                : null;
                                        @endphp

                                        <div x-data="{ response: '{{ $aiResponse?->response ?? 'si' }}' }">
                                            <label
                                                class="block font-medium text-sm text-gray-700">{{ $criterion->text }}</label>
                                            <div class="mt-2 flex items-center space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input x-model="response" type="radio"
                                                        name="criteria[{{ $criterion->id }}][response]" value="si"
                                                        class="text-indigo-600">
                                                    <span class="ms-2">Sí</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input x-model="response" type="radio"
                                                        name="criteria[{{ $criterion->id }}][response]" value="no"
                                                        class="text-indigo-600">
                                                    <span class="ms-2">No</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input x-model="response" type="radio"
                                                        name="criteria[{{ $criterion->id }}][response]" value="adecuar"
                                                        class="text-indigo-600">
                                                    <span class="ms-2">Adecuar</span>
                                                </label>
                                            </div>
                                            <div x-show="response === 'no' || response === 'adecuar'" x-cloak
                                                class="mt-2">
                                                <label for="comment_{{ $criterion->id }}"
                                                    class="block text-sm font-medium text-gray-500">Comentario:</label>
                                                <textarea id="comment_{{ $criterion->id }}" name="criteria[{{ $criterion->id }}][comment]" rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ $aiResponse?->comment ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- FORMULARIO DE DECISIÓN FINAL --}}
<form action="{{ route('validations.process_review', $question) }}" method="POST" x-data="{ decision: 'approve' }">
                    @csrf
                    
                    {{-- FORMULARIO DE DECISIÓN FINAL (IMPLEMENTADO) --}}
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-bold text-gray-900">Decisión Final del Validador</h3>
                        <x-validation-errors class="my-4" />

                        <div class="mt-4 space-y-2">
                            {{-- Opción Aprobar --}}
                            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-green-50 has-[:checked]:border-green-400 cursor-pointer">
                                <input type="radio" x-model="decision" name="decision" value="approve" class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <span class="ms-3 font-semibold text-green-800">Aprobar Pregunta</span>
                            </label>
                            
                            {{-- Opción Rechazar para Corrección --}}
                            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-orange-50 has-[:checked]:border-orange-400 cursor-pointer">
                                <input type="radio" x-model="decision" name="decision" value="reject" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                <span class="ms-3 font-semibold text-orange-800">Rechazar y Enviar a Corrección</span>
                            </label>

                            {{-- Opción Rechazar Definitivamente --}}
                            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-red-50 has-[:checked]:border-red-400 cursor-pointer">
                                <input type="radio" x-model="decision" name="decision" value="reject_permanently" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                <span class="ms-3 font-semibold text-red-800">Rechazar Definitivamente</span>
                            </label>
                        </div>
                        
                        {{-- Campo de Feedback (visible si se rechaza de cualquier forma) --}}
                        <div x-show="decision === 'reject' || decision === 'reject_permanently'" class="mt-4" style="display: none;">
                            <x-label for="feedback" value="Justificación para el validador (obligatorio si se rechaza):" />
                            <textarea name="feedback" id="feedback" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                            x-bind:required="decision !== 'approve'">{{ old('feedback') }}</textarea>
                        </div>

                        <div class="mt-6 flex justify-end space-x-4">
                             <a href="{{ route('validations.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Confirmar Decisión
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
