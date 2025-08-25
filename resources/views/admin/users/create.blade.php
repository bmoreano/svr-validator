<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                {{-- Contenedor secundario para centrar y limitar el ancho del formulario --}}
                <div class="max-w-xl mx-auto">
                    {{-- Muestra los errores de validación del backend --}}
                    <x-validation-errors class="mb-4" />

                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        {{-- Campo Nombre --}}
                        <div class="mb-4">
                            <x-label for="name" value="Nombre Completo" />
                            <x-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name')" required autofocus autocomplete="name" />
                        </div>

                        {{-- Campo Email --}}
                        <div class="mb-4">
                            <x-label for="email" value="Correo Electrónico" />
                            <x-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email')" required autocomplete="username" />
                        </div>


                        <div x-data="{ selectedRole: '{{ old('role', 'autor') }}' }">
                            {{-- Campo Rol --}}
                            <div class="mb-4">
                                <x-label for="role" value="Rol del Usuario" />
                                {{-- x-model vincula este select a la variable 'selectedRole' --}}
                                <select id="role" name="role" x-model="selectedRole"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="autor">Autor</option>
                                    <option value="validador">Validador</option>
                                    <option value="jefe_carrera">Jefe de Carrera</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="tester">Tester</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>

                            {{-- Campo Carrera (AHORA CONDICIONAL) --}}
                            <div class="mb-4" x-show="['autor', 'validador', 'jefe_carrera'].includes(selectedRole)"
                                x-transition>
                                <x-label for="career_id" value="Carrera Asignada" />
                                <select id="career_id" name="career_id" {{-- Deshabilita el campo si está oculto, para que no se envíe --}}
                                    :disabled="!['autor', 'validador', 'jefe_carrera'].includes(selectedRole)"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Sin Asignar --</option>
                                    @foreach ($careers as $career)
                                        <option value="{{ $career->id }}" @selected(old('career_id') == $career->id)>
                                            {{ $career->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Campos Contraseña --}}
                        <div class="mt-6 border-t pt-4">
                            <div class="mb-4">
                                <x-label for="password" value="Contraseña" />
                                <x-input id="password" name="password" type="password" class="mt-1 block w-full"
                                    required autocomplete="new-password" />
                            </div>

                            <div class="mb-4">
                                <x-label for="password_confirmation" value="Confirmar Contraseña" />
                                <x-input id="password_confirmation" name="password_confirmation" type="password"
                                    class="mt-1 block w-full" required autocomplete="new-password" />
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-between mt-8 border-t pt-5">
                            <a href="{{ route('admin.users.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                Cancelar
                            </a>
                            <x-button>
                                Crear Usuario
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
