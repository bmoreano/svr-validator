<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Jobs\ValidateQuestionJob;

class QuestionSubmissionController extends Controller
{
    public function __invoke(Request $request, Question $question): RedirectResponse
    {
        Gate::authorize('submitForValidation', $question);

        $validated = $request->validate([
            'ai_engine' => ['required', 'string', Rule::in(['chatgpt', 'gemini'])],
            'prompt_id' => 'nullable|integer|exists:prompts,id',
        ]);
        if (!in_array($question->status, ['borrador', 'necesita_correccion'])) {
            return back()->with('error', 'Esta pregunta no se puede enviar a validación en su estado actual.');
        }

        try {
            $question->update(['status' => 'en_validacion_ai']);
            
            // Despachamos el único Job, pasándole el nombre del motor
            ValidateQuestionJob::dispatch(
                $question,
                $validated['ai_engine'],
                $validated['prompt_id'] ?? null
            )->onQueue('high'); // Usamos la cola de alta prioridad   
        } catch (\Exception $e) {
            Log::error('Fallo al enviar la pregunta a validación: ' . $e->getMessage());
            $question->update(['status' => 'borrador']);
            return back()->with('error', 'Ocurrió un error al procesar tu solicitud.');
        }

        return redirect()->route('questions.index')
            ->with('status', "Pregunta enviada a validación con {$validated['ai_engine']}.");
    }
}