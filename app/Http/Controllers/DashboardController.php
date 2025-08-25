<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use App\Models\ValidationDisagreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard de la aplicación, adaptado al rol del usuario.
     */
    public function __invoke(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $viewData = [];

        // Preparamos los datos según el rol del usuario
        switch ($user->role) {
            case 'autor':
                $viewData = $this->getAuthorDashboardData($user);
                break;
            case 'validador':
                // Para el validador, el dashboard principal es la lista de validaciones.
                // Podríamos pasar datos aquí o simplemente dejar que el componente Livewire se encargue.
                break;
            case 'administrador':
                // El dashboard de admin no necesita datos precargados complejos,
                // las tarjetas enlazarán a las secciones correspondientes.
                break;
            // (casos para 'jefe_carrera', 'tecnico', etc. irían aquí)
        }

        return view('dashboard', $viewData);
    }

    /**
     * Recopila todos los datos y métricas para el panel de rendimiento del autor.
     */
    private function getAuthorDashboardData(User $author): array
    {
        // Resumen de Contenido (KPIs y Gráfico)
        $statusCounts = $author->questions()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $kpis = [
            'total' => $author->questions()->count(),
            'borrador' => $statusCounts->get('borrador', 0),
            'en_revision' => $statusCounts->except(['borrador', 'aprobado', 'necesita_correccion', 'rechazado_permanentemente'])->sum(),
            'aprobadas' => $statusCounts->get('aprobado', 0),
        ];

        // Feedback para la Mejora (Top 5 Criterios con Desacuerdo)
        $topDisagreeingCriteria = ValidationDisagreement::whereHas('question', function ($query) use ($author) {
                $query->where('author_id', $author->id);
            })
            ->select('criterion_id', DB::raw('count(*) as total'))
            ->groupBy('criterion_id')
            ->orderByDesc('total')
            ->with('criterion')
            ->take(5)
            ->get();
            
        // Historial Personal (Preguntas que requieren acción)
        $actionableQuestions = $author->questions()
            ->whereIn('status', ['necesita_correccion', 'rechazado_permanentemente'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return [
            'kpis' => $kpis,
            'statusDistribution' => $statusCounts,
            'topDisagreeingCriteria' => $topDisagreeingCriteria,
            'actionableQuestions' => $actionableQuestions,
        ];
    }
}