<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected ?string $apiKey; 
    protected string $model;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->model = config('services.openai.embedding_model', 'text-embedding-3-small');
        $this->baseUrl = 'https://api.openai.com/v1/embeddings';

        if (empty($this->apiKey)) {
            Log::warning('OPENAI_API_KEY no está configurada. La generación de embeddings estará deshabilitada.');
        }
    }

    /**
     * Comprueba si el servicio de embeddings de OpenAI está configurado y disponible.
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Genera un vector de embedding para un texto dado.
     *
     * @param string $text
     * @return array|null El vector de embedding como un array de floats, o null si falla o no está disponible.
     */
    public function generateEmbedding(string $text): ?array
    {
        if (!$this->isAvailable()) {
            return null; // No se puede generar embedding si la API key no está.
        }
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'input' => $text,
                'model' => $this->model,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'][0]['embedding'])) {
                    return $data['data'][0]['embedding'];
                }
            }

            Log::error('Error al generar embedding con OpenAI:', [
                'status' => $response->status(),
                'response' => $response->body(),
                'text_input' => $text
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Excepción al llamar a la API de OpenAI Embeddings: ' . $e->getMessage(), ['text_input' => $text]);
            return null;
        }
    }

    /**
     * Calcula la similitud de coseno entre dos vectores.
     *
     * @param array $vec1
     * @param array $vec2
     * @return float Similitud de coseno entre 0 y 1.
     */
    public static function cosineSimilarity(array $vec1, array $vec2): float
    {
        if (empty($vec1) || empty($vec2) || count($vec1) !== count($vec2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] * $vec1[$i];
            $magnitude2 += $vec2[$i] * $vec2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * COMENTARIO: Fallback para similitud léxica (palabras comunes).
     * Calcula la similitud Jaccard entre dos textos.
     * Cuanto mayor sea el número de palabras comunes, mayor será la similitud.
     * @param string $text1
     * @param string $text2
     * @return float Similitud Jaccard entre 0 y 1.
     */
    public static function lexicalSimilarity(string $text1, string $text2): float
    {
        $words1 = array_unique(array_filter(preg_split('/\W+/', strtolower($text1))));
        $words2 = array_unique(array_filter(preg_split('/\W+/', strtolower($text2))));

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));

        return $intersection / $union;
    }
}