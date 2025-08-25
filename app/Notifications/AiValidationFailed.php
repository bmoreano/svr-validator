<?php

namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class AiValidationFailed
 *
 * Notificación que se envía al autor de una pregunta cuando un job de validación
 * individual falla después de todos los reintentos.
 */
class AiValidationFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @param \App\Models\Question $question La pregunta cuya validación falló.
     * @param string $engineName El nombre del motor de IA que se intentó usar.
     * @param string|null $errorMessage El mensaje de error detallado de la excepción.
     */
    public function __construct(
        public Question $question,
        public string $engineName,
        public ?string $errorMessage = null
    ) {}

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
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
        $url = route('questions.index');

        $mailMessage = (new MailMessage)
                    ->level('error')
                    ->subject('❌ Fallo en la Validación con IA de tu Pregunta')
                    ->greeting('Hola, ' . $notifiable->name . '.')
                    ->line("Lamentablemente, ocurrió un error y no se pudo completar la validación automática con **{$this->engineName}** para tu pregunta.")
                    ->line('**Pregunta:** "' . $this->question->code . '"');

        if ($this->errorMessage) {
            $mailMessage->line('**Detalle del error reportado:**')
                        ->line('> ' . $this->errorMessage);
        }

        $mailMessage->line('Hemos revertido el estado de la pregunta a "borrador" para que puedas intentarlo de nuevo. Si el problema persiste, por favor, contacta a soporte.')
                    ->action('Volver a Mis Preguntas', $url)
                    ->line('Disculpa las molestias ocasionadas.');

        return $mailMessage;
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
            'message' => "La validación con {$this->engineName} para la pregunta {$this->question->code} ha fallado.",
            'error_details' => $this->errorMessage,
            'link' => route('questions.index'),
        ];
    }
}