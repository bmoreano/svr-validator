<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Criterion
 *
 * @property int $id
 * @property string $text
 * @property string $category
 * @property bool $is_active
 * @property int $sort_order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationResponse> $validationResponses
 * @property-read int|null $validation_responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Criterion whereText($value)
 * @mixin \Eloquent
 */
class Criterion extends Model
{
    use HasFactory;

    /**
     * Indica si el modelo debe tener timestamps (created_at y updated_at).
     *
     * En este caso, los criterios son datos maestros que probablemente no
     * necesitarán seguimiento de cuándo fueron creados o actualizados,
     * por lo que lo establecemos en false. Si quisieras auditar cambios,
     * podrías dejarlo en true y añadir las columnas a la migración.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * Esto es una medida de seguridad de Laravel para prevenir que se
     * guarden datos no deseados en la base de datos.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'text',
        'category',
        'is_active',
        'sort_order',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * Esto asegura que cuando accedas a `$criterion->is_active`,
     * siempre obtendrás un valor booleano (true/false) en lugar de 1/0.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Define la relación inversa con ValidationResponse.
     * Un criterio puede tener muchas respuestas de validación.
     */
    public function validationResponses()
    {
        return $this->hasMany(ValidationResponse::class);
    }
}