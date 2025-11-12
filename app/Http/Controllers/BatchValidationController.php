<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBatchFile;
use App\Models\Question;
use App\Models\Prompt; // <-- AÑADIR IMPORTACIÓN
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Class BatchValidationController
 *
 * Gestiona la funcionalidad de subida y procesamiento de archivos
 * para la validación de reactivos por lotes.
 */
class BatchValidationController extends Controller
{
    // Usamos este trait para tener acceso al método $this->authorize(),
    // lo que nos permite centralizar la lógica de permisos en las Policies.
    use AuthorizesRequests;

    /**
     * Muestra el formulario para subir el archivo de validación por lotes.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $this->authorize('create', Question::class);
        
        // --- INICIO DE LA SOLUCIÓN (Flujo completo) ---
        // Pasamos los prompts a la vista para que el selector pueda mostrarlos.
        // Asumimos que solo queremos mostrar prompts que ya están 'aprobados'.
        $prompts = Prompt::where('status', 'aprobado')->orderBy('name')->get();
        
        return view('questions.batch-upload', compact('prompts'));
        // --- FIN DE LA SOLUCIÓN ---
    }

    /**
     * Valida la solicitud, almacena el archivo subido y despacha
     * un job para su procesamiento en segundo plano.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Question::class);

       
        $validated = $request->validate([
            'reactivos_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'ai_engine' => ['required', 'string', Rule::in(['chatgpt', 'gemini'])],
            'prompt_id' => 'nullable|exists:prompts,id',
        ], [
            'reactivos_file.required' => 'Debes seleccionar un archivo para subir.',
            'reactivos_file.mimes' => 'El archivo debe ser de tipo CSV.',
            'reactivos_file.max' => 'El archivo no puede pesar más de 10MB.', // Ajustado a 10240
            'prompt_id.exists' => 'El prompt seleccionado no es válido.',
        ]);

        try {
            $path = $request->file('reactivos_file')->store('batch-uploads');
            
            // 2. Obtener el prompt_id (será null si no se envió)
            $prompt_id = $validated['prompt_id'] ?? null;

            // 3. Despachamos el job con los 4 argumentos correctos
            ProcessBatchFile::dispatch(
                $path,
                optional(auth())->id,
                $validated['ai_engine'],
                $prompt_id 
            );

        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'No se pudo procesar el archivo en este momento. Por favor, inténtelo de nuevo más tarde.');
        }

        return redirect()->route('questions.index')
            ->with('status', '¡Archivo recibido correctamente! Las preguntas se están creando y validando en segundo plano. Este proceso puede tardar varios minutos.');
    }
}