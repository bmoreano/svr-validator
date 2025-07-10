<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Question;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard de la aplicación, preparando los datos
     * específicos para el rol del usuario autenticado.
     */
    public function __invoke(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // --- INICIALIZACIÓN EXPLÍCITA DE VARIABLES ---
        // Inicializamos todas las posibles variables de datos como null.
        // Esto garantiza que siempre existirán cuando se pasen a la vista.
        $autorData = null;
        $validadorData = null;
        $adminData = null;

        // --- LÓGICA DE ASIGNACIÓN DE DATOS BASADA EN ROLES ---
        
        // Si el usuario es 'autor' o 'administrador', preparamos los datos del autor.
        if (in_array($user->role, ['autor', 'administrador'])) {
            $autorData = $this->getDataForAutor($user);
        }

        // Si el usuario es 'validador' o 'administrador', preparamos los datos del validador.
        if (in_array($user->role, ['validador', 'administrador'])) {
            $validadorData = $this->getDataForValidador($user);
        }
        
        // Si el usuario es 'administrador', preparamos los datos globales de administración.
        if ($user->role === 'administrador') {
            $adminData = $this->getDataForAdministrador();
        }

        // --- ENVÍO DE DATOS A LA VISTA ---
        // Usamos el método compact() o un array asociativo para pasar
        // todas las variables a la vista. Ahora, aunque una sea null,
        // la variable existirá en el contexto de la vista.
        return view('dashboard', [
            'autorData' => $autorData,
            'validadorData' => $validadorData,
            'adminData' => $adminData,
        ]);
    }

    /**
     * Obtiene los datos para el dashboard del rol "Autor".
     */
    private function getDataForAutor(User $user): array
    {
        return [
            'questions' => $user->questions()->latest()->take(5)->get(),
            'stats' => [
                'total' => $user->questions()->count(),
                'borradores' => $user->questions()->where('status', 'borrador')->count(),
                'en_revision' => $user->questions()->whereIn('status', ['en_validacion_ai', 'revisado_por_ai'])->count(),
                'aprobadas' => $user->questions()->where('status', 'aprobado')->count(),
            ]
        ];
    }

    /**
     * Obtiene los datos para el dashboard del rol "Validador".
     */
    private function getDataForValidador(User $user): array
    {
        return [
            'pending_validation' => Question::where('status', 'revisado_por_ai')->latest()->take(10)->get(),
            'stats' => [
                'total_validations' => $user->validations()->count(),
            ]
        ];
    }
    
    /**
     * Obtiene los datos para el dashboard del rol "Administrador".
     */
    private function getDataForAdministrador(): array
    {
        return [
            'global_stats' => [
                'total_questions' => Question::count(),
                'total_users' => User::where('email', '!=', 'ai@svr.com')->count(),
            ]
        ];
    }
}