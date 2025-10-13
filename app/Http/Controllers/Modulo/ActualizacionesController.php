<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ActualizacionesController extends Controller
{
    public function index()
    {
        $owner = env('GITHUB_OWNER', 'muchcco');
        $repo = env('GITHUB_REPO', 'centromac');
        $branch = env('GITHUB_BRANCH', 'main');

        try {
            $response = Http::withToken(env('GITHUB_TOKEN'))
                ->get("https://api.github.com/repos/{$owner}/{$repo}/commits?sha={$branch}&per_page=10");

            $commits = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $commits = [];
        }

        return view('actualizaciones.index', compact('commits'));
    }
}
