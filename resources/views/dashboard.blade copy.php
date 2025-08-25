<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de Control
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Bloque para mostrar mensajes de estado (éxito o error) después de una acción --}}
            @if (session('status'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('status') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                <h1 class="text-2xl font-medium text-gray-900">Hola, {{ Auth::user()->name }}</h1>
                <p class="mt-2 text-gray-600">Tu rol es: <span
                        class="font-bold">{{ ucfirst(Auth::user()->role) }}</span>. Selecciona una acción para comenzar:
                </p>

                {{-- ========================================================== --}}
                {{-- Contenedor de las tarjetas de acción                       --}}
                {{-- ========================================================== --}}
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                    {{-- ========================================================== --}}
                    {{-- TARJETA 1: GESTIONAR PREGUNTAS                             --}}
                    {{-- Visible para 'autor' y 'administrador'                     --}}
                    {{-- ========================================================== --}}
                    @if (in_array(auth()->user()->role, ['autor', 'administrador']))
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center">
                                <div class="p-3 bg-indigo-100 rounded-full">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                        </path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Gestionar Preguntas</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Crea, edita y envía tus preguntas a un proceso de validación
                                automática.</p>
                            <div class="mt-6">
                                <a href="{{ route('questions.index') }}"
                                    class="font-semibold text-indigo-600 hover:text-indigo-800">
                                    Ir a Mis Preguntas →
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 2: REVISIÓN DE PREGUNTAS                           --}}
                    {{-- Visible para 'validador' y 'administrador'                 --}}
                    {{-- ========================================================== --}}
                    @if (in_array(auth()->user()->role, ['validador', 'administrador']))
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center">
                                <div class="p-3 bg-teal-100 rounded-full">
                                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Revisión de Preguntas</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Accede a la cola de preguntas listas para tu revisión y
                                aprobación final.</p>
                            <div class="mt-6">
                                <a href="{{ route('validations.index') }}"
                                    class="font-semibold text-teal-600 hover:text-teal-800">
                                    Ver Preguntas a Validar →
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 3: PROPONER UN PROMPT                              --}}
                    {{-- Visible para TODOS los roles autenticados                  --}}
                    {{-- ========================================================== --}}
                    <div class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                            </div>
                            <h2 class="ms-4 text-xl font-semibold text-gray-800">Proponer un Prompt</h2>
                        </div>
                        <p class="mt-4 text-gray-500">Envía un nuevo prompt para que nuestro sistema de IA evalúe su
                            calidad y seguridad.</p>
                        <div class="mt-6">
                            <a href="{{ route('prompts.propose') }}"
                                class="font-semibold text-green-600 hover:text-green-800">
                                Proponer Nuevo Prompt →
                            </a>
                        </div>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- TARJETA 4: CARGA MASIVA DE PREGUNTAS                       --}}
                    {{-- Visible para 'autor' y 'administrador'                     --}}
                    {{-- ========================================================== --}}
                    @if (in_array(auth()->user()->role, ['autor', 'administrador']))
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center">
                                <div class="p-3 bg-cyan-100 rounded-full">
                                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                                        </path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Carga Masiva de Preguntas</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Sube un conjunto de reactivos desde un archivo CSV para
                                agilizar la creación.</p>
                            <div class="mt-6">
                                <a href="{{ route('questions-upload.create') }}"
                                    class="font-semibold text-cyan-600 hover:text-cyan-800">
                                    Cargar Preguntas →
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 5: CARGA MASIVA DE CRITERIOS (Solo para Admin)     --}}
                    {{-- ========================================================== --}}
                    @if (auth()->user()->role === 'administrador')
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300 bg-gray-50">
                            <div class="flex items-center">
                                <div class="p-3 bg-orange-100 rounded-full">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Carga Masiva de Criterios</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Añade o actualiza en masa los criterios de validación desde
                                un archivo CSV.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.criteria-upload.create') }}"
                                    class="font-semibold text-orange-600 hover:text-orange-800">
                                    Cargar Criterios →
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 6: GESTIONAR USUARIOS (Solo para Admin)            --}}
                    {{-- ========================================================== --}}
                    @if (auth()->user()->role === 'administrador')
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300 bg-gray-50">
                            <div class="flex items-center">
                                <div class="p-3 bg-red-100 rounded-full">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Gestionar Usuarios</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Crea, edita y gestiona los roles y permisos de todas las
                                cuentas del sistema.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.users.index') }}"
                                    class="font-semibold text-red-600 hover:text-red-800">
                                    Ir a la Gestión de Usuarios →
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 7:  CONVERSOR DOCX A TXT (Solo para Admin)         --}}
                    {{-- ========================================================== --}}
                    @if (in_array(auth()->user()->role, ['autor', 'administrador']))
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Convertidor de Word</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Sube un archivo `.docx` para convertirlo a texto plano
                                (`.txt`), listo para copiar y pegar en los formularios.</p>
                            <div class="mt-6">
                                <a href="{{ route('tools.docx-converter.create') }}"
                                    class="font-semibold text-blue-600 hover:text-blue-800">
                                    Usar Herramienta &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 8: ADMINISTRAR PROMPTS (Solo para Admin)           --}}
                    {{-- ========================================================== --}}
                    @if (auth()->user()->role === 'administrador')
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300 bg-gray-50">
                            <div class="flex items-center">
                                <div class="p-3 bg-gray-100 rounded-full">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold">Administrar Prompts</h2>
                            </div>
                            <p class="mt-2 text-gray-500">Revisa, aprueba y gestiona todos los prompts propuestos en el
                                sistema.</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.prompts.index') }}"
                                    class="font-semibold text-gray-600 hover:text-gray-800">
                                    Ir al CRUD de Prompts →
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    {{-- ========================================================== --}}
                    {{-- TARJETA 9:  ADMINISTRACION CARRERAS (Solo para Admin)      --}}
                    {{-- ========================================================== --}}
                    @if (auth()->user()->role === 'administrador')
                        <div
                            class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300 bg-gray-50">
                            <div class="flex items-center">
                                <div class="p-3 bg-pink-100 rounded-full">
                                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Gestionar Carreras</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Define y administra las carreras o programas académicos que
                                se utilizarán para clasificar las preguntas.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.careers.index') }}"
                                    class="font-semibold text-pink-600 hover:text-pink-800">
                                    Ir al CRUD de Carreras &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                    {{-- ========================================================== --}}
                    {{-- TARJETA 10: HERRAMIENTA LIMPIEZA DE TEXTO Solo tecnico     --}}
                    {{-- ========================================================== --}}
                    @if(in_array(auth()->user()->role, ['tecnico', 'administrador']))
                        <div class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                             <div class="flex items-center">
                                <div class="p-3 bg-gray-100 rounded-full">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Herramientas Técnicas</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Accede a utilidades para preparar y limpiar contenido antes de ingresarlo al sistema.</p>
                            <div class="mt-6">
                                <a href="{{ route('tools.text-sanitizer.create') }}" class="font-semibold text-gray-600 hover:text-gray-800">
                                    Limpiar Texto &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================== --}}
                    {{-- TARJETA 11: EJECUTAR PROMPTS                               --}}
                    {{-- ========================================================== --}}
                    @if(in_array(auth()->user()->role, ['autor', 'administrador']))
                        <div class="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow duration-300">
                             <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m-2-2a5 5 0 11-8 4.982A5.002 5.002 0 0112 17.5a5 5 0 01-3.5-1.482M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <h2 class="ms-4 text-xl font-semibold text-gray-800">Ejecutar Prompts</h2>
                            </div>
                            <p class="mt-4 text-gray-500">Selecciona una de tus preguntas y un prompt aprobado para realizar una prueba de validación específica.</p>
                            <div class="mt-6">
                                <a href="{{ route('prompt-execution.create') }}" class="font-semibold text-blue-600 hover:text-blue-800">
                                    Iniciar Ejecución &rarr;
                                </a>
                            </div>
                        </div>
                    @endif                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
