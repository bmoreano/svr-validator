<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\ProcessCriteriaCsv;

class CriteriaUploadController extends Controller
{
    /**
     * Muestra el formulario para la carga masiva de criterios.
     */
    public function create(): View
    {
        return view('admin.criteria-upload.create');
    }

    /**
     * Procesa el archivo de criterios subido y despacha el job.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'criteria_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $validated['criteria_file']->store('criteria-uploads');
        ProcessCriteriaCsv::dispatch($path, Auth::id());

        return redirect()->route('dashboard')->with('status', 'Archivo de criterios recibido. Se está procesando en segundo plano.');
    }

    /**
     * Descarga la plantilla CSV para la carga masiva de criterios.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $filePath = 'public/templates/plantilla_criterios.csv';
        if (!Storage::exists($filePath)) {
            abort(404, 'Archivo de plantilla no encontrado.');
        }
        return Storage::download($filePath, 'plantilla_criterios.csv');
    }
}
