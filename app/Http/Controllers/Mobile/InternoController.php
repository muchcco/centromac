<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mac;
use Illuminate\Support\Facades\DB;
use App\Models\Entidad;

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

    public function det_entidad(Request $request, $idcentro_mac, $identidad)
    {
        $mac = Mac::where('IDCENTRO_MAC', $idcentro_mac)->first();

        $entidad = Entidad::where('IDENTIDAD', $identidad)->first();

        $ent_mac = DB::table('M_MAC_ENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                        ->leftJoin('CONFIGURACION_SIST', 'CONFIGURACION_SIST.IDCONFIGURACION', '=', 'M_MAC_ENTIDAD.TIPO_REFRIGERIO')
                        ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $idcentro_mac)
                        ->where('M_MAC_ENTIDAD.IDENTIDAD', $identidad)                        
                        ->first();

        $serv_m_e = DB::table('D_ENT_SERV as DES')
                        ->join('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'DES.IDENTIDAD')
                        ->join('D_ENTIDAD_SERVICIOS as DEV', 'DEV.IDSERVICIOS', '=', 'DES.IDSERVICIOS')
                        ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'DES.IDMAC')
                        ->where('DES.IDMAC',  $idcentro_mac)
                        ->where('DES.IDENTIDAD', $identidad)
                        ->get();

        // dd($identidad);
        return view('mobile.det_entidad', compact('mac', 'entidad', 'ent_mac', 'serv_m_e'));
    }
}
