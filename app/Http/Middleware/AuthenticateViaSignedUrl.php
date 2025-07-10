<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticateViaSignedUrl
{
    public function handle(Request $request, Closure $next)
    {
        // El middleware 'signed' de Laravel ya debería haber validado la firma.
        // Aquí solo nos preocupamos de loguear al usuario.
        
        // Comprobamos si el usuario ya está logueado. Si es así, no hacemos nada.
        if (Auth::check()) {
            return $next($request);
        }

        // Buscamos el ID del validador en los parámetros de la ruta.
        if ($request->has('validator')) {
            $user = User::find($request->validator);

            // Si el usuario existe, lo logueamos.
            if ($user) {
                Auth::login($user);
                // Redirigimos a la misma ruta pero sin los parámetros de firma para limpiar la URL.
                return redirect($request->url());
            }
        }
        
        // Si no hay parámetro o el usuario no existe, denegamos el acceso.
        return abort(403, 'Acceso no autorizado.');
    }
}