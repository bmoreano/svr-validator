<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ValidationPolicy
{
    /**
     * Realiza una comprobación previa para un super-administrador si fuera necesario.
     * En este caso, un administrador puede ver y realizar validaciones.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'administrador') {
            return true;
        }
        return null;
    }

    /**
     * Determina si el usuario puede ver la interfaz de validación para una pregunta dada.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Question $question
     * @return \Illuminate\Auth\Access\Response
     */
    public function viewInterface(User $user, Question $question): Response
    {
        // Regla 1: El usuario debe tener el rol de validador.
        if ($user->role !== 'validador') {
            return Response::deny('Solo los validadores pueden acceder a esta interfaz.');
        }

        // Regla 2: La pregunta debe estar en un estado que requiera validación humana.
        if ($question->status !== 'revisado_por_ai') {
            return Response::deny('Esta pregunta no está actualmente pendiente de validación.');
        }

        // Futura Regla 3 (opcional): Si tuvieras un sistema de asignación,
        // podrías verificar aquí si la pregunta fue asignada específicamente a este validador.
        // if (!$question->isAssignedTo($user)) {
        //     return Response::deny('Esta pregunta no te ha sido asignada para su validación.');
        // }

        return Response::allow();
    }

    /**
     * Determina si el usuario puede guardar (store) una nueva validación para una pregunta.
     *
     * La lógica es idéntica a la de ver la interfaz, ya que si puedes verla,
     * deberías poder enviarla. Se puede reutilizar el método anterior.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Question $question
     * @return \Illuminate\Auth\Access\Response
     */
    public function store(User $user, Question $question): Response
    {
        return $this->viewInterface($user, $question);
    }
}