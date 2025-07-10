<?php
namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Str;

class QuestionReadyForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Question $question) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Generamos la URL firmada que es válida por 48 horas.
        $signedUrl = URL::temporarySignedRoute(
            'validations.show.signed', // Un nuevo nombre de ruta para el acceso seguro
            now()->addHours(48),
            [
                'question' => $this->question->id,
                'validator' => $notifiable->id, // Pasamos el ID del validador
            ]
        );

        return (new MailMessage)
                    ->subject('Nueva Pregunta Lista para su Revisión: #' . $this->question->id)
                    ->greeting('Hola ' . $notifiable->name . ',')
                    ->line('Una nueva pregunta está lista para su revisión final después del análisis de la IA.')
                    ->line('Enunciado: "' . Str::limit($this->question->stem, 100) . '"')
                    ->action('Revisar Pregunta Ahora', $signedUrl)
                    ->line('Este enlace es válido por 48 horas. ¡Gracias por su colaboración!');
    }
}