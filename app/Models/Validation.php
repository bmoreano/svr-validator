<?php

// app/Models/Validation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Question;

/**
 * Representa una única sesión de validación para una pregunta,
 * realizada por un validador específico (ya sea humano o IA).
 * 
 * App\Models\Validation
 *
 * @property int $id
 * @property int $question_id
 * @property int $validator_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Question $question
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationResponse> $responses
 * @property-read int|null $responses_count
 * @property-read \App\Models\User $validator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Validation whereValidatorId($value)
 * @mixin \Eloquent
 */
class Validation extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Es crucial proteger las llaves foráneas para que no se
     * puedan manipular directamente desde una solicitud HTTP.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_id',
        'validator_id',
        'status',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     * Aunque 'status' es un enum en la BD, aquí lo tratamos como string.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // No se necesitan casts especiales para los campos de esta tabla por ahora.
    ];

    // --- RELACIONES ELOQUENT ---

    /**
     * Define la relación inversa: Una validación pertenece a una única pregunta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question(): BelongsTo
    {
        // Esta relación permite hacer $validation->question para obtener la pregunta asociada.
        return $this->belongsTo(Question::class);
    }

    /**
     * Define la relación inversa: Una validación es realizada por un único usuario (validador).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function validator(): BelongsTo
    {
        // Esta relación permite hacer $validation->validator para obtener el usuario que validó.
        // El segundo argumento 'validator_id' le dice a Laravel explícitamente qué
        // columna de llave foránea usar, ya que el nombre 'validator' no sigue
        // la convención estándar de 'user_id'.
        return $this->belongsTo(User::class, 'validator_id');
    }

    /**
     * Define la relación: Una validación tiene muchas respuestas a criterios.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responses(): HasMany
    {
        // Esta es la relación que conecta esta validación con todas sus respuestas
        // individuales a los criterios. Permite hacer $validation->responses.
        return $this->hasMany(ValidationResponse::class);
    }
}