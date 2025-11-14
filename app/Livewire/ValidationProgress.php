<?php

namespace App\Livewire;

use App\Models\Question;
use Livewire\Component;

class ValidationProgress extends Component
{
    public Question $question;

    /**
     * Inicializa el componente con la pregunta actual.
     */
    public function mount(Question $question)
    {
        $this->question = $question;
    }

    /**
     * El motor de Livewire. Se ejecuta en cada sondeo (poll).
     */
    public function render()
    {
        // Refresca el estado del modelo desde la BD en cada sondeo
        $this->question->refresh();

        $latestValidation = null;
        
        // Comprobar si la validación ha terminado
        if (in_array($this->question->status, [
            'revisado_por_ai', 
            'revisado_comparativo', 
            'aprobado', 
            'rechazado',
            'fallo_comparativo'
        ])) {
            // Si terminó, cargar las relaciones para mostrar los resultados
            $this->question->load('validations.validator', 'validations.responses.criterion');
            $latestValidation = $this->question->validations->sortByDesc('created_at')->first();
        }

        return view('livewire.validation-progress', [
            'latestValidation' => $latestValidation
        ]);
    }
}