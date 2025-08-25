<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Prompt;
use App\Models\Career;
use App\Services\QuestionCodeGeneratorService;
use App\Services\QuestionVersioningService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    // Proporciona los métodos de autorización como $this->authorize()
    use AuthorizesRequests;

    /**
     * Muestra una lista paginada de preguntas.
     * La vista es global para administradores y personal para autores.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Question::class);

        $activePrompts = Prompt::where('status', 'active')->where('is_active', true)->orderBy('name')->get();
        $query = Question::query();
        $relationsToLoad = ['career', 'validations'];

        if (Auth::user()->role === 'administrador') {
            $relationsToLoad[] = 'author';
        } else {
            $query->where('author_id', Auth::id());
        }
        
        $questions = $query->with($relationsToLoad)->latest()->paginate(15);

        return view('questions.index', [
            'questions' => $questions,
            'prompts' => $activePrompts,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva pregunta.
     */
    public function create(): View
    {
        $this->authorize('create', Question::class);
        $careers = Career::where('is_active', true)->orderBy('name')->get();
        return view('questions.create', compact('careers'));
    }

    /**
     * Guarda una nueva pregunta en la base de datos y crea su primera revisión.
     */
    public function store(Request $request, QuestionVersioningService $versioningService, QuestionCodeGeneratorService $codeGenerator): RedirectResponse
    {
        $this->authorize('create', Question::class);

        $validated = $request->validate([
            'stem' => 'required|string|min:20',
            'bibliography' => 'required|string|min:10',
            'career_id' => 'required|integer|exists:careers,id',
            'tema' => 'nullable|string|max:255',
            'options' => 'required|array|size:4',
            'options.*.text' => 'required|string|min:1',
            'options.*.argumentation' => 'nullable|string|max:1000',
            'correct_option' => 'required|integer|in:0,1,2,3',
            'grado_dificultad' => ['required', Rule::in(['muy_facil', 'facil', 'dificil', 'muy_dificil'])],
            'poder_discriminacion' => ['required', Rule::in(['muy_alto', 'alto', 'moderado', 'bajo', 'muy_bajo'])],
        ]);

        DB::transaction(function () use ($validated, $versioningService, $codeGenerator) {
            $author = Auth::user();
            $newCode = $codeGenerator->generateForNewQuestion($author);

            $question = $author->questions()->create([
                'code' => $newCode,
                'stem' => $validated['stem'],
                'bibliography' => $validated['bibliography'],
                'status' => 'borrador',
                'career_id' => $validated['career_id'],
                'tema' => $validated['tema'],
                'grado_dificultad' => $validated['grado_dificultad'],
                'poder_discriminacion' => $validated['poder_discriminacion'],
            ]);

            foreach ($validated['options'] as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => ($index == $validated['correct_option']),
                    'argumentation' => $optionData['argumentation'],
                ]);
            }
            
            $versioningService->createRevision($question->fresh(), 'Creación Inicial');
        });

        return redirect()->route('questions.index')->with('status', 'Pregunta creada exitosamente como borrador.');
    }

    /**
     * Muestra los detalles de una pregunta específica.
     */
    public function show(Question $question): View
    {
        $this->authorize('view', $question);
        $question->load('options', 'author', 'career', 'revisions');
        return view('questions.show', compact('question'));
    }

    /**
     * Muestra el formulario para editar una pregunta existente.
     */
    public function edit(Question $question): View
    {
        $this->authorize('update', $question);
        $question->load('options');
        $careers = Career::where('is_active', true)->orderBy('name')->get();
        return view('questions.edit', compact('question', 'careers'));
    }

    /**
     * Actualiza una pregunta existente y crea una nueva revisión.
     */
    public function update(Request $request, Question $question, QuestionVersioningService $versioningService, QuestionCodeGeneratorService $codeGenerator): RedirectResponse
    {
        $this->authorize('update', $question);
        
        $validated = $request->validate([
            'stem' => 'required|string|min:20',
            'bibliography' => 'required|string|min:10',
            'career_id' => 'required|integer|exists:careers,id',
            'tema' => 'nullable|string|max:255',
            'options' => 'required|array|size:4',
            'options.*.id' => 'nullable|integer|exists:options,id',
            'options.*.text' => 'required|string|min:1',
            'options.*.argumentation' => 'nullable|string|max:1000',
            'correct_option' => 'required|integer|in:0,1,2,3',
            'grado_dificultad' => ['required', Rule::in(['muy_facil', 'facil', 'dificil', 'muy_dificil'])],
            'poder_discriminacion' => ['required', Rule::in(['muy_alto', 'alto', 'moderado', 'bajo', 'muy_bajo'])],
        ]);

        DB::transaction(function () use ($validated, $question, $versioningService, $codeGenerator) {
            $newCode = $codeGenerator->updateTimestamp($question->code);
            
            $question->update([
                'code' => $newCode,
                'stem' => $validated['stem'],
                'bibliography' => $validated['bibliography'],
                'career_id' => $validated['career_id'],
                'tema' => $validated['tema'],
                'grado_dificultad' => $validated['grado_dificultad'],
                'poder_discriminacion' => $validated['poder_discriminacion'],
            ]);
            
            $existingOptionIds = [];
            foreach ($validated['options'] as $index => $optionData) {
                $option = $question->options()->updateOrCreate(
                    ['id' => $optionData['id'] ?? null],
                    [
                        'option_text' => $optionData['text'],
                        'is_correct' => ($index == $validated['correct_option']),
                        'argumentation' => $optionData['argumentation'],
                    ]
                );
                $existingOptionIds[] = $option->id;
            }
            $question->options()->whereNotIn('id', $existingOptionIds)->delete();
            
            $versioningService->createRevision($question->fresh(), 'Actualización por Autor');
        });

        return redirect()->route('questions.index')->with('status', 'Pregunta actualizada exitosamente.');
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