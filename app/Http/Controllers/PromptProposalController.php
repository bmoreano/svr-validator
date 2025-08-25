<?php
// app/Http/Controllers/PromptProposalController.php
namespace App\Http\Controllers;

use App\Jobs\MetaValidatePrompt;
use App\Models\Prompt;
use Illuminate\Http\Request;

class PromptProposalController extends Controller
{
    // Muestra el formulario para proponer un nuevo prompt.
    public function create()
    {
        return view('prompts.propose');
    }

    // Guarda y envía la propuesta de prompt a meta-validación.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ai_engine' => 'required|in:chatgpt,gemini',
            'content' => 'required|string|min:50',
        ]);
        
        // Creamos el prompt con estado pendiente.
        // El 'owner_id' se podría añadir a la tabla de prompts para saber quién lo propuso.
        $prompt = Prompt::create($validated + ['status' => 'pending_review', 'is_active' => false]);
        
        // Despachamos el job para que lo evalúe.
        MetaValidatePrompt::dispatch($prompt);

        return redirect()->route('dashboard')
            ->with('status', '¡Gracias! Tu propuesta de prompt ha sido enviada a revisión automática.');
    }
}