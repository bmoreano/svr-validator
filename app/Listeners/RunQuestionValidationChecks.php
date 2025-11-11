<?php

namespace App\Listeners;

use App\Events\QuestionSubmittedForValidation;
use App\Jobs\ValidateQuestionJob; // Importar el Job
use Illuminate\Contracts\Queue\ShouldQueue; // COMENTARIO: Implementar ShouldQueue para que el listener se ejecute en cola.
use Illuminate\Queue\InteractsWithQueue;

class RunQuestionValidationChecks implements ShouldQueue 
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QuestionSubmittedForValidation $event): void
    {
        // COMENTARIO: Despachar el Job a la cola.
        ValidateQuestionJob::dispatch($event->question);
        \Log::info("Despachado ValidateQuestionJob para pregunta ID: {$event->question->id}");
    }
}