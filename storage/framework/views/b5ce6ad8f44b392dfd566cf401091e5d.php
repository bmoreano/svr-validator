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
            Revisión Humana de la Pregunta #<?php echo e($question->id); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8">

            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 space-y-6">
                
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Enunciado</h3>
                    <p class="mt-2 p-4 bg-gray-50 rounded-md text-gray-700"><?php echo e($question->stem); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Opciones</h3>
                    <div class="mt-4 space-y-4 max-h-[60vh] overflow-y-auto pr-4">
                        <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            
                            <div
                                class="p-4 rounded-md border <?php echo e($option->is_correct ? 'bg-teal-50 border-teal-300' : 'bg-gray-50 border-gray-200'); ?>">

                                
                                <p class="text-gray-900 font-semibold">
                                    <?php echo e($option->option_text); ?>

                                    <?php if($option->is_correct): ?>
                                        <span class="ml-2 text-xs font-bold text-teal-700">(Respuesta Correcta)</span>
                                    <?php endif; ?>
                                </p>

                                
                                <?php if($option->argumentation): ?>
                                    <div class="mt-2 pl-4 border-l-2 border-gray-300">
                                        <p class="text-xs font-semibold text-gray-500">Argumentación:</p>
                                        <p class="text-sm text-gray-700 italic">"<?php echo e($option->argumentation); ?>"</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                        <h3 class="text-lg font-bold text-gray-900">Bibliografía:</h3>
                <?php if($question->bibliography): ?>
                    <div class="mt-2 pl-4 border-l-2 border-gray-300">
                        <p class="text-sm text-gray-700 italic">"<?php echo e($question->bibliography); ?>"</p>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <?php if($aiValidation): ?>
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Asistente de IA (<?php echo e($aiValidation->ai_engine); ?>)</p>
                        <p>Los siguientes campos han sido pre-llenados. Revisa y corrige si es necesario.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Revisión Manual</p>
                        <p>No se encontró una validación de IA previa. Por favor, realiza la evaluación completa.</p>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('validations.process_review', $question)); ?>" method="POST"
                    x-data="{ decision: 'approve' }">
                    <?php echo csrf_field(); ?>

                    
                    
                    
                    <div class="space-y-8 max-h-[60vh] overflow-y-auto pr-4">
                        <?php $__currentLoopData = $criteria->groupBy('category'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2 capitalize border-b pb-2">
                                    <?php echo e($category); ?></h3>
                                <div class="space-y-4 mt-4">
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            // Buscamos la respuesta de la IA para este criterio.
                                            $aiResponse = $aiValidation
                                                ? $aiValidation->responses->firstWhere('criterion_id', $criterion->id)
                                                : null;
                                        ?>

                                        <div x-data="{ response: '<?php echo e($aiResponse?->response ?? 'si'); ?>' }">
                                            <label
                                                class="block font-medium text-sm text-gray-700"><?php echo e($criterion->text); ?></label>
                                            <div class="mt-2 flex items-center space-x-4">
                                                <label class="inline-flex items-center">
                                                    <input x-model="response" type="radio"
                                                        name="criteria[<?php echo e($criterion->id); ?>][response]" value="si"
                                                        class="text-indigo-600">
                                                    <span class="ms-2">Sí</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input x-model="response" type="radio"
                                                        name="criteria[<?php echo e($criterion->id); ?>][response]" value="no"
                                                        class="text-indigo-600">
                                                    <span class="ms-2">No</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input x-model="response" type="radio"
                                                        name="criteria[<?php echo e($criterion->id); ?>][response]" value="adecuar"
                                                        class="text-indigo-600">
                                                    <span class="ms-2">Adecuar</span>
                                                </label>
                                            </div>
                                            <div x-show="response === 'no' || response === 'adecuar'" x-cloak
                                                class="mt-2">
                                                <label for="comment_<?php echo e($criterion->id); ?>"
                                                    class="block text-sm font-medium text-gray-500">Comentario:</label>
                                                <textarea id="comment_<?php echo e($criterion->id); ?>" name="criteria[<?php echo e($criterion->id); ?>][comment]" rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo e($aiResponse?->comment ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
<form action="<?php echo e(route('validations.process_review', $question)); ?>" method="POST" x-data="{ decision: 'approve' }">
                    <?php echo csrf_field(); ?>
                    
                    
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-bold text-gray-900">Decisión Final del Validador</h3>
                        <?php if (isset($component)) { $__componentOriginalb24df6adf99a77ed35057e476f61e153 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb24df6adf99a77ed35057e476f61e153 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.validation-errors','data' => ['class' => 'my-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('validation-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'my-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb24df6adf99a77ed35057e476f61e153)): ?>
<?php $attributes = $__attributesOriginalb24df6adf99a77ed35057e476f61e153; ?>
<?php unset($__attributesOriginalb24df6adf99a77ed35057e476f61e153); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb24df6adf99a77ed35057e476f61e153)): ?>
<?php $component = $__componentOriginalb24df6adf99a77ed35057e476f61e153; ?>
<?php unset($__componentOriginalb24df6adf99a77ed35057e476f61e153); ?>
<?php endif; ?>

                        <div class="mt-4 space-y-2">
                            
                            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-green-50 has-[:checked]:border-green-400 cursor-pointer">
                                <input type="radio" x-model="decision" name="decision" value="approve" class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <span class="ms-3 font-semibold text-green-800">Aprobar Pregunta</span>
                            </label>
                            
                            
                            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-orange-50 has-[:checked]:border-orange-400 cursor-pointer">
                                <input type="radio" x-model="decision" name="decision" value="reject" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                <span class="ms-3 font-semibold text-orange-800">Rechazar y Enviar a Corrección</span>
                            </label>

                            
                            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-red-50 has-[:checked]:border-red-400 cursor-pointer">
                                <input type="radio" x-model="decision" name="decision" value="reject_permanently" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                <span class="ms-3 font-semibold text-red-800">Rechazar Definitivamente</span>
                            </label>
                        </div>
                        
                        
                        <div x-show="decision === 'reject' || decision === 'reject_permanently'" class="mt-4" style="display: none;">
                            <?php if (isset($component)) { $__componentOriginald8ba2b4c22a13c55321e34443c386276 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8ba2b4c22a13c55321e34443c386276 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.label','data' => ['for' => 'feedback','value' => 'Justificación para el validador (obligatorio si se rechaza):']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'feedback','value' => 'Justificación para el validador (obligatorio si se rechaza):']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $attributes = $__attributesOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__attributesOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $component = $__componentOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__componentOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
                            <textarea name="feedback" id="feedback" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                            x-bind:required="decision !== 'approve'"><?php echo e(old('feedback')); ?></textarea>
                        </div>

                        <div class="mt-6 flex justify-end space-x-4">
                             <a href="<?php echo e(route('validations.index')); ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Confirmar Decisión
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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
<?php endif; ?>
<?php /**PATH C:\Users\byron.moreano\Herd\svr-validator\resources\views/validations/review.blade.php ENDPATH**/ ?>