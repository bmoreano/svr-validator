<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question; // Asegúrate de que tu modelo se llame así

class QuestionsController99 extends Controller
{
    /**
     * Muestra una lista de todas las preguntas.
     */
    public function index()
    {
        $questions = Question::latest()->where('status','borrador')->get();
        return view('admin.questions.index', compact('questions'));
    }
}