<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Carga Masiva de Datos</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <p class="text-gray-600 mb-6">Utiliza este formulario para subir un conjunto de preguntas y/o criterios de validación desde archivos CSV. Los campos son opcionales, puedes subir uno, otro, o ambos a la vez.</p>

                <x-validation-errors class="mb-4" />

                <form action="{{ route('bulk-upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Campo de Carga de Archivo de PREGUNTAS --}}
                    <div class="mb-6 p-4 border rounded-lg">
                        <x-label for="questions_file" value="1. Cargar Conjunto de Preguntas (Reactivos)" class="font-bold text-lg" />
                        <p class="text-xs text-gray-500 mt-1 mb-2">Sube un archivo `.csv` para crear múltiples preguntas a la vez.</p>
                        <input type="file" id="questions_file" name="questions_file" accept=".csv,.txt" class="..."/>
                        <p class="mt-2 text-xs text-gray-500">
                            Asegúrate de que tu archivo CSV tenga las cabeceras correctas. 
                            {{-- ENLACE CORREGIDO --}}
                            <a href="{{ route('bulk-upload.download.questions') }}" class="text-indigo-600 hover:underline font-semibold">Descargar plantilla para preguntas</a>
                        </p>
                    </div>

                    {{-- Campo de Carga de Archivo de CRITERIOS (Solo para Admin) --}}
                    @if(Auth::user()->role === 'administrador')
                        <div class="mb-6 p-4 border rounded-lg">
                            <x-label for="criteria_file" value="2. Cargar/Actualizar Criterios de Validación" class="font-bold text-lg" />
                            <p class="text-xs text-gray-500 mt-1 mb-2">Sube un archivo `.csv` para añadir o actualizar los criterios.</p>
                            <input type="file" id="criteria_file" name="criteria_file" accept=".csv,.txt" class="..."/>
                             <p class="mt-2 text-xs text-gray-500">
                                {{-- ENLACE CORREGIDO --}}
                                <a href="{{ route('bulk-upload.download.criteria') }}" class="text-indigo-600 hover:underline font-semibold">Descargar plantilla para criterios</a>
                            </p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between mt-8 border-t pt-5">
                        <a href="{{ route('dashboard') }}" class="... ">Cancelar</a>
                        <x-button>Iniciar Carga</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>