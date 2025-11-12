<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use App\Models\ValidationDisagreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard de la aplicación, adaptado al rol del usuario.
     */
    public function __invoke(Request $request)
    {
        $role = Auth::user()->role;

        if ($role === 'validador') {
            return view('livewire.validator-dashboard');
        }

        if ($role === 'jefe_carrera') {
            // Asumiendo que tienes una ruta con este nombre para el dashboard de Jefe de Carrera
            return redirect()->route('career-dashboard.index');
        }

        // --- INICIO DE LA SOLUCIÓN ---
        // Comentamos la línea original
        switch ($role) {
            case 'autor':
                $viewData = $this->getAuthorDashboardData();
                break;
            case 'administrador':
                $viewData = $this->getAdministradorDashboardData();
                break;
        }

        return view('dashboard', $viewData);
       // return view('dashboard');

        // Redirigimos a la ruta 'questions.index' (/questions)
        // para que la URL sea la que el usuario desea ver.
        //return redirect()->route('questions.index');
        // --- FIN DE LA SOLUCIÓN ---
    }



    public function __invoke1(Request $request): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $viewData = [];
        if (!$user) {
            abort(403, 'Unauthorized.');
        }
        logger()->info('Dashboard User Role: ', ['role' => $user->role]);
        // Preparamos los datos según el rol del usuario
        switch ($user->role) {
            case 'autor':
                $viewData = $this->getAuthorDashboardData();
                break;
            case 'validador':
                // Para el validador, el dashboard principal es la lista de validaciones.
                // Podríamos pasar datos aquí o simplemente dejar que el componente Livewire se encargue.
                break;
            case 'administrador':
                $viewData = $this->getAdministradorDashboardData();
                break;
            // (casos para 'jefe_carrera', 'tecnico', etc. irían aquí)
        }
        logger()->info('Dashboard View Data: ' . $viewData[0]);

        return view('dashboard', $viewData);
    }
    /**
     * Recopila todos los datos y métricas para el panel de rendimiento del autor.
     */
    private function getAuthorDashboardData(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            // Si no hay usuario autenticado devolvemos estructuras vacías para evitar errores.
            return [
                'kpis' => [
                    'total' => 0,
                    'borrador' => 0,
                    'en_revision' => 0,
                    'aprobadas' => 0,
                ],
                'statusDistribution' => collect(),
                'topDisagreeingCriteria' => collect(),
                'actionableQuestions' => collect(),
            ];
        }

        // Resumen de Contenido (KPIs y Gráfico)
        $statusCounts = $user->questions()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $kpis = [
            'total' => $user->questions()->count(),
            'borrador' => $statusCounts->get('borrador', 0),
            'en_revision' => $statusCounts->except(['borrador', 'aprobado', 'necesita_correccion', 'rechazado_permanentemente'])->sum(),
            'aprobadas' => $statusCounts->get('aprobado', 0),
        ];

        // Feedback para la Mejora (Top 5 Criterios con Desacuerdo)
        $topDisagreeingCriteria = ValidationDisagreement::whereHas('question', function ($query) use ($user) {
            $query->where('author_id', $user->id);
        })
            ->select('criterion_id', DB::raw('count(*) as total'))
            ->groupBy('criterion_id')
            ->orderByDesc('total')
            ->with('criterion')
            ->take(5)
            ->get();

        // Historial Personal (Preguntas que requieren acción)
        $actionableQuestions = $user->questions()
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


    /**
     * Recopila todos los datos y métricas para el panel de rendimiento del autor.
     */
    private function getAdministradorDashboardData(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            // Si no hay usuario autenticado devolvemos estructuras vacías para evitar errores.
            return [
                'kpis' => [
                    'total' => 0,
                    'borrador' => 0,
                    'en_revision' => 0,
                    'aprobadas' => 0,
                ],
                'statusDistribution' => collect(),
                'topDisagreeingCriteria' => collect(),
                'actionableQuestions' => collect(),
            ];
        }

        // Resumen de Contenido (KPIs y Gráfico)
        $statusCounts = Question::select(['status', DB::raw('count(*) as total')])
            ->groupBy('status')
            ->pluck('total', 'status');

        $kpis = [
            'total' => Question::count(),
            'borrador' => $statusCounts->get('borrador', 0),
            'en_revision' => $statusCounts->except(['borrador', 'aprobado', 'necesita_correccion', 'rechazado_permanentemente'])->sum(),
            'aprobadas' => $statusCounts->get('aprobado', 0),
        ];

        // Feedback para la Mejora (Top 5 Criterios con Desacuerdo)
        $topDisagreeingCriteria = ValidationDisagreement::whereHas('question')
            ->select('criterion_id', DB::raw('count(*) as total'))
            ->groupBy('criterion_id')
            ->orderByDesc('total')
            ->with('criterion')
            ->take(5)
            ->get();

        // Historial Personal (Preguntas que requieren acción)
        $actionableQuestions = $user->questions()
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