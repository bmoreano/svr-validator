<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBatchFile;
use App\Models\Question;
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
        // Reutilizamos la QuestionPolicy para verificar si el usuario actual
        // tiene permiso para crear preguntas. Si no, Laravel lanzará
        // automáticamente una excepción de autorización (error 403).
        $this->authorize('create', Question::class);
        
        return view('questions.batch-upload');
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
        // Verificamos de nuevo que el usuario tenga permiso para esta acción.
        $this->authorize('create', Question::class);

        // Se definen las reglas de validación para la solicitud.
        // Esto asegura que solo se procesen archivos válidos y seguros.
        $validated = $request->validate([
            // 'reactivos_file' debe ser:
            // - 'required': Obligatorio en la solicitud.
            // - 'file': Debe ser un archivo válido.
            // - 'mimes:csv,txt': Solo se permiten extensiones .csv o .txt.
            // - 'max:2048': El tamaño máximo del archivo es 2MB (2048 KB).
            'reactivos_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            
            // 'ai_engine' es obligatorio y su valor debe ser uno de los definidos en la lista.
            'ai_engine' => ['required', 'string', Rule::in(['chatgpt', 'gemini'])],
        ], [
            // Mensajes de error personalizados para una mejor experiencia de usuario.
            'reactivos_file.required' => 'Debes seleccionar un archivo para subir.',
            'reactivos_file.mimes' => 'El archivo debe ser de tipo CSV.',
            'reactivos_file.max' => 'El archivo no puede pesar más de 2MB.',
        ]);

        try {
            // Guardamos el archivo subido en el disco de almacenamiento de Laravel,
            // dentro de una carpeta 'batch-uploads' para mantener el orden.
            // El método store() genera un nombre de archivo único para evitar colisiones.
            $path = $request->file('reactivos_file')->store('batch-uploads');

            // Despachamos el job principal que procesará el archivo.
            // Le pasamos la información necesaria para que funcione de forma independiente:
            // la ruta del archivo, el ID del usuario que lo subió y el motor de IA elegido.
            // Toda la lógica pesada se delega a este job, que se ejecutará en la cola.
            ProcessBatchFile::dispatch(
                $path,
                auth()->id(),
                $validated['ai_engine']
            );

        } catch (\Exception $e) {
            // Si algo falla (ej. problemas de permisos de escritura, Redis no disponible),
            // registramos el error para depuración y notificamos al usuario.
            report($e);
            return back()->with('error', 'No se pudo procesar el archivo en este momento. Por favor, inténtelo de nuevo más tarde.');
        }

        // Si todo sale bien, redirigimos al usuario a su lista de preguntas
        // con un mensaje de éxito que gestiona sus expectativas.
        return redirect()->route('questions.index')
            ->with('status', '¡Archivo recibido correctamente! Las preguntas se están creando y validando en segundo plano. Este proceso puede tardar varios minutos.');
    }
}