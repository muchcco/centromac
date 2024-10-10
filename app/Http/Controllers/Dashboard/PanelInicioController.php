<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PanelInicioController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.index');
    }

    public function getion_interna(Request $request)
    {
        return view('dashboard.getion_interna');
    }
}
