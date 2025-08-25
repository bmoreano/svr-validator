<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Carga Masiva de Criterios</h2>
        </x-slot>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <p class="text-gray-600 mb-6">Sube un archivo CSV para añadir o actualizar en masa los criterios de validación. Si un criterio con el mismo texto ya existe, será actualizado.</p>
                    <x-validation-errors class="mb-4" />
                    <form action="{{ route('admin.criteria-upload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6 p-4 border rounded-lg">
                            <x-label for="criteria_file" value="Selecciona el archivo CSV de Criterios" class="font-bold text-lg" />
                            <input type="file" id="criteria_file" name="criteria_file" required accept=".csv,.txt" class="..." />
                            <p class="mt-2 text-xs text-gray-500">
                                <a href="{{ route('admin.criteria-upload.download.template') }}" class="text-indigo-600 hover:underline font-semibold">Descargar plantilla para criterios</a>
                            </p>
                        </div>
                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('dashboard') }}" class="...">Cancelar</a>
                            <x-button>Iniciar Carga de Criterios</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>