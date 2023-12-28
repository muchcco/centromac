<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mac;
use Illuminate\Support\Facades\DB;

class InternoController extends Controller
{
    public function index()
    {
        $macs = Mac::get();

        return view('mobile.index', compact('macs'));
    }

    public function entidad_dat(Request $request)
    {
        $mac = Mac::where('IDCENTRO_MAC', $request->idcentro_mac)->first();

        $entidades = DB::table('M_MAC_ENTIDAD')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $mac->IDCENTRO_MAC)
                            ->get();

        return view('mobile.entidad_dat', compact('mac', 'entidades'));
    }
}
