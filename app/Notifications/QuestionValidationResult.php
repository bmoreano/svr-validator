<?php

namespace App\Notifications;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QuestionValidationResult extends Notification implements ShouldQueue
{
    use Queueable;

    public Question $question;
    public array $validationReport;

    /**
     * Create a new notification instance.
     */
    public function __construct(Question $question, array $validationReport)
    {
        $this->question = $question;
        $this->validationReport = $validationReport;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Puedes añadir 'database' para notificaciones en la UI
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "Resultado de Validación de Pregunta #" . $this->question->id . ": " . ucfirst(str_replace('_', ' ', $this->validationReport['final_decision']));
        $greeting = "Hola " . $notifiable->name . ",";

        $mailMessage = (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting);

        if ($this->validationReport['final_decision'] === 'revisado_por_ai') {
            $mailMessage->line('Tu pregunta ha sido revisada automáticamente y ha pasado todas las comprobaciones de originalidad.');
        } elseif ($this->validationReport['final_decision'] === 'necesita_correccion') {
            $mailMessage->error()
                        ->line('Tu pregunta necesita corrección.')
                        ->line('Motivo: ' . $this->validationReport['reason']);
            if (isset($this->validationReport['internal_validation']['exact_duplicate']['question_id'])) {
                $mailMessage->line('Duplicado exacto de la pregunta ID: ' . $this->validationReport['internal_validation']['exact_duplicate']['question_id']);
            }
            if (isset($this->validationReport['internal_validation']['semantic_similarity']['question_id'])) {
                $mailMessage->line('Similar semánticamente a la pregunta ID: ' . $this->validationReport['internal_validation']['semantic_similarity']['question_id'] . ' (Similitud: ' . $this->validationReport['internal_validation']['semantic_similarity']['similarity_score'] . ')');
            }
            if (isset($this->validationReport['external_validation']['web_search']['urls'])) {
                $mailMessage->line('Posibles fuentes de plagio encontradas:');
                foreach ($this->validationReport['external_validation']['web_search']['urls'] as $url) {
                    $mailMessage->line('- ' . $url);
                }
            }
            if (isset($this->validationReport['external_validation']['deep_plagiarism']['sources'])) {
                 $mailMessage->line('Plagio detectado por análisis profundo (' . $this->validationReport['external_validation']['deep_plagiarism']['percent_copied'] . '%):');
                foreach ($this->validationReport['external_validation']['deep_plagiarism']['sources'] as $source) {
                    $mailMessage->line('- ' . $source['url'] . ' (' . $source['percent'] . '%)');
                }
            }
            $mailMessage->line('Por favor, revisa tu pregunta y envíala de nuevo.');
        } else { // error_validacion_ai
            $mailMessage->error()
                        ->line('Hubo un error al procesar tu pregunta. Por favor, contacta a un administrador.');
            $mailMessage->line('Error: ' . ($this->validationReport['reason'] ?? 'Desconocido'));
        }

        return $mailMessage->action('Ver Mis Preguntas', url('/dashboard/questions')); // Ajusta esta ruta a tu dashboard de preguntas
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'status' => $this->validationReport['final_decision'],
            'reason' => $this->validationReport['reason'],
            'report' => $this->validationReport,
        ];
    }
}