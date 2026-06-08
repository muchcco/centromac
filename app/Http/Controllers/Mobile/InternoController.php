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

        $entidades = DB::table('m_mac_entidad')
                            ->join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'm_mac_entidad.IDCENTRO_MAC')
                            ->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_mac_entidad.IDENTIDAD')
                            ->where('m_mac_entidad.IDCENTRO_MAC', $mac->IDCENTRO_MAC)
                            ->get();

        return view('mobile.entidad_dat', compact('mac', 'entidades'));
    }

    public function det_entidad(Request $request, $idcentro_mac, $identidad)
    {
        $mac = Mac::where('IDCENTRO_MAC', $idcentro_mac)->first();

        $entidad = Entidad::where('IDENTIDAD', $identidad)->first();

        $ent_mac = DB::table('m_mac_entidad')
                        ->join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'm_mac_entidad.IDCENTRO_MAC')
                        ->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_mac_entidad.IDENTIDAD')
                        ->leftJoin('configuracion_sist', 'configuracion_sist.IDCONFIGURACION', '=', 'm_mac_entidad.TIPO_REFRIGERIO')
                        ->where('m_mac_entidad.IDCENTRO_MAC', $idcentro_mac)
                        ->where('m_mac_entidad.IDENTIDAD', $identidad)                        
                        ->first();

        $serv_m_e = DB::table('d_ent_serv as DES')
                        ->join('m_entidad as ME', 'ME.IDENTIDAD', '=', 'DES.IDENTIDAD')
                        ->join('d_entidad_servicios as DEV', 'DEV.IDSERVICIOS', '=', 'DES.IDSERVICIOS')
                        ->join('m_centro_mac as MCM', 'MCM.IDCENTRO_MAC', '=', 'DES.IDMAC')
                        ->where('DES.IDMAC',  $idcentro_mac)
                        ->where('DES.IDENTIDAD', $identidad)
                        ->get();

        // dd($identidad);
        return view('mobile.det_entidad', compact('mac', 'entidad', 'ent_mac', 'serv_m_e'));
    }
}
