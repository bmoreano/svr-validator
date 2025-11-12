<x-app-layout>
    {{-- ========================================================== --}}
    {{-- ENCABEZADO DE LA PÁGINA --}}
    {{-- ========================================================== --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-2 sm:mb-0">
                {{ auth()->user()->role === 'administrador' ? 'Gestión Global de Preguntas' : 'Las Preguntas' }}
            </h2>
            @if (auth()->user()->role === 'autor')
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
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                {{-- ========================================================== --}}
                {{-- SECCIÓN DE NOTIFICACIONES FLASH (ÉXITO/ERROR) --}}
                {{-- (SE MANTIENE IGUAL) --}}
                {{-- ========================================================== --}}
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                        role="alert">
                        <span class="block sm:inline">{{ session('status') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6"
                        role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                {{-- ========================================================== --}}
                {{-- SECCIÓN DE FILTROS --}}
                {{-- (SE MANTIENE IGUAL, YA ERA FUNCIONAL Y CORRECTO) --}}
                {{-- ========================================================== --}}
                <div class="bg-gray-50 border rounded-lg p-4 mb-6">
                    <form action="{{ route('questions.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
                            {{-- Filtro Carrera --}}
                            <div>
                                <label for="filter_career"
                                    class="block text-sm font-medium text-gray-700">Carrera</label>
                                <select name="filter_career" id="filter_career"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Todas</option>
                                    @foreach ($careers as $id => $name)
                                        <option value="{{ $id }}" @selected(request('filter_career') == $id)>
                                            {{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro Autor --}}
                            @if (auth()->user()->role === 'administrador')
                                <div>
                                    <label for="filter_author"
                                        class="block text-sm font-medium text-gray-700">Autor</label>
                                    <select name="filter_author" id="filter_author"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Todos</option>
                                        @foreach ($authors as $id => $name)
                                            <option value="{{ $id }}" @selected(request('filter_author') == $id)>
                                                {{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Filtro Estado --}}
                            <div>
                                <label for="filter_status"
                                    class="block text-sm font-medium text-gray-700">Estado</label>
                                <select name="filter_status" id="filter_status"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Todos</option>
                                    @foreach ($statuses as $key => $value)
                                        <option value="{{ $key }}" @selected(request('filter_status') == $key)>
                                            {{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtro Fecha Desde --}}
                            <div>
                                <label for="filter_date_from"
                                    class="block text-sm font-medium text-gray-700">Desde</label>
                                <input type="date" name="filter_date_from" id="filter_date_from"
                                    value="{{ request('filter_date_from') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>

                            {{-- Filtro Fecha Hasta --}}
                            <div>
                                <label for="filter_date_to"
                                    class="block text-sm font-medium text-gray-700">Hasta</label>
                                <input type="date" name="filter_date_to" id="filter_date_to"
                                    value="{{ request('filter_date_to') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>

                            {{-- Botones --}}
                            <div class="flex items-center space-x-2">
                                <button type="submit"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Filtrar</button>
                                <a href="{{ route('questions.index') }}"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Limpiar</a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ========================================================== --}}
                {{-- TABLA DE PREGUNTAS --}}
                {{-- ========================================================== --}}
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
                                    Fecha Creación</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones de Validación</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gestión</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($questions as $question)
                                <tr>
                                    {{-- CÓDIGO --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                        {{ Str::limit($question->code, 25) }}</td>

                                    {{-- CARRERA --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $question->career->name ?? 'N/A' }}</td>

                                    {{-- AUTOR (Solo para admin) --}}
                                    @if (auth()->user()->role === 'administrador')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $question->author->name ?? 'N/A' }}</td>
                                    @endif


                                    {{-- FECHA DE CREACIÓN --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $question->created_at->format('Y-m-d H:i') }}</td>
                                    {{-- ESTADO (Con lógica de color refactorizada) --}}

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = match ($question->status) {
                                                'aprobado', 'revisado_comparativo' => 'bg-green-100 text-green-800',
                                                'borrador' => 'bg-gray-200 text-gray-800',
                                                'necesita_correccion' => 'bg-orange-100 text-orange-800',
                                                'fallo_comparativo',
                                                'rechazado_permanentemente',
                                                'error_validacion_ai'
                                                    => 'bg-red-100 text-red-800',
                                                'en_revision_humana' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-yellow-100 text-yellow-800', // para estados en proceso
                                            };
                                        @endphp
                                        @if ($question->status === 'error_validacion_ia')
                                            <div class="bg-red-100 border border-red-400 text-red-700 px-2 py-1 rounded text-sm"
                                                role="alert">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $question->status)) }}</span>
                                                {{-- Es mejor mostrar el mensaje de error real si lo tienes --}}
                                                <strong>Error:</strong>
                                                {{ $question->ia_error_message ?? 'Fallo en la validación' }}
                                            </div>
                                        @else
                                            {{-- Muestra otros estados normalmente --}}
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $question->status)) }}</span>
                                        @endif
                                    </td>
                                    {{-- <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = match ($question->status) {
                                                'aprobado', 'revisado_comparativo' => 'bg-green-100 text-green-800',
                                                'borrador' => 'bg-gray-200 text-gray-800',
                                                'necesita_correccion' => 'bg-orange-100 text-orange-800',
                                                'fallo_comparativo',
                                                'rechazado_permanentemente',
                                                'error_validacion_ai'
                                                    => 'bg-red-100 text-red-800',
                                                'en_revision_humana' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-yellow-100 text-yellow-800', // para estados en proceso
                                            };
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $question->status)) }}
                                        </span>
                                    </td> --}}

                                    {{-- ========================================================== --}}
                                    {{-- COLUMNA DE ACCIONES DE VALIDACIÓN (REFACTORIZADA) --}}
                                    {{-- ========================================================== --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            @if (in_array($question->status, ['borrador', 'necesita_correccion', 'fallo_comparativo']))
                                                {{-- FORMULARIO 1: Validación Simple (Botón Azul) --}}
                                                <form
                                                    action="{{ route('questions.submit_validation', ['question' => $question]) }}"
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
                                                        <option value="">-- Prompt x Defecto --</option>
                                                        @foreach ($prompts as $prompt)
                                                            <option value="{{ $prompt->id }}">{{ $prompt->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" title="Validar con motor seleccionado"
                                                        class="p-2 rounded-full text-blue-600 bg-blue-100 hover:bg-blue-200 transition">
                                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>
                                                </form>

                                                <span class="text-gray-300">|</span>

                                                {{-- FORMULARIO 2: Validación Comparativa (Botón Morado) --}}
                                                <form action="{{ route('questions.compare.submit', $question) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        title="Validación Comparativa (GPT vs Gemini)"
                                                        class="p-2 bg-purple-100 text-purple-600 rounded-full hover:bg-purple-200 transition">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @elseif (in_array($question->status, ['en_validacion_ai', 'en_validacion_comparativa']))
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-yellow-500"
                                                        fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    Procesando...
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500 italic">No hay acciones
                                                    disponibles</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- COLUMNA DE GESTIÓN (Ver, Editar, Eliminar) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-4">
                                            <a href="{{ route('questions.show', $question) }}"
                                                class="text-gray-500 hover:text-gray-800" title="Ver Detalles">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </a>

                                            @can('update', $question)
                                                <a href="{{ route('questions.edit', $question) }}"
                                                    class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                        </path>
                                                    </svg>
                                                </a>
                                            @endcan

                                            @can('delete', $question)
                                                <button onclick="deleteQuestion({{ $question->id }})"
                                                    class="text-red-600 hover:text-red-900" title="Eliminar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->role === 'administrador' ? '7' : '6' }}"
                                        class="px-6 py-12 text-center text-gray-500 italic">
                                        No se encontraron preguntas que coincidan con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ========================================================== --}}
                {{-- PAGINACIÓN --}}
                {{-- (SE MANTIENE IGUAL) --}}
                {{-- ========================================================== --}}
                <div class="mt-6">
                    {{ $questions->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- JAVASCRIPT PARA ACCIONES DINÁMICAS (Refactorizado) --}}
    {{-- ========================================================== --}}
    @push('scripts')
        <script>
            /**
             * Función auxiliar robusta para crear y enviar formularios dinámicos para acciones
             * como eliminar o duplicar, que requieren confirmación y método POST/DELETE.
             */
            function createAndSubmitForm(actionUrl, method = 'POST', confirmationMessage = null) {
                if (confirmationMessage && !confirm(confirmationMessage)) {
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST'; // Siempre es POST para el navegador
                form.action = actionUrl;
                form.style.display = 'none';

                // 1. Añadir Token CSRF de forma segura
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfMeta) {
                    console.error('Error Crítico: La meta tag CSRF no se encontró.');
                    alert('Error de seguridad. No se puede procesar la solicitud.');
                    return;
                }
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfMeta.content;
                form.appendChild(csrfInput);

                // 2. Añadir "Method Spoofing" para DELETE
                if (method.toUpperCase() === 'DELETE') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                }

                document.body.appendChild(form);
                form.submit();
            }

            // Funciones wrapper para ser llamadas desde el HTML
            function deleteQuestion(questionId) {
                // Asume que la ruta de borrado es 'questions.destroy'
                const url = `{{ url('questions') }}/${questionId}`;
                createAndSubmitForm(url, 'DELETE',
                    '¿Estás seguro de que quieres eliminar esta pregunta? Esta acción es irreversible.');
            }

            // Ejemplo si tuvieras una ruta para duplicar
            /*
            function duplicateQuestion(questionId) {
                const url = `{{ url('questions') }}/${questionId}/duplicate`;
                createAndSubmitForm(url, 'POST', '¿Estás seguro de que quieres duplicar esta pregunta?');
            }
            */
        </script>
    @endpush
</x-app-layout>


