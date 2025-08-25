<?php
namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ReviewRequestForValidators extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Question $question, public string $engineName) {}

    public function via(object $notifiable): array
    {
        // Se enviará por correo y a la base de datos para la campana.
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Nueva Pregunta Lista para Revisión Humana")
                    ->greeting('Hola, equipo de validadores,')
                    ->line("Una nueva pregunta ha sido procesada por **{$this->engineName}** y está lista para su revisión final.")
                    ->line('**Pregunta:** "' . \Illuminate\Support\Str::limit($this->question->stem, 80) . '"')
                    ->action('Iniciar Revisión', route('validations.review', $this->question))
                    ->line('Gracias por su colaboración.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'message' => 'La pregunta #' . $this->question->id . ' está lista para tu revisión.',
        ];
    }
}