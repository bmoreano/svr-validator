<?php

namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL; // <-- Importante

class StaleQuestionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $question;

    /**
     * Crea una nueva instancia de notificación.
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Enviar a email y a la campana de BD
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // --- INICIO DE LA SOLUCIÓN ---
        // Generamos las URLs firmadas para las acciones
        
        // 1. URL para "Sí, deseo retirarme"
        $withdrawUrl = URL::signedRoute(
            'questions.action.withdraw', 
            ['user' => $notifiable->id]
        );

        // 2. URL para "No, continuaré trabajando"
        $resumeUrl = URL::signedRoute(
            'questions.action.resume', 
            ['question' => $this->question->id]
        );

        return (new MailMessage)
                    ->subject('Actividad Requerida en Reactivo Pendiente')
                    ->line("Hemos notado que el reactivo '{$this->question->code}' (ID: {$this->question->id}) ha estado en estado 'borrador' por más de 72 horas.")
                    ->line('Su estado ha sido cambiado a "En Espera" y ya no es editable.')
                    ->line('¿Deseas retirarte de la generación de preguntas? Tu cuenta será desactivada y tus preguntas serán reasignadas.')
                    ->action('Sí, deseo retirarme', $withdrawUrl) // Botón de "SÍ"
                    ->line('Si deseas continuar trabajando en esta pregunta, por favor haz clic en el siguiente enlace:')
                    ->action('No, continuaré trabajando', $resumeUrl); // Botón de "NO"
        // --- FIN DE LA SOLUCIÓN ---
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'warning',
            'title' => 'Reactivo Estancado',
            'body' => "Acción requerida para el reactivo #{$this->question->id}. ¿Deseas retirarte?",
            'url' => route('questions.show', $this->question->id), // Link para ver (no editar)
        ];
    }
}