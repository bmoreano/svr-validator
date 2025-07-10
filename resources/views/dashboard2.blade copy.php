<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                {{-- <x-welcome/> --}}
                <x-app-layout>
                    <div class="py-12">
                        <div class="max-w-7xl mx-auto w-20 h:f sm:px-6 lg:px-8">
                            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                                {{-- Mensaje de Bienvenida Genérico --}}
                                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                                    <h1 class="mt-4 text-2xl font-medium text-gray-900">
                                        ¡Bienvenido a tu Sistema de Validación de Reactivos, !
                                    </h1>
                                    <p class="mt-4 text-gray-500 leading-relaxed">
                                        Desde aquí puedes gestionar tus preguntas, realizar validaciones o administrar
                                        el sistema según tu rol. {{ Auth::user()->role }}
                                    </p>
                                </div>

                                {{-- Contenido Específico por Rol --}}
                                <div
                                    class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">

                                    {{-- === SECCIÓN PARA AUTOR === --}}
                                    @if (Auth::user()->role === 'autor' || Auth::user()->role === 'administrador')
                                        <div class="md:col-span-2">
                                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Panel de Autor</h3>
                                            <!-- Estadísticas del Autor -->
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                                <div class="bg-blue-100 p-4 rounded-lg text-center">
                                                    <div class="text-3xl font-bold text-blue-800">
                                                        {{ $stats['total'] ?? 0 }}</div>
                                                    <div class="text-sm text-blue-600">Total Preguntas</div>
                                                </div>
                                                <div class="bg-yellow-100 p-4 rounded-lg text-center">
                                                    <div class="text-3xl font-bold text-yellow-800">
                                                        {{ $stats['en_revision'] ?? 0 }}</div>
                                                    <div class="text-sm text-yellow-600">En Revisión</div>
                                                </div>
                                                <div class="bg-green-100 p-4 rounded-lg text-center">
                                                    <div class="text-3xl font-bold text-green-800">
                                                        {{ $stats['aprobadas'] ?? 0 }}</div>
                                                    <div class="text-sm text-green-600">Aprobadas</div>
                                                </div>
                                                <div class="bg-gray-100 p-4 rounded-lg text-center">
                                                    <div class="text-3xl font-bold text-gray-800">
                                                        {{ $stats['borradores'] ?? 0 }}</div>
                                                    <div class="text-sm text-gray-600">Borradores</div>
                                                </div>
                                            </div>
                                            <!-- Lista de Últimas Preguntas -->
                                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Tus Últimas Preguntas
                                            </h4>
                                            <div class="bg-white p-4 rounded-lg shadow">
                                                @forelse($questions as $question)
                                                    <div class="flex jutify-between items-center py-2 border-b">
                                                        <p class="truncate">{{ Str::limit($question->stem, 80) }}</p>
                                                        <span
                                                            class="text-xs font-semibold px-2 py-1 rounded-full
                                                            @switch($question->status)
                                                                @case('aprobado') bg-green-200 text-green-800 @break
                                                                @case('borrador') bg-gray-200 text-gray-800 @break
                                                                @case('necesita_correccion') bg-red-200 text-red-800 @break
                                                                @default bg-yellow-200 text-yellow-800
                                                            @endswitch
                                                        ">
                                                            {{ ucfirst($question->status) }}</span>
                                                        <a href="{{ route('questions.edit', $question) }}"
                                                            class="ml-4 px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Editar</a>
                                                    </div>
                                                @empty
                                                    <p class="text-gray-500">Aún no has creado ninguna pregunta. <a
                                                            href="{{ route('questions.create') }}"
                                                            class="text-indigo-600 hover:underline">¡Crea una ahora!</a>
                                                    </p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endif

                                    {{-- === SECCIÓN PARA VALIDADOR === --}}
                                    @if (Auth::user()->role === 'validador' || Auth::user()->role === 'administrador')
                                        <div class="md:col-span-2">
                                            <h3 class="text-xl font-semibold text-gray-800 my-4">Panel de Validador</h3>
                                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Preguntas Pendientes de
                                                Revisión</h4>
                                            <div class="bg-white p-4 rounded-lg shadow">
                                                @forelse($pendingValidation as $question)
                                                    <div class="flex justify-between items-center py-2 border-b">
                                                        <p class="truncate">{{ Str::limit($question->stem, 80) }}</p>
                                                        <a href="{{ route('validations.show', $question) }}"
                                                            class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Revisar</a>
                                                    </div>
                                                @empty
                                                    <p class="text-gray-500">¡Buen trabajo! No hay preguntas pendientes
                                                        de revisión por el momento.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endif

                                    {{-- === SECCIÓN PARA ADMINISTRADOR === --}}
                                    @if (Auth::user()->role === 'administrador')
                                        <div class="md:col-span-2">
                                            <h3 class="text-xl font-semibold text-gray-800 my-4">Panel de Administrador
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="bg-purple-100 p-4 rounded-lg text-center">
                                                    <div class="text-3xl font-bold text-purple-800">
                                                        {{ $globalStats['total_questions'] ?? 0 }}</div>
                                                    <div class="text-sm text-purple-600">Total de Preguntas en el
                                                        Sistema</div>
                                                </div>
                                                <div class="bg-pink-100 p-4 rounded-lg text-center">
                                                    <div class="text-3xl font-bold text-pink-800">
                                                        {{ $globalStats['total_users'] ?? 0 }}</div>
                                                    <div class="text-sm text-pink-600">Total de Usuarios Registrados
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </x-app-layout>
            </div>
        </div>
    </div>
</x-app-layout>
