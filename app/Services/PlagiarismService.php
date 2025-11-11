<?php

namespace App\Services;

class PlagiarismService
{
    /**
     * Busca frases en la web para detectar posible plagio.
     *
     * @param array $phrases Frases clave a buscar
     * @return array URLs con posible plagio
     */
    public function searchWebForPhrases(array $phrases): array
    {
        // Simulación: retorna URLs si hay más de 3 frases
        return (count($phrases) > 3) ? ['https://example.com/plagiarized'] : [];
    }

    /**
     * Realiza un chequeo profundo de plagio usando una API externa.
     *
     * @param string $text Texto a analizar
     * @return array Reporte de plagio
     */
    public function deepPlagiarismCheck(string $text): array
    {
        // Simulación: 10% de plagio aleatorio
        return ['status' => 'success', 'percent_copied' => rand(5, 15), 'sources' => ['https://source.com']];
    }
}