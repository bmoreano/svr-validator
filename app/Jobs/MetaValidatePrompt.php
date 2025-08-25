<?php

namespace App\Jobs;

use App\Models\Prompt;
use App\Models\User;
use App\Notifications\PromptMetaValidationFailed;
use App\Services\PromptBuilderService;
use App\Services\ValidationParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use OpenAI\Client as OpenAIClient;
use Gemini\Client as GeminiClient;
use Throwable;

class MetaValidatePrompt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Prompt $prompt;

    /**
     * Crea una nueva instancia del job.
     */
    public function __construct(Prompt $prompt)
    {
        $this->prompt = $prompt;
    }

    /**
     * Ejecuta el job de meta-validación.
     */
    public function handle(
        OpenAIClient $openAIClient,
        GeminiClient $geminiClient,
        PromptBuilderService $builder,
        ValidationParserService $parser
    ): void {
        $jsonResponse = null;

        try {
            // Construimos el meta-prompt. Es un array de mensajes por defecto.
            $metaPrompt = $builder->buildMetaValidationPrompt($this->prompt->content);

            // ==========================================================
            // --- LÓGICA COMPLETA DEL SWITCH PARA MANEJAR CADA MOTOR ---
            // ==========================================================
            switch ($this->prompt->ai_engine) {
                case 'chatgpt':
                    $response = $openAIClient->chat()->create([
                        'model' => 'gpt-4o', // Usamos un modelo potente para la evaluación
                        'response_format' => ['type' => 'json_object'],
                        'messages' => $metaPrompt,
                    ]);
                    $jsonResponse = $response->choices[0]->message->content;
                    break;
                
                case 'gemini':
                    // Convertimos el array de mensajes a un string simple para Gemini.
                    $geminiPromptString = "SYSTEM: {$metaPrompt[0]['content']}\n\nUSER: {$metaPrompt[1]['content']}";
                    
                    $model = $geminiClient->generativeModel('gemini-1.5-flash');
                    $response = $model->generateContent($geminiPromptString);
                    $jsonResponse = $response->text();
                    break;
                
                default:
                    // Si se añade un nuevo motor en el futuro pero no aquí, fallará de forma segura.
                    throw new \Exception("Motor de IA no soportado: " . $this->prompt->ai_engine);
            }
            
            if (is_null($jsonResponse)) {
                 throw new \Exception("La respuesta de la API de IA estaba vacía.");
            }

            // Limpiamos la respuesta y la decodificamos
            $cleanedJson = $parser->cleanJsonResponse($jsonResponse);
            $evaluation = json_decode($cleanedJson, true);

            if (!is_array($evaluation) || !isset($evaluation['scores'], $evaluation['is_safe_to_run'])) {
                throw new \Exception("El JSON de respuesta de la IA no contiene la estructura esperada. Respuesta recibida: " . $jsonResponse);
            }

            // Evaluamos el prompt según los umbrales de calidad
            $scores = $evaluation['scores'];
            $isApproved = $evaluation['is_safe_to_run'] === true
                          && ($scores['security'] ?? 0) >= 4
                          && ($scores['objectivity'] ?? 0) >= 3
                          && ($scores['clarity'] ?? 0) >= 3;

            if ($isApproved) {
                $this->prompt->update([
                    'status' => 'active',
                    'review_feedback' => 'Aprobado automáticamente. Evaluación: ' . ($evaluation['overall_assessment'] ?? 'OK'),
                    'is_active' => true,
                ]);
            } else {
                $this->prompt->update([
                    'status' => 'rejected',
                    'review_feedback' => 'Rechazado. Razón: ' . ($evaluation['overall_assessment'] ?? 'Puntuaciones bajas') . '. Sugerencia: ' . ($evaluation['suggested_improvement'] ?? 'Revisar claridad y formato.'),
                    'is_active' => false,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Fallo en la meta-validación del prompt #{$this->prompt->id}", [
                'original_response' => $jsonResponse ?? 'No se recibió respuesta.',
                'exception' => $e
            ]);
            $this->fail($e);
        }
    }
    
    /**
     * Maneja un fallo permanente en el job.
     */
    public function failed(Throwable $exception): void
    {
        Log::critical("La meta-validación del prompt #{$this->prompt->id} ha fallado permanentemente.", [
            'prompt_name' => $this->prompt->name,
            'exception_message' => $exception->getMessage(),
        ]);
        
        $this->prompt->update([
            'status' => 'rejected',
            'is_active' => false,
            'review_feedback' => 'La revisión automática falló debido a un error del sistema. Requiere revisión manual. Error: ' . $exception->getMessage(),
        ]);

        $administrators = User::where('role', 'administrador')->get();
        if ($administrators->isNotEmpty()) {
            Notification::send($administrators, new PromptMetaValidationFailed($this->prompt, $exception->getMessage()));
        }
    }
}
