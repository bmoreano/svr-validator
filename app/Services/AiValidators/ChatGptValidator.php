<?php
namespace App\Services\AiValidators;

use App\Contracts\AiValidatorInterface;
use App\Models\Question;
use App\Services\PromptBuilderService;
use OpenAI\Client as OpenAIClient;
use Illuminate\Support\Collection;

class ChatGptValidator implements AiValidatorInterface
{
    public function __construct(private OpenAIClient $client, private PromptBuilderService $promptBuilder) {}

    public function validate(Question $question, ?int $promptId, Collection $criteriaBatch): string
    {
        $messages = $this->promptBuilder->buildForChatGpt($question, $promptId, $criteriaBatch);
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'response_format' => ['type' => 'json_object'],
            'messages' => $messages,
        ]);
        return $response->choices[0]->message->content;
    }
}