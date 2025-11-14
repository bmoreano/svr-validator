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
                                    {{-- Botón VER --}}
                                    @if($question->status !== 'aprobado')
                                        @can('view', $question)
                                            <a href="{{ route('questions.show', $question) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver Detalles">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        @endcan
                                    @endif
                                    
                                    {{-- Botón ASIGNAR VALIDADOR (Solo Admin) --}}
                                    @if(auth()->user()->role === 'administrador')
                                        <button wire:click="openAssignValidatorModal({{ $question->id }})" class="text-teal-600 hover:text-teal-900" title="Asignar Validador">
                                            {{-- Ícono de Usuario con signo + --}}
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    {{-- Botón EDITAR --}}
                                    @can('update', $question)
                                        <a href="{{ route('questions.edit', $question) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endcan
                                    
                                    {{-- Botón ELIMINAR --}}
                                    @can('delete', $question)
                                        <button wire:click="confirmQuestionDeletion({{ $question->id }})" class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
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

    <x-dialog-modal wire:model.live="assigningValidator">
        <x-slot name="title">
            Asignar Validador
        </x-slot>

        <x-slot name="content">
            <p class="mb-4 text-sm text-gray-600">Selecciona un validador experto para revisar este reactivo.</p>
            
            <form id="assign-validator-form" action="{{ $questionToAssignId ? route('admin.questions.assign_validator', $questionToAssignId) : '#' }}" method="POST">
                @csrf
                @method('PATCH')

                <div>
                    <x-label for="validator_id" value="Validador" />
                    <select id="validator_id" name="validator_id" class="mt-1 block w-full input-form">
                        <option value="">Seleccione un validador...</option>
                        {{-- La variable $validators viene del método render() de la clase Livewire --}}
                        @if(isset($validators))
                            @foreach($validators as $validator)
                                <option value="{{ $validator->id }}">{{ $validator->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </form>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('assigningValidator', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-button class="ml-2" type="submit" form="assign-validator-form" wire:loading.attr="disabled">
                Asignar
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <x-confirmation-modal wire:model.live="confirmingQuestionDeletion">
        <x-slot name="title">
            Eliminar Reactivo
        </x-slot>

        <x-slot name="content">
            ¿Estás seguro de que deseas eliminar este reactivo? Esta acción no se puede deshacer.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingQuestionDeletion', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="deleteQuestion" wire:loading.attr="disabled">
                Eliminar Reactivo
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

</div>