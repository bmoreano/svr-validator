<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Mis Preguntas
                </h2>
                @if($currentStatus)
                    <div class="flex items-center mt-2">
                        <span class="text-sm text-gray-500">Filtrando por estado:</span>
                        <span class="ml-2 font-semibold px-2.5 py-1 bg-gray-200 text-gray-800 rounded-full text-xs">
                            {{ ucfirst(str_replace('_', ' ', $currentStatus)) }}
                        </span>
                        <a href="{{ route('questions.index') }}" class="ml-3 text-gray-400 hover:text-red-500 transition" title="Quitar filtro">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
            <div class="mt-4 sm:mt-0">
                @can('create', App\Models\Question::class)
                    <a href="{{ route('questions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Crear Nueva Pregunta
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enunciado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                                <th class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($questions as $question)
                                <tr>
                                    {{-- ... celdas de datos ... --}}
                                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 truncate" title="{{ $question->stem }}" style="max-width: 40ch;">{{ $question->stem }}</div></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($question->status)
                                                @case('aprobado') bg-green-100 text-green-800 @break
                                                @case('borrador') bg-gray-200 text-gray-800 @break
                                                @case('necesita_correccion') bg-orange-100 text-orange-800 @break
                                                @case('rechazado') bg-red-200 text-red-800 font-bold @break
                                                @default bg-yellow-100 text-yellow-800
                                            @endswitch">
                                            {{ ucfirst(str_replace('_', ' ', $question->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $question->created_at->format('d/m/Y') }}</td>
                                    
                                    {{-- ======================================================= --}}
                                    {{-- ==       CELDA DE ACCIONES CON ÍCONOS MEJORADOS        == --}}
                                    {{-- ======================================================= --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-4">
                                            
                                            <!-- Acción de Validación con IA (Ícono de Bot) -->
                                            @can('submitForValidation', $question)
                                                @if(in_array($question->status, ['borrador', 'necesita_correccion']))
                                                    <div x-data="{ open: false }" class="relative">
                                                        <button @click="open = !open" class="text-green-600 hover:text-green-900" title="Validar con IA">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M12 6V3m0 18v-3m6-6h3m-3 6h3M6 12H3m18 0h-3" /></svg>
                                                        </button>
                                                        <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20" style="display: none;" role="menu">
                                                            <div class="py-1">
                                                                <form action="{{ route('questions.submit', $question) }}" method="POST" class="block w-full"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="ai_engine" value="chatgpt"><button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">con ChatGPT</button></form>
                                                                <form action="{{ route('questions.submit', $question) }}" method="POST" class="block w-full"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="ai_engine" value="gemini"><button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">con Gemini</button></form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endcan
                                            
                                            <!-- Acción de Ver (Ícono de Ojo) -->
                                            @can('view', $question)
                                                <a href="{{ route('questions.show', $question) }}" class="text-gray-500 hover:text-gray-800" title="Ver Detalles">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                </a>
                                            @endcan

                                            <!-- Acción de Editar (Ícono de Lápiz) -->
                                            @can('update', $question)
                                                <a href="{{ route('questions.edit', $question) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </a>
                                            @endcan
                                            
                                            <!-- Acción de Eliminar (Ícono de Papelera) -->
                                            @can('delete', $question)
                                                <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta pregunta?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No se encontraron preguntas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    {{ $questions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>