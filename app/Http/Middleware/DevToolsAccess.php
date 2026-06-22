<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DevToolsAccess
{
    // Solo el usuario con DNI 47286140 puede acceder
    private const ALLOWED_USERS = ['47286140', '71858503', '70780270'];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !in_array($user->email, self::ALLOWED_USERS, true)) {
            abort(403, 'Acceso restringido.');
        }

        return $next($request);
    }
}
