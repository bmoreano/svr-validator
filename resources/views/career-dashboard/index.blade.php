<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard de Carrera: {{ auth()->user()->career->name ?? 'Sin Asignar' }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- SECCIÓN DE INVENTARIO -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="text-lg font-semibold">Distribución por Dificultad</h3>
                        <canvas id="difficultyChart"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="text-lg font-semibold">Balance por Tema</h3>
                        @if ($topicDistribution->isNotEmpty())
                            <canvas id="topicChart"></canvas>
                        @else
                            <p class="text-gray-500 italic mt-4">No hay suficientes datos de temas para mostrar el
                                gráfico.</p>
                        @endif
                    </div>
                </div>

                <!-- SECCIÓN DE RENDIMIENTO DE AUTORES -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-semibold">Rendimiento de Autores en la Carrera</h3>
                    <table class="min-w-full divide-y divide-gray-200 mt-4">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left ...">Nombre del Autor</th>
                                <th class="px-4 py-2 text-center ...">Preguntas Creadas</th>
                                <th class="px-4 py-2 text-center ...">Tasa de Aprobación</th>
                                <th class="px-4 py-2 text-center ...">Tasa de Rechazo Permanente</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($authorPerformance as $author)
                                <tr>
                                    <td class="px-4 py-3 ...">{{ $author->name }}</td>
                                    <td class="px-4 py-3 text-center ...">{{ $author->questions_created }}</td>
                                    <td class="px-4 py-3 text-center ...">{{ $author->approval_rate }}%</td>
                                    <td class="px-4 py-3 text-center ...">{{ $author->rejection_rate }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Gráfico de Dificultad
                const difficultyData = @json($difficultyDistribution);
                new Chart(document.getElementById('difficultyChart'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(difficultyData).map(s => s.replace('_', ' ')),
                        datasets: [{
                            data: Object.values(difficultyData)
                        }]
                    }
                });

                // Gráfico de Temas
                const topicData = @json($topicDistribution);
                if (Object.keys(topicData).length > 0) {
                    new Chart(document.getElementById('topicChart'), {
                        type: 'radar',
                        data: {
                            labels: Object.keys(topicData),
                            datasets: [{
                                label: 'Nº de Preguntas',
                                data: Object.values(topicData)
                            }]
                        },
                        options: {
                            scales: {
                                r: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            </script>
        @endpush
    </x-app-layout>
</div>
