<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Panel de Analíticas y Reportería
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    {{-- ========================================================== --}}
                    {{-- GRÁFICO: DESACUERDOS POR MOTOR DE IA --}}
                    {{-- ========================================================== --}}
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Precisión de Motores de IA (Menos
                            desacuerdos es mejor)</h3>
                        {{-- El canvas donde se renderizará el gráfico --}}
                        <canvas id="disagreementsByEngineChart"></canvas>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- TABLA: TOP 5 CRITERIOS PROBLEMÁTICOS --}}
                    {{-- ========================================================== --}}
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Criterios con Mayor Desacuerdo</h3>
                        <div class="divide-y divide-gray-200">
                            @forelse($topDisagreeingCriteria as $item)
                                <div class="py-3 flex justify-between items-center">
                                    <span class="text-sm text-gray-700 truncate" title="{{ $item->criterion->text }}">
                                        {{ Str::limit($item->criterion->text, 50) }}
                                    </span>
                                    <span class="text-lg font-bold text-indigo-600">{{ $item->total }}</span>
                                </div>
                            @empty
                                <p class="text-gray-500 italic">No hay suficientes datos de desacuerdos para mostrar.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- TABLA: VALIDadores con más desacuerdos --}}
                    {{-- ========================================================== --}}
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ranking de Desacuerdos por Validador</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Validador</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Email</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                            Total de Desacuerdos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($disagreementsByUser as $item)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->humanValidator->name }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->humanValidator->email }}</td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                                {{ $item->total }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-gray-500 italic">No hay
                                                suficientes datos para mostrar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- SCRIPTS PARA LOS GRÁFICOS --}}
        {{-- ========================================================== --}}
        @push('scripts')
            {{-- Incluimos Chart.js desde una CDN --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // --- Gráfico de Desacuerdos por Motor ---
                    const engineData = @json($disagreementsByEngine);
                    const engineCtx = document.getElementById('disagreementsByEngineChart');

                    if (engineCtx && Object.keys(engineData).length > 0) {
                        new Chart(engineCtx, {
                            type: 'doughnut', // Gráfico de dona (o 'pie' para circular)
                            data: {
                                labels: Object.keys(engineData),
                                datasets: [{
                                    label: 'Nº de Desacuerdos',
                                    data: Object.values(engineData),
                                    backgroundColor: [
                                        'rgba(79, 70, 229, 0.7)', // Indigo
                                        'rgba(2, 132, 199, 0.7)', // Sky
                                        'rgba(217, 70, 239, 0.7)', // Fuchsia
                                    ],
                                    borderColor: [
                                        'rgba(79, 70, 229, 1)',
                                        'rgba(2, 132, 199, 1)',
                                        'rgba(217, 70, 239, 1)',
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    title: {
                                        display: false
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
        @endpush
    </x-app-layout>
</div>
