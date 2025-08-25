<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-2 sm:mb-0">
                {{ auth()->user()->role === 'administrador' ? 'Gestión Global de Preguntas' : 'Mis Preguntas' }}
            </h2>
            @can('create', App\Models\Question::class)
                <a href="{{ route('questions.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                        </path>
                    </svg>
                    Crear Nueva Pregunta
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notificaciones Flash --}}
            @if (session('status'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Carrera</th>
                                @if (auth()->user()->role === 'administrador')
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Autor</th>
                                @endif
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones de Validación</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gestión</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($questions as $question)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-mono" title="{{ $question->code }}">
                                            {{ Str::limit($question->code, 25) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">
                                            {{ $question->career->name ?? 'Sin Asignar' }}</div>
                                    </td>
                                    @if (auth()->user()->role === 'administrador')
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $question->author->name }}</div>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
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
                                                        <form action="{{ route('questions.submit_validation', $question) }}"
                                                            method="POST" class="flex items-center space-x-2">
                                                            @csrf
                                                            <select name="ai_engine"
                                                                class="w-28 rounded-md border-gray-300 shadow-sm text-xs py-1"
                                                                title="Seleccionar motor de IA">
                                                                <option value="chatgpt">ChatGPT</option>
                                                                <option value="gemini">Gemini</option>
                                                            </select>
                                                            <select name="prompt_id"
                                                                class="w-40 rounded-md border-gray-300 shadow-sm text-xs py-1"
                                                                title="Seleccionar un prompt personalizado">
                                                                <option value="">-- Prompt por Defecto --</option>
                                                                @foreach ($prompts as $prompt)
                                                                    <option value="{{ $prompt->id }}">{{ $prompt->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit"
                                                                class="p-2 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition"
                                                                title="Validar con motor seleccionado"><svg class="w-5 h-5"
                                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg></button>
                                                        </form>
                                                        <span class="text-gray-300">|</span>
                                                        <form action="{{ route('questions.compare.submit', $question) }}"
                                                            method="POST">
                                                            @csrf
                                                            <button type="submit"
                                                                class="p-2 bg-purple-100 text-purple-600 rounded-full hover:bg-purple-200 transition"
                                                                title="Validación Comparativa (GPT vs Gemini)"><svg class="w-5 h-5"
                                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                                    </path>
                                                                </svg></button>
                                                        </form>
                                                    @endcan
                                                @break

                                                @case('en_validacion_ai')
                                                @case('en_validacion_comparativa')
                                                @case('revisado_comparativo')
                                                @case('revisado_por_ai')
                                                    @if(auth()->user()->role === 'administrador')
                                                        <form action="{{ route('admin.questions.send_to_review', $question) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                class="px-3 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-md hover:bg-yellow-600 transition" 
                                                                title="Forzar el paso a Revisión Humana">
                                                                Forzar Revisión
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- Lógica de vista para el autor --}}
                                                        @if(in_array($question->status, ['en_validacion_ai', 'en_validacion_comparativa']))
                                                             <div class="flex items-center space-x-2 text-gray-500" title="Procesando..."><svg class="animate-spin h-5 w-5 text-gray-400" ...></svg><span class="text-xs italic">En Proceso...</span></div>
                                                        @elseif($question->status === 'revisado_comparativo')
                                                            <a href="{{ route('questions.compare', $question) }}" class="p-2 ..." title="Ver Comparativa"><svg ...></svg></a>
                                                        @else
                                                            <span class="text-xs text-gray-500 italic">Listo para revisión</span>
                                                        @endif
                                                    @endif
                                                    @break

                                                @case('en_revision_humana')
                                                    <div class="flex items-center space-x-2 text-blue-600"
                                                        title="Pregunta en revisión por un validador experto."><svg
                                                            class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                            </path>
                                                        </svg><span class="text-xs font-semibold">En Revisión Humana</span>
                                                    </div>
                                                @break

                                                @case('aprobado')
                                                    <div class="flex items-center justify-center space-x-2 text-green-600"
                                                        title="Pregunta aprobada y finalizada"><svg class="w-6 h-6"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd" />
                                                        </svg></div>
                                                @break

                                                @case('rechazado_permanentemente')
                                                    <div class="flex items-center justify-center space-x-2 text-red-600"
                                                        title="Esta pregunta fue rechazada permanentemente."><svg
                                                            class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                            </path>
                                                        </svg><span class="text-xs font-semibold">Rechazado</span></div>
                                                @break
                                            @endswitch
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-4">
                                            @can('view', $question)
                                                <a href="{{ route('questions.show', $question) }}"
                                                    class="text-gray-500 hover:text-gray-800" title="Ver Detalles"><svg
                                                        class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg></a>
                                            @endcan
                                            @can('update', $question)
                                                <a href="{{ route('questions.edit', $question) }}"
                                                    class="text-indigo-600 hover:text-indigo-900" title="Editar"><svg
                                                        class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                        </path>
                                                    </svg></a>
                                            @endcan
                                            @can('delete', $question)
                                                <form action="{{ route('questions.destroy', $question) }}" method="POST"
                                                    onsubmit="return confirm('¿Estás seguro?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                                        title="Eliminar"><svg class="w-5 h-5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>

                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->role === 'administrador' ? '6' : '5' }}"
                                            class="px-6 py-12 ...">No hay preguntas para mostrar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($questions->hasPages())
                        <div class="p-4 bg-gray-50 border-t">{{ $questions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </x-app-layout>
