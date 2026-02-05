<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class RedirectIfSessionExpired
{
    public function handle($request, Closure $next)
    {
        // Verifica si el usuario no está autenticado
        if (!Auth::check()) {
            // Redirige a la URL configurada en el archivo .env si la sesión ha expirado
            return Redirect::to(env('AUTH_SERVER_LOGIN_URL', '/login'));
        }

        return $next($request);
    }
}
