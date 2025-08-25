<?php

namespace App\Services;

use Throwable;

class ApiErrorExtractorService
{
    /**
     * Extrae un mensaje de error amigable de una excepción de API.
     *
     * @param Throwable $exception
     * @return string
     */
    public function getFriendlyMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        // --- LÓGICA DE EXTRACCIÓN PARA ERRORES DE OPENAI ---
        // Los errores de OpenAI a menudo vienen en formato JSON dentro del mensaje.
        if (str_contains($message, 'OpenAI')) {
            // Intentamos encontrar el mensaje JSON dentro de la cadena
            preg_match('/(?<=\{)(.|\s)+(?=\})/s', $message, $matches);
            if (isset($matches[0])) {
                $errorData = json_decode('{' . $matches[0] . '}', true);
                // Buscamos el mensaje específico dentro del objeto de error
                if (isset($errorData['error']['message'])) {
                    return $errorData['error']['message'];
                }
            }
        }
        
        // --- LÓGICA PARA OTROS ERRORES CONOCIDOS (EJ. GEMINI) ---
        if (str_contains($message, 'The model is overloaded')) {
            return 'El servicio de IA está sobrecargado. Por favor, intenta de nuevo más tarde.';
        }

        // --- MENSAJE GENÉRICO POR DEFECTO ---
        // Si no es un error de API conocido, devolvemos una parte del mensaje original.
        return \Illuminate\Support\Str::limit($message, 150);
    }
}