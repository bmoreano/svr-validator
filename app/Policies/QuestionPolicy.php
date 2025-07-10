<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * El método before() ha sido eliminado.
     * Ya no queremos que el administrador tenga permisos automáticos sobre las preguntas.
     * Cada regla se evaluará de forma explícita.
     */

    /**
     * Determina si el usuario puede ver la lista de sus propias preguntas.
     * Solo los autores pueden tener una lista de "mis preguntas".
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'autor';
    }

    /**
     * Determina si el usuario puede ver una pregunta específica.
     * Permitiremos que cualquiera (autor, validador, admin) vea los detalles
     * de una pregunta, ya que es una acción de solo lectura.
     */
    public function view(User $user, Question $question): bool
    {
        // En este caso, permitimos que todos los roles autenticados vean la pregunta.
        // Si quisiéramos ser más estrictos (ej. solo el autor y validadores asignados),
        // la lógica iría aquí. Por ahora, es una acción de lectura simple.
        return true;
    }

    /**
     * Determina si el usuario puede crear preguntas.
     * REGLA CLAVE: Solo y exclusivamente el rol 'autor'.
     */
    public function create(User $user): bool
    {
        return $user->role === 'autor';
    }

    /**
     * Determina si el usuario puede actualizar (editar) una pregunta.
     * REGLA CLAVE: Solo el 'autor' que es propietario de la pregunta.
     */
    public function update(User $user, Question $question): Response
    {
        // Regla 1: ¿Es el usuario un autor?
        if ($user->role !== 'autor') {
            return Response::deny('Solo los autores pueden editar preguntas.');
        }

        // Regla 2: ¿Es el autor el propietario de la pregunta?
        if ($user->id !== $question->author_id) {
            return Response::deny('No eres el propietario de esta pregunta.');
        }

        // Regla 3: ¿Está la pregunta en un estado editable?
        if (!in_array($question->status, ['borrador', 'necesita_correccion'])) {
            return Response::deny('No se puede editar una pregunta que ya ha sido enviada o aprobada.');
        }

        return Response::allow();
    }

    /**
     * Determina si el usuario puede eliminar una pregunta.
     * REGLA CLAVE: Solo el 'autor' propietario y solo si es un borrador.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->role === 'autor'
            && $user->id === $question->author_id
            && $question->status === 'borrador';
    }
    
    /**
     * Determina si el usuario puede enviar la pregunta a validación.
     * REGLA CLAVE: Solo el 'autor' propietario.
     */
    public function submitForValidation(User $user, Question $question): bool
    {
        return $user->role === 'autor' && $user->id === $question->author_id;
    }

    // Los métodos restore() y forceDelete() se pueden dejar como estaban o
    // ajustarlos con la misma lógica si se usan soft deletes.
}