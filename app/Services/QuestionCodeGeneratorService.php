<?php

namespace App\Services;

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuestionCodeGeneratorService
{
    /**
     * Genera un nuevo código único para una pregunta que está a punto de ser creada.
     * El código ahora usará el ID de la carrera del autor.
     *
     * @param User $author
     * @return string
     */
    public function generateForNewQuestion(User $author): string
    {
        $sequential = 0;
        DB::transaction(function () use ($author, &$sequential) {
            $count = Question::where('author_id', $author->id)->count();
            $sequential = $count + 1;
        });

        $initials = $this->generateInitials($author->name);
        
        // Obtenemos el ID de la carrera del perfil del autor.
        // Si no tiene una carrera asignada, usamos '0' como un ID por defecto.
        $careerId = $author->career_id ?? 0;

        $timestamp = now()->format('dmY_H_i_s');

        // Ensamblamos el nuevo formato de código.
        // Ejemplo: BAMZ-5-1.05082025_12_38_26 (donde 1 es el ID de la carrera 'Medicina')
        return "{$initials}-{$sequential}-{$careerId}.{$timestamp}";
    }

    /**
     * Actualiza el timestamp de un código existente, preservando el resto de la información.
     */
    public function updateTimestamp(string $existingCode): string
    {
        if (!str_contains($existingCode, '.')) {
            return $existingCode; 
        }
        
        $parts = explode('.', $existingCode);
        $mainPart = $parts[0];
        $newTimestamp = now()->format('dmY_H_i_s');
        return "{$mainPart}.{$newTimestamp}";
    }
    
    /**
     * Genera las iniciales a partir de un nombre completo.
     */
    private function generateInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        return $initials;
    }
}