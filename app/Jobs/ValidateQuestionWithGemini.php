<?php

namespace App\Jobs;

// ... otras importaciones

use App\Models\Question;
use App\Services\PromptBuilderService;
use App\Services\ValidationParserService;
use Gemini\Client as GeminiClient; // Importamos la clase del cliente
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ValidateQuestionWithGemini implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels, InteractsWithQueue;

    public function __construct(public Question $question) {}

    /**
     * Ejecuta el job.
     *
     * Laravel automáticamente inyectará las dependencias especificadas en los
     * argumentos del método handle (inyección de método).
     */
    public function handle(
        GeminiClient $geminiClient, // <-- ¡AQUÍ ESTÁ LA MAGIA!
        PromptBuilderService $promptBuilder, 
        ValidationParserService $parser
    ): void
    {
        $prompt = $promptBuilder->buildForQuestion($this->question);

        try {
            // Ya no necesitas instanciar el cliente aquí. ¡Ya está listo para usar!
            // $geminiClient = \Gemini::client(...); // <-- ESTA LÍNEA SE ELIMINA

            $result = $geminiClient->geminiPro()->generateContent($prompt);

            // ... resto de la lógica
            
        } catch (\Exception $e) {
            // ... manejo de errores
        }
    }
}