<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Importamos la clase del cliente de OpenAI que vamos a registrar.
use OpenAI\Client as OpenAIClient; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Este método se usa para registrar 'bindings' en el contenedor de servicios de Laravel.
     * Se ejecuta antes de que la aplicación esté completamente "arrancada".
     */
    public function register(): void
    {
        // --- REGISTRO DEL CLIENTE DE OPENAI ---
        // Le decimos a Laravel cómo construir un objeto 'OpenAI\Client' cada vez que sea necesario.
        // Usamos el método 'singleton' para que se cree una única instancia del cliente
        // por cada ciclo de vida de la aplicación (ej. por cada job que se procese).
        $this->app->singleton(OpenAIClient::class, function ($app) {
            
            // Obtenemos la API Key desde el archivo de configuración services.php,
            // que a su vez la lee desde el archivo .env.
            $apiKey = config('services.openai.api_key');

            // Añadimos una comprobación de seguridad para fallar rápido si la clave no está configurada.
            if (is_null($apiKey)) {
                throw new \InvalidArgumentException('OpenAI API Key is missing. Please add it to your .env file as OPENAI_API_KEY.');
            }
            
            // Usamos el helper del paquete para instanciar el cliente con la API Key.
            return \OpenAI::client($apiKey);
        });

        // =======================================================
        // ==              AQUÍ ESTÁ LA CORRECCIÓN              ==
        // =======================================================
        // Registrar el paquete 'ide-helper' solo en el entorno de desarrollo local.
        // Usamos el método `environment()` para comprobar si el entorno es 'local'.
        if ($this->app->environment('local')) {
            // Si estamos en local, registramos el Service Provider de ide-helper.
            // Esto evita que se cargue en producción, donde no es necesario.
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * Este método se ejecuta después de que todos los service providers han sido registrados.
     * Es el lugar ideal para registrar observers, gates, policies, etc.
     */
    public function boot(): void
    {
        // No se necesitan modificaciones aquí para esta solución.
    }
}