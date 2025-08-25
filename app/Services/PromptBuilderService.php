<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\Prompt;
use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Class PromptBuilderService
 *
 * Responsabilidad: Construir los prompts que se enviarán a los modelos de IA.
 * Esta clase ahora soporta la división de criterios en lotes.
 */
class PromptBuilderService
{
    /**
     * Construye un prompt para Gemini usando un lote específico de criterios.
     *
     * @param Question $pregunta
     * @param int|null $prompt_id
     * @param Collection $criteriaBatch El lote de criterios a incluir en el prompt.
     * @return string
     */
    public function buildForGemini(Question $pregunta, ?int $prompt_id, Collection $criteriaBatch): string
    {
        $promptContent = $this->getPromptContent($prompt_id, 'gemini');
        $formatoEstricto = "[{\"criterion_id\": 1, \"response\": \"si\", \"comment\": \"...\"}]";
        $promptContent = str_replace('{{FORMATO_ESTRICTO}}', $formatoEstricto, $promptContent);

        // La llamada a replaceVariables ahora incluye el tercer argumento.
        return $this->replaceVariables($promptContent, $pregunta, $criteriaBatch);
    }

    /**
     * Construye un prompt para ChatGPT usando un lote específico de criterios.
     *
     * @param Question $pregunta
     * @param int|null $prompt_id
     * @param Collection $criteriaBatch El lote de criterios a incluir en el prompt.
     * @return array
     */
    public function buildForChatGpt(Question $pregunta, ?int $prompt_id, Collection $criteriaBatch): array
    {
        $promptContent = $this->getPromptContent($prompt_id, 'chatgpt');
        
        // ========================================================================
        // La llamada a replaceVariables ahora incluye el tercer argumento '$criteriaBatch',
        // cumpliendo con la firma del método y solucionando el error.
        // ========================================================================
        $finalPromptText = $this->replaceVariables($promptContent, $pregunta, $criteriaBatch);
        
        return [
            ['role' => 'system', 'content' => 'Eres un validador académico experto y riguroso. Tu única función es analizar una pregunta y devolver tus hallazgos en formato JSON.'],
            ['role' => 'user', 'content' => $finalPromptText],
        ];
    }

    /**
     * Obtiene la plantilla de prompt, ya sea de la BD o una por defecto.
     */
    private function getPromptContent(?int $prompt_id, string $engine): string
    {
        if ($prompt_id) {
            $promptModel = Prompt::find($prompt_id);
            if ($promptModel) return $promptModel->content;
        }
        
        return "ROL: Eres un evaluador académico experto.\nTAREA: Evalúa la pregunta en {{PREGUNTA_JSON}} contra los criterios en {{CRITERIOS}}.\nREGLAS DE SALIDA (MUY IMPORTANTE):\nTu respuesta DEBE ser únicamente un array JSON válido con las claves \"criterion_id\", \"response\", y \"comment\".\nEJEMPLO DE FORMATO:\n{{FORMATO_ESTRICTO}}";
    }
    
    /**
     * Reemplaza las variables dinámicas en una plantilla de prompt.
     * Esta es la definición del método que espera TRES argumentos.
     */
    private function replaceVariables(string $content, Question $pregunta, Collection $criteriaBatch): string
    {
        $variables = [
            '{{PREGUNTA_JSON}}' => $this->formatearPreguntaComoJson($pregunta),
            '{{CRITERIOS}}' => $this->formatearCriterios($criteriaBatch),
        ];
        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * Formatea una colección de criterios a texto.
     */
    private function formatearCriterios(Collection $criteria): string
    {
        return $criteria->map(fn($criterio) => "- ID: {$criterio->id}, Criterio: {$criterio->text}")->implode("\n");
    }
    
    /**
     * Formatea una pregunta a JSON.
     */
    private function formatearPreguntaComoJson(Question $pregunta): string
    {
        return json_encode([
            'enunciado' => $pregunta->stem,
            'opciones' => $pregunta->options->map(fn($opcion) => ['texto' => $opcion->option_text, 'es_correcta' => $opcion->is_correct]),
            'bibliografia' => $pregunta->bibliography,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}