<?php

namespace App\Http\Controllers;

use App\Jobs\ValidateQuestionWithChatGpt;
use App\Jobs\ValidateQuestionWithGemini;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FileValidationController extends Controller
{
    /**
     * Muestra el formulario para subir archivos de prompt.
     */
    public function create()
    {
        // Obtenemos solo las preguntas del usuario que están en un estado válido.
        $userQuestions = Auth::user()->questions()
            ->whereIn('status', ['borrador', 'necesita_correccion'])
            ->latest()->get();

        return view('validation.from-file.create', ['questions' => $userQuestions]);
    }

    /**
     * Procesa la solicitud de validación con prompts de archivos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer', Rule::exists('questions', 'id')->where('author_id', Auth::id())],
            'ai_engine' => ['required', 'string', Rule::in(['chatgpt', 'gemini'])],
            'prompt_file' => 'nullable|file|mimes:txt|max:1024', // 1MB Max
            'prompt_files' => 'nullable|array',
            'prompt_files.*' => 'file|mimes:txt|max:1024',
        ]);

        $question = Question::find($validated['question_id']);
        $engine = $validated['ai_engine'];

        // Marcamos la pregunta como en proceso de validación.
        $question->update(['status' => 'en_validacion_ai']);
        $dispatchedCount = 0;

        // Lógica para un solo archivo
        if ($request->hasFile('prompt_file')) {
            $content = $request->file('prompt_file')->get();
            // Creamos un prompt temporal para no contaminar los aprobados
            $tempPrompt = \App\Models\Prompt::create([
                'name' => 'Temporal - ' . $request->file('prompt_file')->getClientOriginalName(),
                'ai_engine' => $engine,
                'content' => $content,
                'status' => 'active', // Lo marcamos como activo para usarlo
                'is_active' => true,
            ]);
            
            if ($engine === 'chatgpt') {
                ValidateQuestionWithChatGpt::dispatch($question, $tempPrompt->id);
            } else {
                ValidateQuestionWithGemini::dispatch($question, $tempPrompt->id);
            }
            $dispatchedCount++;
        }

        // Lógica para múltiples archivos
        if ($request->hasFile('prompt_files')) {
            foreach ($request->file('prompt_files') as $file) {
                $content = $file->get();
                $tempPrompt = \App\Models\Prompt::create([
                    'name' => 'Temporal (Batch) - ' . $file->getClientOriginalName(),
                    'ai_engine' => $engine,
                    'content' => $content,
                    'status' => 'active',
                    'is_active' => true,
                ]);

                if ($engine === 'chatgpt') {
                    ValidateQuestionWithChatGpt::dispatch($question, $tempPrompt->id);
                } else {
                    ValidateQuestionWithGemini::dispatch($question, $tempPrompt->id);
                }
                $dispatchedCount++;
            }
        }

        if ($dispatchedCount === 0) {
            return back()->with('error', 'Debes subir al menos un archivo de prompt.');
        }

        return redirect()->route('questions.index')->with('status', "Se han enviado {$dispatchedCount} validaciones desde archivo para la pregunta #{$question->id}.");
    }
}