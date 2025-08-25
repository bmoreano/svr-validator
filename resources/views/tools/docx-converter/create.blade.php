<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Herramienta de Conversión: Word (.docx) a Texto Plano (.txt)
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <p class="text-gray-600 mb-6">Sube un archivo de Microsoft Word (.docx) para convertir todo su
                        contenido a un archivo de texto plano (.txt), que luego podrás usar para copiar y pegar en los
                        formularios de creación de preguntas o prompts.</p>

                    <x-validation-errors class="mb-4" />

                    <form action="{{ route('tools.docx-converter.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <x-label for="docx_file" value="Selecciona el archivo .docx a convertir"
                                class="font-bold text-lg" />
                            <input type="file" id="docx_file" name="docx_file" required accept=".docx"
                                class="mt-2 block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100" />
                            <p class="mt-2 text-xs text-gray-500">
                                Límite de tamaño: 5MB. El sistema extraerá solo el texto del documento.
                            </p>
                        </div>

                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md ...">
                                Cancelar
                            </a>
                            <x-button>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h5M5 5l14 14M19 4h5v5"></path>
                                </svg>
                                Convertir y Descargar
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>
