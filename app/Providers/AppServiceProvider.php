<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// --- IMPORTACIONES NECESARIAS PARA LAS VINCULACIONES ---
use App\Services\AiValidatorFactory;
use App\Services\AiValidators\ChatGptValidator;
use App\Services\AiValidators\GeminiValidator;
use App\Services\PromptBuilderService;
use OpenAI\Client as OpenAIClient;
use Gemini\Client as GeminiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Este método es el lugar central para registrar clases en el
     * Service Container de Laravel. Le enseñamos a Laravel cómo construir
     * nuestras clases de servicio complejas para que pueda inyectarlas
     * automáticamente donde las necesitemos (ej. en los constructores o
     * métodos 'handle' de nuestros Jobs).
     */
    public function register(): void
    {
        // ==========================================================
        // VINCULACIÓN DE CLIENTES DE API EXTERNOS
        // ==========================================================
        
        // Le decimos a Laravel cómo construir el cliente de OpenAI.
        // Usamos un 'singleton' para que se cree una sola instancia por solicitud.
        logger()->info("construir el cliente de OpenAI");
        logger()->info("");
        $this->app->singleton(OpenAIClient::class, function (Application $app) {
            $apiKey = $app['config']->get('services.openai.api_key');
            if (empty($apiKey)) {
                throw new \InvalidArgumentException('OpenAI API Key is not configured in services.php or .env file.');
            }
            return \OpenAI::client($apiKey);
        });
        
        // El paquete `google-gemini-php/laravel` ya registra su propio cliente,
        // por lo que no es estrictamente necesario volver a vincularlo aquí.
        // Sin embargo, dejarlo de forma explícita no causa problemas y puede
        // ayudar a la claridad del código.
        logger()->info("construir el cliente de Gemini");
        logger()->info("");
        $this->app->singleton(GeminiClient::class, function (Application $app) {
            $apiKey = $app['config']->get('services.gemini.api_key'); // O config('gemini.api_key')
            if (empty($apiKey)) {
                throw new \InvalidArgumentException('Gemini API Key is not configured in services.php or .env file.');
            }
            return \Gemini::client($apiKey);
        });

        // ==========================================================
        // VINCULACIÓN DE NUESTRAS CLASES DE SERVICIO (PATRÓN STRATEGY)
        // ==========================================================

        // Le enseñamos a Laravel cómo construir nuestro validador concreto para ChatGPT.
        // Este necesita el cliente de OpenAI y el PromptBuilderService.
        logger()->info("validador concreto para ChatGPT.");
        logger()->info("");
        $this->app->singleton(ChatGptValidator::class, function (Application $app) {
            return new ChatGptValidator(
                $app->make(OpenAIClient::class),
                $app->make(PromptBuilderService::class)
            );
        });

        // Le enseñamos a Laravel cómo construir nuestro validador concreto para Gemini.
        // Este necesita el cliente de Gemini y el PromptBuilderService.
        logger()->info("validador concreto para Gemini.");
        logger()->info("");
        $this->app->singleton(GeminiValidator::class, function (Application $app) {
            return new GeminiValidator(
                $app->make(GeminiClient::class),
                $app->make(PromptBuilderService::class)
            );
        });

        // Finalmente, registramos nuestro Factory, que depende de las vinculaciones anteriores.
        $this->app->singleton(AiValidatorFactory::class, function (Application $app) {
            return new AiValidatorFactory();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En este caso, no necesitamos registrar nada en el método 'boot'.
    }
}