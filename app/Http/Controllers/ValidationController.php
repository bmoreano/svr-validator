<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Criterion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ValidationController extends Controller
{
    /**
     * Muestra la interfaz para que un validador humano revise una pregunta.
     */
    public function show(Question $question): View
    {
        Gate::authorize('view-validation-interface', $question);

        $criteria = Criterion::where('is_active', true)->orderBy('category')->orderBy('sort_order')->get();
        $aiValidator = User::where('email', 'ai@svr.com')->first();
        
        $aiValidation = $question->validations()
            ->where('validator_id', $aiValidator?->id)
            ->latest()
            ->with('responses')
            ->first();

        return view('validations.show', compact('question', 'criteria', 'aiValidation'));
    }

    /**
     * Guarda la validación enviada por un validador humano.
     */
    public function store(Request $request, Question $question): RedirectResponse
    {
        Gate::authorize('store-validation', $question);

        // Validación que ahora incluye 'rechazado' como estado final válido.
        $validated = $request->validate([
            'criteria' => 'required|array',
            'criteria.*.response' => ['required', Rule::in(['si', 'no', 'adecuar'])],
            'criteria.*.comment' => 'required_if:criteria.*.response,no,adecuar|nullable|string|max:1000',
            'final_status' => ['required', Rule::in(['aprobado', 'necesita_correccion', 'rechazado'])],
        ], [
            'criteria.*.comment.required_if' => 'Debes añadir un comentario si la respuesta no es "Sí".',
            'final_status.required' => 'Debes seleccionar una decisión final para la pregunta.',
        ]);

        try {
            DB::transaction(function () use ($validated, $question) {
                $validation = $question->validations()->create([
                    'validator_id' => Auth::id(),
                    'status' => 'completado'
                ]);

                foreach ($validated['criteria'] as $criterionId => $data) {
                    $validation->responses()->create([
                        'criterion_id' => $criterionId,
                        'response' => $data['response'],
                        'comment' => $data['comment'] ?? null,
                    ]);
                }

                // El estado de la pregunta se actualiza con la decisión del validador.
                $question->update(['status' => $validated['final_status']]);

                // Opcional: Notificar al autor sobre el resultado.
                // $question->author->notify(new QuestionReviewCompleted($question, $validated['final_status']));
            });
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Ocurrió un error inesperado al guardar la validación.');
        }

        return redirect()->route('dashboard')->with('status', "¡Validación guardada con éxito! La pregunta ha sido marcada como '{$validated['final_status']}'.");
    }
}