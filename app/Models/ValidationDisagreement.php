<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ValidationDisagreement
 *
 * Registra una instancia donde la evaluación de un validador humano
 * difiere de la evaluación de la IA para un criterio específico.
 *
 * @property int $id
 * @property int $question_id
 * @property int $criterion_id
 * @property int $human_validator_id
 * @property string $ai_engine
 * @property string $ai_response
 * @property string $human_response
 *
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\Criterion $criterion
 * @property-read \App\Models\User $humanValidator
 */
class ValidationDisagreement extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'question_id',
        'criterion_id',
        'human_validator_id',
        'ai_engine',
        'ai_response',
        'human_response',
    ];

    /**
     * Define la relación: Un desacuerdo pertenece a una Pregunta.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Define la relación: Un desacuerdo se refiere a un Criterio.
     */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    /**
     * Define la relación: Un desacuerdo fue registrado por un Validador Humano (User).
     */
    public function humanValidator(): BelongsTo
    {
        // Especificamos la llave foránea 'human_validator_id' ya que no sigue la convención estándar.
        return $this->belongsTo(User::class, 'human_validator_id');
    }
}
