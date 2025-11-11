<?php

namespace App\Events;

use App\Models\Question; // Importar el modelo Question
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionSubmittedForValidation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Question $question;

    /**
     * Create a new event instance.
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }
}