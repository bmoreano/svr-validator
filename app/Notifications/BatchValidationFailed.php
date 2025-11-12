<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Batch; // Importante

class BatchValidationFailed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $batch;
    protected $exception;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(Batch $batch, \Throwable $exception)
    {
        $this->batch = $batch;
        $this->exception = $exception;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->error() // Tono de error
                    ->line('Ha ocurrido un problema con tu lote de validación.')
                    ->line('Un job dentro del lote falló y no se pudo completar.')
                    ->line('Error: ' . $this->exception->getMessage())
                    ->line('El lote tenía ' . $this->batch->totalJobs . ' trabajos en total.')
                    ->action('Ir a Preguntas', route('questions.index'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'error',
            'title' => 'Fallo en Lote',
            'body' => 'El lote de validación de preguntas ha fallado.',
            'url' => route('questions.index'),
        ];
    }
}