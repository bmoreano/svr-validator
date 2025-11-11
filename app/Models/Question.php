<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt; 
use Illuminate\Database\Eloquent\Casts\Attribute; 

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'author_id',
        'career_id',
        'assigned_validator_id',
        'tema',
        'stem',
        'bibliography',
        'grado_dificultad',
        'poder_discriminacion',
        'status',
        'revision_feedback',
        'content_hash',
        'corregido_administrador', 
        'comentario_administrador',
        'embedding_vector',
        'validation_report',
    ];

    protected $casts = [
        'embedding_vector' => 'array',
        'validation_report' => 'array',
        'corregido_administrador' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(Option::class);
    }
    
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }

    public function assignedValidator()
    {
        return $this->belongsTo(User::class, 'assigned_validator_id');
    }

    // COMENTARIO: Nueva relación: una pregunta puede tener muchas validaciones
    public function validations()
    {
        return $this->hasMany(Validation::class);
    }

    protected function stem(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function bibliography(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    
    /**
     * Define la relación con las revisiones de la pregunta.
     * Asumiendo que tienes un modelo Revision y una tabla 'revisions'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        // Esto asume que tienes un modelo QuestionRevision y que tiene una foreign key 'question_id'
        // Si tu modelo de revisiones se llama diferente (ej. QuestionRevision), ajústalo.
        // También si la foreign key se llama diferente.
        return $this->hasMany(QuestionRevision::class); 
    }
}