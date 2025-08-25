<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Criterios de Validación
            </h2>
            <a href="{{ route('admin.criteria.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Añadir Nuevo Criterio
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notificaciones de estado --}}
            @if (session('status'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Éxito:</p>
                    <p>{{ session('status') }}</p>
                </div>
            @endif
            @if (session('error'))
                 <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Error:</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8 space-y-8">
                @forelse ($criteria->groupBy('category') as $category => $items)
                    <div class="border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 capitalize border-b pb-2 mb-4">
                            Categoría: {{ ucfirst($category) }}
                        </h3>
                        <div class="space-y-3">
                            @foreach ($items->sortBy('sort_order') as $criterion)
                                <div class="flex justify-between items-center p-3 rounded-md transition-colors duration-200 {{ $criterion->is_active ? 'bg-white hover:bg-gray-50' : 'bg-gray-200 text-gray-500 hover:bg-gray-300' }}">
                                    <div class="flex items-center">
                                        <span class="font-mono text-sm text-gray-400 mr-4">{{ $criterion->sort_order }}.</span>
                                        <p>{{ $criterion->text }}</p>
                                    </div>
                                    <div class="flex items-center space-x-4 flex-shrink-0 ml-4">
                                        @if(!$criterion->is_active)
                                            <span class="text-xs font-bold text-gray-500 uppercase">Inactivo</span>
                                        @endif
                                        <a href="{{ route('admin.criteria.edit', $criterion) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form method="POST" action="{{ route('admin.criteria.destroy', $criterion) }}" onsubmit="return confirm('¿Estás seguro de eliminar este criterio?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                     <div class="text-center py-12">
                        <p class="text-gray-500">No hay criterios definidos en el sistema.</p>
                        <a href="{{ route('admin.criteria.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Crea el primer criterio
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>