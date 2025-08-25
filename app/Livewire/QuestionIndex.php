<?php

namespace App\Livewire;

use App\Models\Prompt;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class QuestionIndex extends Component
{
    use WithPagination;

    // Esto asegura que la paginación de Livewire use los estilos de Tailwind.
    protected $paginationTheme = 'tailwind';

    /**
     * Renderiza el componente.
     * Esta función se ejecuta en la carga inicial y cada vez que el componente se refresca.
     */
    public function render()
    {
        // Obtenemos los prompts activos para los selectores en la vista.
        $activePrompts = Prompt::where('status', 'active')->where('is_active', true)->orderBy('name')->get();
        
        $query = Question::query();
        $relationsToLoad = ['career', 'validations'];

        if (Auth::user()->role === 'administrador') {
            $relationsToLoad[] = 'author';
        } else {
            $query->where('author_id', Auth::id());
        }
        
        $questions = $query->with($relationsToLoad)->latest()->paginate(15);

        return view('livewire.question-index', [
            'questions' => $questions,
            'prompts' => $activePrompts,
        ]);
    }
}