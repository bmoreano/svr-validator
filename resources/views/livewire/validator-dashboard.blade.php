<div wire:poll.15s>
    {{-- Interruptor de filtro (sin cambios) --}}
    <div class="flex justify-end items-center mb-4 px-4 py-2 bg-gray-50 rounded-lg border">
        @if($filterByMyCareer && !auth()->user()->career_id)
            <span class="mr-4 text-xs text-yellow-700 italic">
                El filtro "mi carrera" está activo, pero tu perfil no tiene una carrera asignada.
            </span>
        @endif
        <label for="careerFilter" class="flex items-center cursor-pointer">
            <span class="mr-3 text-sm font-medium text-gray-900">Mostrar solo mi carrera</span>
            <div class="relative">
                <input id="careerFilter" type="checkbox" class="sr-only" wire:model="filterByMyCareer">
                <div class="block bg-gray-200 w-14 h-8 rounded-full transition"></div>
                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform"></div>
            </div>
        </label>
        <style> input:checked ~ .dot { transform: translateX(100%); } input:checked ~ .block { background-color: #4f46e5; } </style>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        {{-- ========================================================== --}}
                        {{-- 1. ENCABEZADO DE COLUMNA MODIFICADO --}}
                        {{-- ========================================================== --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Autor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Carrera
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Última Act.
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acción
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($pendingQuestions as $question)
                        <tr class="hover:bg-gray-50">
                            {{-- ========================================================== --}}
                            {{-- 2. CONTENIDO DE CELDA MODIFICADO --}}
                            {{-- ========================================================== --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono" title="{{ $question->code }}">
                                    {{ Str::limit($question->code, 25) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $question->author->name }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $question->career->name ?? 'N/A' }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst(str_replace('_', ' ', $question->status)) }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $question->updated_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('validations.review', $question) }}" class="inline-flex items-center p-2 bg-indigo-100 text-indigo-600 rounded-full hover:bg-indigo-200 transition" title="Revisar Pregunta">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">¡Excelente trabajo! No hay preguntas pendientes de revisión.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pendingQuestions->hasPages())
            <div class="p-4 bg-gray-50 border-t">
                {{ $pendingQuestions->links() }}
            </div>
        @endif
    </div>
</div>