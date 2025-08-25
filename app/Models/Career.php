<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Career
 *
 * Representa una carrera o programa académico en el sistema.
 * Sirve para categorizar las preguntas y, potencialmente, a los usuarios.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 */
class Career extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'careers';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * Esto asegura que la propiedad 'is_active' siempre sea un booleano (true/false)
     * en lugar de un entero (1/0) cuando se accede a ella desde el modelo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     * Define la relación: Una Carrera puede tener muchas Preguntas.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function questions(): HasMany { return $this->hasMany(Question::class); }
    /**
     * Define la relación: Una Carrera puede tener muchos Autores.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function users(): HasMany { return $this->hasMany(User::class); }
}