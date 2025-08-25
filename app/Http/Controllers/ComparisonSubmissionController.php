<?php

namespace App\Http\Controllers;

use App\Jobs\ValidateQuestionJob;
use App\Models\Question;
use App\Models\User; // Asegúrate de importar User
use App\Notifications\ComparativeValidationCompleted;
use App\Notifications\ComparativeValidationFailed;
use Illuminate\Bus\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification; // Asegúrate de importar Notification
use Throwable;

class ComparisonSubmissionController extends Controller
{
    public function __invoke(Request $request, Question $question): RedirectResponse
    {
        Gate::authorize('submitForValidation', $question);

        if (!in_array($question->status, ['borrador', 'necesita_correccion', 'fallo_comparativo'])) {
            return back()->with('error', 'Esta pregunta no se puede enviar en su estado actual.');
        }

        try {
            DB::transaction(function () use ($question) {
                $question->update(['status' => 'en_validacion_comparativa']);

                Bus::batch([
                    new ValidateQuestionJob($question->id, 'chatgpt', null),
                    new ValidateQuestionJob($question->id, 'gemini', null),
                ])
                ->then(function (Batch $batch) use ($question) {
                    // Se ejecuta si NINGÚN job falló.
                    $question->fresh()->update(['status' => 'revisado_comparativo']);
                    $question->author->notify(new ComparativeValidationCompleted($question, true));
                    // Notificamos a los validadores
                    $validators = User::where('role', 'validador')->get();
                    if ($validators->isNotEmpty()) {
                        Notification::send($validators, new \App\Notifications\ReviewRequestForValidators($question, 'Ambos Motores'));
                    }
                })
                ->catch(function (Batch $batch, Throwable $e) use ($question) {
                    // Solo para errores catastróficos.
                    $question->fresh()->update(['status' => 'fallo_comparativo']);
                    $question->author->notify(new ComparativeValidationFailed($question, "Error del sistema de colas."));
                })
                ->finally(function (Batch $batch) use ($question) {
                    
                    // Si hubo fallos, pero el lote no fue cancelado.
                    if ($batch->hasFailures() && !$batch->cancelled()) {
                        $question->fresh()->update(['status' => 'revisado_comparativo']); // Aún así lo marcamos como revisado
                        
                        // Enviamos la notificación de "éxito parcial". La notificación
                        // no necesita el mensaje de error, ya que la vista lo leerá de la BD.
                        $question->author->notify(new ComparativeValidationCompleted($question, false)); // false = éxito parcial

                        // También notificamos a los validadores.
                        $validators = User::where('role', 'validador')->get();
                        if ($validators->isNotEmpty()) {
                            Notification::send($validators, new \App\Notifications\ReviewRequestForValidators($question, 'Uno o más motores'));
                        }
                    }
                })
                ->name('Validacion Comparativa para Pregunta #' . $question->id)
                ->onQueue('default')
                ->allowFailures()
                ->dispatch();
            });

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'No se pudo iniciar el proceso de validación comparativa.');
        }

        return redirect()->route('questions.index')
            ->with('status', '¡Validación comparativa iniciada!');
    }
}