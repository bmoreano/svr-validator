<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use App\Jobs\MetaValidatePrompt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

/**
 * Class PromptController
 *
 * Gestiona el CRUD completo para los Prompts del sistema.
 * Solo accesible para administradores.
 */
class PromptController extends Controller
{
    /**
     * Muestra una lista paginada de todos los prompts del sistema.
     */
    public function index(): View
    {
        $prompts = Prompt::latest()->paginate(15);

        return view('admin.prompts.index', compact('prompts'));
    }

    /**
     * Muestra el formulario para crear un nuevo prompt.
     */
    public function create(): View
    {
        return view('admin.prompts.create');
    }

    /**
     * Guarda un nuevo prompt en la base de datos y lo envía a revisión.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ai_engine' => 'required|in:chatgpt,gemini',
            'content' => 'required|string|min:50',
        ]);

        // Los prompts creados por un admin se consideran pre-aprobados, pero aun así
        // los pasaremos por la meta-validación como una buena práctica de seguridad.
        $prompt = Prompt::create($validated + [
            'status' => 'pending_review',
            'is_active' => false, // Se activará automáticamente si la meta-validación es exitosa
        ]);

        // Despachamos el job para que lo evalúe
        MetaValidatePrompt::dispatch($prompt);

        return redirect()->route('admin.prompts.index')
            ->with('status', 'Prompt enviado a revisión automática. El estado se actualizará en breve.');
    }

    /**
     * Muestra los detalles de un prompt específico (solo lectura).
     */
    public function show(Prompt $prompt): View
    {
        return view('admin.prompts.show', compact('prompt'));
    }

    /**
     * Muestra el formulario para editar un prompt existente.
     */
    public function edit(Prompt $prompt): View
    {
        return view('admin.prompts.edit', compact('prompt'));
    }

    /**
     * Actualiza un prompt existente en la base de datos.
     */
    public function update(Request $request, Prompt $prompt): RedirectResponse
    {
        $validated = $request->validate([
            // La regla 'unique' ignora el prompt actual al verificar
            'name' => ['required', 'string', 'max:255', Rule::unique('prompts')->ignore($prompt->id)],
            'description' => 'nullable|string',
            'ai_engine' => ['required', Rule::in(['chatgpt', 'gemini'])],
            'content' => 'required|string|min:50',
            'is_active' => 'sometimes|boolean',
            'status' => ['required', Rule::in(['pending_review', 'active', 'rejected'])],
            'review_feedback' => 'nullable|string',
        ]);

        // Convertimos el 'sometimes' a un valor booleano explícito
        $validated['is_active'] = $request->has('is_active');

        $prompt->update($validated);

        // Si el admin re-envía a revisión, despachamos el job de nuevo
        if ($validated['status'] === 'pending_review') {
            MetaValidatePrompt::dispatch($prompt);
            return redirect()->route('admin.prompts.index')
                ->with('status', "Prompt #{$prompt->id} actualizado y re-enviado a revisión.");
        }

        return redirect()->route('admin.prompts.index')
            ->with('status', "Prompt #{$prompt->id} actualizado exitosamente.");
    }

    /**
     * Elimina un prompt de la base de datos.
     */
    public function destroy(Prompt $prompt): RedirectResponse
    {
        try {
            $prompt->delete();
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el prompt. Es posible que esté en uso.');
        }

        return redirect()->route('admin.prompts.index')
            ->with('status', 'Prompt eliminado exitosamente.');
    }
}
