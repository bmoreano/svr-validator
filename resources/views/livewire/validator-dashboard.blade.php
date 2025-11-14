<div wire:poll.15s>
    {{-- Interruptor de filtro (sin cambios) --}}
    <div class="flex justify-end items-center mb-4 px-4 py-2 bg-gray-50 rounded-lg border">
        @if ($filterByMyCareer && !auth()->user()->career_id)
            <span class="mr-4 text-xs text-yellow-700 italic">
                El filtro "mi carrera" está activo, pero tu perfil no tiene una carrera asignada.
            </span>
        @endif
        <label for="careerFilter" class="flex items-center cursor-pointer">
            <span class="mr-3 text-sm font-medium text-gray-900">Mostrar solo mi carrera</span>
            <div class="relative">
                <input id="careerFilter" type="checkbox" class="sr-only" wire:model="filterByMyCareer">
                <div class="block bg-gray-200 w-14 h-8 rounded-full transition"></div>
                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform"></div>
            </div>
        </label>
        <style>
            input:checked~.dot {
                transform: translateX(100%);
            }

            input:checked~.block {
                background-color: #4f46e5;
            }
        </style>
    </div>
    @php
        // 1. Verificamos las variables de entorno y construimos un array de advertencias.
        $configWarnings = [];
        if (empty(env('OPENAI_API_KEY'))) {
            $configWarnings[] = [
                'title' => 'OpenAI API Key no configurada',
                'message' =>
                    'La generación de embeddings para análisis semántico estará deshabilitada. Esta función es clave para encontrar preguntas duplicadas o similares con alta precisión.',
            ];
        }
        if (empty(env('COPYSCAPE_API_KEY')) || empty(env('COPYSCAPE_USERNAME'))) {
            $configWarnings[] = [
                'title' => 'Credenciales de Copyscape no configuradas',
                'message' =>
                    'El análisis profundo de plagio contra fuentes externas de internet estará deshabilitado. Solo se realizarán comprobaciones de plagio internas.',
            ];
        }
    @endphp
    {{-- 2. Si hay al menos una advertencia, mostramos la modal. --}}
    @if (count($configWarnings) > 0)
        <div x-data="{ showModal: true }" x-show="showModal" @keydown.escape.window="showModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4"
            style="display: none;">

            {{-- Panel de la modal --}}
            <div @click.away="showModal = false" x-show="showModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-lg shadow-2xl w-full max-w-2xl transform transition-all overflow-hidden">

                {{-- Encabezado --}}
                <div class="flex items-center justify-between px-6 py-4 bg-yellow-50 border-b border-yellow-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 text-yellow-600 rounded-full p-2">
                            {{-- Icono de Advertencia --}}
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-4 text-lg font-medium text-yellow-800">Advertencias de Configuración</h3>
                    </div>
                    <button @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-gray-400">&times;</button>
                </div>

                {{-- Cuerpo con la lista de advertencias --}}
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        El sistema ha detectado que faltan algunas configuraciones importantes. Las siguientes
                        funcionalidades estarán deshabilitadas o limitadas hasta que se configuren las claves de API
                        correspondientes en el archivo `.env`:
                    </p>
                    <div class="space-y-4">
                        @foreach ($configWarnings as $warning)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <p class="font-bold text-yellow-800">{{ $warning['title'] }}</p>
                                <p class="mt-1 text-sm text-yellow-700">{{ $warning['message'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Pie de la modal --}}
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
                    <button type="button" @click="showModal = false"
                        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    @endif
    {{ 'livewire' }}
    {{ $pendingQuestionslivewire }}
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        {{-- ========================================================== --}}
                        {{-- 1. ENCABEZADO DE COLUMNA MODIFICADO --}}
                        {{-- ========================================================== --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Autor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Carrera
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Última Act.
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acción
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($pendingQuestionslivewire as $question)
                        <tr class="hover:bg-gray-50">
                            {{-- ========================================================== --}}
                            {{-- 2. CONTENIDO DE CELDA MODIFICADO --}}
                            {{-- ========================================================== --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono" title="{{ $question->code }}">
                                    {{ Str::limit($question->code, 25) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $question->author->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $question->career->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst(str_replace('_', ' ', $question->status)) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $question->updated_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('validations.review', $question) }}"
                                    class="inline-flex items-center p-2 bg-indigo-100 text-indigo-600 rounded-full hover:bg-indigo-200 transition"
                                    title="Revisar Pregunta">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                        </path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">¡Excelente trabajo!
                                No hay preguntas pendientes de revisión.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pendingQuestionslivewire->hasPages())
            <div class="p-4 bg-gray-50 border-t">
                {{ $pendingQuestionslivewire->links() }}
            </div>
        @endif
    </div>
</div>
