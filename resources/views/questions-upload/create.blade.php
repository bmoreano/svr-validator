<div>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Carga Masiva de Preguntas (Reactivos)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <p class="text-gray-600 mb-6">Utiliza este formulario para subir un conjunto de preguntas desde un archivo plano CSV. Los datos serán procesados en segundo plano y añadidos a tu lista de preguntas como "borradores".</p>

                <x-validation-errors class="mb-4" />

                <form action="{{ route('questions-upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Campo de Carga de Archivo de PREGUNTAS --}}
                    <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                        <x-label for="questions_file" value="Selecciona el archivo CSV de Preguntas" class="font-bold text-lg" />
                        <p class="text-xs text-gray-500 mt-1 mb-2">Sube un archivo `.csv` o `.txt` para crear múltiples preguntas a la vez.</p>
                        <input type="file" id="questions_file" name="questions_file" required accept=".csv,.txt" 
                               class="mt-1 block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100"/>
                                      
                        <p class="mt-2 text-xs text-gray-500">
                            Asegúrate de que tu archivo CSV tenga las cabeceras correctas. 
                            <a href="{{ route('questions-upload.download.template') }}" class="text-indigo-600 hover:underline font-semibold">
                                Descargar plantilla de ejemplo
                            </a>
                        </p>
                    </div>

                    <div class="flex items-center justify-between mt-8 border-t pt-5">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            Cancelar
                        </a>
                        <x-button>
                            Iniciar Carga de Preguntas
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
</div>
