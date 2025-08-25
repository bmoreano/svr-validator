<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/QuestionRevision.php
class QuestionRevision extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id', 'user_id', 'code', 'stem', 'bibliography', 
        'grado_dificultad', 'poder_discriminacion', 'options_snapshot', 'change_reason'
    ];
    protected $casts = ['options_snapshot' => 'array'];

    public function question() { return $this->belongsTo(Question::class); }
    public function user() { return $this->belongsTo(User::class); }
}
