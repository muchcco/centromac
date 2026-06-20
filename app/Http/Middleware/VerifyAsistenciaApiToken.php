<?php

namespace App\Http\Middleware;

use App\Models\AsistenciaApiToken;
use Closure;
use Illuminate\Http\Request;

class VerifyAsistenciaApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();

        if (!$bearer) {
            return response()->json(['ok' => false, 'error' => 'Token requerido.'], 401);
        }

        $hash  = hash('sha256', $bearer);
        $token = AsistenciaApiToken::where('token_hash', $hash)
            ->where('activo', 1)
            ->first();

        if (!$token) {
            return response()->json(['ok' => false, 'error' => 'Token inválido o inactivo.'], 401);
        }

        $token->update(['ultimo_uso' => now()]);
        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
