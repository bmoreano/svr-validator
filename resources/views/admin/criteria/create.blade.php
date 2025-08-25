<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Criterio de Validación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('admin.criteria.store') }}" method="POST">
                    @csrf
                    @include('admin.criteria.partials.form', ['criterion' => new \App\Models\Criterion()])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>