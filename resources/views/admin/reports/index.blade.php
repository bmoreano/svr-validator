<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard de Reportería
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- KPIs Generales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-500 uppercase text-sm">Total Preguntas</h3>
                        <p class="text-3xl font-bold">{{ $kpis['total_questions'] }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-500 uppercase text-sm">Total Autores</h3>
                        <p class="text-3xl font-bold">{{ $kpis['total_authors'] }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-500 uppercase text-sm">Total Validadores</h3>
                        <p class="text-3xl font-bold">{{ $kpis['total_validators'] }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-gray-500 uppercase text-sm">Preguntas Aprobadas</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $kpis['approved_questions'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Gráfico de Distribución por Estado -->
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold mb-4">Distribución Actual por Estado</h3><canvas
                            id="statusChart"></canvas>
                    </div>
                    <!-- Gráfico de Tasa de Desacuerdo -->
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h3 class="font-semibold mb-4">Tasa de Desacuerdo IA vs. Humano (%)</h3><canvas
                            id="disagreementChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Flujo de Preguntas -->
                <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
                    <h3 class="font-semibold mb-4">Flujo de Preguntas (Últimos 30 días)</h3><canvas
                        id="flowChart"></canvas>
                </div>

                <!-- Tabla de Criterios Problemáticos -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="font-semibold mb-4">Top 5 Criterios con Mayor Desacuerdo</h3>
                    <div class="divide-y divide-gray-200">
                        @forelse($topDisagreeingCriteria as $item)
                            <div class="py-3 flex justify-between items-center"><span
                                    class="text-sm text-gray-700">{{ $item->criterion->text }}</span><span
                                class="text-lg font-bold text-indigo-600">{{ $item->total }}</span></div>@empty<p>
                                No hay datos.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Data from controller
                const statusData = @json($statusDistribution);
                const flowData = @json($questionFlow);
                const disagreementData = @json($disagreementRate);

                // Status Chart (Pie)
                new Chart(document.getElementById('statusChart'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(statusData).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, l => l
                        .toUpperCase())),
                        datasets: [{
                            data: Object.values(statusData)
                        }]
                    }
                });

                // Disagreement Chart (Bar)
                new Chart(document.getElementById('disagreementChart'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(disagreementData),
                        datasets: [{
                            label: 'Tasa de Desacuerdo (%)',
                            data: Object.values(disagreementData),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)'
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });

                // Flow Chart (Line)
                new Chart(document.getElementById('flowChart'), {
                    type: 'line',
                    data: {
                        labels: flowData.map(row => new Date(row.date).toLocaleDateString('es-ES', {
                            day: '2-digit',
                            month: 'short'
                        })),
                        datasets: [{
                                label: 'Creadas',
                                data: flowData.map(row => row.created),
                                borderColor: 'rgba(59, 130, 246, 1)',
                                tension: 0.1
                            },
                            {
                                label: 'Aprobadas',
                                data: flowData.map(row => row.approved),
                                borderColor: 'rgba(16, 185, 129, 1)',
                                tension: 0.1
                            }
                        ]
                    }
                });
            </script>
        @endpush
    </x-app-layout>
</div>
