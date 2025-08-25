<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use App\Notifications\AiValidationCompleted;
use App\Notifications\AiValidationFailed;
use App\Services\PromptBuilderService;
use App\Services\ValidationParserService;
use App\Notifications\ReviewRequestForValidators;
use Illuminate\Bus\Batchable; // Asegúrate de tener este trait
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Gemini\Client as GeminiClient;
use Illuminate\Support\Facades\Notification;

class ValidateQuestionWithGemini implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;
    public int $timeout = 600;

    public int $questionId; // Propiedad para el ID
    public ?int $prompt_id;

    // El constructor ahora espera un ID de pregunta, no el modelo completo.
    public function __construct(?int $questionId = null, ?int $prompt_id = null)
    {
        $this->questionId = $questionId;
        $this->prompt_id = $prompt_id;
    }


    public function handle(
        GeminiClient $geminiClient,
        PromptBuilderService $promptBuilder,
        ValidationParserService $parser
    ): void {
        $question = Question::findOrFail($this->questionId);
        if ($question->status !== 'en_validacion_ai' && $question->status !== 'en_validacion_comparativa') {
            Log::warning("Job de Gemini cancelado para la pregunta #{$question->id} porque su estado es '{$question->status}'.");
            return;
        }

        try {
            $prompt = $promptBuilder->buildForGemini($question, $this->prompt_id);

            // Esto es a prueba de futuro; si mañana quieres usar 'gemini-1.5-pro-latest', solo cambias este string.
            $model = $geminiClient->generativeModel('gemini-2.5-flash'); // O 'gemini-pro' si prefieres
            $result = $model->generateContent($prompt);

            $jsonResponse = $result->text();

            if ($parser->saveAiValidation($question, $jsonResponse, 'gemini')) {
                if (!$this->batch() || $this->batch()->finished()) {
                    $question->update(['status' => 'revisado_por_ai']);
                    $question->author->notify(new AiValidationCompleted($question, 'Gemini'));
                }

                $validators = User::where('role', 'validador')->get();
                if ($validators->isNotEmpty()) {
                    Notification::send($validators, new ReviewRequestForValidators($question, 'ChatGPT'));
                }
                Log::info("Pregunta #{$question->id} validada con éxito por Gemini.");
            } else {
                $this->fail(new \Exception("El ValidationParserService falló al procesar la respuesta de Gemini para la pregunta #{$question->id}."));
            }
        } catch (\Exception $e) {
            Log::error("Excepción en Job Gemini para la pregunta #{$question->id}", ['exception' => $e]);
            $this->fail($e);
        }
    }
    /**
     * Calcula el número de segundos a esperar antes de reintentar el job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        // Espera 1 minuto, luego 5 minutos, luego 10 minutos
        logger('ValidateQuestionWithGemini' . '  ' . 'backoff');
        return [60, 300, 600];
    }

    public function failed(\Throwable $exception): void
    {
        $question = Question::findOrFail($this->questionId);
        if (!$this->batch() || $this->batch()->cancelled()) {
            $question->update(['status' => 'borrador']);
        }

        Log::critical("El Job de Gemini para la pregunta #{$question->id} ha fallado permanentemente.", ['exception_message' => $exception->getMessage()]);

        // La notificación de fallo del lote se maneja en el controlador,
        // pero podemos mantener una notificación individual como respaldo si no es parte de un lote.
        if (!$this->batch()) {
            $question->author->notify(new AiValidationFailed($question, 'Gemini', $exception->getMessage()));
        }
    }
}
