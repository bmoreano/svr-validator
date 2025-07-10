<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle de la Pregunta #{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <!-- SECCIÓN DE METADATOS -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-6 border-b border-gray-200">
                        <div>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Autor</h3>
                            <p class="mt-1 text-gray-900">{{ $question->author->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha de Creación</h3>
                            <p class="mt-1 text-gray-900">{{ $question->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Estado Actual</h3>
                            <span class="mt-1 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @switch($question->status)
                                    @case('aprobado') bg-green-100 text-green-800 @break
                                    @case('borrador') bg-gray-200 text-gray-800 @break
                                    @case('necesita_correccion') bg-red-100 text-red-800 @break
                                    @default bg-yellow-100 text-yellow-800
                                @endswitch">
                                {{ ucfirst(str_replace('_', ' ', $question->status)) }}
                            </span>
                        </div>
                    </div>

                    <!-- SECCIÓN DE CONTENIDO PRINCIPAL -->
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Enunciado</h3>
                            <div class="mt-2 text-gray-700 bg-gray-50 p-4 rounded-md prose max-w-none">{!! nl2br(e($question->stem)) !!}</div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Opciones y Argumentación</h3>
                            <div class="mt-2 space-y-4">
                                @foreach($question->options as $option)
                                    <div class="p-4 rounded-md border {{ $option->is_correct ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-200' }}">
                                        <p class="text-gray-900 font-semibold">
                                            {{ $option->option_text }}
                                            @if($option->is_correct)
                                                <span class="ml-2 text-xs font-bold text-green-700">(Respuesta Correcta)</span>
                                            @endif
                                        </p>
                                        <div class="mt-2 pt-2 border-t border-dashed {{ $option->is_correct ? 'border-green-200' : 'border-gray-300' }}">
                                            <p class="text-sm text-gray-600">
                                                <strong class="font-medium text-gray-800">Argumentación:</strong> 
                                                @if($option->argumentation)
                                                    {!! nl2br(e($option->argumentation)) !!}
                                                @else
                                                    <span class="italic text-gray-400">No se proporcionó argumentación.</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($question->bibliography)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Bibliografía</h3>
                                <div class="mt-2 text-gray-700 bg-gray-50 p-4 rounded-md prose max-w-none">{!! nl2br(e($question->bibliography)) !!}</div>
                            </div>
                        @endif
                    </div>
                    
                    @can('update', $question)
                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-6">
                            <a href="{{ route('questions.edit', $question) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Editar Pregunta</a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>