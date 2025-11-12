<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Asegúrate de importar User si lo necesitas (aunque no se usa en este método)

class AnalyticsController extends Controller
{
    /**
     * Muestra el panel de analíticas.
     */
    public function index()
    {
        // Definir todos los estados posibles
        $statuses = [
            'borrador', 'en_validacion_ai', 'en_validacion_comparativa', 
            'revisado_por_ai', 'en_revision_humana', 'aprobado', 'rechazado', 'en_espera',
        ];

        // 1. Obtener conteos de la BD
        $statusCounts = Question::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
logger()->info('Status Counts Raw: ', $statusCounts);
        // Rellenar con ceros (tipo INT, no STRING)
        foreach ($statuses as $status) {
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0; 
            }
        }

        // Preparar datos para el gráfico de pastel "Estado General"
        $statusChartData = [
            'labels' => array_map('ucfirst', str_replace('_', ' ', $statuses)),
            'data' => array_map(fn($status) => $statusCounts[$status] ?? 0, $statuses),
        ];
logger()->info('Status Chart Data: ', $statusChartData);
        // Preparar datos para el gráfico de "Actividad de Validación"
        // (Asegura que las claves existan para evitar errores)
        $validationActivityData = [
            'labels' => ['En Validación (IA)', 'En Revisión (Humana)', 'Aprobado'],
            'data' => [
                ($statusCounts['en_validacion_ai'] ?? 0) + ($statusCounts['en_validacion_comparativa'] ?? 0),
                $statusCounts['en_revision_humana'] ?? 0,
                $statusCounts['aprobado'] ?? 0,
            ],
        ];

        // Conteo total
        $totalQuestions = array_sum($statusCounts);
        
//dd($totalQuestions);

        // Pasar los datos a la vista
        return view('admin.analytics.index', compact(
            'statusCounts',
            'totalQuestions',
            'statusChartData',
            'validationActivityData'
        ));
    }
}