<?php

namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class YouHaveBeenAssigned
 *
 * Notificación que se envía a un validador específico cuando un administrador
 * le ha asignado manualmente una pregunta para su revisión.
 */
class YouHaveBeenAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * La pregunta que ha sido asignada.
     * @var \App\Models\Question
     */
    public Question $question;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @param \App\Models\Question $question
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Se enviará por correo y se guardará en la base de datos para la campana.
        return ['mail', 'database'];
    }

    /**
     * Obtiene la representación por correo de la notificación.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // El enlace directo para que el validador comience la revisión.
        $url = route('validations.review', $this->question);

        return (new MailMessage)
                    ->subject("Asignación de Revisión: Pregunta #{$this->question->id}")
                    ->greeting('Hola, ' . $notifiable->name . '.')
                    ->line('Has sido asignado para realizar la revisión humana de la siguiente pregunta:')
                    ->line('**Pregunta:** "' . \Illuminate\Support\Str::limit($this->question->stem, 80) . '"')
                    ->line('Por favor, procede a revisarla haciendo clic en el siguiente botón.')
                    ->action('Revisar Pregunta Asignada', $url)
                    ->line('Tu experiencia es fundamental para mantener la calidad de nuestro banco de preguntas.');
    }

    /**
     * Obtiene la representación en array de la notificación (para la campana).
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'message' => 'Se te ha asignado la pregunta #' . $this->question->id . ' para su revisión.',
            'link' => route('validations.review', $this->question),
        ];
    }
}