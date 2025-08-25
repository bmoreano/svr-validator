<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Question;

/**
 * Class AiValidationCompleted
 *
 * Esta clase representa una notificación que se envía a un usuario (Notifiable)
 * cuando la validación automática de una de sus preguntas se ha completado con éxito.
 *
 * Implementa ShouldQueue para que el envío de correos se procese en segundo plano,
 * mejorando la velocidad y la resiliencia de la aplicación.
 */
class AiValidationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * La pregunta específica que fue validada.
     * Hacemos la propiedad pública para que sea accesible dentro de los métodos de la notificación.
     * @var \App\Models\Question
     */
    public Question $question;

    /**
     * El nombre del motor de IA que realizó la validación (ej. 'ChatGPT', 'Gemini').
     * @var string
     */
    public string $engineName; 
    /**
     * El nombre de la cola a ejecutar (ej. 'defaulr', 'notifications',...).
     * @var string
     */
        //public $queue = 'notifications';

    /**
     * Crea una nueva instancia de la notificación.
     * El constructor recibe todos los datos necesarios para construir el mensaje.
     *
     * @param \App\Models\Question $question La pregunta validada.
     * @param string $engineName El nombre del motor de IA utilizado.
     */
    public function __construct(Question $question, string $engineName)
    {
        $this->question = $question;
        $this->engineName = $engineName;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * Laravel puede enviar notificaciones a través de varios "canales".
     * Aquí especificamos que esta notificación debe ser enviada por 'mail'.
     * Otros canales comunes son 'database' (para notificaciones en la UI) o 'slack'.
     *
     * @param  mixed  $notifiable La entidad que recibe la notificación (en este caso, un objeto User).
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Obtiene la representación por correo de la notificación.
     *
     * Este método construye el correo electrónico que se enviará.
     * Laravel proporciona una API fluida (MailMessage) para definir el contenido
     * del correo de forma sencilla y legible.
     *
     * @param  mixed  $notifiable La entidad que recibe la notificación.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Creamos una URL para que el usuario pueda ver directamente la pregunta validada.
        $url = route('questions.show', $this->question);

        return (new MailMessage)
                    // Asunto del correo electrónico.
                    ->subject("Validación de IA completada para tu pregunta")
                    
                    // Saludo inicial, personalizado con el nombre del usuario.
                    ->greeting('¡Hola, ' . $notifiable->name . '!')
                    
                    // Líneas de texto del cuerpo del correo. El texto en Markdown será renderizado.
                    ->line("La validación automática con **{$this->engineName}** para tu pregunta ha sido completada con éxito.")
                    ->line('**Pregunta:** "' . \Illuminate\Support\Str::limit($this->question->stem, 80) . '"')
                    ->line('Ahora está lista para la revisión final por parte de un validador humano. Puedes revisar los detalles y los comentarios de la IA haciendo clic en el siguiente botón.')
                    
                    // Botón de llamada a la acción.
                    ->action('Ver Detalles de la Pregunta', $url)
                    
                    // Línea final de despedida.
                    ->line('¡Gracias por contribuir a nuestro banco de preguntas!');
    }

    /**
     * Obtiene la representación en array de la notificación.
     *
     * Este método es necesario si alguna vez decides usar el canal 'database'.
     * Define qué datos se guardarán en la tabla `notifications` de la base de datos.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    
    public function toArray(object $notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'question_stem' => $this->question->stem,
            'engine' => $this->engineName,
            'message' => "La validación con {$this->engineName} para tu pregunta '{$this->question->stem}' ha finalizado.",
        ];
    }
}