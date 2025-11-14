<div>
    {{-- 
    wire:poll.2s le dice a Livewire que llame al método render() 
    de este componente cada 2 segundos.
--}}
    <div wire:poll.2s>

        {{-- 1. ESTADO DE CARGA: Mientras la pregunta se está validando --}}
        @if (in_array($question->status, ['en_validacion_ai', 'en_validacion_comparativa']))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8 text-center">

                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Validando con IA...</h3>

                <div class="flex items-center justify-center space-x-2 text-blue-600">
                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-lg italic">Procesando...</span>
                </div>

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    La página se actualizará automáticamente cuando la IA complete la revisión. Esto puede tomar hasta
                    un minuto.
                </p>

            </div>

            {{-- 2. ESTADO FINALIZADO: Cuando el sondeo detecta que la validación terminó --}}
        @elseif($latestValidation)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">¡Validación de IA Completada!
                </h3>
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    A continuación se muestran los resultados "en caliente" de la validación automática.
                </p>

                {{-- Reutilizamos la vista que ya tienes para mostrar el historial --}}
                @include('validations.index', ['validations' => collect([$latestValidation])])

                <div class="mt-6 text-right">
                    <a href="{{ route('questions.show', $question) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                        Volver al Reactivo
                    </a>
                </div>
            </div>

            {{-- 3. ESTADO DE ERROR: Si algo salió mal --}}
        @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8 text-center">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-4">Error de Estado</h3>
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    El estado actual de la pregunta ({{ $question->status }}) no es válido para esta vista de progreso.
                </p>
                <div class="mt-6 text-right">
                    <a href="{{ route('questions.show', $question) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                        Volver
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
