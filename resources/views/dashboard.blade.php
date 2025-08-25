<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de Control
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notificaciones Flash --}}
            @if(session('status'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('status') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            {{-- ========================================================== --}}
            {{-- VISTA PARA EL ROL AUTOR --}}
            {{-- ========================================================== --}}
            @if(auth()->user()->role === 'autor')
                <div class="space-y-8">
                    <!-- KPIs y Gráfico -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold">{{ $kpis['total'] }}</div><div class="text-sm text-gray-500">Total Creadas</div></div>
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold text-gray-800">{{ $kpis['borrador'] }}</div><div class="text-sm text-gray-500">Borradores</div></div>
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold text-yellow-600">{{ $kpis['en_revision'] }}</div><div class="text-sm text-gray-500">En Revisión</div></div>
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold text-green-600">{{ $kpis['aprobadas'] }}</div><div class="text-sm text-gray-500">Aprobadas</div></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-lg font-semibold text-center">Distribución por Estado</h3><canvas id="statusPieChart"></canvas></div>
                    </div>
                    <!-- Feedback y Historial -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-lg font-semibold mb-4">Mis 5 Criterios a Mejorar</h3><div class="divide-y divide-gray-200">@forelse($topDisagreeingCriteria as $item)<div class="py-3"><div class="flex justify-between items-center"><span class="text-sm text-gray-700">{{ $item->criterion->text }}</span><span class="text-sm font-bold text-indigo-600">{{ $item->total }} correc.</span></div></div>@empty<p class="text-gray-500 italic">No hay correcciones a tus preguntas aún.</p>@endforelse</div></div>
                        <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-lg font-semibold mb-4">Preguntas que Requieren tu Atención</h3><div class="divide-y divide-gray-200">@forelse($actionableQuestions as $question)<div class="py-3 flex justify-between items-center"><div><p class="text-sm font-semibold text-gray-800">{{ $question->code }}</p><p class="text-xs text-gray-500">{{ $question->revision_feedback ?? 'Rechazado permanentemente.' }}</p></div><a href="{{ route('questions.edit', $question) }}" class="px-3 py-1 bg-orange-500 text-white text-xs rounded-md">Corregir</a></div>@empty<p class="text-gray-500 italic">No tienes preguntas pendientes de corrección.</p>@endforelse</div></div>
                    </div>
                </div>

            {{-- ========================================================== --}}
            {{-- VISTA PARA OTROS ROLES (ADMIN, VALIDADOR, ETC.) --}}
            {{-- ========================================================== --}}
@else
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <h1 class="text-2xl font-medium text-gray-900">Hola, {{ Auth::user()->name }}</h1>
                    <p class="mt-2 text-gray-600">Tu rol es: <span class="font-bold">{{ ucfirst(auth()->user()->role) }}</span>. Selecciona una acción:</p>
                    
                    <div class="space-y-10 mt-8">
                        <!-- SECCIÓN: GESTIÓN DE CONTENIDO -->
                        <section class="bg-blue-50 p-6 rounded-lg shadow-inner">
                            <h2 class="text-xs font-bold uppercase text-blue-500 tracking-wider mb-4">Gestión de Contenido</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @if(in_array(auth()->user()->role, ['autor', 'administrador']))
                                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-blue-900">Gestionar Preguntas</h3>
                                        <p class="text-sm text-blue-800 mt-1">Crea, edita y envía tus reactivos a validación.</p>
                                        <div class="mt-4 pt-3 border-t border-blue-100">
                                            <a href="{{ route('questions.index') }}" class="font-semibold text-blue-600 hover:text-blue-800 text-sm">Ir a Mis Preguntas &rarr;</a>
                                        </div>
                                    </div>
                                @endif
                                @if(in_array(auth()->user()->role, ['validador', 'administrador']))
                                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-blue-900">Revisión de Preguntas</h3>
                                        <p class="text-sm text-blue-800 mt-1">Accede a la cola de preguntas para la revisión humana final.</p>
                                        <div class="mt-4 pt-3 border-t border-blue-100">
                                            <a href="{{ route('validations.index') }}" class="font-semibold text-blue-600 hover:text-blue-800 text-sm">Ver Preguntas a Validar &rarr;</a>
                                        </div>
                                    </div>
                                @endif
                                @if(in_array(auth()->user()->role, ['autor', 'administrador']))
                                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-blue-900">Carga Masiva de Preguntas</h3>
                                        <p class="text-sm text-blue-800 mt-1">Sube un conjunto de reactivos desde un archivo CSV.</p>
                                        <div class="mt-4 pt-3 border-t border-blue-100">
                                            <a href="{{ route('questions-upload.create') }}" class="font-semibold text-blue-600 hover:text-blue-800 text-sm">Cargar Preguntas &rarr;</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <!-- SECCIÓN: INGENIERÍA DE PROMPTS -->
                        <section class="bg-teal-50 p-6 rounded-lg shadow-inner">
                            <h2 class="text-xs font-bold uppercase text-teal-500 tracking-wider mb-4">Ingeniería de Prompts</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-white p-4 rounded-lg border border-teal-200 shadow hover:shadow-md transition-shadow duration-300">
                                    <h3 class="font-bold text-lg text-teal-900">Proponer Prompts</h3>
                                    <p class="text-sm text-teal-800 mt-1">Crea y solicita la revisión de nuevos prompts para el sistema.</p>
                                    <div class="mt-4 pt-3 border-t border-teal-100">
                                        <a href="{{ route('prompts.propose') }}" class="font-semibold text-teal-600 hover:text-teal-800 text-sm">Proponer un Prompt &rarr;</a>
                                    </div>
                                </div>
                                @if(auth()->user()->role === 'administrador')
                                    <div class="bg-white p-4 rounded-lg border border-teal-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-teal-900">Administrar Prompts</h3>
                                        <p class="text-sm text-teal-800 mt-1">Revisa, aprueba y gestiona los prompts del sistema.</p>
                                        <div class="mt-4 pt-3 border-t border-teal-100">
                                            <a href="{{ route('admin.prompts.index') }}" class="font-semibold text-teal-600 hover:text-teal-800 text-sm">Administrar Prompts &rarr;</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <!-- SECCIÓN: ADMINISTRACIÓN DEL SISTEMA -->
                        @if(auth()->user()->role === 'administrador')
                            <section class="bg-gray-100 p-6 rounded-lg shadow-inner">
                                 <h2 class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-4">Administración del Sistema</h2>
                                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Gestionar Usuarios</h3><p class="text-sm text-gray-700 mt-1">Gestiona los roles y permisos de las cuentas.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="{{ route('admin.users.index') }}" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Gestionar Usuarios &rarr;</a></div></div>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Gestionar Carreras</h3><p class="text-sm text-gray-700 mt-1">Define y administra los programas académicos.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="{{ route('admin.careers.index') }}" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Gestionar Carreras &rarr;</a></div></div>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Cargar Criterios</h3><p class="text-sm text-gray-700 mt-1">Añade o actualiza en masa los criterios de validación.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="{{ route('admin.criteria-upload.create') }}" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Cargar Criterios &rarr;</a></div></div>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Analíticas</h3><p class="text-sm text-gray-700 mt-1">Visualiza reportes y métricas de rendimiento.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="{{ route('admin.analytics.index') }}" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Ver Analíticas &rarr;</a></div></div>
                                 </div>
                            </section>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        @if(auth()->user()->role === 'autor')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const statusData = @json($statusDistribution ?? []);
                    const statusChartCtx = document.getElementById('statusPieChart');
                    if (statusChartCtx && Object.keys(statusData).length > 0) {
                        new Chart(statusChartCtx, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(statusData).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                                datasets: [{ 
                                    data: Object.values(statusData),
                                    backgroundColor: ['#6B7280', '#F59E0B', '#10B981', '#EF4444', '#3B82F6'],
                                }]
                            },
                            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                        });
                    }
                });
            </script>
        @endif
    @endpush
</x-app-layout>