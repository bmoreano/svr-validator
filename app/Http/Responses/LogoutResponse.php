<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Crea una respuesta HTTP que representa al objeto.
     *
     * Este método se ejecuta cuando un usuario cierra sesión exitosamente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // Si la solicitud es una petición AJAX, devolvemos una respuesta JSON vacía.
        // Esto es útil para SPAs (Single Page Applications).
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        // --- ESTA ES NUESTRA LÓGICA PERSONALIZADA ---
        // Redirigimos al usuario a la ruta con el nombre 'login'
        // y le pasamos un mensaje flash para confirmar que cerró sesión.
        return redirect()->route('login')->with('status', 'Has cerrado sesión correctamente.');
    }
}