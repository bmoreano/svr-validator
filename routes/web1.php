<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\QuestionSubmissionController;
use App\Http\Controllers\ComparisonSubmissionController;
use App\Http\Controllers\PromptProposalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\QuestionAssignmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde se registran todas las rutas web para la aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y todas se asignarán
| al grupo de middleware "web".
|
*/

// --- RUTA PÚBLICA DE BIENVENIDA ---
// Accesible para cualquier visitante, autenticado o no.
Route::get('/', function () {
    return view('components.welcome');
});

// =========================================================================
// --- GRUPO PRINCIPAL DE RUTAS PROTEGIDAS ---
// Todas las rutas dentro de este grupo requieren que el usuario esté
// autenticado (logeado) y que su email esté verificado.
// =========================================================================
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // --- RUTA DEL DASHBOARD ---
    // El punto de entrada para todos los usuarios autenticados.
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // --- GRUPO DE RUTAS PARA AUTORES ---
    // Funcionalidades relacionadas con la creación y gestión de preguntas.
    // Solo accesible para usuarios con rol 'autor' o 'administrador'.
    Route::middleware(['role:autor,administrador'])->group(function () {
        
        // CRUD de Preguntas (Crear, Editar, Actualizar, Borrar, Listar).
        Route::resource('questions', QuestionController::class)->except(['show']);
        
        // Acción para enviar una pregunta a validación individual.
        Route::post('questions/{question}/submit_validation', QuestionSubmissionController::class)->name('questions.submit_validation');
        
        // Acción para iniciar la validación comparativa.
        Route::post('questions/{question}/compare-submit', ComparisonSubmissionController::class)->name('questions.compare.submit');
    });

    // --- RUTAS DE SOLO LECTURA PARA PREGUNTAS ---
    // Se definen fuera del grupo de roles para que todos los usuarios autenticados puedan acceder.
    // La autorización final se delega a la QuestionPolicy dentro de cada controlador.
    Route::get('questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    Route::get('questions/{question}/compare', [ComparisonController::class, 'show'])->name('questions.compare');

    
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
    });

    // --- GRUPO DE RUTAS PARA GESTIÓN DE PROMPTS ---
    // La propuesta de prompts es para todos, pero la administración es solo para el admin.

    // Página para que cualquier usuario autenticado pueda proponer un nuevo prompt.
    Route::get('/prompts/propose', [PromptProposalController::class, 'create'])->name('prompts.propose');
    Route::post('/prompts/propose', [PromptProposalController::class, 'store'])->name('prompts.propose.store');

    // --- GRUPO DE RUTAS PARA ADMINISTRADORES ---
    // Funcionalidades de alto nivel solo para el rol 'administrador'.
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {
        
        // CRUD para gestionar todos los Prompts del sistema.
        Route::resource('prompts', PromptController::class);
        
        // CRUD para gestionar todos los Usuarios del sistema.
        Route::resource('users', UserController::class);

        // Acción para asignar un validador específico a una pregunta.
        Route::patch('/questions/{question}/assign-validator', [QuestionAssignmentController::class, 'assign'])
            ->name('questions.assign_validator');
    });
});