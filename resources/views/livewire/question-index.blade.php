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