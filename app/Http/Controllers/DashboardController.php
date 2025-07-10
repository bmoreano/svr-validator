<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class DashboardController
 *
 * Controlador de Acción Única (invocable) que se encarga de
 * construir y mostrar el dashboard principal de la aplicación.
 * Prepara los datos específicos para el rol del usuario que
 * ha iniciado sesión.
 */
class DashboardController extends Controller
{
    /**
     * Muestra el dashboard de la aplicación.
     *
     * Este método determina el rol del usuario autenticado y llama a los
     * métodos privados correspondientes para recopilar las estadísticas y
     * los datos necesarios. Luego, pasa estos datos a la vista 'dashboard'.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Inicializamos todas las posibles variables de datos como null.
        // Esto garantiza que siempre existirán en el contexto de la vista,
        // evitando errores de "Undefined variable".
        $autorData = null;
        $validadorData = null;
        $adminData = null;

        // Preparamos los datos si el usuario tiene el rol correspondiente.
        // Un administrador tendrá acceso a todos los paneles.
        if (in_array($user->role, ['autor', 'administrador'])) {
            $autorData = $this->getDataForAutor($user);
        }

        if (in_array($user->role, ['validador', 'administrador'])) {
            $validadorData = $this->getDataForValidador($user);
        }
        
        if ($user->role === 'administrador') {
            $adminData = $this->getDataForAdministrador();
        }

        // Pasamos todas las variables de datos a la vista.
        // Si una variable es null, la condición @if en la vista Blade la ignorará.
        return view('dashboard', [
            'autorData' => $autorData,
            'validadorData' => $validadorData,
            'adminData' => $adminData,
        ]);
    }

    /**
     * Recopila los datos necesarios para el panel del rol "Autor".
     *
     * @param \App\Models\User $user El usuario autenticado.
     * @return array
     */
    private function getDataForAutor(User $user): array
    {
        // Usamos una sola consulta para obtener las últimas 5 preguntas
        $latestQuestions = $user->questions()->latest()->take(5)->get();
        
        // Obtenemos las estadísticas con consultas eficientes
        $stats = [
            'total' => $user->questions()->count(),
            'borradores' => $user->questions()->where('status', 'borrador')->count(),
            'en_revision' => $user->questions()->whereIn('status', ['en_validacion_ai', 'revisado_por_ai'])->count(),
            'aprobadas' => $user->questions()->where('status', 'aprobado')->count(),
            'rechazadas' => $user->questions()->where('status', 'rechazado')->count(),
        ];

        return [
            'questions' => $latestQuestions,
            'stats' => $stats
        ];
    }

    /**
     * Recopila los datos necesarios para el panel del rol "Validador".
     *
     * @param \App\Models\User $user El usuario autenticado.
     * @return array
     */
    private function getDataForValidador(User $user): array
    {
        // Buscamos preguntas que estén listas para la revisión humana.
        // `with('author')` es una carga ansiosa (eager loading) para evitar
        // el problema N+1 si la vista necesita mostrar el nombre del autor.
        $pendingValidation = Question::where('status', 'revisado_por_ai')
            ->with('author')
            ->latest()
            ->take(10)
            ->get();
        
        $stats = [
            'total_validations' => $user->validations()->count(),
        ];
        
        return [
            'pending_validation' => $pendingValidation,
            'stats' => $stats
        ];
    }
    
    /**
     * Recopila los datos necesarios para el panel del rol "Administrador".
     *
     * @return array
     */
    private function getDataForAdministrador(): array
    {
        $stats = [
            'total_questions' => Question::count(),
            // Excluimos la cuenta del sistema de IA del conteo de usuarios.
            'total_users' => User::where('email', '!=', 'ai@svr.com')->count(),
        ];

        return [
            'global_stats' => $stats
        ];
    }
}