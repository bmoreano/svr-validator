<?php
namespace App\Contracts;

use App\Models\Question;
use Illuminate\Support\Collection;

interface AiValidatorInterface
{
    /**
     * Valida una pregunta contra un lote específico de criterios.
     */
    public function validate(Question $question, ?int $promptId, Collection $criteriaBatch): string;
}