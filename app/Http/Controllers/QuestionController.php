<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra una lista paginada de las preguntas del usuario autenticado.
     * Acepta un parámetro opcional 'status' para filtrar los resultados.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Question::class);

        $validStatuses = ['borrador', 'en_validacion_ai', 'revisado_por_ai', 'necesita_correccion', 'aprobado', 'rechazado', 'en_revision'];

        $request->validate([
            'status' => ['nullable', 'string', Rule::in($validStatuses)]
        ]);

        $query = Auth::user()->questions()->latest();

        if ($status = $request->query('status')) {
            if ($status === 'en_revision') {
                $query->whereIn('status', ['en_validacion_ai', 'revisado_por_ai']);
            } else {
                $query->where('status', $status);
            }
        }
        
        $questions = $query->paginate(10)->withQueryString();
        $currentStatus = $request->query('status');

        return view('questions.index', compact('questions', 'currentStatus'));
    }

    /**
     * Muestra el formulario para crear una nueva pregunta.
     */
    public function create(): View
    {
        $this->authorize('create', Question::class);
        return view('questions.create');
    }

    /**
     * Guarda una nueva pregunta en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Question::class);

        $validated = $request->validate([
            'stem' => 'required|string|min:20',
            'bibliography' => 'required|string|min:10',
            'options' => 'required|array|size:4',
            'options.*.text' => 'required|string|min:1',
            'options.*.argumentation' => 'required|string|min:10',
            'correct_option' => 'required|integer|in:0,1,2,3',
        ], [
            'options.size' => 'Debe proporcionar exactamente 4 opciones de respuesta.',
            'options.*.argumentation.required' => 'La argumentación es obligatoria para cada opción.',
            'correct_option.required' => 'Debe seleccionar una opción como la respuesta correcta.',
        ]);

        DB::transaction(function () use ($validated) {
            $question = Auth::user()->questions()->create([
                'stem' => $validated['stem'],
                'bibliography' => $validated['bibliography'],
                'status' => 'borrador',
            ]);

            foreach ($validated['options'] as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => ($index == $validated['correct_option']),
                    'argumentation' => $optionData['argumentation'],
                ]);
            }
        });

        return redirect()->route('questions.index')->with('status', '¡Pregunta creada exitosamente como borrador!');
    }

    /**
     * Muestra los detalles de una pregunta específica.
     */
    public function show(Question $question): View
    {
        $this->authorize('view', $question);
        $question->load('options', 'author');
        return view('questions.show', compact('question'));
    }

    /**
     * Muestra el formulario para editar una pregunta existente.
     */
    public function edit(Question $question): View
    {
        $this->authorize('update', $question);
        $question->load('options');
        return view('questions.edit', compact('question'));
    }

    /**
     * Actualiza una pregunta existente en la base de datos.
     */
    public function update(Request $request, Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'stem' => 'required|string|min:20',
            'bibliography' => 'required|string|min:10',
            'options' => 'required|array|size:4',
            'options.*.text' => 'required|string|min:1',
            'options.*.argumentation' => 'required|string|min:10',
            'correct_option' => 'required|integer|in:0,1,2,3',
        ]);

        DB::transaction(function () use ($validated, $question) {
            $question->update([
                'stem' => $validated['stem'],
                'bibliography' => $validated['bibliography'],
            ]);

            $question->options()->delete();

            foreach ($validated['options'] as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => ($index == $validated['correct_option']),
                    'argumentation' => $optionData['argumentation'],
                ]);
            }
        });

        return redirect()->route('questions.index')->with('status', '¡Pregunta actualizada exitosamente!');
    }

    /**
     * Elimina una pregunta de la base de datos.
     */
    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);
        $question->delete();
        return redirect()->route('questions.index')->with('status', 'Pregunta eliminada exitosamente.');
    }
}