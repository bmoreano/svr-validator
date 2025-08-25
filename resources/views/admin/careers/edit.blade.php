<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Carrera: {{ $career->name }}</h2>
        </x-slot>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form action="{{ route('admin.careers.update', $career) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-label for="name" value="Nombre de la Carrera" />
                            <x-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $career->name)" required />
                        </div>
                        <div class="mb-4">
                            <x-label for="description" value="Descripción (Opcional)" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('description', $career->description) }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label for="is_active" class="flex items-center">
                                <x-checkbox id="is_active" name="is_active" :checked="old('is_active', $career->is_active)" />
                                <span class="ms-2 text-sm text-gray-600">Activa</span>
                            </label>
                        </div>
                        <div class="flex justify-end mt-6">
                            <a href="{{ route('admin.careers.index') }}"
                                class="px-4 py-2 mr-4 bg-white border rounded-md ...">Cancelar</a>
                            <x-button>Actualizar Carrera</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>
</div>
