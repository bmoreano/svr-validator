<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use App\Notifications\YouHaveBeenAssigned;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class QuestionAssignmentController extends Controller
{
    /**
     * Asigna un validador a una pregunta y le notifica.
     */
    public function assign(Request $request, Question $question): RedirectResponse
    {
        // ==========================================================
        // --- NUEVA REGLA DE NEGOCIO ---
        // Verificamos que la pregunta esté en un estado que permita la asignación.
        // Solo se pueden asignar preguntas que ya han sido revisadas por la IA.
        // ==========================================================
        $allowedStatuses = ['revisado_por_ai', 'revisado_comparativo', 'en_revision_humana'];
        if (!in_array($question->status, $allowedStatuses)) {
            return back()->with('error', 'Solo se puede asignar un validador a preguntas que ya han completado la revisión por IA.');
        }

        $validated = $request->validate([
            'validator_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'validador');
                }),
            ],
        ]);
        
        $validatorId = $validated['validator_id'] ?? null;

        // Actualizamos el validador asignado y, opcionalmente, el estado.
        $question->update([
            'assigned_validator_id' => $validatorId,
            'status' => 'en_revision_humana' // Cambiamos el estado para reflejar que está en revisión manual.
        ]);

        if ($validatorId) {
            $validator = User::find($validatorId);
            if ($validator) {
                $validator->notify(new YouHaveBeenAssigned($question));
            }
        }

        return back()->with('status', 'Validador asignado con éxito. La pregunta está ahora en revisión humana.');
    }
}