<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * --- SOLUCIÓN ---
     * Refactorizamos el 'before' para que ya no intente dar permisos globales.
     * Solo manejará el caso especial de 'submit' para el admin,
     * dejando que los otros métodos decidan el resto.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Si el admin está intentando 'submit', dejamos que el método submit()
        // (que tiene la lógica de autor inactivo) decida.
        if ($user->hasRole('administrador') && $ability === 'submit') {
            return null;
        }

        // Para cualquier otra habilidad, dejamos que los métodos individuales decidan.
        // Ya no retornamos 'true' aquí para el admin.
        return true;
    }

    /**
     * Determina si el usuario puede ver la lista de preguntas.
     */
    public function viewAny(User $user): bool
    {
        // Todos los roles autenticados pueden ver el índice
        return $user->hasAnyRole([ 'autor', 'administrador']);
        //return true;
    }

    /**
     * Determina si el usuario puede ver una pregunta específica.
     */
    public function view(User $user, Question $question): bool
    {
        // --- SOLUCIÓN ---
        // El admin siempre puede ver.
        if ($user->hasRole('administrador')) {
            return true;
        }

        // Un autor solo puede ver sus propias preguntas.
        if ($user->hasRole('autor')) {
            return $user->id === $question->author_id;
        }

        // Validadores y Jefes de Carrera pueden ver.
        return $user->hasRole('validador') || $user->hasRole('jefe_carrera');
    }

    /**
     * Determina si el usuario puede crear preguntas.
     */
    public function create(User $user): bool
    {
        // --- SOLUCIÓN ---
        // El admin puede crear.
        if ($user->hasRole('administrador')) {
            return true;
        }
        // Los autores también pueden crear.
        return $user->hasRole('autor');
    }

    /**
     * Determina si el usuario puede actualizar una pregunta.
     * (Este era el bug de la imagen)
     */
    public function update(User $user, Question $question): bool
    {
        // 1. El admin puede actualizar SI Y SOLO SI el autor está inactivo
        if ($user->hasRole('administrador')) {
            $author = $question->author;

            // Comprueba que el autor exista y que 'activo' sea false
            if ($author && $author->activo === false) {
                return true;
            }
        }

        // 2. El autor puede actualizar su pregunta SOLO si está en 'borrador'.
        return $user->id === $question->author_id && $question->status === 'borrador';
    }

    /**
     * Determina si el usuario puede eliminar una pregunta.
     */
    public function delete(User $user, Question $question): bool
    {
        // 1. El admin puede eliminar SI Y SOLO SI el autor está inactivo
        if ($user->hasRole('administrador')) {
            $author = $question->author;

            if ($author && $author->activo === false) {
                return true;
            }
        }

        // 2. El autor puede eliminar su pregunta SOLO si está en 'borrador'.
        return $user->id === $question->author_id && $question->status === 'borrador';
    }

    /**
     * Determina si el usuario puede enviar la pregunta a validación de IA.
     * (Esta lógica ya era correcta y explícita para el 'autor inactivo').
     */
    public function submit(User $user, Question $question): bool
    {
        // 1. El autor puede enviar su propia pregunta
        if ($user->id === $question->author_id) {
            return true;
        }

        // 2. Un Admin puede enviar la pregunta SI el autor está inactivo.
        if ($user->hasRole('administrador')) {
            $author = $question->author;

            if ($author && $author->activo === false) {
                return true;
            }
        }

        // Si no se cumple ninguna condición, se deniega.
        return false;
    }
}