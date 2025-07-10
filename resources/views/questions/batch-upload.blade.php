<x-app-layout>
    {{-- ====================================================================== --}}
    {{-- ==                             ENCABEZADO                           == --}}
    {{-- ====================================================================== --}}
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            {{-- Logo para mantener la identidad visual --}}
            <img src="{{ asset('images/val_exam.png') }}" alt="Logo CACES" class="h-12 w-auto"> 
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Validación de Reactivos por Lotes
                </h2>
                <p class="text-sm text-gray-500">Sube un archivo CSV para crear y validar múltiples preguntas de una sola vez.</p>
            </div>
        </div>
    </x-slot>

    {{-- ====================================================================== --}}
    {{-- ==                         CONTENIDO PRINCIPAL                      == --}}
    {{-- ====================================================================== --}}
    <div class="py-12">
        {{-- Contenedor principal que coincide con el dashboard y otras páginas --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Usamos un contenedor interno más estrecho para el formulario, mejorando la legibilidad --}}
            <div class="max-w-4xl mx-auto">
                
                <form action="{{ route('questions.batch.store') }}" method="POST" enctype="multipart/form-data" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    {{-- El formulario ahora es la tarjeta principal --}}
                    @csrf
                    
                    {{-- Contenido del formulario --}}
                    <div class="p-6 md:p-8 space-y-8">
                        
                        {{-- Mostrar errores de validación si los hay --}}
                        <x-validation-errors class="mb-4" />
                        @if (session('error'))
                            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                                <p class="font-bold">Error</p>
                                <p>{{ session('error') }}</p>
                            </div>
                        @endif

                        {{-- Sección de Instrucciones para el Usuario --}}
                        <div class="p-6 border rounded-lg bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Instrucciones para el Archivo</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                Por favor, asegúrate de que tu archivo esté en formato <strong class="font-semibold">CSV</strong> y codificado en **UTF-8**.
                                La primera fila debe ser la cabecera y contener exactamente las siguientes columnas (el orden no importa):
                            </p>
                            <div class="mt-3 bg-gray-200 p-2 rounded-md text-xs font-mono text-gray-700 overflow-x-auto">
                                enunciado,opcion1,opcion2,opcion3,opcion4,respuesta_correcta_idx,bibliografia,argumentacion1,argumentacion2,argumentacion3,argumentacion4
                            </div>
                             <p class="mt-3 text-sm text-gray-600">
                                La columna `respuesta_correcta_idx` debe ser un número del 0 al 3 (0 para la opción 1, 1 para la opción 2, etc.).
                            </p>
                        </div>

                        {{-- Campo para subir el archivo --}}
                        <div class="space-y-2">
                            <x-label for="reactivos_file" value="1. Seleccionar Archivo CSV" class="text-lg font-semibold"/>
                            <x-input id="reactivos_file" name="reactivos_file" type="file" class="block w-full border-gray-300 rounded-md file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required accept=".csv, text/csv" />
                            <p class="text-xs text-gray-500">Tamaño máximo del archivo: 10MB.</p>
                        </div>
                        
                        {{-- Campo para seleccionar el motor de IA --}}
                        <div class="space-y-2">
                            <x-label for="ai_engine" value="2. Motor de IA para la Validación" class="text-lg font-semibold"/>
                            <select name="ai_engine" id="ai_engine" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="chatgpt">ChatGPT (GPT-4)</option>
                                <option value="gemini">Gemini Pro</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Barra de Acciones en el pie de la tarjeta --}}
                    <div class="flex items-center justify-end space-x-4 px-8 py-4 bg-gray-50 border-t border-gray-200 sm:rounded-b-lg">
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Cancelar</a>
                        <x-button>
                            Subir y Procesar Archivo
                        </x-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>