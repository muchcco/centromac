<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserJwt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;


class AuthController extends Controller
{

    public function showLoginForm()
    {
        return redirect()->away(env('REDIRECT_URL', 'http://default-login-url.com'));
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {

            Auth::login($user);

            // dd(Auth::user());
           
            $token = $user->createToken('Personal Access Token')->accessToken;

            // dd($token);

            return redirect('/'); 
        } else {
            \Log::info('Login attempt failed', ['credentials' => $credentials]);
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
    }

    public function authenticateWithToken(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('login')->withErrors(['msg' => 'Token missing']);
        }

        try {
            $user = JWTAuth::setToken($token)->toUser();
            Auth::login($user);
            return redirect('/'); // Cambia '/home' a la ruta de tu intranet
        } catch (Exception $e) {
            return redirect('login')->withErrors(['msg' => 'Invalid token']);
        }
    }

    public function logout(Request $request)
    {
        // Cierra la sesión del usuario autenticado
        Auth::logout();

        // Elimina la sesión del usuario
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirecciona a la URL configurada en el .env
        return Redirect::to(env('REDIRECT_URL', '/login'));
    }
}
