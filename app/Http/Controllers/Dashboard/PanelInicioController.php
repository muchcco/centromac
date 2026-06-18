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
    public function indicadores_ans(Request $request)
    {
        return view('dashboard.indicadores_ans');
    }
    public function alo_mac(Request $request)
    {
        return view('dashboard.alo_mac');
    }

    public function incidencias_mac(Request $request)
    {
        return view('dashboard.incidencias_mac');
    }

    public function monitoreo_novo_sumaq(Request $request)
    {
        return view('dashboard.monitoreo_novo_sumaq');
    }
    public function monitoreo_novo_estados(Request $request)
    {
        return view('dashboard.monitoreo_novo_estados');
    }
    public function monitoreo_novo_servicios(Request $request)
    {
        return view('dashboard.monitoreo_novo_servicios');
    }
    
}
