<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Question;
use App\Models\Career; 
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Illuminate\Support\Facades\Log; // <-- INICIO SOLUCIÓN: Importar el Logger

class QuestionIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterByCareer = '';
    public $filterByStatus = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Para los modales
    public $confirmingQuestionDeletion = false;
    public $questionToDeleteId = null;
    
    public $assigningValidator = false;
    public $questionToAssignId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterByCareer' => ['except' => ''],
        'filterByStatus' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // ... (comentando métodos updatingSearch, updatingFilterByCareer, sortBy por brevedad)

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFilterByCareer()
    {
        $this->resetPage();
    }
    public function updatingFilterByStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }


    public function render(){
        // Este log se ejecutará CADA VEZ que el componente se refresque (ej. en wire:poll);
        Log::info("--- render() ---");
        Log::info("Renderizando... \$assigningValidator = " . ($this->assigningValidator ? 'true' : 'false'));
        // --- FIN SOLUCIÓN ---

        $user = Auth::user();
        
        $query = Question::with(['author', 'career']);

        // Filtrar por rol
        if ($user->hasRole('autor')) {
            $query->where('author_id', $user->id);
        } elseif ($user->hasRole('validador')) {
            $query->where('assigned_validator_id', $user->id);
        }
        // El admin puede ver todo (ningún filtro de rol)

        // (lógica de filtros...)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('stem', 'like', '%'.$this->search.'%')
                  ->orWhere('code', 'like', '%'.$this->search.'%');
            });
        }
        if ($this->filterByStatus) {
            $query->where('status', $this->filterByStatus);
        }
        if ($this->filterByCareer) {
            $query->where('career_id', $this->filterByCareer);
        }

        // Aplicar ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        $questions = $query->paginate(10);
        $careers = Career::orderBy('name')->get();

        // Obtener lista de validadores para el modal
        $validators = User::where('role', 'validador')->orderBy('name')->get();

        return view('livewire.question-index', [
            'questions' => $questions,
            'careers' => $careers,
            'validators' => $validators,
        ]);
    }
    
    public function confirmQuestionDeletion($id)
    {
        $this->questionToDeleteId = $id;
        $this->confirmingQuestionDeletion = true;
    }

    public function deleteQuestion()
    {
        $question = Question::findOrFail($this->questionToDeleteId);
        
        if (Gate::denies('delete', $question)) {
            session()->flash('error', 'No tienes permiso para eliminar esta pregunta.');
            $this->confirmingQuestionDeletion = false;
            return;
        }

        try {
            $question->delete();
            session()->flash('status', 'Pregunta eliminada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudo eliminar la pregunta. Es posible que tenga validaciones asociadas.');
        }

        $this->confirmingQuestionDeletion = false;
    }

    // --- INICIO SOLUCIÓN: Logger en el método de clic ---
    /**
     * Este método es llamado por wire:click desde la vista.
     * Establece las propiedades públicas para mostrar
     * el modal de asignación.
     */
    public function openAssignValidatorModal($id)
    {
        // 1. Log para saber que el método fue alcanzado
        Log::info("--- INICIO openAssignValidatorModal ---");
        Log::info("1. openAssignValidatorModal() fue llamado.");
        Log::info("2. ID de pregunta recibido: " . $id);

        // 2. Asignar las propiedades
        $this->questionToAssignId = $id;
        $this->assigningValidator = true;

        // 3. Log para confirmar que las propiedades cambiaron
        Log::info("3. \$questionToAssignId establecido en: " . $this->questionToAssignId);
        Log::info("4. \$assigningValidator establecido en: " . ($this->assigningValidator ? 'true' : 'false'));
        Log::info("--- FIN openAssignValidatorModal ---");
    }
    // --- FIN SOLUCIÓN ---
}