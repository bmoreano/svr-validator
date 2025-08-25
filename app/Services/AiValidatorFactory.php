<?php

namespace App\Services;

use App\Contracts\AiValidatorInterface;
use App\Services\AiValidators\ChatGptValidator;
use App\Services\AiValidators\GeminiValidator;

class AiValidatorFactory
{
    public function make(string $engineName): AiValidatorInterface
    {
        return match ($engineName) {
            'chatgpt' => app(ChatGptValidator::class),
            'gemini' => app(GeminiValidator::class),
            default => throw new \InvalidArgumentException("Motor de IA no soportado: {$engineName}"),
        };
    }
}