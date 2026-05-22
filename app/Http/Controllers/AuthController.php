<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    private string $jwtError = '';

    /* =========================
     * Helpers JWT HS256
     * ========================= */

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        return $decoded !== false ? $decoded : '';
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        return rtrim(trim($url), '/');
    }

    private function jwtAudMatches($aud, ?string $expectedAud): bool
    {
        if (!$expectedAud) {
            return true;
        }

        if (is_string($aud)) {
            return $aud === $expectedAud;
        }

        if (is_array($aud)) {
            return in_array($expectedAud, $aud, true);
        }

        return false;
    }

    private function failJwt(string $message): ?array
    {
        $this->jwtError = $message;
        return null;
    }

    private function secretCandidates(string $secret): array
    {
        $candidates = [];

        if ($secret !== '') {
            $candidates[] = $secret;
        }

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);

            if ($decoded !== false && $decoded !== '') {
                $candidates[] = $decoded;
            }
        }

        return $candidates;
    }

    private function verifyJwt(
        string $jwt,
        string $secret,
        ?string $expectedIss = null,
        ?string $expectedAud = null
    ): ?array {
        $this->jwtError = '';

        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return $this->failJwt('JWT mal formado. No tiene 3 partes.');
        }

        [$h64, $p64, $s64] = $parts;

        $headerJson = $this->base64UrlDecode($h64);
        $payloadJson = $this->base64UrlDecode($p64);
        $signature = $this->base64UrlDecode($s64);

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header)) {
            return $this->failJwt('Header JWT inválido.');
        }

        if (!is_array($payload)) {
            return $this->failJwt('Payload JWT inválido.');
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return $this->failJwt('Algoritmo JWT no permitido. Se esperaba HS256.');
        }

        $signatureOk = false;

        foreach ($this->secretCandidates($secret) as $candidateSecret) {
            $expectedSignature = hash_hmac('sha256', "$h64.$p64", $candidateSecret, true);

            if (hash_equals($expectedSignature, $signature)) {
                $signatureOk = true;
                break;
            }
        }

        if (!$signatureOk) {
            return $this->failJwt('Firma JWT inválida. Revisar SSO_SHARED_SECRET.');
        }

        $now = time();
        $leeway = 60;

        if (!isset($payload['exp'])) {
            return $this->failJwt('JWT sin campo exp.');
        }

        if ((int) $payload['exp'] < ($now - $leeway)) {
            return $this->failJwt('JWT expirado.');
        }

        if (isset($payload['nbf']) && (int) $payload['nbf'] > ($now + $leeway)) {
            return $this->failJwt('JWT aún no válido por campo nbf.');
        }

        $expectedIss = $this->normalizeUrl($expectedIss);
        $payloadIss = $this->normalizeUrl($payload['iss'] ?? null);

        if ($expectedIss && $payloadIss !== $expectedIss) {
            return $this->failJwt(
                'Issuer inválido. Recibido: ' . ($payloadIss ?? 'null') . ' | Esperado: ' . $expectedIss
            );
        }

        if (!$this->jwtAudMatches($payload['aud'] ?? null, $expectedAud)) {
            return $this->failJwt(
                'Audience inválido. Recibido: ' . json_encode($payload['aud'] ?? null) . ' | Esperado: ' . $expectedAud
            );
        }

        return $payload;
    }

    private function ssoSecret(): string
    {
        return (string) config('sso.shared_secret', '');
    }

    private function expectedIssuer(): ?string
    {
        $issuer = (string) config('sso.expected_issuer', '');

        return $issuer !== '' ? $issuer : null;
    }

    private function expectedAudience(): ?string
    {
        $audience = (string) config('sso.expected_audience', '');

        return $audience !== '' ? $audience : null;
    }

    private function authServerLoginUrl(): string
    {
        return (string) config('sso.auth_server_login_url', 'https://sismac.mac.pe/auth/public/login');
    }

    private function authServerLogoutBaseUrl(): string
    {
        return (string) config('sso.auth_server_logout_base_url', 'https://sismac.mac.pe/auth/public/logout');
    }

    private function authServerLogoutNext(): string
    {
        return (string) config('sso.auth_server_logout_next', 'https://sismac.mac.pe/centromac/public/login?noauto=1');
    }

    private function modelHasColumn(string $modelClass, string $column): bool
    {
        try {
            $model = new $modelClass();

            return Schema::hasColumn($model->getTable(), $column);
        } catch (\Throwable $e) {
            Log::warning('SSO: no se pudo validar columna del modelo', [
                'model' => $modelClass,
                'column' => $column,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function findUserFromPayload(array $payload): ?User
    {
        $email = $payload['email'] ?? null;
        $dni = $payload['dni'] ?? ($payload['num_doc'] ?? null);

        $baseQuery = User::query();

        if ($this->modelHasColumn(User::class, 'flag')) {
            $baseQuery->where('flag', 1);
        }

        if ($email) {
            $user = (clone $baseQuery)
                ->where('email', $email)
                ->first();

            if ($user) {
                return $user;
            }
        }

        if ($dni) {
            $user = (clone $baseQuery)
                ->where('email', $dni)
                ->first();

            if ($user) {
                return $user;
            }

            if ($this->modelHasColumn(User::class, 'dni')) {
                $user = (clone $baseQuery)
                    ->where('dni', $dni)
                    ->first();

                if ($user) {
                    return $user;
                }
            }

            if ($this->modelHasColumn(User::class, 'num_doc')) {
                $user = (clone $baseQuery)
                    ->where('num_doc', $dni)
                    ->first();

                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }

    /* =========================
     * GET /login
     * ========================= */

    public function showLoginForm(Request $request)
    {
        Log::info('SSO: ingreso a showLoginForm', [
            'has_token' => $request->filled('token'),
            'has_rt' => $request->filled('rt'),
            'has_noauto' => $request->boolean('noauto'),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        /*
         * Flujo actual:
         * Auth Server redirige a:
         * /centromac/public/login?token=<JWT>
         */
        if ($request->filled('token')) {
            return $this->consumeTokenAndLogin($request);
        }

        /*
         * Flujo futuro:
         * /centromac/public/login?rt=<reference_token>
         */
        if ($request->filled('rt')) {
            Log::warning('SSO: llegó rt pero aún no está implementado', [
                'rt_length' => strlen((string) $request->query('rt')),
            ]);

            return redirect('login?noauto=1')
                ->withErrors(['msg' => 'Reference token no implementado en SISMAC.']);
        }

        /*
         * Legacy temporal:
         * /login?email=...&password=...
         */
        if ($request->filled('email') && $request->filled('password')) {
            return $this->consumeQueryCredentials($request);
        }

        /*
         * Evita loop después de logout.
         */
        if ($request->boolean('noauto')) {
            return response('Sesión cerrada. Vuelve a iniciar desde el Auth-Server.', 200);
        }

        /*
         * Si alguien abre SISMAC directo, lo mandamos al Auth Server.
         */
        return redirect()->away($this->authServerLoginUrl());
    }

    /* =========================
     * GET /authenticate?token=...
     * ========================= */

    public function authenticate(Request $request)
    {
        return $this->consumeTokenAndLogin($request);
    }

    private function consumeTokenAndLogin(Request $request)
    {
        $token = (string) $request->query('token', '');

        Log::info('SSO: procesando token', [
            'has_token' => $token !== '',
            'token_length' => strlen($token),
            'expected_issuer' => $this->expectedIssuer(),
            'expected_audience' => $this->expectedAudience(),
            'ip' => $request->ip(),
        ]);

        if ($token === '') {
            Log::warning('SSO: token faltante');

            return redirect('login?noauto=1')
                ->withErrors(['msg' => 'Token faltante']);
        }

        $secret = $this->ssoSecret();

        if ($secret === '') {
            Log::error('SSO: SSO_SHARED_SECRET vacío o no cargado');

            return redirect('login?noauto=1')
                ->withErrors(['msg' => 'SSO_SHARED_SECRET no configurado en SISMAC']);
        }

        $payload = $this->verifyJwt(
            $token,
            $secret,
            $this->expectedIssuer(),
            $this->expectedAudience()
        );

        if (!$payload) {
            Log::warning('SSO: token inválido o expirado', [
                'jwt_error' => $this->jwtError,
                'expected_issuer' => $this->expectedIssuer(),
                'expected_audience' => $this->expectedAudience(),
            ]);

            return redirect('login?noauto=1')
                ->withErrors(['msg' => 'Token inválido o expirado']);
        }

        Log::info('SSO: token válido', [
            'uid' => $payload['uid'] ?? null,
            'app' => $payload['app'] ?? null,
            'email' => $payload['email'] ?? null,
            'dni' => $payload['dni'] ?? null,
            'iss' => $payload['iss'] ?? null,
            'aud' => $payload['aud'] ?? null,
            'roles' => $payload['roles'] ?? [],
            'permissions' => $payload['permissions'] ?? [],
        ]);

        $user = $this->findUserFromPayload($payload);

        if (!$user) {
            Log::warning('SSO: usuario no encontrado en SISMAC', [
                'email' => $payload['email'] ?? null,
                'dni' => $payload['dni'] ?? null,
                'num_doc' => $payload['num_doc'] ?? null,
            ]);

            return redirect('login?noauto=1')
                ->withErrors(['msg' => 'Usuario no encontrado en SISMAC']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('SSO: login local correcto', [
            'user_id' => $user->id ?? null,
            'email' => $user->email ?? null,
        ]);

        return redirect()->intended(url('/'));
    }

    private function consumeQueryCredentials(Request $request)
    {
        Log::warning('SSO: usando login legacy por query string. No recomendado.');

        $email = (string) $request->query('email');
        $password = (string) $request->query('password');

        $query = User::query();

        if ($this->modelHasColumn(User::class, 'flag')) {
            $query->where('flag', 1);
        }

        $user = $query->where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            Log::info('SSO: login legacy correcto', [
                'user_id' => $user->id ?? null,
                'email' => $user->email ?? null,
            ]);

            return redirect()->intended(url('/'));
        }

        Log::warning('SSO: credenciales legacy inválidas', [
            'email' => $email,
        ]);

        return redirect('login?noauto=1')
            ->withErrors(['msg' => 'Credenciales inválidas']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $logoutBaseUrl = trim($this->authServerLogoutBaseUrl());
        $logoutNext = trim($this->authServerLogoutNext());

        if ($logoutBaseUrl === '') {
            $logoutBaseUrl = 'https://sismac.mac.pe/auth/public/logout';
        }

        if ($logoutNext === '') {
            $logoutNext = url('login?noauto=1');
        }

        $logoutUrl = $logoutBaseUrl . '?next=' . urlencode($logoutNext);

        return Redirect::away($logoutUrl);
    }
}