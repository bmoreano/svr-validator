<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionReadyForReview;
use App\Services\PromptBuilderService;
use App\Services\ValidationParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
// --- IMPORTAMOS LA CLASE DEL CLIENTE DIRECTAMENTE ---
use OpenAI\Client as OpenAIClient;

class ValidateQuestionWithChatGpt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * El número de veces que el job puede ser reintentado en caso de fallo.
     * @var int
     */
    public $tries = 3;

    /**
     * El número de segundos que el job puede correr antes de ser marcado como fallido (timeout).
     * @var int
     */
    public $timeout = 300; // 5 minutos

    /**
     * Crea una nueva instancia del job.
     *
     * @param \App\Models\Question $question
     */
    public function __construct(public Question $question)
    {
        // Se asegura de que el job solo se procese en la cola 'validations'
        $this->onQueue('validations');
    }

    /**
     * Ejecuta el job de validación con ChatGPT.
     *
     * @param \OpenAI\Client $openAIClient El cliente de OpenAI, inyectado por el contenedor de servicios de Laravel.
     * @param \App\Services\PromptBuilderService $promptBuilder
     * @param \App\Services\ValidationParserService $parser
     * @return void
     * @throws \Throwable
     */
    public function handle(OpenAIClient $openAIClient, PromptBuilderService $promptBuilder, ValidationParserService $parser): void
    {
        try {
            // La variable $openAIClient ya está instanciada y lista para usarse gracias a la inyección de dependencias.
            
            // Construimos el prompt específico para el modelo de chat.
            $messages = $promptBuilder->buildForChatGpt($this->question);

            // Realizamos la llamada a la API de Chat Completions.
            $response = $openAIClient->chat()->create([
                'model' => 'gpt-4-turbo-preview',
                'response_format' => ['type' => 'json_object'], // Solicitamos una respuesta en formato JSON garantizado.
                'messages' => $messages,
            ]);

            $jsonResponse = $response->choices[0]->message->content;

            // Usamos nuestro servicio para parsear y guardar la validación en la base de datos.
            if ($parser->saveAiValidation($this->question, $jsonResponse)) {
                $this->question->update(['status' => 'revisado_por_ai']);
                $this->notifyValidators();
            } else {
                throw new \Exception("El servicio de parseo falló al procesar la respuesta de la IA para la pregunta #{$this->question->id}.");
            }

        } catch (\Exception $e) {
            // Si ocurre cualquier error, lo registramos, actualizamos el estado
            // de la pregunta y marcamos el job como fallido.
            $this->question->update(['status' => 'error_validacion_ai']);
            Log::error("Error en el Job ValidateQuestionWithChatGpt para la pregunta #{$this->question->id}: " . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Busca a los usuarios con el rol de validador y les envía la notificación por correo.
     */
    private function notifyValidators(): void
    {
        $validators = User::where('role', 'validador')->get();
        
        if ($validators->isNotEmpty()) {
            Notification::send($validators, new QuestionReadyForReview($this->question));
        }
    }
}