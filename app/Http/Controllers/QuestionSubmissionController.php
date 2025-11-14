<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Jobs\ValidateQuestionWithChatGpt;
use App\Jobs\ValidateQuestionWithGemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuestionSubmissionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Maneja la solicitud para enviar una pregunta a validación de IA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Question $question)
    {
        // 1. Autorizar la acción
        $this->authorize('submit', $question);

        // 2. Validar la entrada del formulario
        $validated = $request->validate([
            'prompt_id' => 'nullable|exists:prompts,id',
            'ai_engine' => ['required', 'string', Rule::in(['chatgpt', 'gemini', 'comparative'])],
        ]);

        $prompt_id = $validated['prompt_id'] ?? null;
        $aiEngine = $validated['ai_engine'];

        // 3. Prevenir doble envío si ya está en proceso
        if (in_array($question->status, ['en_validacion_ai', 'en_validacion_comparativa'])) {
            return back()->with('error', 'Esta pregunta ya está siendo procesada por la IA.');
        }

        try {
            // 4. Lógica de Despacho
            if ($aiEngine === 'comparative') {
                // Opción A: Validación Comparativa
                $question->update(['status' => 'en_validacion_comparativa']);

                $jobs = [
                    new ValidateQuestionWithChatGpt($question->id, $prompt_id),
                    new ValidateQuestionWithGemini($question->id, $prompt_id),
                ];
                
                Bus::batch($jobs)
                   ->name('Comparative Validation for Q: ' . $question->id)
                   ->dispatch();

            } else {
                // Opción B: Validación con un solo motor
                $question->update(['status' => 'en_validacion_ai']);

                // --- INICIO DE LA SOLUCIÓN ---
                //
                // Aquí comprobamos el valor de $aiEngine.
                // Si es 'gemini', despachamos ValidateQuestionWithGemini.
                // De lo contrario (else), despachamos ValidateQuestionWithChatGpt.
                
                if ($aiEngine === 'gemini') {
                    ValidateQuestionWithGemini::dispatch($question->id, $prompt_id);
                } else {
                    ValidateQuestionWithChatGpt::dispatch($question->id, $prompt_id);
                }

                // --- FIN DE LA SOLUCIÓN ---
            }

        } catch (\Exception $e) {
            report($e);
            // Revertir el estado si el despacho falla
            $question->update(['status' => 'borrador']); 
            return back()->with('error', 'Error al despachar el job de validación: ' . $e->getMessage());
        }

        // 5. Redirigir con mensaje de éxito
        
        // Comentamos la redirección original
        // return redirect()->route('questions.show', $question)
        //     ->with('status', '¡Éxito! La pregunta ha sido enviada a validación de IA.');

        // Redirigimos a la nueva página de progreso en vivo
        return redirect()->route('questions.progress', $question)
            ->with('status', 'La pregunta está siendo procesada. Esta página se actualizará automáticamente.');
    }
}