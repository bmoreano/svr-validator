<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Criterion;
use App\Models\ValidationDisagreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class ValidationController extends Controller
{
    public function index(): View
    {
        return view('validations.index');
    }
    public function review(Question $question): View
    {
        $allowedStatuses = ['revisado_por_ai', 'revisado_comparativo', 'en_revision_humana'];
        if (!in_array($question->status, $allowedStatuses)) {
            abort(403, 'Esta pregunta no está actualmente en estado de revisión.');
        }

        $criteria = Criterion::orderBy('category')->orderBy('sort_order')->get();
        
        // Esta consulta busca la validación de IA más reciente para la pregunta.
        // Es la clave para que la vista muestre los datos.
        $aiValidation = $question->validations()
            ->whereNotNull('ai_engine')
            ->latest()
            ->with('responses')
            ->first();

        return view('validations.review', compact('question', 'criteria', 'aiValidation'));
    }

    public function processReview1(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'reject_permanently'])],
            'feedback' => 'required_if:decision,reject,reject_permanently|nullable|string|min:10',
        ]);

        switch ($validated['decision']) {
            case 'approve':
                $question->update(['status' => 'aprobado']);
                return redirect()->route('validations.index')->with('status', 'Pregunta aprobada exitosamente.');
            
            case 'reject':
                $question->update(['status' => 'necesita_correccion', 'revision_feedback' => $validated['feedback']]);
                return redirect()->route('validations.index')->with('status', 'La pregunta ha sido devuelta al autor para su corrección.');

            case 'reject_permanently':
                $question->update(['status' => 'rechazado_permanentemente', 'revision_feedback' => $validated['feedback']]);
                return redirect()->route('validations.index')->with('status', 'La pregunta ha sido rechazada permanentemente.');
        }
        
        return back()->with('error', 'Ocurrió un error al procesar la decisión.');
    }


    /**
     * Procesa la decisión del validador humano y registra los desacuerdos con la IA.
     */
    public function processReview(Request $request, Question $question): RedirectResponse
    {
        if (Auth::user()->role !== 'validador') {
            return back()->with('error', 'Solo los validadores pueden procesar una revisión.');
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'reject_permanently'])],
            'feedback' => 'required_if:decision,reject,reject_permanently|nullable|string|min:10',
            'criteria' => 'sometimes|array',
            'criteria.*.response' => ['required', Rule::in(['si', 'no', 'adecuar'])],
        ]);

        // --- LÓGICA PARA DETECTAR Y REGISTRAR DESACUERDOS ---
        $this->logDisagreements($request->input('criteria', []), $question);

        // --- Lógica de la decisión final (sin cambios) ---
        switch ($validated['decision']) {
            case 'approve':
                $question->update(['status' => 'aprobado']);
                return redirect()->route('validations.index')->with('status', 'Pregunta aprobada exitosamente.');
            
            case 'reject':
                $question->update(['status' => 'necesita_correccion', 'revision_feedback' => $validated['feedback']]);
                return redirect()->route('validations.index')->with('status', 'La pregunta ha sido devuelta al autor para su corrección.');

            case 'reject_permanently':
                $question->update(['status' => 'rechazado_permanentemente', 'revision_feedback' => $validated['feedback']]);
                return redirect()->route('validations.index')->with('status', 'La pregunta ha sido rechazada permanentemente.');
        }
        
        return back()->with('error', 'Ocurrió un error al procesar la decisión.');
    }

    
    /**
     * Compara las respuestas humanas con las de la IA y registra las diferencias.
     */
    private function logDisagreements(array $humanResponses, Question $question): void
    {
        // 1. Obtenemos la última validación de IA para esta pregunta.
        $aiValidation = $question->validations()
            ->whereNotNull('ai_engine')
            ->with('responses') // Carga las respuestas de la IA
            ->latest()
            ->first();

        // Si no hay validación de IA, no hay nada que comparar.
        if (!$aiValidation) {
            return;
        }

        // 2. Iteramos sobre las respuestas enviadas por el humano.
        foreach ($humanResponses as $criterionId => $humanData) {
            $humanResponseValue = $humanData['response'];

            // 3. Buscamos la respuesta de la IA para el mismo criterio.
            $aiResponseObject = $aiValidation->responses->firstWhere('criterion_id', $criterionId);

            if ($aiResponseObject) {
                $aiResponseValue = $aiResponseObject->response;

                // 4. Comparamos y, si son diferentes, creamos un registro.
                if ($humanResponseValue !== $aiResponseValue) {
                    ValidationDisagreement::create([
                        'question_id' => $question->id,
                        'criterion_id' => $criterionId,
                        'human_validator_id' => Auth::id(),
                        'ai_engine' => $aiValidation->ai_engine,
                        'ai_response' => $aiResponseValue,
                        'human_response' => $humanResponseValue,
                    ]);
                }
            }
        }
    }
}