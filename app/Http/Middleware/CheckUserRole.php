<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Los roles permitidos para acceder a la ruta.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Si el usuario no está logueado, el middleware 'auth' ya lo habrá redirigido.
        if (!Auth::check()) {
            return redirect('login');
        }

        // Obtenemos el usuario autenticado
        $user = Auth::user();

        // Comprobamos si el rol del usuario está en la lista de roles permitidos.
        foreach ($roles as $role) {
            if ($user->role === $role) {
                // Si encontramos una coincidencia, permitimos que la solicitud continúe.
                return $next($request);
            }
        }

        // Si el bucle termina y no hay coincidencia, el usuario no tiene permiso.
        // Abortamos con un error 403 (Prohibido).
        abort(403, 'Acceso no autorizado.');
    }
}