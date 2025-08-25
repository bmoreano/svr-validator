<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Herramienta de Limpieza de Texto</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8" x-data="{ inputType: 'text' }">
                <p class="text-gray-600 mb-6">Pega texto directamente o sube un archivo (.docx, .pdf) para eliminar caracteres no soportados y normalizar el formato. El resultado limpio aparecerá a la derecha.</p>
                
                {{-- Notificaciones y Errores --}}
                @if(session('error')) <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">{{ session('error') }}</div> @endif
                <x-validation-errors class="mb-4" />
                
                <form action="{{ route('tools.text-sanitizer.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Columna de Entrada --}}
                        <div>
                            <div class="flex border-b mb-4">
                                <button type="button" @click="inputType = 'text'" :class="{'border-b-2 border-indigo-500 text-indigo-600': inputType === 'text'}" class="px-4 py-2 text-sm font-medium">Pegar Texto</button>
                                <button type="button" @click="inputType = 'file'" :class="{'border-b-2 border-indigo-500 text-indigo-600': inputType === 'file'}" class="px-4 py-2 text-sm font-medium">Subir Archivo</button>
                            </div>
                            
                            {{-- Pestaña para Pegar Texto --}}
                            <div x-show="inputType === 'text'" x-transition>
                                <x-label for="input_text" value="Texto de Entrada" class="font-bold text-lg" />
                                <textarea id="input_text" name="input_text" rows="15" class="mt-2 block w-full ..." placeholder="Pega aquí el texto sucio...">{{ $inputText ?? old('input_text') }}</textarea>
                            </div>

                            {{-- Pestaña para Subir Archivo --}}
                            <div x-show="inputType === 'file'" style="display: none;" x-transition>
                                <x-label for="input_file" value="Archivo de Entrada (.docx, .pdf)" class="font-bold text-lg" />
                                <input type="file" id="input_file" name="input_file" accept=".docx,.pdf" 
                                       class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                <p class="mt-2 text-xs text-gray-500">Se extraerá todo el texto del documento para su limpieza.</p>
                            </div>
                        </div>

                        {{-- Columna de Salida --}}
                        <div>
                            <x-label for="cleaned_text" value="Texto Limpio de Salida" class="font-bold text-lg" />
                            <textarea id="cleaned_text" rows="15" class="mt-2 block w-full border-green-300 bg-green-50 ..." readonly>{{ $cleanedText ?? '' }}</textarea>
                            @if(isset($cleanedText))
                                <button type="button" x-data="{ text: 'Copiar al Portapapeles' }" 
                                        @click="navigator.clipboard.writeText($el.nextElementSibling.value); text = '¡Copiado!'; setTimeout(() => text = 'Copiar al Portapapeles', 2000)"
                                        class="mt-2 px-3 py-1 bg-gray-700 text-white text-xs font-semibold rounded-md hover:bg-gray-600"
                                        x-text="text"></button>
                                <textarea class="hidden">{{ $cleanedText ?? '' }}</textarea> {{-- Textarea oculto para el botón de copiar --}}
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 border-t pt-5">
                        <x-button>Limpiar Texto</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>