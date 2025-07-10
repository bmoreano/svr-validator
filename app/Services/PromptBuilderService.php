<?php

// app/Services/PromptBuilderService.php
namespace App\Services;

use App\Models\Criterion;
use App\Models\Question;


class PromptBuilderService
{
// Define the prompt template as a class constant
private const PROMPT = <<<EOT
Eres un validador académico experto y riguroso. Tu tarea es analizar una pregunta y evaluarla contra una lista de criterios de calidad.

**Instrucciones:**
1.  Analiza la pregunta en el bloque "PREGUNTA".
2.  Evalúa CADA UNO de los criterios en el bloque "CRITERIOS".
3.  Tu respuesta debe ser "si", "no", o "adecuar".
4.  Para "no" o "adecuar", proporciona un comentario claro y conciso explicando la razón.
5.  Tu respuesta final DEBE ser únicamente un objeto JSON válido, un array de objetos.

**CRITERIOS A EVALUAR:**
{{CRITERIOS}}

**PREGUNTA A EVALUAR:**
{{PREGUNTA}}
EOT;

    /**
     * Build the prompt by replacing placeholders with actual criteria and question data.
     *
     * @param string $criteriaText
     * @param string $questionData
     * @return string
     */
    public function buildPrompt(string $criteriaText, string $questionData): string
    {
        return str_replace(['{{CRITERIOS}}', '{{PREGUNTA}}'], [$criteriaText, $questionData], self::PROMPT);
    }
    public function buildForChatGpt(Question $question): string
    {
        $criteriaText = Criterion::where('is_active', true)->orderBy('sort_order')->get()->map(function ($criterion) {
            return "- ID: {$criterion->id}, Criterio: {$criterion->text}";
        })->implode("\n");

        $questionData = json_encode([
            'enunciado' => $question->stem,
            'opciones' => $question->options->map(fn ($opt) => [
                'texto' => $opt->option_text,
                'es_correcta' => $opt->is_correct,
                'argumentacion' => $opt->argumentation
            ]),
            'bibliografia' => $question->bibliography
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $questionData . "\n\n" . $criteriaText;
    }       
}