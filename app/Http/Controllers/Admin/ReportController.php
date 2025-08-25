<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use App\Models\Validation;
use App\Models\ValidationDisagreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Muestra el dashboard de reportería y analíticas.
     */
    public function index(): View
    {
        // --- 1. KPIs Generales ---
        $kpis = [
            'total_questions' => Question::count(),
            'total_authors' => User::where('role', 'autor')->count(),
            'total_validators' => User::where('role', 'validador')->count(),
            'approved_questions' => Question::where('status', 'aprobado')->count(),
        ];

        // --- 2. Distribución de Preguntas por Estado (Gráfico Circular) ---
        $statusDistribution = Question::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // --- 3. Flujo de Preguntas (Gráfico de Líneas) ---
        $questionFlow = Question::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as created'),
                DB::raw("count(case when status = 'aprobado' then 1 end) as approved")
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // --- 4. Tasa de Desacuerdo IA vs. Humano (Gráfico de Barras) ---
        $totalValidationsByEngine = Validation::where('status', 'completado')
            ->select('ai_engine', DB::raw('count(*) as total'))
            ->groupBy('ai_engine')
            ->pluck('total', 'ai_engine');

        $disagreementsByEngine = ValidationDisagreement::query()
            ->select('ai_engine', DB::raw('count(*) as disagreements'))
            ->groupBy('ai_engine')
            ->pluck('disagreements', 'ai_engine');

        $disagreementRate = $disagreementsByEngine->map(function ($disagreements, $engine) use ($totalValidationsByEngine) {
            $total = $totalValidationsByEngine->get($engine, 1); // Evitar división por cero
            return round(($disagreements / $total) * 100, 2);
        });

        // --- 5. Top Criterios Problemáticos (Tabla) ---
        $topDisagreeingCriteria = ValidationDisagreement::query()
            ->select('criterion_id', DB::raw('count(*) as total'))
            ->groupBy('criterion_id')
            ->orderByDesc('total')
            ->with('criterion')
            ->take(5)
            ->get();

        return view('admin.reports.index', compact(
            'kpis',
            'statusDistribution',
            'questionFlow',
            'disagreementRate',
            'topDisagreeingCriteria'
        ));
    }
}