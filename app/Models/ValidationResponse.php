<?php
// app/Models/ValidationResponse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Validation;

/**
 * 
 *
 * @property int $id
 * @property int $validation_id
 * @property int $criterion_id
 * @property string $response
 * @property string|null $comment
 * @property-read \App\Models\Criterion $criterion
 * @property-read Validation $validation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereCriterionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationResponse whereValidationId($value)
 * @mixin \Eloquent
 */
class ValidationResponse extends Model
{
    use HasFactory;

    // Esta tabla tampoco necesita timestamps
    public $timestamps = false;

    // Los campos que se pueden llenar masivamente
    protected $fillable = [
        'validation_id',
        'criterion_id',
        'response',
        'comment',
    ];

    /**
     * Define la relación inversa: una respuesta pertenece a una validación.
     */
    public function validation(): BelongsTo
    {
        return $this->belongsTo(Validation::class);
    }

    /**
     * Define la relación inversa: una respuesta pertenece a un criterio.
     */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }
}