<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Carga Masiva de Preguntas (Reactivos)
        </h2>
    </x-slot>

    {{-- ======================================================================== --}}
    {{-- INICIALIZAMOS UN ÚNICO COMPONENTE ALPINE.JS EN EL DIV PRINCIPAL --}}
    {{-- ======================================================================== --}}
    <div class="py-12" x-data="csvUploader()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">

                <p class="text-gray-600 mb-4">Arrastra y suelta tu archivo CSV o haz clic en el área para seleccionarlo.
                    El sistema lo validará en tu navegador antes de enviarlo al servidor.</p>

                <x-validation-errors class="mb-4" />

                <form id="upload-form" action="{{ route('questions-upload.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Área de Drag-and-Drop y Selección de Archivo --}}
                    <div x-ref="dropArea" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleFileSelect($event)" @click="$refs.fileInput.click()"
                        class="flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-lg cursor-pointer transition-colors duration-200"
                        :class="isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-gray-50 hover:bg-gray-100'">

                        {{-- ÚNICO INPUT DE TIPO FILE (OCULTO) --}}
                        <input type="file" id="questions_file" name="questions_file"
                            @change="handleFileSelect($event)" accept=".csv,.txt" class="hidden" x-ref="fileInput">

                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48" aria-hidden="true">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m-4-4h2m-4 0h-2"
                                    stroke-width="2"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                <span class="font-medium text-indigo-600">Haz clic para seleccionar</span> o arrastra y
                                suelta
                            </p>
                            <p x-text="file ? file.name : 'Ningún archivo seleccionado'"
                                class="mt-1 text-sm text-gray-500"></p>
                            <p class="text-xs text-gray-500 mt-1">Sube un archivo `.csv` o `.txt` (Máx. 2MB)</p>
                        </div>
                    </div>

                    <p class="mt-2 text-xs text-gray-500">
                        Asegúrate de que tu archivo CSV tenga las cabeceras correctas.
                        <button type="button" @click.prevent="templateModalOpen = true"
                            class="text-indigo-600 hover:text-indigo-800 font-semibold underline">
                            Visualizar plantilla de ejemplo
                        </button>
                    </p>

                    <div class="flex items-center justify-between mt-8 border-t pt-5">
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Cancelar</a>
                        <button type="button" @click="validateAndSubmit()" :disabled="!file"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            Validar e Iniciar Carga
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ventana Modal para el Progreso y Errores (Ajustado) --}}
        <div x-show="uploadModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            style="display: none;">
            <div @click.away="uploadModalOpen = false" x-show="uploadModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto transform transition-all p-6 md:p-8 relative">

                <div class="px-6 py-4 border-b -mx-6 -mt-6 mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Proceso de Validación de Archivo</h3>
                </div>

                {{-- Contenido del progreso y errores --}}
                <div class="p-6 max-h-[60vh] overflow-y-auto -mx-6" x-html="progressHtml"></div>

                <div class="px-6 py-4 bg-gray-50 border-t -mx-6 -mb-6 mt-4 flex justify-end space-x-3">
                    <button type="button" x-show="errors.length > 0" @click="exportErrors()"
                        class="inline-flex justify-center rounded-md border border-transparent bg-yellow-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">Exportar
                        Errores</button>
                    <button type="button" @click="uploadModalOpen = false"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Cerrar</button>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- VENTANA MODAL PARA VISUALIZAR LA PLANTILLA (Ajustado) --}}
        {{-- ========================================================== --}}
        <div x-show="templateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">

            {{-- Panel de la modal --}}
            <div @click.away="templateModalOpen = false" x-show="templateModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" {{-- Ancho de la modal disminuido al 80% con sm:max-w-5xl y lg:max-w-5xl --}}
                class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full">

                {{-- Encabezado de la modal --}}
                <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Plantilla de Ejemplo para carga de archivo con varias
                        preguntas</h3>
                    <button @click="templateModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">&times;</button>
                </div>

                {{-- Cuerpo de la modal con el textarea --}}
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Este es el formato exacto que tu archivo CSV debe tener. Las cabeceras deben coincidir. Puedes
                        copiar este contenido y usarlo como base.
                    </p>

                    {{-- Lógica de Alpine.js para auto-ajustar la altura del textarea --}}
                    <div x-data="{
                        content: `{{ $templateContent }}`,
                        resize() {
                            const el = this.$refs.templateText;
                            el.style.height = '1px';
                            el.style.overflowY = 'hidden';
                            el.style.height = (el.scrollHeight + 5) + 'px';
                            el.style.overflowY = 'auto';
                        }
                    }" x-init="$nextTick(() => resize())">
                        <textarea x-ref="templateText" id="content" name="content" rows="15"
                            class="mt-1 block w-full font-mono text-sm border-gray-300 rounded-md shadow-sm" readonly>{{ old('content', $templateContent) }}
                        </textarea>
                    </div>
                </div>

                        <textarea x-ref="templateText" id="content" name="content" rows="10"
                            class="mt-1 block w-full font-mono text-sm border-gray-300 rounded-md shadow-sm" readonly>{{ old('content', $templateContent) }}
                        </textarea>
                {{-- Pie de la modal con botones de acción --}}
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end items-center space-x-4">
                    <button type="button" x-data="{ text: 'Copiar Contenido' }"
                        @click="navigator.clipboard.writeText($refs.templateText.value); text = '¡Copiado!'; setTimeout(() => text = 'Copiar Contenido', 2000)"
                        class="px-3 py-1 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        x-text="text"></button>
                    <button type="button" @click="templateModalOpen = false"
                        class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            function csvUploader() {
                return {
                    file: null,
                    isDragging: false,
                    uploadModalOpen: false,
                    templateModalOpen: false,
                    progressHtml: '',
                    errors: [],

                    handleFileSelect(event) {
                        const files = event.target.files || event.dataTransfer.files;
                        this.file = files.length ? files[0] : null;

                        // COMENTARIO: FIX para adjuntar el archivo al input oculto antes del envío del formulario
                        if (this.file) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(this.file);
                            this.$refs.fileInput.files = dataTransfer.files;
                        } else {
                            // Si no se selecciona ningún archivo (ej. el usuario cancela), limpiar el input
                            this.$refs.fileInput.files = new DataTransfer().files;
                        }
                    },

                    validateAndSubmit() {
                        // Ahora, esta comprobación se basa en si this.file tiene un valor
                        // que ya ha sido correctamente asignado al $refs.fileInput.files
                        if (!this.file) {
                            alert('Por favor, selecciona un archivo CSV.');
                            return;
                        }

                        this.uploadModalOpen = true;
                        this.progressHtml = '<p class="text-gray-700">Iniciando validación del archivo...</p>';
                        this.errors = [];

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const text = e.target.result;
                            const lines = text.split(/\r\n|\n/).filter(line => line.trim() !== '');

                            if (lines.length === 0) {
                                this.progressHtml = `<p class="text-red-600 font-bold">Error: El archivo está vacío.</p>`;
                                this.errors.push(['General', 'Archivo Vacío', 'No se encontraron datos en el archivo.']);
                                this.renderErrors();
                                return;
                            }

                            const header = lines[0] ? lines[0].trim() : '';
                            const expectedHeader =
                                "stem,bibliography,grado_dificultad,poder_discriminacion,opcion_1,argumentacion_1,opcion_2,argumentacion_2,opcion_3,argumentacion_3,opcion_4,argumentacion_4,respuesta_correcta";
                            if (header !== expectedHeader) {
                                this.progressHtml =
                                    `<p class="text-red-600 font-bold">Error: La cabecera del archivo es incorrecta.</p>`;
                                this.errors.push(['Línea 1', 'Cabecera Inválida',
                                    `Esperada: "${expectedHeader}" / Encontrada: "${header}"`
                                ]);
                            } else {
                                this.progressHtml = '<p class="text-green-600 font-semibold">✔️ Cabecera correcta.</p>';
                            }

                            if (this.errors.length === 0) {
                                for (let i = 1; i < lines.length; i++) {
                                    const line = lines[i];
                                    const fields = this.parseCsvLine(line);

                                    if (fields.length !== 13) {
                                        const truncatedLine = line.length > 100 ? line.substring(0, 100) + '...' : line;
                                        this.errors.push([`Línea ${i + 1}`,
                                            `Número de campos incorrecto (esperaba 13, encontró ${fields.length})`,
                                            truncatedLine
                                        ]);
                                        continue;
                                    }

                                    const respuestaCorrecta = parseInt(fields[12]);
                                    if (isNaN(respuestaCorrecta) || respuestaCorrecta < 1 || respuestaCorrecta > 4) {
                                        const truncatedLine = line.length > 100 ? line.substring(0, 100) + '...' : line;
                                        this.errors.push([`Línea ${i + 1}`,
                                            `Valor inválido para 'respuesta_correcta' (debe ser 1, 2, 3 o 4). Valor: '${fields[12]}'`,
                                            truncatedLine
                                        ]);
                                    }
                                }
                            }

                            this.renderErrors();
                        };
                        reader.readAsText(this.file);
                    },

                    renderErrors() {
                        if (this.errors.length > 0) {
                            this.progressHtml +=
                                `<p class="text-red-600 font-bold mt-4">${this.errors.length} errores encontrados. Por favor, corrígelos.</p>`;
                            let errorTable =
                                '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 mt-2"><thead class="bg-gray-50"><tr><th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Línea</th><th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error</th><th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contenido (Truncado)</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                            this.errors.forEach(error => {
                                const originalContent = error[2] || '';
                                const truncatedContent = originalContent.length > 100 ? originalContent.substring(0,
                                    97) + '...' : originalContent;
                                errorTable +=
                                    `<tr><td class="px-6 py-4 whitespace-nowrap">${error[0]}</td><td class="px-6 py-4 whitespace-nowrap text-red-500">${error[1]}</td><td class="px-6 py-4 font-mono text-gray-700 break-words">${truncatedContent}</td></tr>`;
                            });
                            errorTable += '</tbody></table></div>';
                            this.progressHtml += errorTable;
                        } else {
                            this.progressHtml +=
                                '<p class="text-green-600 font-bold mt-4">✔️ Archivo validado con éxito. Enviando al servidor...</p>';
                            setTimeout(() => {
                                document.getElementById('upload-form').submit();
                            }, 2000);
                        }
                    },

                    parseCsvLine(line) {
                        const fields = [];
                        let inQuote = false;
                        let currentField = '';
                        for (let i = 0; i < line.length; i++) {
                            const char = line[i];
                            if (char === '"') {
                                if (inQuote && line[i + 1] === '"') {
                                    currentField += '"';
                                    i++;
                                } else {
                                    inQuote = !inQuote;
                                }
                            } else if (char === ',' && !inQuote) {
                                fields.push(currentField.trim());
                                currentField = '';
                            } else {
                                currentField += char;
                            }
                        }
                        fields.push(currentField.trim());
                        return fields;
                    },

                    exportErrors() {
                        let csvContent = "data:text/csv;charset=utf-8,";
                        csvContent += "Linea,Error,Contenido\r\n";

                        this.errors.forEach(rowArray => {
                            const sanitizedRow = rowArray.map(item => `"${String(item).replace(/"/g, '""')}"`).join(
                                ",");
                            csvContent += sanitizedRow + "\r\n";
                        });

                        const encodedUri = encodeURI(csvContent);
                        const link = document.createElement("a");
                        link.setAttribute("href", encodedUri);
                        link.setAttribute("download", "errores_en_plantilla.csv");
                        document.body.appendChild(link);

                        link.click();
                        document.body.removeChild(link);
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
