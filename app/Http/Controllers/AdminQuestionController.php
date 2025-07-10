<?php
namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use View;

class AdminQuestionController extends Controller
{
    // Muestra el formulario de corrección
    public function correctForm1(Question $question)
    {
        // La vista será muy similar a 'questions.edit'
        return view('admin.questions.correct', compact('question'));
    }
    /**
     * Muestra el formulario para que un administrador corrija una pregunta.
     *
     * @param \App\Models\Question $question
     * @return \Illuminate\Contracts\View\View
     */
    public function correctForm(Question $question): \Illuminate\Contracts\View\View
    {
        // Usamos carga ansiosa (eager loading) para obtener las relaciones
        // 'author' y 'options' en una sola consulta eficiente.
        $question->load('author', 'options');

        // Pasamos la pregunta completa a la vista.
        return view('admin.questions.correct', compact('question'));
    }
    // Guarda la corrección
    public function saveCorrection(Request $request, Question $question)
    {
        $validated = $request->validate([
            'stem' => 'required|string',
            'options' => 'required|array|size:4',
            'options.*.text' => 'required|string',
            'correct_option' => 'required|integer',
            'bibliography' => 'required|string',
            'comentario_administrador' => 'required|string|min:20', // Comentario es obligatorio
        ]);

        DB::transaction(function () use ($validated, $question) {
            // 1. Guardar la "instantánea" del estado original
            $originalData = [
                'stem' => $question->stem,
                'bibliography' => $question->bibliography,
                'options' => $question->options->toArray(),
            ];

            // 2. Actualizar la pregunta con los nuevos datos y el comentario
            $question->update([
                'stem' => $validated['stem'],
                'bibliography' => $validated['bibliography'],
                'corregido_administrador' => $originalData,
                'comentario_administrador' => $validated['comentario_administrador'],
                'status' => 'corregido_por_admin', // Nuevo estado
            ]);

            // 3. Borrar y recrear las opciones
            $question->options()->delete();
            foreach ($validated['options'] as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => ($index == $validated['correct_option']),
                ]);
            }
        });

        return redirect()->route('dashboard')->with('status', 'Pregunta #' . $question->id . ' corregida y guardada exitosamente.');
    }
}