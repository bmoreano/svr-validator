<?php

namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComparativeValidationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    // Nuevo parámetro para saber si fue un éxito total
    public function __construct(public Question $question, public bool $wasFullySuccessful = true) {}

    public function via(object $notifiable): array { return ['mail', 'database']; }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('questions.compare', $this->question);

        $mailMessage = (new MailMessage);

        if ($this->wasFullySuccessful) {
            $mailMessage->subject('✅ Validación Comparativa Completada')
                        ->greeting('¡Hola, ' . $notifiable->name . '!')
                        ->line('La validación con ambos motores de IA para tu pregunta ha finalizado con éxito.')
                        ->line('**Pregunta:** "' . $this->question->code . '"')
                        ->action('Ver Resultados Comparativos', $url);
        } else {
            $mailMessage->level('warning')
                        ->subject('⚠️ Validación Comparativa Completada con Errores')
                        ->greeting('¡Hola, ' . $notifiable->name . '!')
                        ->line('El proceso de validación comparativa para tu pregunta ha finalizado, pero uno de los motores de IA encontró un error.')
                        ->line('**Pregunta:** "' . $this->question->code . '"')
                        ->action('Ver Resultados y Detalles del Error', $url);
        }

        return $mailMessage->line('Ya puedes revisar el análisis.');
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->wasFullySuccessful
            ? "La validación comparativa para la pregunta {$this->question->code} ha finalizado."
            : "La validación comparativa para la pregunta {$this->question->code} finalizó con errores.";
            
        return ['message' => $message, 'link' => route('questions.compare', $this->question)];
    }
}