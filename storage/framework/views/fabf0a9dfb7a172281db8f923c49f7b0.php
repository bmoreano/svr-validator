<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle de la Pregunta #<?php echo e($question->id); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    
    <div class="py-12" x-data="{ tab: 'current' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            
            <div class="mb-4 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'current'" 
                            :class="{ 'border-indigo-500 text-indigo-600': tab === 'current', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'current' }" 
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                        Versión Actual
                    </button>
                    <button @click="tab = 'history'" 
                            :class="{ 'border-indigo-500 text-indigo-600': tab === 'history', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'history' }" 
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                        Historial de Revisiones (<?php echo e($question->revisions->count()); ?>)
                    </button>
                </nav>
            </div>

            
            <div x-show="tab === 'current'" x-transition>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <?php if($question->status === 'rechazado_permanentemente'): ?>
                        <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-100 border-2 border-red-500" role="alert">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                <h3 class="text-lg font-medium">Pregunta Rechazada Permanentemente</h3>
                            </div>
                            <div class="mt-3 text-base">
                                <p class="font-semibold">Justificación del Validador:</p>
                                <blockquote class="mt-2 pl-4 border-l-4 border-red-400 italic">
                                    <?php echo e($question->revision_feedback ?? 'No se proporcionó una justificación detallada.'); ?>

                                </blockquote>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b">
                        <div><h3 class="text-sm font-medium text-gray-500">Autor</h3><p class="mt-1 text-base text-gray-900"><?php echo e($question->author->name); ?></p></div>
                        <div><h3 class="text-sm font-medium text-gray-500">Estado</h3><p class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php switch($question->status):
                                case ('aprobado'): ?> <?php case ('revisado_comparativo'): ?> class="bg-green-100 text-green-800" <?php break; ?>
                                <?php case ('borrador'): ?> class="bg-gray-200 text-gray-800" <?php break; ?>
                                <?php case ('necesita_correccion'): ?> class="bg-orange-100 text-orange-800" <?php break; ?>
                                <?php case ('fallo_comparativo'): ?> <?php case ('rechazado_permanentemente'): ?> class="bg-red-100 text-red-800" <?php break; ?>
                                <?php default: ?> class="bg-yellow-100 text-yellow-800"
                            <?php endswitch; ?>"><?php echo e(ucfirst(str_replace('_', ' ', $question->status))); ?></span></p></div>
                        <div><h3 class="text-sm font-medium text-gray-500">Última Actualización</h3><p class="mt-1 text-base text-gray-900"><?php echo e($question->updated_at->format('d/m/Y H:i')); ?></p></div>
                    </div>
                    
                    
                    <div class="mt-6">
                        <?php echo $__env->make('admin.questions.partials._form', ['question' => $question, 'disabled' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    
                    <?php $isAssignable = in_array($question->status, ['revisado_por_ai', 'revisado_comparativo', 'en_revision_humana']); ?>
                    <?php if(Auth::user()->role === 'administrador' && $isAssignable): ?>
                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-lg font-bold">Asignar Validador</h3>
                            <p class="text-sm text-gray-600">
                                Validador actual: <?php echo e($question->assignedValidator->name ?? 'Ninguno (abierto a todos los validadores)'); ?>

                            </p>
                            <form action="<?php echo e(route('admin.questions.assign_validator', $question)); ?>" method="POST" class="mt-4">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <div class="flex items-center space-x-2">
                                    <select name="validator_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">-- Desasignar (cola general) --</option>
                                        <?php $__currentLoopData = \App\Models\User::where('role', 'validador')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $validator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($validator->id); ?>" <?php if($question->assigned_validator_id == $validator->id): echo 'selected'; endif; ?>>
                                                <?php echo e($validator->name); ?> (<?php echo e($validator->email); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-700">Asignar</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    
                    <div class="flex items-center justify-between mt-8 pt-5 border-t">
                        <a href="<?php echo e(url()->previous(route('questions.index'))); ?>" 
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            Volver
                        </a>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $question)): ?>
                            <a href="<?php echo e(route('questions.edit', $question)); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                Editar Pregunta
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div x-show="tab === 'history'" x-transition class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8 space-y-6" style="display: none;">
                <?php
                    $revisions = $question->revisions;
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $revisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $revision): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-lg">Revisión #<?php echo e($revisions->count() - $index); ?></h3>
                            <div class="text-xs text-gray-500 text-right">
                                <span><?php echo e($revision->created_at->format('d/m/Y H:i')); ?> por <?php echo e($revision->user->name ?? 'Sistema'); ?></span><br>
                                <span class="font-semibold"><?php echo e($revision->change_reason); ?></span>
                            </div>
                        </div>
                        <?php if(isset($revisions[$index + 1])): ?>
                            <?php
                                $oldRevision = $revisions[$index + 1];
                                $oldText = "Código: {$oldRevision->code}\nEnunciado: {$oldRevision->stem}\nBibliografía: {$oldRevision->bibliography}\nDificultad: {$oldRevision->grado_dificultad}\nDiscriminación: {$oldRevision->poder_discriminacion}\nOpciones: " . json_encode($oldRevision->options_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                $newText = "Código: {$revision->code}\nEnunciado: {$revision->stem}\nBibliografía: {$revision->bibliography}\nDificultad: {$revision->grado_dificultad}\nDiscriminación: {$revision->poder_discriminacion}\nOpciones: " . json_encode($revision->options_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                
                                // Calculate and render the diff
                                $differ = new \Jfcherng\Diff\Differ(explode("\n", $oldText), explode("\n", $newText));
                                $renderer = new \Jfcherng\Diff\Renderer\Html\Combined();
                                $renderedDiff = $renderer->render($differ);
                            ?>
                            <div class="mt-4 border-t pt-4">
                                <h4 class="font-semibold text-gray-700">Cambios Realizados:</h4>
                                <div class="diff-renderer font-mono text-sm mt-2 p-4 bg-gray-50 rounded overflow-x-auto"><?php echo $renderedDiff; ?></div>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 border-t pt-4">
                                <p class="italic text-gray-600">Creación inicial de la pregunta.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p>No hay historial de revisiones para esta pregunta.</p>
                <?php endif; ?>
            </div>
            
            
            <?php $__env->startPush('styles'); ?>
            <style>
                .diff-renderer table { width: 100%; border-collapse: collapse; }
                .diff-renderer td { padding: 2px 8px; vertical-align: top; }
                .diff-renderer .unchanged { color: #6b7280; }
                .diff-renderer ins { background-color: #d1fae5; text-decoration: none; }
                .diff-renderer del { background-color: #fee2e2; text-decoration: none; }
            </style>
            <?php $__env->stopPush(); ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\byron.moreano\Herd\svr-validator08102025\resources\views/admin/questions/show.blade.php ENDPATH**/ ?>