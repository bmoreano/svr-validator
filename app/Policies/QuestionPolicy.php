<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * Determina si el usuario puede ver la lista de preguntas.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['autor', 'administrador']);
    }

    /**
     * Determina si el usuario puede ver una pregunta específica.
     */
    public function view(User $user, Question $question): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede crear preguntas.
     * REGLA CLAVE: Solo y exclusivamente el rol 'autor'.
     */
    public function create(User $user): bool
    {
        // Si el rol del usuario no es 'autor', no puede crear.
        return $user->role === 'autor';
    }

    /**
     * Determina si el usuario puede actualizar (editar) una pregunta.
     * Solo se pueden editar preguntas en estado 'borrador'.
     */
    public function update(User $user, Question $question): bool
    {
        $isEditableStatus = in_array($question->status, [
            'borrador', 
            'necesita_correccion', 
            'fallo_comparativo'
        ]);
        
        if ($user->role === 'administrador') {
            return $isEditableStatus;
        }
        
        return $user->id === $question->author_id && $isEditableStatus;
    }

    /**
     * Determina si el usuario puede eliminar una pregunta.
     */
    public function delete(User $user, Question $question): bool
    {
        // Aplicamos la misma lógica para la eliminación.
        $isDeletableStatus = in_array($question->status, [
            'borrador', 
            'necesita_correccion', 
            'fallo_comparativo'
        ]);
        
        if ($user->role === 'administrador') {
            return $isDeletableStatus;
        }

        return $user->id === $question->author_id && $isDeletableStatus;
    }
    
    /**
     * Determina si el usuario puede eliminar una pregunta.
     * Solo se pueden eliminar preguntas en estado 'borrador'.
     */
    public function delete1(User $user, Question $question): bool
    {
        if ($question->status !== 'borrador') {
            return false;
        }
        
        if ($user->role === 'administrador') {
            return true;
        }

        if ($user->role === 'autor') {
            return $user->id === $question->author_id;
        }
        
        return false;
    }
    

        // ==========================================================
        // Determina si el usuario puede enviar la pregunta a validación.
        // Permitimos que tanto el autor propietario como cualquier administrador
        // puedan enviar una pregunta a validación si está en el estado correcto.
        // ==========================================================    
    public function submitForValidation(User $user, Question $question): bool
    {

        $isSubmittableStatus = in_array($question->status, ['borrador', 'necesita_correccion', 'fallo_comparativo']);
        
        if ($user->role === 'administrador') {
            return $isSubmittableStatus;
        }

        return $user->id === $question->author_id && $isSubmittableStatus;
    }

}
