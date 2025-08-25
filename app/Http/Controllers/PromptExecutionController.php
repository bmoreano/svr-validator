<?php

namespace App\Http\Controllers;

use App\Jobs\ValidateQuestionJob;
use App\Models\Prompt;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;

class PromptExecutionController extends Controller
{
    /**
     * Muestra el formulario para ejecutar un prompt.
     */
    public function create(): View
    {
        // Obtenemos las preguntas del autor que están en un estado válido.
        $questions = Auth::user()->questions()
            ->whereIn('status', ['borrador', 'necesita_correccion', 'fallo_comparativo'])
            ->latest()->get();
        
        // Obtenemos los prompts aprobados.
        $prompts = Prompt::where('status', 'active')->where('is_active', true)->orderBy('name')->get();

        return view('prompt-execution.create', compact('questions', 'prompts'));
    }

    /**
     * Procesa la solicitud y despacha el job de validación.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer', Rule::exists('questions', 'id')->where('author_id', Auth::id())],
            'prompt_id' => 'required|integer|exists:prompts,id',
        ]);

        $question = Question::find($validated['question_id']);
        $prompt = Prompt::find($validated['prompt_id']);
        
        // El motor de IA se determina por el prompt seleccionado.
        $engineName = $prompt->ai_engine;

        try {
            $question->update(['status' => 'en_validacion_ai']);
            
            ValidateQuestionJob::dispatch(
                $question->id,
                $engineName,
                $prompt->id
            )->onQueue('high');

        } catch (\Exception $e) {
            report($e);
            $question->update(['status' => 'borrador']);
            return back()->with('error', 'No se pudo iniciar el proceso de validación.');
        }

        return redirect()->route('questions.index')
            ->with('status', "La pregunta #{$question->id} ha sido enviada a validación con el prompt '{$prompt->name}'.");
    }
}