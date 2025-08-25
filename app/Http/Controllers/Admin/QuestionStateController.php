<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use App\Notifications\ReviewRequestForValidators;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class QuestionStateController extends Controller
{
    /**
     * Cambia el estado de una pregunta a 'en_revision_humana' y notifica a los validadores.
     */
public function sendToReview(Question $question): RedirectResponse
    {
        $allowedStatuses = [
            'revisado_por_ai', 
            'revisado_comparativo',
            'en_validacion_ai',
            'en_validacion_comparativa'
        ];

        // Ahora, si el estado es 'en_validacion_ai', esta guarda permitirá continuar.
        if (!in_array($question->status, $allowedStatuses)) {
            return back()->with('error', 'Esta pregunta no está en un estado válido para ser enviada a revisión.');
        }

        $question->update(['status' => 'en_revision_humana']);
        
        // Notificamos a los validadores que el admin ha priorizado esta pregunta.
        $validators = User::where('role', 'validador')->get();
        if ($validators->isNotEmpty()) {
            Notification::send($validators, new ReviewRequestForValidators($question, 'Revisión Priorizada por Admin'));
        }

        return back()->with('status', 'La pregunta ha sido enviada a la cola de revisión humana.');
    }
}