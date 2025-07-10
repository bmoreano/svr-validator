<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex items-center">
                        <img src="{{ asset('images/caces-logo.png') }}" alt="Logo CACES" class="h-12 w-auto mr-4">
                        <h1 class="text-2xl font-medium text-gray-900">
                            ¡Bienvenido a tu Sistema de Validación de Reactivos, {{ Auth::user()->name }}!
                        </h1>
                    </div>
                    <p class="mt-4 text-gray-600 leading-relaxed">
                        Desde aquí puedes gestionar tus preguntas, realizar validaciones o administrar el sistema según tu rol de <span class="font-semibold">{{ ucfirst(Auth::user()->role) }}</span>.
                    </p>
                </div>

                <div class="bg-gray-200 bg-opacity-25 p-6 lg:p-8 space-y-8">

                    {{-- === SECCIÓN PARA AUTOR === --}}
                    @if($autorData)
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Panel de Autor</h3>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6 mb-8">
                                <a href="{{ route('questions.index') }}" class="block bg-blue-50 p-4 rounded-lg text-center border border-blue-200 hover:shadow-lg transition">
                                    <div class="text-3xl font-bold text-blue-800">{{ $autorData['stats']['total'] }}</div>
                                    <div class="text-sm font-medium text-blue-600">Total Preguntas</div>
                                </a>
                                <a href="{{ route('questions.index', ['status' => 'en_revision']) }}" class="block bg-yellow-50 p-4 rounded-lg text-center border border-yellow-200 hover:shadow-lg transition">
                                    <div class="text-3xl font-bold text-yellow-800">{{ $autorData['stats']['en_revision'] }}</div>
                                    <div class="text-sm font-medium text-yellow-600">En Revisión</div>
                                </a>
                                <a href="{{ route('questions.index', ['status' => 'aprobado']) }}" class="block bg-green-50 p-4 rounded-lg text-center border border-green-200 hover:shadow-lg transition">
                                    <div class="text-3xl font-bold text-green-800">{{ $autorData['stats']['aprobadas'] }}</div>
                                    <div class="text-sm font-medium text-green-600">Aprobadas</div>
                                </a>
                                <a href="{{ route('questions.index', ['status' => 'rechazado']) }}" class="block bg-red-50 p-4 rounded-lg text-center border border-red-200 hover:shadow-lg transition">
                                    <div class="text-3xl font-bold text-red-800">{{ $autorData['stats']['rechazadas'] }}</div>
                                    <div class="text-sm font-medium text-red-600">Rechazadas</div>
                                </a>
                                <a href="{{ route('questions.index', ['status' => 'borrador']) }}" class="block bg-gray-100 p-4 rounded-lg text-center border border-gray-200 hover:shadow-lg transition">
                                    <div class="text-3xl font-bold text-gray-800">{{ $autorData['stats']['borradores'] ?? 0 }}</div>
                                    <div class="text-sm font-medium text-gray-600">Borradores</div>
                                </a>
                            </div>
                             
                            <h4 class="text-lg font-medium text-gray-700 mb-2">Tus Últimas Preguntas</h4>
                            <div class="bg-white border rounded-lg">
                                @forelse($autorData['questions'] as $question)
                                    <div class="flex justify-between items-center px-4 py-3 border-b last:border-b-0">
                                        <p class="text-gray-800 truncate">{{ Str::limit($question->stem, 80) }}</p>
                                        <a href="{{ route('questions.show', $question) }}" class="text-indigo-600 hover:underline text-sm font-semibold">Ver detalles</a>
                                    </div>
                                @empty
                                    <div class="px-4 py-3 text-center text-gray-500">
                                        Aún no has creado ninguna pregunta. <a href="{{ route('questions.create') }}" class="text-indigo-600 hover:underline font-semibold">¡Crea una ahora!</a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- === SECCIÓN PARA VALIDADOR === --}}
                    @if($validadorData)
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Panel de Validador</h3>
                             <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                                <div class="bg-purple-50 p-4 rounded-lg text-center border border-purple-200 md:col-span-1">
                                    <div class="text-3xl font-bold text-purple-800">{{ $validadorData['stats']['total_validations'] }}</div>
                                    <div class="text-sm font-medium text-purple-600">Validaciones Realizadas</div>
                                </div>
                            </div>
                            <h4 class="text-lg font-medium text-gray-700">Preguntas Pendientes de Revisión</h4>
                            <div class="mt-2 bg-white border rounded-lg">
                                @forelse($validadorData['pending_validation'] as $question)
                                    <div class="flex justify-between items-center px-4 py-3 border-b last:border-b-0">
                                        <div>
                                            <p class="text-gray-800 truncate" style="max-width: 60ch;">{{ $question->stem }}</p>
                                            <p class="text-xs text-gray-500">Autor: {{ $question->author->name }}</p>
                                        </div>
                                        <a href="{{ route('validations.show', $question) }}" class="flex-shrink-0 ml-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Revisar</a>
                                    </div>
                                @empty
                                    <div class="px-4 py-3 text-center text-gray-500"><p>¡Buen trabajo! No hay preguntas pendientes de revisión.</p></div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- === SECCIÓN PARA ADMINISTRADOR === --}}
                    @if($adminData)
                         <div class="p-6 bg-white rounded-lg shadow-md">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Panel de Administración Global</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-700 p-6 rounded-lg text-white">
                                    <div class="text-4xl font-bold">{{ $adminData['global_stats']['total_questions'] }}</div>
                                    <div class="text-lg mt-1">Total de Preguntas en el Sistema</div>
                                </div>
                                <div class="bg-gray-700 p-6 rounded-lg text-white">
                                    <div class="text-4xl font-bold">{{ $adminData['global_stats']['total_users'] }}</div>
                                    <div class="text-lg mt-1">Total de Usuarios Registrados</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>