<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa una opción de respuesta para una pregunta de opción múltiple.
 * 
 * Puede ser la respuesta correcta o un distractor.
 * 
 * App\Models\Option
 *
 * @property int $id
 * @property int $question_id
 * @property string $option_text
 * @property bool $is_correct
 * @property string|null $argumentation
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereArgumentation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereOptionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereQuestionId($value)
 * @mixin \Eloquent
 */
class Option extends Model
{
    use HasFactory;

    /**
     * Indica si el modelo debe tener timestamps (created_at y updated_at).
     * Para las opciones, generalmente no es necesario auditar cuándo se
     * crearon o modificaron, por lo que lo desactivamos para mantener
     * la tabla más limpia.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'argumentation',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     * Es crucial para que 'is_correct' se maneje como un booleano (true/false)
     * en lugar de un entero (1/0).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'option_text' => 'encrypted',
        'argumentation' => 'encrypted',
    ];


    
    // --- RELACIÓN ELOQUENT ---

    /**
     * Define la relación inversa: Una opción pertenece a una única pregunta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question(): BelongsTo
    {
        // Esta relación permite hacer $option->question para obtener la
        // pregunta a la que pertenece esta opción.
        return $this->belongsTo(Question::class);
    }
}