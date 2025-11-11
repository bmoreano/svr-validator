<div x-data="{ open: false }" class="relative" wire:poll.15s>

    
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 w-80 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        style="display: none;">

        <div class="py-1">
            <div class="px-4 py-2 flex justify-between items-center border-b">
                <span class="font-semibold text-gray-800">Notificaciones</span>
                <!--[if BLOCK]><![endif]--><?php if($unreadCount > 0): ?>
                    <button wire:click="markAllAsRead"
                        class="text-xs text-indigo-600 hover:underline focus:outline-none">
                        Marcar todas como leídas
                    </button>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
            <div class="max-h-96 overflow-y-auto">
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="#" wire:click.prevent="markAsRead('<?php echo e($notification->id); ?>')"
                        class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 <?php echo e(is_null($notification->read_at) ? 'bg-blue-50' : ''); ?>">
                        <p class="font-bold">
                            <!--[if BLOCK]><![endif]--><?php if($notification->type === 'App\Notifications\AiValidationCompleted'): ?>
                                ✅ Validación Completada
                            <?php elseif($notification->type === 'App\Notifications\AiValidationFailed'): ?>
                                ❌ Validación Fallida
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </p>
                        <p class="truncate"><?php echo e($notification->data['message'] ?? 'Tienes una nueva notificación.'); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e($notification->created_at->diffForHumans()); ?></p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="px-4 py-3 text-sm text-center text-gray-500">No tienes notificaciones.</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\byron.moreano\Herd\svr-validator08102025\resources\views/livewire/notification-bell.blade.php ENDPATH**/ ?>