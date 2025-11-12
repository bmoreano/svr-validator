<?php
namespace App\Services;

use App\Models\Question; 
use Illuminate\Support\Facades\Auth;

class QuestionVersioningService
{
    /**
     * Crea una nueva revisión para una pregunta.
     *
     * @param Question $question El modelo de la pregunta con sus datos actualizados.
     * @param string $reason La razón del cambio (ej. "Creación inicial").
     */
    public function createRevision(Question $question, string $reason): void
    {
        // Preparamos el snapshot de las opciones
        $optionsSnapshot = $question->options->map(function ($option) {
            return [
                'text' => $option->option_text,
                'is_correct' => $option->is_correct,
                'argumentation' => $option->argumentation,
            ];
        })->toArray();

        // Creamos el registro de la revisión
        $question->revisions()->create([
            'user_id' => Auth::id(),
            'code' => $question->code,
            'stem' => $question->stem,
            'bibliography' => $question->bibliography,
            'grado_dificultad' => $question->grado_dificultad,
            'poder_discriminacion' => $question->poder_discriminacion,
            'options_snapshot' => $optionsSnapshot,
            'change_reason' => $reason,
        ]);
    }
}