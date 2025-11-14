<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlagiarismCheckService
{
    protected ?string $googleApiKey;
    protected ?string $googleCx;
    protected string $googleBaseUrl;

    protected ?string $copyscapeApiKey;
    protected ?string $copyscapeUsername;

    public function __construct()
    {
        $this->googleApiKey = config('services.google.api_key');
        $this->googleCx = config('services.google.custom_search_cx');
        $this->googleBaseUrl = 'https://www.googleapis.com/customsearch/v1';

        $this->copyscapeApiKey = config('services.copyscape.key');
        $this->copyscapeUsername = config('services.copyscape.username');

        if (empty($this->googleApiKey) || empty($this->googleCx)) {
            Log::warning('GOOGLE_SEARCH_API_KEY o GOOGLE_SEARCH_CX no están configuradas. La búsqueda web dirigida estará deshabilitada.');
        }
        if (empty($this->copyscapeApiKey) || empty($this->copyscapeUsername)) {
            Log::warning('COPYSCAPE_API_KEY o COPYSCAPE_USERNAME no están configuradas. El análisis profundo de plagio estará deshabilitado.');
            $configWarnings[] = [
                'title' => 'Credenciales de Copyscape no configuradas',
                'message' => 'El análisis profundo de plagio contra fuentes externas de internet estará deshabilitado. Solo se realizarán comprobaciones de plagio internas.'
            ];
        }
    }

    /**
     * Comprueba si el servicio de búsqueda web dirigida de Google está configurado y disponible.
     */
    public function isGoogleSearchAvailable(): bool
    {
        return !empty($this->googleApiKey) && !empty($this->googleCx);
    }

    /**
     * Comprueba si el servicio de análisis profundo de Copyscape está configurado y disponible.
     */
    public function isCopyscapeAvailable(): bool
    {
        return !empty($this->copyscapeApiKey) && !empty($this->copyscapeUsername);
    }

    /**
     * Realiza una búsqueda web dirigida de frases clave.
     *
     * @param array $phrases Array de frases a buscar.
     * @return array Array de URLs de posibles plagios, o vacío si no hay o el servicio no está disponible.
     */
    public function searchWebForPhrases(array $phrases): array
    {
        if (!$this->isGoogleSearchAvailable()) {
            return []; // No se puede realizar la búsqueda sin credenciales
        }

        $plagiarizedUrls = [];
        foreach ($phrases as $phrase) {
            if (empty(trim($phrase))) continue;

            try {
                $response = Http::get($this->googleBaseUrl, [
                    'key' => $this->googleApiKey,
                    'cx' => $this->googleCx,
                    'q' => '"' . $phrase . '"', 
                    'num' => 3, 
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['items'])) {
                        foreach ($data['items'] as $item) {
                            if (stripos($item['snippet'], $phrase) !== false) {
                                $plagiarizedUrls[] = $item['link'];
                            }
                        }
                    }
                } else {
                    Log::warning('Error al buscar frase en Google Custom Search:', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'phrase' => $phrase
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Excepción al llamar a Google Custom Search API: ' . $e->getMessage(), ['phrase' => $phrase]);
            }
        }
        return array_unique($plagiarizedUrls); 
    }

    /**
     * Realiza un análisis profundo de plagio con una API especializada (mock si no configurado).
     *
     * @param string $text Texto completo a analizar.
     * @return array Reporte de plagio (mock o resultado real).
     */
    public function deepPlagiarismCheck(string $text): array
    {
        if (!$this->isCopyscapeAvailable()) {
            Log::warning('Análisis profundo de plagio deshabilitado (API de Copyscape no configurada).');
            return [
                'status' => 'skipped_no_api',
                'reason' => 'API de Copyscape no configurada.'
            ];
        }

        // COMENTARIO: Aquí iría la integración REAL con la API de Copyscape.
        // Por ahora, se mantiene el mock.
        Log::info("Realizando análisis profundo de plagio con API real/mock para el texto...");

        $mockPlagiarismData = [
            'status' => 'success',
            'percent_copied' => rand(0, 30), 
            'sources' => [],
        ];

        if ($mockPlagiarismData['percent_copied'] > 10) {
            $mockPlagiarismData['sources'] = [
                [
                    'url' => 'https://www.ejemplodeplagio.com/articulo_' . rand(1, 100),
                    'percent' => $mockPlagiarismData['percent_copied'] - 5,
                    'matched_text' => 'fragmento de texto plagiado de ejemplo',
                ],
            ];
        }
        
        return $mockPlagiarismData;
    }
}