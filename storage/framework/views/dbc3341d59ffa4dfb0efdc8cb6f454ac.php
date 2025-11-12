<div wire:poll.15s>
    
    <div class="flex justify-end items-center mb-4 px-4 py-2 bg-gray-50 rounded-lg border">
        <!--[if BLOCK]><![endif]--><?php if($filterByMyCareer && !auth()->user()->career_id): ?>
            <span class="mr-4 text-xs text-yellow-700 italic">
                El filtro "mi carrera" está activo, pero tu perfil no tiene una carrera asignada. 
            </span>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        <label for="careerFilter" class="flex items-center cursor-pointer">
            <span class="mr-3 text-sm font-medium text-gray-900">Mostrar solo mi carrera</span>
            <div class="relative">
                <input id="careerFilter" type="checkbox" class="sr-only" wire:model="filterByMyCareer">
                <div class="block bg-gray-200 w-14 h-8 rounded-full transition"></div>
                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform"></div>
            </div>
        </label>
        <style> input:checked ~ .dot { transform: translateX(100%); } input:checked ~ .block { background-color: #4f46e5; } </style>
    </div>

            <?php echo e('livewire'); ?>

            <?php echo e($pendingQuestionslivewire); ?>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        
                        
                        
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Autor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Carrera
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Última Act.
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acción
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $pendingQuestionslivewire; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            
                            
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono" title="<?php echo e($question->code); ?>">
                                    <?php echo e(Str::limit($question->code, 25)); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500"><?php echo e($question->author->name); ?></div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500"><?php echo e($question->career->name ?? 'N/A'); ?></div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><?php echo e(ucfirst(str_replace('_', ' ', $question->status))); ?></span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($question->updated_at->diffForHumans()); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="<?php echo e(route('validations.review', $question)); ?>" class="inline-flex items-center p-2 bg-indigo-100 text-indigo-600 rounded-full hover:bg-indigo-200 transition" title="Revisar Pregunta">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">¡Excelente trabajo! No hay preguntas pendientes de revisión.</td></tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>

        <!--[if BLOCK]><![endif]--><?php if($pendingQuestionslivewire->hasPages()): ?>
            <div class="p-4 bg-gray-50 border-t">
                <?php echo e($pendingQuestionslivewire->links()); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH C:\Users\byron.moreano\Herd\svr-validator08102025\resources\views/livewire/validator-dashboard.blade.php ENDPATH**/ ?>