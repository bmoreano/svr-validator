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
            Panel de Control
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <?php if(session('status')): ?>
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p><?php echo e(session('status')); ?></p>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo e(session('error')); ?></p>
                </div>
            <?php endif; ?>
            
            
            
            
            <?php if(auth()->user()->role === 'autor'): ?>
                <div class="space-y-8">
                    <!-- KPIs y Gráfico -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold"><?php echo e($kpis['total']); ?></div><div class="text-sm text-gray-500">Total Creadas</div></div>
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold text-gray-800"><?php echo e($kpis['borrador']); ?></div><div class="text-sm text-gray-500">Borradores</div></div>
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold text-yellow-600"><?php echo e($kpis['en_revision']); ?></div><div class="text-sm text-gray-500">En Revisión</div></div>
                            <div class="bg-white p-4 rounded-lg shadow text-center"><div class="text-3xl font-bold text-green-600"><?php echo e($kpis['aprobadas']); ?></div><div class="text-sm text-gray-500">Aprobadas</div></div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-lg font-semibold text-center">Distribución por Estado</h3><canvas id="statusPieChart"></canvas></div>
                    </div>
                    <!-- Feedback y Historial -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-lg font-semibold mb-4">Mis 5 Criterios a Mejorar</h3><div class="divide-y divide-gray-200"><?php $__empty_1 = true; $__currentLoopData = $topDisagreeingCriteria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="py-3"><div class="flex justify-between items-center"><span class="text-sm text-gray-700"><?php echo e($item->criterion->text); ?></span><span class="text-sm font-bold text-indigo-600"><?php echo e($item->total); ?> correc.</span></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="text-gray-500 italic">No hay correcciones a tus preguntas aún.</p><?php endif; ?></div></div>
                        <div class="bg-white p-6 rounded-lg shadow"><h3 class="text-lg font-semibold mb-4">Preguntas que Requieren tu Atención</h3><div class="divide-y divide-gray-200"><?php $__empty_1 = true; $__currentLoopData = $actionableQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="py-3 flex justify-between items-center"><div><p class="text-sm font-semibold text-gray-800"><?php echo e($question->code); ?></p><p class="text-xs text-gray-500"><?php echo e($question->revision_feedback ?? 'Rechazado permanentemente.'); ?></p></div><a href="<?php echo e(route('questions.edit', $question)); ?>" class="px-3 py-1 bg-orange-500 text-white text-xs rounded-md">Corregir</a></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="text-gray-500 italic">No tienes preguntas pendientes de corrección.</p><?php endif; ?></div></div>
                    </div>
                </div>

            
            
            
<?php else: ?>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <h1 class="text-2xl font-medium text-gray-900">Hola, <?php echo e(Auth::user()->name); ?></h1>
                    <p class="mt-2 text-gray-600">Tu rol es: <span class="font-bold"><?php echo e(ucfirst(auth()->user()->role)); ?></span>. Selecciona una acción:</p>
                    
                    <div class="space-y-10 mt-8">
                        <!-- SECCIÓN: GESTIÓN DE CONTENIDO -->
                        <section class="bg-blue-50 p-6 rounded-lg shadow-inner">
                            <h2 class="text-xs font-bold uppercase text-blue-500 tracking-wider mb-4">Gestión de Contenido</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php if(in_array(auth()->user()->role, ['autor', 'administrador'])): ?>
                                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-blue-900">Gestionar Preguntas</h3>
                                        <p class="text-sm text-blue-800 mt-1">Crea, edita y envía tus reactivos a validación.</p>
                                        <div class="mt-4 pt-3 border-t border-blue-100">
                                            <a href="<?php echo e(route('questions.index')); ?>" class="font-semibold text-blue-600 hover:text-blue-800 text-sm">Ir a Mis Preguntas &rarr;</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if(in_array(auth()->user()->role, ['validador', 'administrador'])): ?>
                                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-blue-900">Revisión de Preguntas</h3>
                                        <p class="text-sm text-blue-800 mt-1">Accede a la cola de preguntas para la revisión humana final.</p>
                                        <div class="mt-4 pt-3 border-t border-blue-100">
                                            <a href="<?php echo e(route('validations.index')); ?>" class="font-semibold text-blue-600 hover:text-blue-800 text-sm">Ver Preguntas a Validar &rarr;</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if(in_array(auth()->user()->role, ['autor', 'administrador'])): ?>
                                    <div class="bg-white p-4 rounded-lg border border-blue-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-blue-900">Carga Masiva de Preguntas</h3>
                                        <p class="text-sm text-blue-800 mt-1">Sube un conjunto de reactivos desde un archivo CSV.</p>
                                        <div class="mt-4 pt-3 border-t border-blue-100">
                                            <a href="<?php echo e(route('questions-upload.create')); ?>" class="font-semibold text-blue-600 hover:text-blue-800 text-sm">Cargar Preguntas &rarr;</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- SECCIÓN: INGENIERÍA DE PROMPTS -->
                        <section class="bg-teal-50 p-6 rounded-lg shadow-inner">
                            <h2 class="text-xs font-bold uppercase text-teal-500 tracking-wider mb-4">Ingeniería de Prompts</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-white p-4 rounded-lg border border-teal-200 shadow hover:shadow-md transition-shadow duration-300">
                                    <h3 class="font-bold text-lg text-teal-900">Proponer Prompts</h3>
                                    <p class="text-sm text-teal-800 mt-1">Crea y solicita la revisión de nuevos prompts para el sistema.</p>
                                    <div class="mt-4 pt-3 border-t border-teal-100">
                                        <a href="<?php echo e(route('prompts.propose')); ?>" class="font-semibold text-teal-600 hover:text-teal-800 text-sm">Proponer un Prompt &rarr;</a>
                                    </div>
                                </div>
                                <?php if(auth()->user()->role === 'administrador'): ?>
                                    <div class="bg-white p-4 rounded-lg border border-teal-200 shadow hover:shadow-md transition-shadow duration-300">
                                        <h3 class="font-bold text-lg text-teal-900">Administrar Prompts</h3>
                                        <p class="text-sm text-teal-800 mt-1">Revisa, aprueba y gestiona los prompts del sistema.</p>
                                        <div class="mt-4 pt-3 border-t border-teal-100">
                                            <a href="<?php echo e(route('admin.prompts.index')); ?>" class="font-semibold text-teal-600 hover:text-teal-800 text-sm">Administrar Prompts &rarr;</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- SECCIÓN: ADMINISTRACIÓN DEL SISTEMA -->
                        <?php if(auth()->user()->role === 'administrador'): ?>
                            <section class="bg-gray-100 p-6 rounded-lg shadow-inner">
                                 <h2 class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-4">Administración del Sistema</h2>
                                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Gestionar Usuarios</h3><p class="text-sm text-gray-700 mt-1">Gestiona los roles y permisos de las cuentas.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="<?php echo e(route('admin.users.index')); ?>" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Gestionar Usuarios &rarr;</a></div></div>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Gestionar Carreras</h3><p class="text-sm text-gray-700 mt-1">Define y administra los programas académicos.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="<?php echo e(route('admin.careers.index')); ?>" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Gestionar Carreras &rarr;</a></div></div>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Cargar Criterios</h3><p class="text-sm text-gray-700 mt-1">Añade o actualiza en masa los criterios de validación.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="<?php echo e(route('admin.criteria-upload.create')); ?>" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Cargar Criterios &rarr;</a></div></div>
                                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow duration-300"><h3 class="font-bold text-lg text-gray-900">Analíticas</h3><p class="text-sm text-gray-700 mt-1">Visualiza reportes y métricas de rendimiento.</p><div class="mt-4 pt-3 border-t border-gray-200"><a href="<?php echo e(route('admin.analytics.index')); ?>" class="font-semibold text-gray-600 hover:text-gray-800 text-sm">Ver Analíticas &rarr;</a></div></div>
                                 </div>
                            </section>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <?php if(auth()->user()->role === 'autor'): ?>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const statusData = <?php echo json_encode($statusDistribution ?? [], 15, 512) ?>;
                    const statusChartCtx = document.getElementById('statusPieChart');
                    if (statusChartCtx && Object.keys(statusData).length > 0) {
                        new Chart(statusChartCtx, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(statusData).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                                datasets: [{ 
                                    data: Object.values(statusData),
                                    backgroundColor: ['#6B7280', '#F59E0B', '#10B981', '#EF4444', '#3B82F6'],
                                }]
                            },
                            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                        });
                    }
                });
            </script>
        <?php endif; ?>
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\byron.moreano\Herd\svr-validator\resources\views/dashboard.blade.php ENDPATH**/ ?>