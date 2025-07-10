<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Option;

/**
 * Representa un reactivo o pregunta de opción múltiple en el sistema.
 * 
 * Es la entidad central del proceso de creación y validación.
 * 
 * App\Models\Question
 *
 * @property int $id
 * @property int $author_id
 * @property string $stem
 * @property string $status
 * @property string|null $bibliography
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Option> $options
 * @property-read int|null $options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Validation> $validations
 * @property-read int|null $validations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereBibliography($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereStem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Question extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos son los campos que se pueden llenar al crear o actualizar una pregunta.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'stem',
        'status',
        'bibliography',
        'corregido_administrador', // <-- Añadido
        'comentario_administrador', // <-- Añadido
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Le decimos a Eloquent que trate este campo como un array/objeto.
        'corregido_administrador' => 'array', // <-- Añadido
    ];


    // --- RELACIONES ELOQUENT ---

    /**
     * Define la relación inversa: Una pregunta pertenece a un único autor (User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author(): BelongsTo
    {
        // Esta relación permite hacer $question->author para obtener el usuario que creó la pregunta.
        // Laravel buscará la llave foránea 'author_id' por convención.
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Define la relación: Una pregunta tiene muchas opciones de respuesta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        // Esta relación permite hacer $question->options para obtener una colección de
        // todas las opciones (distractores y respuesta correcta) asociadas a esta pregunta.
        return $this->hasMany(Option::class);
    }

    /**
     * Define la relación: Una pregunta puede tener muchas sesiones de validación.
     * (Por ejemplo, una validación de la IA, y luego una o más validaciones humanas).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function validations(): HasMany
    {
        // Esta relación permite hacer $question->validations para obtener el historial
        // completo de todas las validaciones que se han realizado para esta pregunta.
        return $this->hasMany(Validation::class);
    }


/*  
    Aquí puedes añadir métodos adicionales para lógica de negocio específica,
    como obtener la respuesta correcta, verificar si la pregunta está completa, etc.
    Por ejemplo:
*/
    public function correctOption(): ?Option
    {
        return $this->options()->where('is_correct', true)->first();
    } 
}