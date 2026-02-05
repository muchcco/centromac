<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;

class AuthController extends Controller
{
    /* =========================
     * Helpers JWT (HS256)
     * ========================= */
    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }

    private function jwtAudMatches($aud, ?string $expectedAud): bool
    {
        if (!$expectedAud) return true;

        // aud puede ser string o array
        if (is_string($aud)) return $aud === $expectedAud;
        if (is_array($aud))  return in_array($expectedAud, $aud, true);

        return false;
    }

    /**
     * Verifica firma HS256, exp y (opcional) issuer/audience.
     * Retorna payload array o null si es inválido.
     */
    private function verifyJwt(string $jwt, string $secret, ?string $expectedIss = null, ?string $expectedAud = null): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        [$h64, $p64, $s64] = $parts;

        $headerJson  = $this->base64UrlDecode($h64);
        $payloadJson = $this->base64UrlDecode($p64);
        $sig         = $this->base64UrlDecode($s64);

        $header  = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) return null;
        if (($header['alg'] ?? null) !== 'HS256') return null;

        // 1) Intento con secret "tal cual" (tu auth-server firma así)
        $expected = hash_hmac('sha256', "$h64.$p64", $secret, true);
        $ok = hash_equals($expected, $sig);

        // 2) Si falla y viene con prefijo base64:, intenta decodificando (por si algún día cambiaste el firmado)
        if (!$ok && str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            if ($decoded !== false) {
                $expected2 = hash_hmac('sha256', "$h64.$p64", $decoded, true);
                $ok = hash_equals($expected2, $sig);
            }
        }

        if (!$ok) return null;

        $now = time();
        if (($payload['exp'] ?? 0) < $now) return null;

        if ($expectedIss && (($payload['iss'] ?? null) !== $expectedIss)) return null;
        if (!$this->jwtAudMatches($payload['aud'] ?? null, $expectedAud)) return null;

        return $payload;
    }

    private function ssoSecret(): string
    {
        return (string) env('SSO_SHARED_SECRET', '');
    }

    private function expectedIssuer(): ?string
    {
        $iss = (string) env('SSO_EXPECTED_ISSUER', '');
        return $iss !== '' ? $iss : null;
    }

    private function expectedAudience(): ?string
    {
        $aud = (string) env('SSO_EXPECTED_AUDIENCE', '');
        return $aud !== '' ? $aud : null;
    }

    /**
     * Busca usuario local por email o por DNI/num_doc (en SISMAC usualmente email = dni).
     */
    private function findUserFromPayload(array $payload): ?User
    {
        $email   = $payload['email'] ?? null;
        $num_doc = $payload['num_doc'] ?? ($payload['dni'] ?? null);

        $q = User::query();

        // si tu tabla usa flag=1
        if (SchemaHasColumn(User::class, 'flag')) {
            $q->where('flag', 1);
        }

        if ($email) {
            $u = (clone $q)->where('email', $email)->first();
            if ($u) return $u;
        }

        if ($num_doc) {
            // en tu SISMAC: email suele ser DNI
            $u = (clone $q)->where('email', $num_doc)->first();
            if ($u) return $u;
        }

        return null;
    }

    /* =========================
     * GET /login  (legacy + noauto)
     * ========================= */
    public function showLoginForm(Request $request)
    {
        // 1) Si llega con token, lo procesamos aquí también (por si pruebas /login?token=)
        if ($request->filled('token')) {
            return $this->consumeTokenAndLogin($request);
        }

        // 2) Legacy por URL: /login?email=...&password=...
        if ($request->filled('email') && $request->filled('password')) {
            return $this->consumeQueryCredentials($request);
        }

        // 3) Evitar loop: si viene noauto=1, NO redirigir al Auth-Server
        if ($request->boolean('noauto')) {
            return response("Sesión cerrada. Vuelve a iniciar desde el Auth-Server.", 200);
        }

        // 4) Flujo normal: manda al Auth-Server
        return redirect()->away(env('REDIRECT_URL', 'http://190.187.182.55:8081/oauth/login'));
    }

    /* =========================
     * GET /authenticate?token=...
     * (este es el endpoint que debe usar tu Auth-Server)
     * ========================= */
    public function authenticate(Request $request)
    {
        return $this->consumeTokenAndLogin($request);
    }

    private function consumeTokenAndLogin(Request $request)
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            return redirect('login?noauto=1')->withErrors(['msg' => 'Token faltante']);
        }

        $secret = $this->ssoSecret();
        if ($secret === '') {
            return redirect('login?noauto=1')->withErrors(['msg' => 'SSO_SHARED_SECRET no configurado en SISMAC']);
        }

        $payload = $this->verifyJwt($token, $secret, $this->expectedIssuer(), $this->expectedAudience());
        if (!$payload) {
            return redirect('login?noauto=1')->withErrors(['msg' => 'Token inválido o expirado']);
        }

        $user = $this->findUserFromPayload($payload);
        if (!$user) {
            Log::warning('SSO: usuario no encontrado', ['payload' => $payload]);
            return redirect('login?noauto=1')->withErrors(['msg' => 'Usuario no encontrado en SISMAC']);
        }

        Auth::login($user);
        return redirect()->intended('/');
    }

    private function consumeQueryCredentials(Request $request)
    {
        // OJO: esto es INSEGURO (credenciales en URL). Úsalo solo interno/temporal.
        $email = (string) $request->query('email');
        $pass  = (string) $request->query('password');

        $user = User::where('email', $email)->where('flag', 1)->first();

        if ($user && Hash::check($pass, $user->password)) {
            Auth::login($user);
            return redirect()->intended('/');
        }

        return redirect('login?noauto=1')->withErrors(['msg' => 'Credenciales inválidas']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to(env('REDIRECT_URL', '/login'));
    }
}

/**
 * Helper: detectar columna en modelo (sin romper si no existe)
 */
function SchemaHasColumn(string $modelClass, string $column): bool
{
    try {
        $model = new $modelClass();
        return \Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), $column);
    } catch (\Throwable $e) {
        return false;
    }
}
