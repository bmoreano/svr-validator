<?php

namespace App\Http\Controllers;

use App\Events\QuestionSubmittedForValidation;
use App\Models\Question;
use App\Models\Prompt;
use App\Models\Career;
use App\Models\User;
use App\Services\QuestionCodeGeneratorService;
use App\Services\QuestionVersioningService;
use App\Services\AiModelHealthService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Log;

class QuestionController extends Controller
{
    // Proporciona los métodos de autorización como $this->authorize()
    use AuthorizesRequests;

    /**
     * Muestra una lista paginada de preguntas.
     * Muestra una lista de TODAS las preguntas del sistema para el administrador,
     * con capacidades de filtrado y ordenación.
     */
    public function index(Request $request): View
    {

        $activePrompts = Prompt::where('status', 'active')->where('is_active', true)->orderBy('name')->get();
        $authorize = $this->authorize('viewAny', Question::class);
        //dd($authorize);
        $user = Auth::user();

        // La consulta base ahora depende del rol.
        if ($user->role === 'administrador') {
            // El admin ve TODAS las preguntas.
            $query = Question::with('author')->latest();
        } else {
            // El autor solo ve SUS preguntas.
            $query = $user->questions()->latest();
        }

        // --- 1. Definir Opciones Válidas para Filtros y Ordenación ---
        // Definimos aquí los valores permitidos para usarlos tanto en la validación
        // como para pasarlos a la vista.
        $authors = User::whereIn('role', ['autor', 'administrador'])->orderBy('name')->pluck('name', 'id');
        $careers = Career::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $statuses = [
            'borrador' => 'Borrador',
            'en_validacion_ai' => 'En Validación IA',
            'revisado_por_ai' => 'Revisado por IA',
            'necesita_correccion' => 'Necesita Corrección',
            'en_validacion_comparativa' => 'En Validacion Comparativa',
            'fallo_comparativo' => 'fallo_comparativo',
            'error_validacion_ai' => 'Error de Validación',
            'aprobado' => 'Aprobado',
            'en_revision_humana' => 'En Revision Humana',
            'revision_feedback' => 'Retroalimentación',
            'corregido_por_admin' => 'Corregido por el administrador',
            'rechazado_permanentemente' => 'Rechazado Permanentemente',
        ];
        $sortableColumns = ['id', 'author_id', 'status', 'created_at'];

        // --- 2. Validación de los Parámetros GET ---
        $validated = $request->validate([
            // Filtros: deben ser opcionales ('nullable')
            'filter_author' => ['nullable', 'integer', Rule::in($authors->keys())],
            'filter_status' => ['nullable', 'string', Rule::in(array_keys($statuses))],
            'filter_career' => ['nullable', 'integer', Rule::in($careers->keys())],
            'filter_date_from' => ['nullable', 'date'],
            'filter_date_to' => ['nullable', 'date', 'after_or_equal:filter_date_from'],

            // Ordenación: deben ser opcionales y estar en nuestra lista blanca
            'sort_by' => ['nullable', 'string', Rule::in($sortableColumns)],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);
        // --- 3. Construir la consulta base ---
        /**$query = Question::query();
        // Si el usuario NO es administrador, restringimos la consulta a sus propias preguntas.
        if ($user->rol !== 'administrador') {
            $query->where('autor_id', $user->id);
        }

        // Siempre cargamos la relación 'author' para evitar N+1 queries en la vista.
        $query->with('author');



        // ////////////*****************  */
        $query = '';
        logger('query: ' . print_r($query, true));
        logger('user->role: ' . $user->role);
        if ($user->role === 'autor') {
            $query = Auth::user()->questions()->with('author');
            logger('SQL Query: ' . $query->toSql());
        }
        if ($user->role === 'administrador') {
            $query = Question::with('author');
        }
        //logger('SQL Query: ' . $query);
        // --- 4. Aplicar Filtros (lógica idéntica al controlador original) ---
        if ($request->filled('filter_author')) {
            $query->where('author_id', $request->input('filter_author'));
        }
        if ($request->filled('filter_status')) {
            $query->where('status', $request->input('filter_status'));
        }
        if ($request->filled('filter_career')) {
            $query->where('career_id', $request->input('filter_career'));
        }
        if ($request->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', $request->input('filter_date_from'));
        }
        if ($request->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', $request->input('filter_date_to'));
        }

        // --- 5. Aplicar Ordenación (lógica idéntica) ---
        $sortableColumns = ['id', 'author_id', 'status', 'created_at'];
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        // -- validar la dirección --
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        if (in_array($sortBy, $sortableColumns, false)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // --- 5. Ejecutar la Consulta y Paginar ---
        $questions = $query->paginate(5)->withQueryString();
        logger('SQL Query: ' . $query->toSql());

        // --- 6. Pasar TODOS los Datos Necesarios a la Vista ---
        return view('admin.questions.index', [
            'prompts' => $activePrompts,
            'questions' => $questions,
            'authors' => $authors,
            'statuses' => $statuses,
            'careers' => $careers,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva pregunta.
     */
    public function create(): View
    {
        $this->authorize('create', Question::class);
        $careers = Career::where('is_active', true)->orderBy('name')->get();
        return view('admin.questions.create', compact('careers'));
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
    public function show(Question $question, AiModelHealthService $healthService)
    {
        $this->authorize('view', $question);

        // --- INICIO DE LA SOLUCIÓN: Lógica de Health Check ---

        // Comentamos la lógica anterior que solo verificaba si la key existía
        // // 1. Verificar qué motores de IA tienen una API key configurada.
        // $availableEngines = [];
        // if (!empty(config('openai.api_key'))) {
        //     $availableEngines['chatgpt'] = 'ChatGPT (OpenAI)';
        // }
        // if (!empty(config('gemini.api_key'))) {
        //     $availableEngines['gemini'] = 'Gemini (Google)';
        // }
        // $activeEnginesCount = count($availableEngines);

        // 1. Verificar el estado funcional REAL de las API keys (usa caché)
        $healthStatus = $healthService->checkModels(); // Retorna ['chatgpt' => bool, 'gemini' => bool]

        // 2. Construir la lista de motores *disponibles y funcionales*
        $availableEngines = [];
        if ($healthStatus['chatgpt']) {
            $availableEngines['chatgpt'] = 'ChatGPT (OpenAI)';
        }
        if ($healthStatus['gemini']) {
            $availableEngines['gemini'] = 'Gemini (Google)';
        }

        // 3. Contar cuántos motores están activos
        $activeEnginesCount = count($availableEngines);
        // --- FIN DE LA SOLUCIÓN ---


        // Cargar relaciones necesarias para la vista
        $question->load([
            'options',
            'author',
            'career',
            'validations.validator',
            'validations.responses.criterion'
        ]);

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
            'availableEngines',     // <-- Motores válidos
            'activeEnginesCount',   // <-- Conteo de motores válidos
            'healthStatus'          // <-- Array [chatgpt => bool, gemini => bool]
        ));
    }

    /**
     * Muestra la página de progreso de validación en vivo.
     */
    public function progress(Question $question)
    {
        // Autorizar que el usuario pueda ver esta pregunta
        $this->authorize('view', $question);

        // Simplemente carga la vista contenedora.
        // El componente Livewire @livewire('validation-progress') se encargará del resto.
        return view('questions.progress', ['question' => $question]);
    }

    public function show1(Question $question): View
    {
        $this->authorize('view', $question);
        $question->load('options', 'author', 'career', 'revisions');
        return view('admin.questions.show', compact('question'));
    }

    /**
     * Muestra el formulario para editar una pregunta existente.
     */
    public function edit(Question $question): View
    {
        $this->authorize('update', $question);
        $question->load('options');
        $careers = Career::where('is_active', true)->orderBy('name')->get();
        return view('admin.questions.edit', compact('question', 'careers'));
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

    /**
     * Permite a un autor enviar una pregunta para validación por IA.
     *
     * @param \App\Models\Question $question
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitForAIVAlidation1(Question $question): RedirectResponse
    {
        // COMENTARIO: Asegurarse de que solo el autor de la pregunta pueda enviarla o un admin.
        if (Auth::id() !== $question->author_id && (!Auth::user() || !Auth::user()->hasRole('administrador'))) {
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        // COMENTARIO: La pregunta debe estar en un estado donde sea elegible para validación AI
        if ($question->status !== 'borrador' && $question->status !== 'necesita_correccion') {
            return redirect()->back()->with('error', 'La pregunta no se puede enviar a validación AI en su estado actual.');
        }

        // COMENTARIO: Cambiar el estado de la pregunta a 'en_validacion_ai'
        $question->status = 'en_validacion_ai';
        $question->save();

        // COMENTARIO: Disparar el evento para iniciar el proceso de validación en segundo plano.
        QuestionSubmittedForValidation::dispatch($question);

        Log::info("Pregunta ID: {$question->id} ha sido enviada para validación AI por el usuario ID: " . (Auth::id() ?? 'Desconocido'));

        return redirect()->back()->with('status', 'La pregunta ha sido enviada para validación automática. Recibirás una notificación con el resultado.');
    }

    /**
     * Permite a un autor enviar una pregunta para validación por IA,
     * aceptando claves API proporcionadas en el request.
     *
     * @param \App\Models\Question $question
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitForAIVAlidation(Question $question, Request $request): RedirectResponse
    {
        // Validación de las claves API (opcionales)
        $request->validate([
            'openai_api_key' => 'nullable|string',
            'google_search_api_key' => 'nullable|string',
            'copyscape_api_key' => 'nullable|string',
        ]);

        // COMENTARIO: Asegurarse de que solo el autor de la pregunta pueda enviarla o un admin.
        if (Auth::id() !== $question->author_id && (!Auth::user() || !Auth::user()->hasRole('administrador'))) {
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        if ($question->status !== 'borrador' && $question->status !== 'necesita_correccion') {
            return redirect()->back()->with('error', 'La pregunta no se puede enviar a validación AI en su estado actual.');
        }

        $question->status = 'en_validacion_ai';
        $question->save();

        // COMENTARIO: Disparar el evento, pasando las claves API obtenidas del request.
        QuestionSubmittedForValidation::dispatch(
            $question,
            $request->input('openai_api_key'),
            $request->input('google_search_api_key'),
            $request->input('copyscape_api_key')
        );

        Log::info("Pregunta ID: {$question->id} ha sido enviada para validación AI por el usuario ID: " . (Auth::id() ?? 'Desconocido'));

        return redirect()->back()->with('status', 'La pregunta ha sido enviada para validación automática. Recibirás una notificación con el resultado.');
    }
}