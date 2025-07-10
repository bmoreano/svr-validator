<?php

namespace App\Http\Controllers;

use App\Jobs\ValidateQuestionWithChatGpt;
use App\Jobs\ValidateQuestionWithGemini;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Class QuestionSubmissionController
 *
 * Controlador de Acción Única (invocable) dedicado exclusivamente a
 * gestionar el envío de una pregunta al proceso de validación automática.
 */
class QuestionSubmissionController extends Controller
{
    /**
     * Maneja la solicitud para enviar una pregunta a validación.
     *
     * Al ser un método __invoke, esta clase entera actúa como una única acción,
     * lo que permite registrarla en las rutas sin especificar un nombre de método.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Question $question El modelo de la pregunta, inyectado por Route Model Binding.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, Question $question): RedirectResponse
    {
        // 1. Autorización:
        // Verifica si el usuario actual tiene permiso para realizar esta acción.
        Gate::authorize('submitForValidation', $question);

        // 2. Validación de la Entrada:
        // Asegura que se especifique un motor de IA válido.
        $validated = $request->validate([
            'ai_engine' => ['required', 'string', Rule::in(['chatgpt', 'gemini'])],
        ]);

        // 3. Validación de Estado de la Pregunta:
        // Previene que se envíen preguntas que ya están en proceso o finalizadas.
        if (!in_array($question->status, ['borrador', 'necesita_correccion'])) {
            return back()->with('error', 'Esta pregunta no se puede enviar a validación en su estado actual.');
        }

        // 4. Lógica de Actualización y Despacho del Job:
        try {
            $question->update(['status' => 'en_validacion_ai']);

            $engineName = '';
            switch ($validated['ai_engine']) {
                case 'chatgpt':
                    ValidateQuestionWithChatGpt::dispatch($question);
                    $engineName = 'ChatGPT';
                    break;
                case 'gemini':
                    ValidateQuestionWithGemini::dispatch($question);
                    $engineName = 'Gemini';
                    break;
            }

        } catch (\Exception $e) {
            // Manejo de errores robusto.
            Log::error('Fallo al enviar la pregunta a validación: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'user_id' => optional(auth())->id(),
            ]);
            
            $question->update(['status' => 'borrador']);

            return back()->with('error', 'Ocurrió un error al procesar tu solicitud. Por favor, intenta de nuevo.');
        }

        // 5. Respuesta de Éxito al Usuario.
        return redirect()->route('questions.index')
            ->with('status', "¡Éxito! La pregunta ha sido enviada a validación con {$engineName}.");
    }
}