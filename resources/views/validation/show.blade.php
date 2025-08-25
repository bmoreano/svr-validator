<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Validación de Pregunta #{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Columna de la Pregunta (Solo Lectura) -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                     <h3 class="text-lg font-semibold text-gray-800">Detalles del Reactivo</h3>
                     {{-- Aquí se reutiliza la vista 'show' para no duplicar código --}}
                     @include('questions.partials.show-content', ['question' => $question])
                </div>
            </div>

            <!-- Columna del Formulario de Validación -->
            <div class="lg:col-span-1">
                 <form action="{{ route('validations.store', $question) }}" method="POST" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 space-y-6 sticky top-8">
                    @csrf
                    <h3 class="text-xl font-bold text-gray-900">Checklist de Validación</h3>
                     
                    @if($aiValidation)
                        <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-3 text-sm" role="alert">
                            <p><span class="font-bold">Asistente IA:</span> Los campos han sido pre-llenados por la IA. Por favor, revísalos y ajústalos.</p>
                        </div>
                    @endif

                    @foreach ($criteria->groupBy('category') as $category => $items)
                        <div class="space-y-4">
                            <h4 class="text-md font-semibold text-gray-700 capitalize border-b pb-1">{{ $category }}</h4>
                            @foreach ($items as $criterion)
                                @php
                                    $aiResponse = $aiValidation ? $aiValidation->responses->firstWhere('criterion_id', $criterion->id) : null;
                                @endphp
                                <x-validation.criterion-row :criterion="$criterion" :aiResponse="$aiResponse" />
                            @endforeach
                        </div>
                    @endforeach
                     
                    <div class="mt-8 border-t pt-6">
                        <label for="final_status" class="block font-medium text-lg text-gray-800">Decisión Final</label>
                        <p class="text-sm text-gray-500 mb-2">Selecciona el estado final para esta pregunta.</p>
                        
                        <select name="final_status" id="final_status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="" disabled selected>-- Elige una opción --</option>
                            <option value="aprobado">Aprobar Pregunta</option>
                            <option value="necesita_correccion">Enviar a Corrección</option>
                            <option value="rechazado" class="text-red-700 font-semibold">Rechazar Pregunta (Decisión Final)</option>
                        </select>
                         <x-input-error for="final_status" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700">
                            Enviar Validación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>