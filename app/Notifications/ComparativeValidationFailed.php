<?php

namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComparativeValidationFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * La pregunta cuya validación comparativa falló.
     * @var \App\Models\Question
     */
    public Question $question;

    /**
     * El mensaje de error de la excepción que causó el fallo.
     * @var string
     */
    public string $errorMessage;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @param \App\Models\Question $question
     * @param string $errorMessage
     */
    public function __construct(Question $question, string $errorMessage)
    {
        $this->question = $question;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Se enviará por correo y se guardará en la base de datos para la campana de notificaciones.
        return ['mail', 'database'];
    }

    /**
     * Obtiene la representación por correo de la notificación.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail1(object $notifiable): MailMessage
    {
        // URL para que el usuario vuelva a la lista de preguntas.
        $url = route('questions.index');

        return (new MailMessage)
                    // Establece el estilo visual del correo a "error" (generalmente con detalles en rojo).
                    ->level('error')
                    
                    // Asunto claro y conciso.
                    ->subject('❌ Fallo en la Validación Comparativa')
                    
                    // Saludo personalizado.
                    ->greeting('Hola, ' . $notifiable->name . '.')
                    
                    // Cuerpo del mensaje.
                    ->line('Lamentablemente, el proceso de validación comparativa para tu pregunta ha fallado. Uno de los motores de IA no pudo completar el análisis.')
                    ->line('**Pregunta:** "' . \Illuminate\Support\Str::limit($this->question->stem, 80) . '"')
                    
                    // Mostramos el detalle técnico del error en un panel resaltado.
                    ->line('**Detalle del error reportado:**')
                    ->line('> ' . $this->errorMessage)
                    //->panel($this->errorMessage)
                    
                    ->line('El estado de tu pregunta ha sido actualizado a "fallo_comparativo". Te recomendamos intentar una validación individual o revisar la pregunta y volver a intentarlo más tarde.')
                    
                    // Botón de llamada a la acción.
                    ->action('Volver a Mis Preguntas', $url)
                    
                    ->line('Disculpa las molestias ocasionadas.');
    }

    /**
     * Obtiene la representación en array de la notificación (para la campana).
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray1(object $notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'message' => "La validación comparativa para la pregunta {$this->question->code} ha fallado.",
            'error_details' => $this->errorMessage,
        ];
    }


    public function toMail(object $notifiable): MailMessage
    {
        // En caso de fallo, siempre enlazamos a la página de comparación
        // para que el usuario pueda ver qué parte funcionó y cuál no.
        $url = route('questions.compare', $this->question);

        return (new MailMessage)
                    ->level('warning') // Usamos 'warning' porque puede ser un éxito parcial
                    ->subject('⚠️ Atención: La Validación Comparativa falló parcialmente')
                    ->greeting('Hola, ' . $notifiable->name . '.')
                    ->line('El proceso de validación comparativa para tu pregunta ha encontrado un problema. Uno de los motores de IA no pudo completar el análisis.')
                    ->line('**Pregunta:** "' . \Illuminate\Support\Str::limit($this->question->stem, 80) . '"')
                    ->line('**Detalle del error reportado:**')
                    ->line('> ' . $this->errorMessage)
                    ->line('Puedes revisar los resultados de la validación que sí se completó haciendo clic en el siguiente botón.')
                    ->action('Ver Resultados Parciales', $url)
                    ->line('El estado de tu pregunta ha sido actualizado a "Fallo Comparativo".');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'message' => 'La validación comparativa para tu pregunta "' . \Illuminate\Support\Str::limit($this->question->stem, 30) . '" falló.',
            'error_details' => $this->errorMessage,
            'link' => route('questions.compare', $this->question),
        ];
    }    
}