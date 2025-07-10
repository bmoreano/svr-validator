<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider; // Opcional, pero recomendado
use Google\Client as GoogleClient; // Importamos la clase base del cliente de Google
use Gemini\Client as GeminiClient; // Importamos el cliente específico de Gemini
use Gemini\Laravel\GeminiServiceProvider as OfficialGeminiServiceProvider; // Importamos el service provider oficial si se usa el paquete de Laravel

class GeminiServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Registra los servicios de la aplicación.
     *
     * Este método se utiliza para vincular el cliente de Gemini en el contenedor
     * de servicios de Laravel. Usamos un singleton para asegurarnos de que solo
     * se cree una instancia del cliente por cada ciclo de solicitud,
     * lo cual es más eficiente.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(GeminiClient::class, function ($app) {
            // Obtenemos la API Key desde el archivo de configuración 'services.php',
            // que a su vez la lee del archivo .env.
            $apiKey = config('services.gemini.api_key');

            // Verificamos que la API Key esté configurada para evitar errores.
            if (is_null($apiKey)) {
                throw new \Exception('Gemini API Key is not configured. Please add it to your .env file.');
            }

            // Instanciamos y devolvemos el cliente de Gemini.
            // El paquete oficial `google-gemini-php/client` proporciona un helper `Gemini::client()`.
            return \Gemini::client($apiKey);
        });
    }

    /**
     * Obtiene los servicios proporcionados por el proveedor.
     *
     * Al implementar DeferrableProvider, le decimos a Laravel que no cargue
     * este service provider en cada solicitud, sino solo cuando uno de los
     * servicios que "provee" sea realmente necesario. Esto mejora el rendimiento.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            GeminiClient::class,
        ];
    }
}