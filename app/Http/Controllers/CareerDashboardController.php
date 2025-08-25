<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CareerDashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $careerId = $user->career_id;

        // Si el usuario no tiene una carrera asignada, no puede ver el dashboard.
        if (!$careerId) {
            return view('career-dashboard.no-career');
        }

        // --- MÉTRICA 1: Inventario de Preguntas (Gráfico Circular) ---
        $difficultyDistribution = Question::where('career_id', $careerId)
            ->select('grado_dificultad', DB::raw('count(*) as total'))
            ->groupBy('grado_dificultad')
            ->pluck('total', 'grado_dificultad');

        // --- MÉTRICA 2: Balance por Tema (Gráfico de Radar) ---
        $topicDistribution = Question::where('career_id', $careerId)
            ->whereNotNull('stem')
            ->select('stem', DB::raw('count(*) as total'))
            ->groupBy('stem')
            ->pluck('total', 'stem');

        // --- MÉTRICA 3: Rendimiento de Autores (Tabla) ---
        $authors = User::where('role', 'autor')
            ->where('career_id', $careerId)
            ->withCount([
                'questions as questions_created',
                'questions as questions_approved' => fn($q) => $q->where('status', 'aprobado'),
                'questions as questions_rejected' => fn($q) => $q->where('status', 'rechazado_permanentemente'),
            ])
            ->get();
        
        // Calculamos las tasas porcentuales
        $authorPerformance = $authors->map(function ($author) {
            $totalReviewed = $author->questions_approved + $author->questions_rejected;
            $author->approval_rate = $totalReviewed > 0 ? round(($author->questions_approved / $totalReviewed) * 100, 2) : 0;
            $author->rejection_rate = $totalReviewed > 0 ? round(($author->questions_rejected / $totalReviewed) * 100, 2) : 0;
            return $author;
        });

        return view('career-dashboard.index', compact(
            'difficultyDistribution',
            'topicDistribution',
            'authorPerformance'
        ));
    }
}