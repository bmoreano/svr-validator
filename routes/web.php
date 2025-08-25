<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\QuestionStateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PromptExecutionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CriteriaUploadController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\BatchValidationController;
use App\Http\Controllers\QuestionSubmissionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CriterionController as AdminCriterionController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\QuestionAssignmentController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ComparisonSubmissionController;
use App\Http\Controllers\FileValidationController;
use App\Http\Controllers\PromptProposalController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\DocxConverterController;
use App\Models\Question;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\CareerDashboardController;
use App\Http\Controllers\TextSanitizerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider y todas ellas se
| asignarán al grupo de middleware "web".
|
*/

// La página de bienvenida, accesible para todos.
Route::get('/', function () {
    return view('components.welcome');
});

// --- 1. RUTAS DE AUTENTICACIÓN Y REGISTRO --- 
// Nota: Las rutas de login, registro, etc. (/login, /register, /forgot-password)
// son manejadas automáticamente por Laravel Fortify y no necesitan ser definidas aquí.


// --- 2. RUTA DE VALIDACIÓN SEGURA PARA VALIDADORES EXTERNOS ---

// Esta ruta especial permite el acceso a la interfaz de validación a través de
// un enlace seguro y temporal enviado por correo electrónico.
// No requiere que el usuario inicie sesión previamente.
Route::get('/validations/{question}/review', [ValidationController::class, 'show'])
    ->middleware(['signed', 'auth.signed']) // 'signed' verifica la firma; 'auth.signed' loguea al usuario.
    ->name('validations.show.signed');

// --- 3. GRUPO PRINCIPAL DE RUTAS PROTEGIDAS POR AUTENTICACIÓN ---

// Todas las rutas dentro de este grupo requieren que el usuario haya iniciado sesión
// y haya verificado su correo electrónico.


Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/questions/{question}/compare', [ComparisonController::class, 'show'])
        ->name('questions.compare');

    // --- GRUPO DE RUTAS SOLO PARA AUTORES ---
    // El administrador ya no tiene acceso a estas rutas de creación.
    Route::middleware(['role:autor'])->group(function () {
        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::post('questions/{question}/submit', QuestionSubmissionController::class)->name('questions.submit');
        Route::get('/questions/batch-upload', [BatchValidationController::class, 'create'])->name('questions.batch.create');
        Route::post('/questions/batch-upload', [BatchValidationController::class, 'store'])->name('questions.batch.store');
    });

    // --- RUTAS DE GESTIÓN DE PREGUNTAS (Accesibles a Autor y Admin) ---
    // Un administrador puede ver la lista de preguntas de todos (se ajusta en el controlador)
    // y editar/eliminar preguntas.
    Route::middleware(['role:autor,administrador'])->group(function () {
        // Página para que cualquier usuario autenticado pueda proponer un nuevo prompt.
        Route::get('/prompts/propose', [PromptProposalController::class, 'create'])->name('prompts.propose');
        Route::post('/prompts/propose', [PromptProposalController::class, 'store'])->name('prompts.propose.store');
        Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::post('questions/{question}/compare-submit', ComparisonSubmissionController::class)->name('questions.compare.submit');
    });

    // Ver una pregunta es accesible a todos los roles logueados.
    Route::get('questions/{question}', [QuestionController::class, 'show'])->name('questions.show');

    // --- GRUPO DE RUTAS SOLO PARA VALIDADORES ---
    // El administrador ya no tiene acceso directo a la validación.
    Route::middleware(['role:validador'])->group(function () {
        Route::get('validations/{question}', [ValidationController::class, 'show'])->name('validations.show');
        Route::post('validations/{question}', [ValidationController::class, 'store'])->name('validations.store');
    });

    // --- GRUPO DE RUTAS SOLO PARA ADMINISTRADORES ---
    // Todas las rutas aquí dentro tendrán el prefijo 'admin/' en la URL y 'admin.' en el nombre.
    // --- GRUPO DE RUTAS PARA VALIDADORES ---
    // Funcionalidades para el proceso de revisión humana.
    // Solo accesible para usuarios con rol 'validador' o 'administrador'.
    Route::middleware(['role:validador,administrador'])->group(function () {

        // Lista de validaciones pendientes (si se implementa un dashboard de validador).
        Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');

        // Muestra la página de revisión de una pregunta específica.
        Route::get('/validations/{question}/review', [ValidationController::class, 'review'])->name('validations.review');

        // Procesa el formulario de decisión del validador.
        Route::post('/validations/{question}/review', [ValidationController::class, 'processReview'])->name('validations.process_review');
       
        //Route::resource('users', AdminUserController::class); // Rutas para el CRUD de gestión de usuarios.
        Route::resource('criteria', AdminCriterionController::class);
        Route::resource('prompts', PromptController::class);
        Route::patch('/questions/{question}/assign-validator', [QuestionAssignmentController::class, 'assign'])->name('questions.assign_validator');
    });

    // --- FUNCIONALIDAD: VALIDACIÓN DE PREGUNTAS (Roles privilegiados) ---
    Route::middleware(['role:autor,validador,administrador'])->group(function () {
        // Esta ruta recibe la solicitud para validar una pregunta existente.
        Route::post('questions/{question}/submit-validation', QuestionSubmissionController::class)->name('questions.submit_validation');
    });


    // --- GRUPO DE RUTAS PARA VALIDACIÓN DESDE ARCHIVO ---
    // Esta funcionalidad puede ser accedida por cualquier usuario autenticado,
    // ya que la lógica interna del controlador filtrará las preguntas por el autor.
    Route::prefix('validation-from-file')->name('validation.from-file.')->group(function () {
        // Ruta GET para mostrar el formulario de carga de archivos.
        // URL: /validation-from-file/create
        // Nombre de la ruta: validation.from-file.create
        Route::get('/create', [FileValidationController::class, 'create'])->name('create');
        // Ruta POST para procesar el formulario enviado.
        // URL: /validation-from-file/store
        // Nombre de la ruta: validation.from-file.store
        Route::post('/store', [FileValidationController::class, 'store'])->name('store');
    });


    // --- GRUPO DE RUTAS PARA ADMINISTRADORES ---
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {

        // ========================================================================
        // Usamos el método `names()` para aplicar explícitamente el prefijo 'admin.'
        // a todos los nombres de ruta generados por `Route::resource`.
        // ========================================================================

        // CRUD para gestionar todos los Prompts del sistema.
        // Ahora los nombres serán: admin.prompts.index, admin.prompts.create, etc.
        Route::resource('prompts', PromptController::class)->names('prompts');

        // CRUD para gestionar todos los Usuarios del sistema.
        // Ahora los nombres serán: admin.users.index, admin.users.create, etc.
        Route::resource('users', AdminUserController::class)->names('users');

        // Acción para asignar un validador específico a una pregunta.
        Route::patch('/questions/{question}/assign-validator', [QuestionAssignmentController::class, 'assign'])
            ->name('questions.assign_validator');
        // CRUD para gestionar todos los Usuarios del sistema.
        Route::resource('prompts', PromptController::class)->names('prompts');
        Route::resource('users', UserController::class)->names('users');
        Route::patch('/questions/{question}/assign-validator', [QuestionAssignmentController::class, 'assign'])
            ->name('questions.assign_validator');

        Route::prefix('criteria-upload')->name('criteria-upload.')->group(function () {
            Route::get('/', [CriteriaUploadController::class, 'create'])->name('create');
            Route::post('/', [CriteriaUploadController::class, 'store'])->name('store');
            Route::get('/download-template', [CriteriaUploadController::class, 'downloadTemplate'])->name('download.template');
        });    
        // --- FUNCIONALIDAD: GESTIÓN Y META-VALIDACIÓN DE PROMPTS ---
        // El CRUD de prompts es solo para administradores.
        Route::resource('prompts', PromptController::class);
        // La página para enviar un prompt a meta-validación es para TODOS los usuarios autenticados.
        Route::get('/prompts/propose', [PromptProposalController::class, 'create'])->name('prompts.propose');
        Route::post('/prompts/propose', [PromptProposalController::class, 'store'])->name('prompts.propose.store');
        Route::resource('prompts', PromptController::class)->names('prompts');
        Route::resource('users', UserController::class)->names('users');
        Route::resource('careers', CareerController::class)->names('careers'); // <-- NUEVA RUTA
        Route::patch('/questions/{question}/assign-validator', [QuestionAssignmentController::class, 'assign'])->name('questions.assign_validator');
        // Nueva ruta para forzar el paso a revisión humana
        Route::patch('/questions/{question}/send-to-review', [QuestionStateController::class, 'sendToReview'])->name('questions.send_to_review');  
        // --- NUEVA RUTA PARA EL PANEL DE ANALÍTICAS ---
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        // --- NUEVA RUTA PARA EL PANEL DE REPORTES ---
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    });  

    // --- GRUPO DE RUTAS PARA CARGA MASIVA DE PREGUNTAS ---
    Route::middleware(['role:autor,administrador'])->prefix('questions-upload')->name('questions-upload.')->group(function () {        
        Route::get('/', [BulkUploadController::class, 'create'])->name('create');
        Route::post('/', [BulkUploadController::class, 'store'])->name('store');        
        Route::get('/download-template', [BulkUploadController::class, 'downloadQuestionsTemplate'])->name('download.template');
    });

    Route::middleware(['role:autor,administrador'])->prefix('bulk-upload')->name('bulk-upload.')->group(function () {
        
        // Ruta GET para mostrar el formulario de carga
        // URL: /bulk-upload
        // Nombre: bulk-upload.create (se genera como 'bulk-upload.index' si usamos resource)
        // Por simplicidad, la nombramos 'create' para que coincida con la vista.
        Route::get('/', [BulkUploadController::class, 'create'])->name('create');

        // Ruta POST para procesar el archivo subido
        // URL: /bulk-upload
        // Nombre: bulk-upload.store
        Route::post('/', [BulkUploadController::class, 'store'])->name('store');

                // --- RUTAS DE DESCARGA ---
        Route::get('/download-questions-template', [BulkUploadController::class, 'downloadQuestionsTemplate'])->name('download.questions');
        Route::get('/download-criteria-template', [BulkUploadController::class, 'downloadCriteriaTemplate'])->name('download.criteria');
    });

    // --- GRUPO DE RUTAS PARA EL CONVERSOR DE DOCX ---
    // Accesible para autores y administradores
    Route::middleware(['role:autor,administrador'])->prefix('tools')->name('tools.')->group(function () {
        Route::get('/docx-converter', [DocxConverterController::class, 'create'])->name('docx-converter.create');
        Route::post('/docx-converter', [DocxConverterController::class, 'store'])->name('docx-converter.store');
    });

    // --- FUNCIONALIDAD: GESTIÓN Y LIMPIEZA DE ARCHIVOS PARA MIGRAR A .tct ---
    // --- GRUPO DE RUTAS PARA HERRAMIENTAS TÉCNICAS ---    
    Route::middleware(['role:tecnico,administrador'])->prefix('tools')->name('tools.')->group(function () {
        // --- NUEVAS RUTAS PARA EL LIMPIADOR DE TEXTO ---
        Route::get('/text-sanitizer', [TextSanitizerController::class, 'create'])->name('text-sanitizer.create');
        Route::post('/text-sanitizer', [TextSanitizerController::class, 'process'])->name('text-sanitizer.process');
    });
    
    // --- GRUPO DE RUTAS PARA EJECUCIÓN DE PROMPTS ---
    // Accesible para autores y administradores.
    Route::middleware(['role:autor,administrador'])->prefix('prompt-execution')->name('prompt-execution.')->group(function () {
        Route::get('/', [PromptExecutionController::class, 'create'])->name('create');
        Route::post('/', [PromptExecutionController::class, 'store'])->name('store');
    });
  
    // --- GRUPO DE RUTAS PARA EJECUCIÓN DE CARRERAS ---
    // Accesible para jefe_carrera,administrador.
        Route::get('/career-dashboard', [CareerDashboardController::class, 'index'])
            ->middleware(['role:jefe_carrera,administrador']) // Accesible para Jefes de Carrera y Admins
            ->name('career-dashboard.index');
});
