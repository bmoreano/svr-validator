<?php

// --- Importaciones de Controladores ---
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CareerDashboardController;
use App\Http\Controllers\CriteriaUploadController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\BatchValidationController;
use App\Http\Controllers\QuestionSubmissionController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ComparisonSubmissionController;
use App\Http\Controllers\FileValidationController;
use App\Http\Controllers\PromptProposalController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\DocxConverterController;
use App\Http\Controllers\TextSanitizerController;
use App\Http\Controllers\PromptExecutionController;
use App\Http\Controllers\QuestionActionController; 

// Controladores de Admin
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\QuestionStateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CriterionController as AdminCriterionController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\QuestionAssignmentController;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

// --- 1. RUTAS PÚBLICAS ---
Route::get('/', function () {
    return view('components.welcome');
});

Route::get('/validations/{question}/review', [ValidationController::class, 'show'])
    ->middleware(['signed', 'auth.signed'])
    ->name('validations.show.signed');


// --- 2. GRUPO PRINCIPAL DE RUTAS PROTEGIDAS ---
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // --- A. RUTAS PARA TODOS LOS ROLES AUTENTICADOS ---

    // El dashboard principal. Sigue siendo el "router" central de roles.
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    
    // --- INICIO DE LA SOLUCIÓN ---
    // Comentamos la redirección que habíamos implementado.
    // Route::redirect('/questions', '/dashboard')->name('questions.index');
    // --- FIN DE LA SOLUCIÓN ---

    Route::get('/questions/{question}/compare', [ComparisonController::class, 'show'])
        ->name('questions.compare');
    Route::get('questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    
    Route::get('/prompts/propose', [PromptProposalController::class, 'create'])->name('prompts.propose');
    Route::post('/prompts/propose', [PromptProposalController::class, 'store'])->name('prompts.propose.store');

    Route::prefix('validation-from-file')->name('validation.from-file.')->group(function () {
        Route::get('/create', [FileValidationController::class, 'create'])->name('create');
        Route::post('/store', [FileValidationController::class, 'store'])->name('store');
    });

    // --- Rutas de Acción para Notificaciones (Firmadas) ---
    Route::get('/questions/action/withdraw', [QuestionActionController::class, 'withdraw'])
        ->name('questions.action.withdraw')
        ->middleware('signed');
    Route::get('/questions/action/resume/{question}', [QuestionActionController::class, 'resume'])
        ->name('questions.action.resume')
        ->middleware('signed');


    // --- B. RUTAS ESPECÍFICAS POR ROL ---

    // --- GRUPO: AUTOR ---
    Route::middleware(['role:autor'])->group(function () {
        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::post('questions/{question}/submit', QuestionSubmissionController::class)->name('questions.submit');
        Route::get('/questions/batch-upload', [BatchValidationController::class, 'create'])->name('questions.batch.create');
        Route::post('/questions/batch-upload', [BatchValidationController::class, 'store'])->name('questions.batch.store');
    });

    // --- GRUPO: AUTOR & ADMINISTRADOR ---
    Route::middleware(['role:autor,administrador'])->group(function () {
        
        // --- INICIO DE LA SOLUCIÓN ---
        // Reactivamos la ruta original de /questions.
        Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
        // --- FIN DE LA SOLUCIÓN ---
        
        Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::post('questions/{question}/compare-submit', ComparisonSubmissionController::class)->name('questions.compare.submit');

        // (El resto del archivo de rutas permanece igual)
        // ...
        Route::prefix('questions-upload')->name('questions-upload.')->group(function () {        
            Route::get('/', [BulkUploadController::class, 'create'])->name('create');
            Route::post('/', [BulkUploadController::class, 'store'])->name('store');        
            Route::get('/download-template', [BulkUploadController::class, 'downloadQuestionsTemplate'])->name('download.template');
        });
        Route::prefix('bulk-upload')->name('bulk-upload.')->group(function () {
            Route::get('/', [BulkUploadController::class, 'create'])->name('create');
            Route::post('/', [BulkUploadController::class, 'store'])->name('store');
            Route::get('/download-questions-template', [BulkUploadController::class, 'downloadQuestionsTemplate'])->name('download.questions');
            Route::get('/download-criteria-template', [BulkUploadController::class, 'downloadCriteriaTemplate'])->name('download.criteria');
        });

        Route::prefix('tools')->name('tools.')->group(function () {
            Route::get('/docx-converter', [DocxConverterController::class, 'create'])->name('docx-converter.create');
            Route::post('/docx-converter', [DocxConverterController::class, 'store'])->name('docx-converter.store');
        });

        Route::prefix('prompt-execution')->name('prompt-execution.')->group(function () {
            Route::get('/', [PromptExecutionController::class, 'create'])->name('create');
            Route::post('/', [PromptExecutionController::class, 'store'])->name('store');
        });
    });

    // --- GRUPO: AUTOR, VALIDADOR & ADMINISTRADOR ---
    Route::middleware(['role:autor,validador,administrador'])->group(function () {
        Route::post('questions/{question}/submit-validation', QuestionSubmissionController::class)->name('questions.submit_validation');
    });

    // --- GRUPO: VALIDADOR ---
    Route::middleware(['role:validador'])->group(function () {
        Route::get('validations/{question}', [ValidationController::class, 'show'])->name('validations.show');
        Route::post('validations/{question}', [ValidationController::class, 'store'])->name('validations.store');
    });

    // --- GRUPO: VALIDADOR & ADMINISTRADOR ---
    Route::middleware(['role:validador,administrador'])->group(function () {
        Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');
        Route::get('/validations/{question}/review', [ValidationController::class, 'review'])->name('validations.review');
        Route::post('/validations/{question}/review', [ValidationController::class, 'processReview'])->name('validations.process_review');
    });

    // --- GRUPO: TÉCNICO & ADMINISTRADOR ---
    Route::middleware(['role:tecnico,administrador'])->prefix('tools')->name('tools.')->group(function () {
        Route::get('/text-sanitizer', [TextSanitizerController::class, 'create'])->name('text-sanitizer.create');
        Route::post('/text-sanitizer', [TextSanitizerController::class, 'process'])->name('text-sanitizer.process');
    });

    // --- GRUPO: JEFE DE CARRERA & ADMINISTRADOR ---
    Route::get('/career-dashboard', [CareerDashboardController::class, 'index'])
        ->middleware(['role:jefe_carrera,administrador'])
        ->name('career-dashboard.index');


    // --- C. GRUPO DE ADMINISTRACIÓN ---
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class)->names('users');
        Route::resource('prompts', PromptController::class)->names('prompts');
        Route::resource('careers', CareerController::class)->names('careers');
        Route::resource('criteria', AdminCriterionController::class)->names('criteria');

        Route::prefix('criteria-upload')->name('criteria-upload.')->group(function () {
            Route::get('/', [CriteriaUploadController::class, 'create'])->name('create');
            Route::post('/', [CriteriaUploadController::class, 'store'])->name('store');
            Route::get('/download-template', [CriteriaUploadController::class, 'downloadTemplate'])->name('download.template');
        });    

        Route::patch('/questions/{question}/assign-validator', [QuestionAssignmentController::class, 'assign'])
            ->name('questions.assign_validator');
        Route::patch('/questions/{question}/send-to-review', [QuestionStateController::class, 'sendToReview'])
            ->name('questions.send_to_review');  
        
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    });

});