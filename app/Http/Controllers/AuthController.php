<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function showLoginForm()
    {
        return redirect()->away(env('REDIRECT_URL', 'http://default-login-url.com'));
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->accessToken;

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
}
