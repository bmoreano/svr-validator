<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\BatchValidationController;
use App\Http\Controllers\QuestionSubmissionController;

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

// --- 1. RUTAS PÚBLICAS / DE INVITADOS ---

// La página de bienvenida, accesible para todos.
Route::get('/', function () {
    return view('components.welcome');
});

// Nota: Las rutas de login, register, forgot-password, etc., son manejadas
// automáticamente por Laravel Fortify, por lo que no necesitan ser definidas aquí.


// --- 2. RUTA DE VALIDACIÓN SEGURA PARA VALIDADORES EXTERNOS ---

// Esta ruta es especial: es pública pero está protegida por dos middlewares:
// - 'signed': Verifica que la firma criptográfica de la URL sea válida.
// - 'auth.signed': Nuestro middleware personalizado que autentica al usuario.
Route::get('/validations/{question}/review', [ValidationController::class, 'show'])
    ->middleware(['signed', 'auth.signed'])
    ->name('validations.show.signed');


// --- 3. GRUPO PRINCIPAL DE RUTAS PROTEGIDAS POR AUTENTICACIÓN ---

// Todas las rutas dentro de este grupo requieren que el usuario haya iniciado sesión
// y, opcionalmente, que haya verificado su correo electrónico.
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // Ruta del Dashboard principal.
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // --- SUB-GRUPO DE RUTAS PARA AUTORES (y Administradores) ---
    // Solo los usuarios con el rol 'autor' o 'administrador' pueden acceder.
    Route::middleware(['role:autor,administrador'])->group(function () {
        
        // Rutas para el CRUD de preguntas. 'show' se excluye porque se define abajo
        // para que sea accesible a más roles.
        Route::resource('questions', QuestionController::class)->except(['show']);
        
        // Ruta para enviar una pregunta a validación por IA.
        Route::post('questions/{question}/submit', QuestionSubmissionController::class)->name('questions.submit');
        
        // Rutas para la subida de archivos por lotes.
        Route::get('/questions/batch-upload', [BatchValidationController::class, 'create'])->name('questions.batch.create');
        Route::post('/questions/batch-upload', [BatchValidationController::class, 'store'])->name('questions.batch.store');
    });

    // Ruta 'show' para ver los detalles de una pregunta. Es accesible a todos los
    // roles autenticados. La autorización final se maneja en la QuestionPolicy.
    Route::get('questions/{question}', [QuestionController::class, 'show'])->name('questions.show');

    // --- SUB-GRUPO DE RUTAS PARA VALIDADORES (y Administradores) ---
    // Solo los usuarios con el rol 'validador' o 'administrador' pueden realizar validaciones.
    Route::middleware(['role:validador,administrador'])->group(function () {
        
        // Muestra la interfaz de validación humana (para administradores que sí inician sesión).
        Route::get('validations/{question}', [ValidationController::class, 'show'])->name('validations.show');
        
        // Guarda la validación enviada por el validador/administrador.
        Route::post('validations/{question}', [ValidationController::class, 'store'])->name('validations.store');

        // (Opcional) Un índice para que los validadores/admins vean las preguntas pendientes.
        // Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');
    });

});