<div wire:poll.10s>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200"> 
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                        @if(auth()->user()->role === 'administrador')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones de Validación</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Gestión</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($questions as $question)
                        <tr class="{{ $question->status === 'rechazado_permanentemente' ? 'bg-red-50 opacity-60' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-mono" title="{{ $question->code }}">{{ Str::limit($question->code, 25) }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-700">{{ $question->career->name ?? 'Sin Asignar' }}</div></td>
                            @if(auth()->user()->role === 'administrador')
                                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $question->author->name }}</div></td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @switch($question->status)
                                        @case('aprobado') @case('revisado_comparativo') class="bg-green-100 text-green-800" @break
                                        @case('borrador') class="bg-gray-200 text-gray-800" @break
                                        @case('necesita_correccion') class="bg-orange-100 text-orange-800" @break
                                        @case('fallo_comparativo') @case('rechazado_permanentemente') class="bg-red-100 text-red-800" @break
                                        @default class="bg-yellow-100 text-yellow-800"
                                    @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $question->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    @switch($question->status)
                                        @case('borrador')
                                        @case('necesita_correccion')
                                        @case('fallo_comparativo')
                                            @can('submitForValidation', $question)
                                                {{-- Formularios de envío con selectores e iconos --}}
                                            @endcan
                                            @break

                                        @case('en_validacion_ai')
                                        @case('en_validacion_comparativa')
                                            <div class="flex items-center space-x-2 text-gray-500" title="Procesando validación automática..."><svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="text-xs italic">En Proceso...</span></div>
                                            @break

                                        @case('revisado_comparativo')
                                        @case('revisado_por_ai')
                                            @if(auth()->user()->role === 'administrador')
                                                <form action="{{ route('admin.questions.send_to_review', $question) }}" method="POST">@csrf @method('PATCH')<button type="submit" class="px-3 py-1 bg-teal-500 text-white text-xs font-semibold rounded-md hover:bg-teal-600 transition" title="Enviar a Revisión Humana">Enviar a Revisión</button></form>
                                            @else
                                                <div class="flex items-center space-x-2 text-gray-500" title="Pendiente de la acción de un validador"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="text-xs italic">Listo para Revisión</span></div>
                                            @endif
                                            @break

                                        @case('en_revision_humana')
                                            <div class="flex items-center space-x-2 text-blue-600" title="Pregunta en revisión por un validador experto."><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg><span class="text-xs font-semibold">En Revisión Humana</span></div>
                                            @break

                                        @case('aprobado')
                                            <div class="flex items-center justify-center space-x-2 text-green-600" title="Pregunta aprobada y finalizada"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg></div>
                                            @break

                                        @case('rechazado_permanentemente')
                                            <div class="flex items-center justify-center space-x-2 text-red-600" title="Esta pregunta fue rechazada permanentemente."><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg><span class="text-xs font-semibold">Rechazado</span></div>
                                            @break
                                    @endswitch
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-4">
                                    @if($question->status !== 'aprobado')
                                        @can('view', $question)
                                            <a href="{{ route('questions.show', $question) }}" ...><svg ...></svg></a>
                                        @endcan
                                    @endif
                                    @can('update', $question)
                                        <a href="{{ route('questions.edit', $question) }}" ...><svg ...></svg></a>
                                    @endcan
                                    @can('delete', $question)
                                        <form ...><button ...><svg ...></svg></button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ auth()->user()->role === 'administrador' ? '6' : '5' }}" class="px-6 py-12 text-center text-gray-500 italic">No hay preguntas para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($questions->hasPages())
            <div class="p-4 bg-gray-50 border-t">{{ $questions->links() }}</div>
        @endif
    </div>
</div>























<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalle del Reactivo') }}: {{ $question->code ?? 'Nuevo Reactivo' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Información del Reactivo</h3>
                        
                        <div class="mb-4">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @switch($question->status)
                                    @case('borrador') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @break
                                    @case('en_espera') bg-indigo-100 text-indigo-800 dark:bg-indigo-600 dark:text-indigo-100 @break
                                    @case('en_validacion_ai') bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100 @break
                                    @case('en_validacion_comparativa') bg-purple-100 text-purple-800 dark:bg-purple-600 dark:text-purple-100 @break
                                    @case('revisado_por_ai') bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100 @break
                                    @case('en_revision_humana') bg-orange-100 text-orange-800 dark:bg-orange-600 dark:text-orange-100 @break
                                    @case('aprobado') bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100 @break
                                    @case('rechazado') bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100 @break
                                @endswitch
                            ">
                                {{ ucfirst(str_replace('_', ' ', $question->status)) }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pregunta (Stem):</label>
                            <div class="prose dark:prose-invert max-w-none p-4 bg-gray-50 dark:bg-gray-700 rounded-md shadow-inner">
                                {!! $question->stem !!}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Opciones:</label>
                            <ul class="list-decimal list-inside mt-2 space-y-2">
                                @foreach($question->options as $option)
                                    <li class="p-3 rounded-md {{ $option->is_correct ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700' : 'bg-gray-50 dark:bg-gray-700' }}">
                                        {{ $option->text }}
                                        @if($option->is_correct)
                                            <span class="text-green-600 dark:text-green-300 font-bold ml-2">(Correcta)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>

                    <div class="md:col-span-1 space-y-6">
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Metadata</h4>
                            <div class="space-y-2">
                                <p class="text-sm"><strong class="text-gray-700 dark:text-gray-300">Autor:</strong> {{ $question->author->name }}</p>
                                <p class="text-sm"><strong class="text-gray-700 dark:text-gray-300">Carrera:</strong> {{ $question->career?->name ?? 'N/A' }}</p>
                                <p class="text-sm"><strong class="text-gray-700 dark:text-gray-300">Creación:</strong> {{ $question->created_at->format('d/m/Y H:i') }}</p>
                                <p class="text-sm"><strong class="text-gray-700 dark:text-gray-300">Últ. Modif.:</strong> {{ $question->updated_at->diffForHumans() }}</p>
                                <p class="text-sm"><strong class="text-gray-700 dark:text-gray-300">Bibliografía:</strong> {{ $question->bibliography ?? 'No especificada' }}</p>
                            </div>
                        </div>

                        <div id="validation-actions" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Acciones de Validación</h4>
                            
                            @can('submit', $question)
                                
                                {{-- REGLA 3: Si no hay motores activos, mostrar mensaje de error --}}
                                @if($activeEnginesCount === 0)
                                    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                                        <span class="font-medium">¡Acción Requerida!</span> Por favor, contacta al administrador para configurar al menos una API Key (OpenAI o Gemini) y poder validar.
                                    </div>

                                {{-- Si hay al menos 1 motor activo, mostrar el formulario --}}
                                @else
                                    <form action="{{ route('questions.submit_validation', $question) }}" method="POST">
                                        @csrf
                                        <div class="space-y-4">
                                            <div>
                                                <label for="ai_engine" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Motor de IA</label>
                                                <select id="ai_engine" name="ai_engine" class="mt-1 block w-full input-form">
                                                    
                                                    {{-- REGLA 1: Listar solo motores activos --}}
                                                    @foreach($availableEngines as $key => $name)
                                                        <option value="{{ $key }}">{{ $name }}</option>
                                                    @endforeach

                                                    {{-- REGLA 2: Mostrar Comparativa solo si hay más de 1 motor --}}
                                                    @if($activeEnginesCount > 1)
                                                        <option value="comparative">Validación Comparativa (Ambos)</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <div>
                                                <label for="prompt_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usar Prompt</label>
                                                <select id="prompt_id" name="prompt_id" class="mt-1 block w-full input-form">
                                                    <option value="">(Usar Prompt por Defecto del Sistema)</option>
                                                    @foreach(App\Models\Prompt::where('status', 'aprobado')->orderBy('name')->get() as $prompt)
                                                        <option value="{{ $prompt->id }}">{{ $prompt->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <x-button class="w-full justify-center">
                                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688 0-1.25-.561-1.25-1.25s.562-1.25 1.25-1.25 1.25.561 1.25 1.25-.562 1.25-1.25 1.25z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.83 15.84c-.688 0-1.25-.561-1.25-1.25s.562-1.25 1.25-1.25 1.25.561 1.25 1.25-.562 1.25-1.25 1.25z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.01 19.34c-2.36 0-4.27-1.909-4.27-4.27 0-2.36 1.91-4.27 4.27-4.27s4.27 1.91 4.27 4.27c0 2.361-1.91 4.27-4.27 4.27z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 19.34c-2.36 0-4.27-1.909-4.27-4.27 0-2.36 1.91-4.27 4.27-4.27s4.27 1.91 4.27 4.27c0 2.361-1.91 4.27-4.27 4.27z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 110-18 9 9 0 010 18z" />
                                                </svg>
                                                Enviar a Validación de IA
                                            </x-button>
                                        </div>
                                    </form>
                                @endif

                            @else
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($question->status == 'en_espera')
                                        Esta pregunta está "En Espera". El autor debe responder a la notificación para desbloquearla.
                                    @else
                                        No tienes permiso para enviar este reactivo a validación. Solo el autor puede enviarlo desde "borrador", o un admin si el autor está inactivo.
                                    @endif
                                </p>
                            @endcan
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Acciones Generales</h4>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('questions.index') }}" class="btn-secondary">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Volver
                                </a>
                                @can('update', $question)
                                    <a href="{{ route('questions.edit', $question) }}" class="btn-secondary-yellow">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Editar
                                    </a>
                                @endcan
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Historial de Validaciones</h3>
                    @include('validations.index', ['validations' => $question->validations])
                </div>

            </div>
        </div>
    </div>
</x-app-layout>