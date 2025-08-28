<?php

namespace App\Services\AiValidators;

use App\Contracts\AiValidatorInterface;
use App\Models\Question;
use App\Services\PromptBuilderService;
use Gemini\Client as GeminiClient;
use Illuminate\Support\Collection;

class GeminiValidator implements AiValidatorInterface
{
    public function __construct(
        private GeminiClient $client,
        private PromptBuilderService $promptBuilder
    ) {}

    public function validate(Question $question, ?int $promptId, Collection $criteriaChunk): string
    {
        logger()->info("GeminiValidator->validate.");
        logger()->info("");
        $prompt = $this->promptBuilder->buildForGemini($question, $promptId, $criteriaChunk);
        $model = $this->client->generativeModel('gemini-2.5-Pro');
        $result = $model->generateContent($prompt);

        return $result->text();
    }
}