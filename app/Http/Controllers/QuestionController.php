<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Prompt;
use App\Models\Career;
use App\Services\QuestionService;
use App\Services\QuestionVersioningService;
use App\Services\QuestionCodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    protected $versioningService;

    public function __construct(QuestionVersioningService $versioningService)
    {
        $this->versioningService = $versioningService;
    }

    /**
     * Muestra el dashboard principal, que es el índice de preguntas.
     */
    public function index()
    {
        // El índice ahora se maneja aquí.
        // Devolvemos la vista 'dashboard' porque es la que
        // contiene el componente @livewire('question-index')
        return view('dashboard');
    }
    /**
     * Muestra el formulario para crear una nueva pregunta.
     */
    public function create()
    {
        $this->authorize('create', Question::class);
        $careers = Career::orderBy('name')->get();
        return view('admin.questions.create', compact('careers'));
    }

    /**
     * Almacena una nueva pregunta en la base de datos.
     */
    public function store(Request $request, QuestionCodeGeneratorService $codeGenerator)
    {
        $this->authorize('create', Question::class);

        $validated = request()->validate([
            'career_id' => 'required|exists:careers,id',
            'stem' => 'required|string',
            'bibliography' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'correct_option' => 'required|integer|min:0',
        ]);

        $question = Question::create([
            'author_id' => Auth::id(),
            'career_id' => $validated['career_id'],
            'stem' => $validated['stem'],
            'bibliography' => $validated['bibliography'],
            'status' => 'borrador',
        ]);

        $question->refresh();
        // Generar código único
        $question->code = $codeGenerator->generateForNewQuestion($question);
        $question->save();

        foreach ($validated['options'] as $index => $optionData) {
            $question->options()->create([
                'text' => $optionData['text'],
                'is_correct' => ($index == $validated['correct_option']),
            ]);
        }

        return redirect()->route('questions.show', $question)
            ->with('status', 'Reactivo creado exitosamente con el código: ' . $question->code);
    }

    /**
     * Muestra el detalle de una pregunta específica.
     */
    public function show(Question $question)
    {
        $this->authorize('view', $question);

        // Aseguramos que siempre tengamos una instancia de Question
        $question->load([
            'options',
            'author',
            'career',
            'validations.validator',
            'validations.responses.criterion'
        ]);

        // --- INICIO DE LA SOLUCIÓN ---
        // 1. Verificar qué motores de IA tienen una API key configurada.
        // Usamos config() para leer de /config/openai.php y /config/gemini.php
        $availableEngines = [];

        // Revisamos si la API key de OpenAI (ChatGPT) está presente
        if (!empty(config('openai.api_key'))) {
            $availableEngines['chatgpt'] = 'ChatGPT (OpenAI)';
        }

        // Revisamos si la API key de Gemini está presente
        if (!empty(config('gemini.api_key'))) {
            $availableEngines['gemini'] = 'Gemini (Google)';
        }

        // 2. Contar cuántos motores están activos
        $activeEnginesCount = count($availableEngines);
        // --- FIN DE LA SOLUCIÓN ---


        // Lógica para obtener la última validación (IA o Humana)
        $latestValidation = $question->validations()
            ->orderBy('created_at', 'desc')
            ->first();

        $humanValidation = $question->validations()
            ->whereHas('validator', fn($q) => $q->where('role', '!=', 'ia'))
            ->orderBy('created_at', 'desc')
            ->first();

        // Pasamos las nuevas variables a la vista
        return view('questions.show', compact(
            'question',
            'latestValidation',
            'humanValidation',
            'availableEngines', // <-- Pasamos los motores disponibles
            'activeEnginesCount' // <-- Pasamos el conteo
        ));
    }
    /**
     * Muestra el formulario para editar una pregunta.
     */
    public function edit(Question $question)
    {
        $this->authorize('update', $question);

        if ($question->status == 'en_espera') {
            return redirect()->route('questions.show', $question)
                ->with('error', 'Esta pregunta está "En Espera" y no puede ser editada. Responde a la notificación para desbloquearla.');
        }

        $careers = Career::orderBy('name')->get();
        return view('admin.questions.edit', compact('question', 'careers'));
    }

    /**
     * Actualiza una pregunta existente en la base de datos.
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'career_id' => 'required|exists:careers,id',
            'stem' => 'required|string',
            'bibliography' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'correct_option' => 'required|string', // El ID de la opción correcta
        ]);

        // Guardar una revisión antes de actualizar
        $this->versioningService->createRevision($question, ' Modificación manual');

        // Actualizar la pregunta
        $question->update([
            'career_id' => $validated['career_id'],
            'stem' => $validated['stem'],
            'bibliography' => $validated['bibliography'],
            'status' => 'borrador', // Volver a borrador después de editar
        ]);

        // Actualizar opciones
        foreach ($validated['options'] as $optionId => $optionData) {
            $option = $question->options()->find($optionId);
            if ($option) {
                $option->update([
                    'text' => $optionData['text'],
                    'is_correct' => ($optionId == $validated['correct_option']),
                ]);
            }
        }

        return redirect()->route('questions.show', $question)
            ->with('status', 'Reactivo actualizado exitosamente.');
    }

    /**
     * Elimina una pregunta de la base de datos.
     */
    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        try {
            $question->delete();
            return redirect()->route('questions.index')
                ->with('status', 'Reactivo eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el reactivo. Es posible que tenga validaciones asociadas.');
        }
    }
}