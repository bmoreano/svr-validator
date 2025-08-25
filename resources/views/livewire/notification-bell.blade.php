<div x-data="{ open: false }" class="relative" wire:poll.15s>

    {{-- El menú desplegable de notificaciones --}}
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 w-80 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        style="display: none;">

        <div class="py-1">
            <div class="px-4 py-2 flex justify-between items-center border-b">
                <span class="font-semibold text-gray-800">Notificaciones</span>
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead"
                        class="text-xs text-indigo-600 hover:underline focus:outline-none">
                        Marcar todas como leídas
                    </button>
                @endif
            </div>
            <div class="max-h-96 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <a href="#" wire:click.prevent="markAsRead('{{ $notification->id }}')"
                        class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                        <p class="font-bold">
                            @if ($notification->type === 'App\Notifications\AiValidationCompleted')
                                ✅ Validación Completada
                            @elseif($notification->type === 'App\Notifications\AiValidationFailed')
                                ❌ Validación Fallida
                            @endif
                        </p>
                        <p class="truncate">{{ $notification->data['message'] ?? 'Tienes una nueva notificación.' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="px-4 py-3 text-sm text-center text-gray-500">No tienes notificaciones.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
