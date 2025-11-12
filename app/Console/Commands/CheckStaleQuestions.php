<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Notifications\StaleQuestionNotification;
use Illuminate\Support\Carbon;

class CheckStaleQuestions extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'app:check-stale-questions';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Busca preguntas en borrador por más de 72h, las marca como "en_espera" y notifica al autor.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Buscando preguntas estancadas...');

        $staleQuestions = Question::where('status', 'borrador')
            ->where('created_at', '<=', Carbon::now()->subHours(72))
            ->with('author') // Cargar al autor
            ->get();

        if ($staleQuestions->isEmpty()) {
            $this->info('No se encontraron preguntas estancadas.');
            return 0;
        }

        $this->info("Se encontraron {$staleQuestions->count()} preguntas estancadas. Procesando...");

        foreach ($staleQuestions as $question) {
            // 1. Cambiar el estado de la pregunta
            $question->status = 'en_espera';
            $question->save();

            // 2. Notificar al autor (si el autor existe y está activo)
            if ($question->author && $question->author->activo) {
                $question->author->notify(new StaleQuestionNotification($question));
                $this->line("Notificado autor: {$question->author->email} por pregunta #{$question->id}");
            }
        }

        $this->info('Proceso completado.');
        return 0;
    }
}