<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ValidationDisagreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Muestra el dashboard de analíticas y reportería.
     */
    public function index(): View
    {
        // 1. ¿Qué criterios generan más desacuerdos?
        $topDisagreeingCriteria = ValidationDisagreement::query()
            ->select('criterion_id', DB::raw('count(*) as total'))
            ->groupBy('criterion_id')
            ->orderByDesc('total')
            ->with('criterion') // Cargar el texto del criterio
            ->take(5)
            ->get();

        // 2. ¿Qué motor de IA es más preciso (tiene menos desacuerdos)?
        $disagreementsByEngine = ValidationDisagreement::query()
            ->select('ai_engine', DB::raw('count(*) as disagreements'))
            ->groupBy('ai_engine')
            ->pluck('disagreements', 'ai_engine'); // Devuelve un array como ['chatgpt' => 20, 'gemini' => 15]

        // 3. ¿Qué validador humano está más a menudo en desacuerdo con la IA?
        $disagreementsByUser = ValidationDisagreement::query()
            ->select('human_validator_id', DB::raw('count(*) as total'))
            ->groupBy('human_validator_id')
            ->orderByDesc('total')
            ->with('humanValidator') // Cargar el nombre del validador
            ->take(10)
            ->get();
            
        // Pasamos los datos a la vista.
        return view('admin.analytics.index', compact(
            'topDisagreeingCriteria',
            'disagreementsByEngine',
            'disagreementsByUser'
        ));
    }
}
