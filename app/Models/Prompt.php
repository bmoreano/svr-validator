<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ai_engine',
        'content',
        'is_active',
        'status', 
        'review_feedback'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}