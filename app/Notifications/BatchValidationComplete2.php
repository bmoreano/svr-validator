<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class BatchValidationComplete
 *
 * Notifica a un usuario que el procesamiento de su archivo
 * de validación por lotes ha finalizado.
 */
class BatchValidationComplete2 extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * Opcionalmente, podríamos pasar contadores de éxito/error para un resumen más detallado.
     *
     * public function __construct(public int $successCount, public int $errorCount)
     * {
     *     //
     * }
     */
    public function __construct()
    {
        //
    }

    /**
     * Obtiene los canales de notificación.
     * En este caso, solo enviaremos por correo electrónico.
     *
     * @param  object  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construye la representación por correo de la notificación.
     *
     * @param  object  $notifiable El usuario que recibirá la notificación.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Procesamiento de Lote de Reactivos Finalizado')
                    ->greeting('Hola ' . $notifiable->name . ',')
                    ->line('Te informamos que el procesamiento de tu archivo de reactivos ha finalizado.')
                    ->line('Todas las preguntas válidas en tu archivo han sido creadas y enviadas al proceso de validación por Inteligencia Artificial.')
                    ->line('Puedes monitorear el estado de cada una de tus preguntas en tu panel de gestión.')
                    ->action('Ver Mis Preguntas', route('questions.index'))
                    ->line('Recibirás notificaciones adicionales a medida que tus preguntas sean revisadas y aprobadas.');
    }

    /**
     * Obtiene la representación de la notificación en un array (para base de datos, etc.).
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // Podríamos guardar datos en la base de datos para un centro de notificaciones en la app.
        ];
    }
}