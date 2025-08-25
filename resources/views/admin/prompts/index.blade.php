<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Prompts del Sistema
            </h2>
            <a href="{{ route('admin.prompts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Crear Nuevo Prompt
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Notificaciones Flash --}}
            @if(session('status')) <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ session('status') }}</div> @endif
            @if(session('error')) <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">{{ session('error') }}</div> @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motor IA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado Revisión</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activo</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($prompts as $prompt)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $prompt->name }}</div>
                                        <div class="text-xs text-gray-500 truncate" style="max-width: 30ch">{{ $prompt->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($prompt->ai_engine) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($prompt->status)
                                                @case('active') bg-green-100 text-green-800 @break
                                                @case('pending_review') bg-yellow-100 text-yellow-800 @break
                                                @case('rejected') bg-red-100 text-red-800 @break
                                            @endswitch">
                                            {{ ucfirst(str_replace('_', ' ', $prompt->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($prompt->is_active)
                                            <span class="text-green-500" title="Visible para usuarios">✓</span>
                                        @else
                                            <span class="text-red-500" title="No visible para usuarios">×</span>
                                        @endif
                                    </td>
                                    
                                    {{-- ========================================================== --}}
                                    {{-- COLUMNA DE "ACCIONES" CON ICONOS --}}
                                    {{-- ========================================================== --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-4">
                                            {{-- Icono de Ver --}}
                                            <a href="{{ route('admin.prompts.show', $prompt) }}" class="text-gray-500 hover:text-gray-800" title="Ver Detalles">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            {{-- Icono de Editar --}}
                                            <a href="{{ route('admin.prompts.edit', $prompt) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </a>
                                            {{-- Icono de Eliminar --}}
                                            <form action="{{ route('admin.prompts.destroy', $prompt) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este prompt?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">No hay prompts en el sistema.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($prompts->hasPages())
                    <div class="p-4 bg-gray-50 border-t">{{ $prompts->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>