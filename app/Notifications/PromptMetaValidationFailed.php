<?php

namespace App\Notifications;

use App\Models\Prompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class PromptMetaValidationFailed
 *
 * Notificación que se envía a los administradores cuando el Job
 * `MetaValidatePrompt` falla de forma permanente.
 */
class PromptMetaValidationFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * El prompt cuya meta-validación ha fallado.
     * @var \App\Models\Prompt
     */
    public Prompt $prompt;

    /**
     * El mensaje de la excepción que causó el fallo.
     * @var string
     */
    public string $errorMessage;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @param \App\Models\Prompt $prompt
     * @param string $errorMessage
     */
    public function __construct(Prompt $prompt, string $errorMessage)
    {
        $this->prompt = $prompt;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     */
    public function via(object $notifiable): array
    {
        // Se enviará por correo y se guardará en la base de datos para la campana de notificaciones del admin.
        return ['mail', 'database'];
    }

    /**
     * Obtiene la representación por correo de la notificación.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // URL para que el administrador pueda editar y revisar el prompt manualmente.
        $url = route('admin.prompts.edit', $this->prompt);

        return (new MailMessage)
                    // Marcamos el correo como un error para que use la plantilla de advertencia.
                    ->level('error')
                    ->subject('⚠️ Fallo Crítico en la Revisión Automática de un Prompt')
                    ->greeting('Hola, Administrador.')
                    ->line("El proceso automático de meta-validación para un prompt ha fallado después de varios reintentos y requiere tu intervención manual.")
                    ->line('**Prompt Afectado:** "' . $this->prompt->name . '" (ID: ' . $this->prompt->id . ')')
                    ->line('**Error Reportado por el Sistema:**')
                    // Usamos la sintaxis de blockquote de Markdown para resaltar el error.
                    ->line('> ' . $this->errorMessage)
                    ->line('El prompt ha sido marcado como "Rechazado" para prevenir su uso. Por favor, revísalo para determinar la causa del fallo.')
                    ->action('Revisar Prompt Manualmente', $url)
                    ->line('Gracias por tu atención.');
    }

    /**
     * Obtiene la representación en array de la notificación (para la campana).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'prompt_id' => $this->prompt->id,
            'message' => "La meta-validación del prompt '{$this->prompt->name}' ha fallado y requiere revisión.",
            'error_details' => $this->errorMessage,
            // Enlace para que el admin pueda hacer clic en la notificación y ir directamente a editar.
            'link' => route('admin.prompts.edit', $this->prompt),
        ];
    }
}