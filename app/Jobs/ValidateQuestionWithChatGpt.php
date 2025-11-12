<?php

namespace App\Jobs;

use App\Models\Prompt;
use App\Models\Question;
use App\Models\User;
use App\Notifications\AiValidationCompleted;
use App\Notifications\AiValidationFailed;
use App\Services\PromptBuilderService;
use App\Services\ValidationParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI\Client as OpenAIClient;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReviewRequestForValidators;

class ValidateQuestionWithChatGpt implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;
    public int $timeout = 600; // 10 minutos
    public ?int $questionId;
    public ?int $prompt_id;

    // El constructor ahora espera un ID de pregunta, no el modelo completo.
    public function __construct(int $questionId, ?int $prompt_id = null)
    {
        $this->questionId = $questionId;
        $this->prompt_id = $prompt_id;
    }

    /**
     * Ejecuta el job.
     */
    public function handle(
        OpenAIClient $openAIClient,
        PromptBuilderService $promptBuilder,
        ValidationParserService $parser
    ): void {
        $question = Question::findOrFail($this->questionId);
        Log::warning("Job de ChatGPT ValidateQuestionWithChatGpt->handle con #{$question->id} porque su estado es '{$question->status}'.");
                $allowedStatuses = ['en_validacion_ai', 'en_validacion_comparativa'];
        if (!in_array($question->status, $allowedStatuses)) {
        //if ($question->status !== 'en_validacion_ai') {
            Log::warning("Job de ChatGPT cancelado para la pregunta #{$question->id} porque su estado es '{$question->status}'.");
            return;
        }

        try {
            Log::warning("Job de ChatGPT ValidateQuestionWithChatGpt->handle->try con #{$question->id} porque su estado es '{$question->status}'.");
            $messages = $promptBuilder->buildForChatGpt(pregunta: $question, prompt_id: $this->prompt_id, criteriaBatch: null);
            $response = $openAIClient->chat()->create([
                'model' => 'gpt-4o',
                'response_format' => ['type' => 'json_object'],
                'messages' => $messages,
            ]);

            Log::warning("Job de ChatGPT ValidateQuestionWithChatGpt->handle con #{$question->id} porque su estado es '{$question->status}'.");
            $jsonResponse = $response->choices[0]->message->content;
            $engineName = 'ChatGPT';
            
            if ($parser->saveAiValidation($question, $jsonResponse,$engineName)) {
                
                // --- INICIO DE LA MEJORA (Lógica de Lote) ---
                // Solo actualiza el estado y notifica al autor si no es parte de un lote
                // o si el lote ya ha terminado.
                if (!$this->batch() || $this->batch()->finished()) {
                    $question->update(['status' => 'revisado_por_ai']);
                    $question->author->notify(new AiValidationCompleted($question, $engineName));
                }
                // --- FIN DE LA MEJORA ---

                $validators = User::where('role', 'validador')->get();
                if ($validators->isNotEmpty()) {
                    Notification::send($validators, new ReviewRequestForValidators($question, $engineName));
                }
                Log::info("Pregunta #{$question->id} validada con éxito por ChatGPT.");
            } else {
                $this->fail(new \Exception("El ValidationParserService falló al procesar la respuesta de ChatGPT para la pregunta #{$question->id}."));
            }
        } catch (\Exception $e) {
            Log::error("Excepción en Job ChatGPT para la pregunta #{$question->id}", [
                'exception' => $e
            ]);
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
        logger('ValidateQuestionWithChatGpt' . ' - ' . 'backoff');
        return [60, 300, 600];
    }

    /**
     * Maneja un fallo en el job después de todos los reintentos.
     */
    public function failed(\Throwable $exception): void
    {
        $question = Question::findOrFail($this->questionId);

        // --- INICIO DE LA MEJORA (Lógica de Lote) ---
        // Solo revierte el estado si el lote ha sido cancelado
        // o si no era parte de un lote.
        if (!$this->batch() || $this->batch()->cancelled()) {
            $question->update(['status' => 'borrador']);
        }
        // --- FIN DE LA MEJORA ---

        Log::critical("El Job de ChatGPT para la pregunta #{$question->id} ha fallado permanentemente.", [
            'exception_message' => $exception->getMessage()
        ]);

        // --- INICIO DE LA MEJORA (Lógica de Lote) ---
        // Solo notifica al autor individualmente si NO es parte de un lote.
        // (El lote enviará su propia notificación de fallo).
        if (!$this->batch()) {
            $question->author->notify(new AiValidationFailed(
                $question,
                'ChatGPT',
                $exception->getMessage()
            ));
        }
    }
}