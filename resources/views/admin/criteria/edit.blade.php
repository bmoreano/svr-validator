<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editando Criterio #{{ $criterion->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('admin.criteria.update', $criterion) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.criteria.partials.form', ['criterion' => $criterion])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>