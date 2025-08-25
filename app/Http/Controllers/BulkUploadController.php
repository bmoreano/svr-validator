<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\ProcessQuestionsCsv;
use App\Jobs\ProcessCriteriaCsv;

class BulkUploadController extends Controller
{
    public function create(): View
    {
        return view('questions-upload.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'questions_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);
        
        $path = $validated['questions_file']->store('questions-uploads');
        
        // --- LÍNEA A DESCOMENTAR ---
        ProcessQuestionsCsv::dispatch($path, Auth::id())->onQueue('low'); // Lo enviamos a la cola de baja prioridad

        return redirect()->route('dashboard')->with('status', 'Archivo de preguntas recibido. Se está procesando en segundo plano y se te notificará al finalizar.');
    }
    
    public function downloadQuestionsTemplate(): StreamedResponse
    {
        $filePath = 'public/templates/plantilla_preguntas.csv';
        echo $filePath;
        echo '           ';
        echo 'storage\app\public\templates\plantilla_preguntas.csv';
        if (!Storage::exists($filePath)) {
            abort(404, 'Archivo de plantilla no encontrado.');
        }
        return Storage::download($filePath, 'plantilla_preguntas.csv');
    }
}