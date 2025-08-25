<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ValidationPolicy
{
    /**
     * El método 'before' se elimina. El admin ya no puede validar.
     */

    /**
     * Determina si el usuario puede ver la interfaz de validación para una pregunta.
     * REGLA ACTUALIZADA: Solo el rol 'validador' puede.
     */
    public function viewInterface(User $user, Question $question): Response
    {
        if ($user->role !== 'validador') {
            return Response::deny('Solo los validadores pueden acceder a esta interfaz.');
        }

        if ($question->status !== 'revisado_por_ai') {
            return Response::deny('Esta pregunta no está pendiente de validación humana.');
        }

        return Response::allow();
    }

    /**
     * Determina si el usuario puede guardar (store) una nueva validación.
     * La lógica es la misma que para ver la interfaz.
     */
    public function store(User $user, Question $question): Response
    {
        return $this->viewInterface($user, $question);
    }
}