<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use OpenAI\Client as OpenAIClient;
use Gemini\Client as GeminiClient;
use \Exception;

class AiModelHealthService
{
    protected $openAiClient;
    protected $geminiClient;

    public function __construct(OpenAIClient $openAiClient, GeminiClient $geminiClient)
    {
        $this->openAiClient = $openAiClient;
        $this->geminiClient = $geminiClient;
    }

    /**
     * Verifica la validez funcional de las API keys de IA y cachea el resultado.
     *
     * @return array [ 'chatgpt' => bool, 'gemini' => bool ]
     */
    public function checkModels(): array
    {
        // 1. Verificar ChatGPT (OpenAI)
        $chatgptStatus = Cache::remember('ai_health_chatgpt', now()->addHours(1), function () {
            // Si la key no existe en config, está inactiva
            if (empty(config('openai.api_key'))) {
                return false;
            }
            // Intentar una llamada ligera a la API
            try {
                $this->openAiClient->models()->list(); // Una llamada simple para probar la key
                return true;
            } catch (Exception $e) {
                return false; // Falla si la key es inválida o hay un error de red
            }
        });

        // 2. Verificar Gemini (Google)
        $geminiStatus = Cache::remember('ai_health_gemini', now()->addHours(1), function () {
            // Si la key no existe en config, está inactiva
            if (empty(config('gemini.api_key'))) {
                return false;
            }
            // Intentar una llamada ligera a la API
            try {
                $this->geminiClient->geminiPro()->generateContent('test'); // Una llamada simple para probar la key
                return true;
            } catch (Exception $e) {
                return false; // Falla si la key es inválida
            }
        });

        return [
            'chatgpt' => $chatgptStatus,
            'gemini' => $geminiStatus,
        ];
    }
}