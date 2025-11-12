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











<div class="p-6 sm:p-8">
    
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <h1 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Mis Reactivos</h1>
        
        @can('create', App\Models\Question::class)
            <a href="{{ route('questions.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Crear Reactivo
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <input wire:model.debounce.300ms="search" type="text" placeholder="Buscar por texto o código..." class="input-form">
        
        <select wire:model="filterByStatus" class="input-form">
            <option value="">Todos los Estados</option>
            <option value="borrador">Borrador</option>
            <option value="en_validacion_ai">En Validación AI</option>
            <option value="revisado_por_ai">Revisado por AI</option>
            <option value="en_revision_humana">En Revisión Humana</option>
            <option value="aprobado">Aprobado</option>
            <option value="rechazado">Rechazado</option>
        </select>
        
        @if(auth()->user()->hasRole('administrador'))
            <select wire:model="filterByCareer" class="input-form">
                <option value="">Todas las Carreras</option>
                @foreach($careers as $career)
                    <option value="{{ $career->id }}">{{ $career->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('code')">
                        Código
                        @if($sortField === 'code') <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span> @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Reactivo (Stem)
                    </th>
                    
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Carrera
                    </th>
                    @if(auth()->user()->hasRole('administrador'))
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('author_id')">
                            Autor
                            @if($sortField === 'author_id') <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span> @endif
                        </th>
                    @endif
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                        Estado
                        @if($sortField === 'status') <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span> @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('updated_at')">
                        Última Modificación
                        @if($sortField === 'updated_at') <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span> @endif
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Acciones</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($questions as $question)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $question->code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 max-w-xs truncate">
                            {{ Str::limit(strip_tags($question->stem), 50) }}
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ $question->career?->name ?? 'Sin Carrera' }}
                        </td>
                        @if(auth()->user()->hasRole('administrador'))
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                {{ $question->author?->name ?? 'Usuario Eliminado' }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($question->status)
                                    @case('borrador') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @break
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ $question->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('questions.show', $question) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-3">Ver</a>
                            
                            @can('update', $question)
                                <a href="{{ route('questions.edit', $question) }}" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-200 mr-3">Editar</a>
                            @endcan

                            @can('delete', $question)
                                <button wire:click="confirmQuestionDeletion({{ $question->id }})" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">
                                    Eliminar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->hasRole('administrador') ? '7' : '6' }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No se encontraron reactivos que coincidan con su búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $questions->links() }}
    </div>

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