<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

class ValidatorDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /**
     * @var bool Controla si el filtro por carrera está activo.
     */
    public bool $filterByMyCareer = true;

    /**
     * Renderiza el componente.
     */
    public function render()
    {
        $user = Auth::user();

        // 1. Empezamos con la consulta base de los estados que nos interesan.
        $query = Question::whereIn('status', [
            'revisado_por_ai',
            'revisado_comparativo',
            'en_revision_humana'
        ]);

        // 2. Aplicamos la lógica de visibilidad:
        // El validador debe ver:
        // a) Todas las preguntas que no estén asignadas a nadie.
        // b) O, las preguntas que estén asignadas específicamente a él.
        $query->where(function ($subQuery) use ($user) {
            $subQuery->whereNull('assigned_validator_id')
                     ->orWhere('assigned_validator_id', $user->id);
        });

        // 3. Aplicamos el filtro de carrera SOLO si el toggle está activo
        //    Y si el validador tiene una carrera asignada en su perfil.
        if ($this->filterByMyCareer && $user->career_id) {
            $query->where('career_id', $user->career_id);
        }

        // 4. Cargamos relaciones y paginamos.
        $pendingQuestions = $query->latest('updated_at')
                                  ->with(['author', 'career'])
                                  ->paginate(10, ['*'], 'validationsPage');

        return view('livewire.validator-dashboard', [
            'pendingQuestions' => $pendingQuestions,
        ]);
    }
}