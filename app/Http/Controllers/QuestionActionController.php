<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class QuestionActionController extends Controller
{
    /**
     * Maneja la acción "Sí, deseo retirarme".
     * El usuario es el autenticado.
     */
    public function withdraw(Request $request)
    {
        // La ruta está firmada, por lo que confiamos en el 'user'
        // pero verificamos que sea el usuario autenticado.
        $user = Auth::user();

        if ($request->user_id && $request->user_id != $user->id) {
            abort(403, 'Acción no autorizada.');
        }

        // 1. Desactivar la cuenta del usuario
        $user->activo = false;
        $user->save();

        // 2. Desconectarlo
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Redirigir al login
        return redirect('/login')->with('status', 'Tu cuenta ha sido desactivada. Gracias por tu contribución. Contacta a un administrador si esto fue un error.');
    }

    /**
     * Maneja la acción "No, continuaré trabajando".
     */
    public function resume(Request $request, Question $question)
    {
        // La ruta está firmada, pero verificamos el permiso
        $user = Auth::user();
        if ($user->id !== $question->author_id) {
            abort(403, 'No eres el autor de esta pregunta.');
        }

        // 1. Devolver la pregunta a 'borrador'
        // (La política de 'update' ahora permitirá editarla)
        $question->status = 'borrador';
        
        // 2. Opcional: Reiniciar el temporizador
        $question->created_at = now(); 
        
        $question->save();

        // 3. Redirigir a la página de edición
        return redirect()->route('questions.edit', $question)
            ->with('status', '¡Gracias! El reactivo ha sido desbloqueado y puedes continuar editándolo.');
    }
}