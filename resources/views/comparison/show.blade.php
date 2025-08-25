<x-app-layout>
    {{-- ========================================================== --}}
    {{-- ENCABEZADO DE LA PÁGINA --}}
    {{-- ========================================================== --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Comparativa de Validación de IA para Pregunta #{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                
                {{-- SECCIÓN DE RESUMEN DE LA PREGUNTA --}}
                <div class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-3">Detalles de la Pregunta</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Código:</p>
                            <p class="font-semibold text-gray-800 font-mono">{{ $question->code }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Propietario:</p>
                            <p class="font-semibold text-gray-800">{{ $question->author->name }}</p>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <p class="text-gray-500">Enunciado:</p>
                            <p class="font-semibold text-gray-800 mt-1">{{ $question->stem }}</p>
                        </div>
                    </div>
                </div>

                {{-- BANNER DE ADVERTENCIA SI EL PROCESO FUE PARCIAL --}}
                @if($question->status === 'fallo_comparativo' || ($chatGptValidation && $chatGptValidation->status === 'failed') || ($geminiValidation && $geminiValidation->status === 'failed'))
                    <div class="p-4 mb-6 text-sm text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-300" role="alert">
                      <p><span class="font-medium">Proceso Incompleto:</span> Uno de los motores de IA falló durante la validación. Los resultados que se muestran pueden ser parciales.</p>
                    </div>
                @endif
                
                {{-- TABLA COMPARATIVA DE VALIDACIONES --}}
                <h3 class="text-xl font-bold text-gray-900 mb-4">Resultados de la Validación</h3>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider w-2/5">Criterio de Validación</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider w-3/10">
                                    <div class="flex items-center">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4d/ChatGPT_logo.svg/120px-ChatGPT_logo.svg.png" alt="ChatGPT" class="h-4 mr-2">
                                        Validación ChatGPT
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase tracking-wider w-3/10">
                                    <div class="flex items-center">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Google_Gemini_logo.svg/120px-Google_Gemini_logo.svg.png" alt="Gemini" class="h-4 mr-2">
                                        Validación Gemini
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($criteria as $criterion)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-800 align-top">{{ $criterion->text }}</td>
                                    
                                    {{-- Celda para los resultados de ChatGPT --}}
                                    <td class="px-6 py-4 text-sm align-top">
                                        @if($chatGptValidation)
                                            @if($chatGptValidation->status === 'failed')
                                                <div class="text-center p-2 text-red-700 bg-red-50 rounded border border-red-200">
                                                    <p class="font-bold">Intento Fallido</p>
                                                    @can('viewAdminContent', App\Models\User::class) {{-- Asumiendo un Gate/Policy para admin --}}
                                                        <p class="text-xs italic mt-1" title="{{ $chatGptValidation->failure_reason }}">{{ Str::limit($chatGptValidation->failure_reason, 40) }}</p>
                                                    @endcan
                                                </div>
                                            @else
                                                @php $response = $chatGptValidation->responses->firstWhere('criterion_id', $criterion->id); @endphp
                                                @if($response)
                                                    <p><span class="font-bold">Respuesta:</span> <span class="font-mono px-2 py-1 text-xs rounded @if($response->response == 'si') bg-green-100 text-green-800 @elseif($response->response == 'no') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">{{ ucfirst($response->response) }}</span></p>
                                                    @if($response->comment) <p class="text-gray-600 mt-2 italic border-l-2 border-gray-300 pl-2">"{{ $response->comment }}"</p> @endif
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic">Pendiente...</span>
                                        @endif
                                    </td>
                                    
                                    {{-- Celda para los resultados de Gemini --}}
                                    <td class="px-6 py-4 text-sm align-top">
                                         @if($geminiValidation)
                                            @if($geminiValidation->status === 'failed')
                                                <div class="text-center p-2 text-red-700 bg-red-50 rounded border border-red-200">
                                                    <p class="font-bold">Intento Fallido</p>
                                                    @can('viewAdminContent', App\Models\User::class)
                                                        <p class="text-xs italic mt-1" title="{{ $geminiValidation->failure_reason }}">{{ Str::limit($geminiValidation->failure_reason, 40) }}</p>
                                                    @endcan
                                                </div>
                                            @else
                                                @php $response = $geminiValidation->responses->firstWhere('criterion_id', $criterion->id); @endphp
                                                @if($response)
                                                    <p><span class="font-bold">Respuesta:</span> <span class="font-mono px-2 py-1 text-xs rounded @if($response->response == 'si') bg-green-100 text-green-800 @elseif($response->response == 'no') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">{{ ucfirst($response->response) }}</span></p>
                                                    @if($response->comment) <p class="text-gray-600 mt-2 italic border-l-2 border-gray-300 pl-2">"{{ $response->comment }}"</p> @endif
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic">Pendiente...</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                 <div class="flex justify-end mt-6">
                    <a href="{{ route('questions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        Volver a la Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>