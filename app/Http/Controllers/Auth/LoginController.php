<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function loggedOut(Request $request)
    {
        $logoutBaseUrl = trim((string) env('AUTH_SERVER_LOGOUT_BASE_URL', ''));
        $logoutNext = trim((string) env('AUTH_SERVER_LOGOUT_NEXT', ''));
        if ($logoutBaseUrl === '') {
            $logoutBaseUrl = 'http://localhost/auth-server/public/logout';
        }
        if ($logoutNext === '') {
            $logoutNext = url('login');
        }
        $logoutUrl = $logoutBaseUrl . '?next=' . urlencode($logoutNext);
        return redirect()->away($logoutUrl);
    }
}
