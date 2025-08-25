<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Criterion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Class ComparisonController
 *
 * Controlador dedicado a mostrar la comparación de resultados de validación
 * de diferentes motores de Inteligencia Artificial para una pregunta específica.
 */
class ComparisonController extends Controller
{
    /**
     * Muestra la vista de la tabla comparativa.
     *
     * Este método es invocado por la ruta 'questions.compare'.
     *
     * @param \App\Models\Question $question El modelo de la pregunta, inyectado automáticamente.
     * @return \Illuminate\View\View
     */
    public function show(Question $question): View
    {
        // 1. Autorización: Verificamos si el usuario tiene permiso para ver esta pregunta.
        // Reutilizamos la lógica del método 'view' de nuestra QuestionPolicy.
        Gate::authorize('view', $question);

        // 2. Recopilación de Datos: Buscamos las validaciones de IA para esta pregunta.
        $validations = $question->validations()
            // Buscamos solo las validaciones que tienen un motor de IA especificado.
            ->whereIn('ai_engine', ['chatgpt', 'gemini'])
            // Carga Ansiosa (Eager Loading): Para optimizar las consultas, cargamos
            // las relaciones 'responses' y, a través de ellas, la relación 'criterion'
            // en una sola consulta adicional, en lugar de una por cada respuesta.
            ->with('responses.criterion')
            ->latest() // Obtenemos las más recientes
            ->get();

        // 3. Organización de Datos: Separamos los resultados para un manejo más fácil en la vista.
        $chatGptValidation = $validations->firstWhere('ai_engine', 'chatgpt');
        $geminiValidation = $validations->firstWhere('ai_engine', 'gemini');
        
        // 4. Criterios: Obtenemos la lista completa de criterios para construir las filas de la tabla.
        $criteria = Criterion::orderBy('category')->orderBy('sort_order')->get();

        // 5. Paso a la Vista: Enviamos todos los datos necesarios a la vista Blade.
        return view('comparison.show', [
            'question' => $question,
            'chatGptValidation' => $chatGptValidation,
            'geminiValidation' => $geminiValidation,
            'criteria' => $criteria,
        ]);
    }
}