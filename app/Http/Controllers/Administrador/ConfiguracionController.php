<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mac;
use Carbon\Carbon;

class ConfiguracionController extends Controller
{
    public function nuevo_mac()
    {
        return view('configuracion.nuevo_mac');
    }

    public function tb_nuevo_mac(Request $request)
    {
        // $mac = DB::table('M_CENTRO_MAC')->where('FLAG', 1)->get();
        $mac = Mac::leftJoin('DISTRITO as D', 'M_CENTRO_MAC.UBICACION', '=', 'D.IDDISTRITO')
                                ->leftJoin('PROVINCIA as P', 'D.PROVINCIA_ID', '=', 'P.IDPROVINCIA')
                                ->leftJoin('DEPARTAMENTO as DEP', 'D.DEPARTAMENTO_ID', '=', 'DEP.IDDEPARTAMENTO')
                                ->select('M_CENTRO_MAC.*', 'D.*', 'P.*', 'DEP.*')
                                ->where('FLAG', 1)
                                ->get();


        return view('configuracion.tablas.tb_nuevo_mac', compact('mac'));
    }

    public function md_add_mac(Request $request)
    {
        $departamento = DB::table('DEPARTAMENTO')->get();

        $view = view('configuracion.modals.md_add_mac', compact('departamento'))->render();

        return response()->json(['html' => $view]); 
    }

    public function store_mac(Request $request)
    {
        try{

            $save = new Mac;
            $save->DIRECCION_MAC = $request->ubicacion;
            $save->UBICACION = $request->distrito;
            $save->NOMBRE_MAC = $request->centro_mac;
            $save->FECHA_APERTURA = $request->fecha_apertura;
            $save->FECHA_INAGURACION = $request->fecha_inaguracion;
            $save->FECHA_CREACION = Carbon::now();
            $save->save();

            return $save;

        }catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function md_edit_mac(Request $request)
    {
        $mac = Mac::leftJoin('DISTRITO as D', 'M_CENTRO_MAC.UBICACION', '=', 'D.IDDISTRITO')
                        ->leftJoin('PROVINCIA as P', 'D.PROVINCIA_ID', '=', 'P.IDPROVINCIA')
                        ->leftJoin('DEPARTAMENTO as DEP', 'D.DEPARTAMENTO_ID', '=', 'DEP.IDDEPARTAMENTO')
                        ->select('M_CENTRO_MAC.*', 'D.*', 'P.*', 'DEP.*')
                        ->where('FLAG', 1)
                        ->where('IDCENTRO_MAC', $request->idcentro_mac)
                        ->first();
                        // dd($mac->IDDEPARTAMENTO);

        $departamento = DB::table('DEPARTAMENTO')->get();
        $provincia = DB::table('PROVINCIA')->get();
        $distrito = DB::table('DISTRITO')->get();

        $view = view('configuracion.modals.md_edit_mac', compact('mac', 'departamento', 'provincia', 'distrito'))->render();

        return response()->json(['html' => $view]);
    }

    public function update_mac(Request $request)
    {
        try{

            $save = Mac::findOrFail($request->idcentro_mac);
            $save->DIRECCION_MAC = $request->ubicacion;
            $save->UBICACION = $request->distrito;
            $save->FECHA_APERTURA = $request->fecha_apertura;
            $save->FECHA_INAGURACION = $request->fecha_inaguracion;
            $save->save();

            return $save;

        }catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function delete_mac(Request $request)
    {
        $delete = Mac::where('IDCENTRO_MAC', $request->idcentro_mac)->delete();

        return $delete;
    }

    /************************************************************************* CONFIGURACION DE TABLAS *******************************************************************/

    public function reg_tablas(Request $request, $idcentro_mac)
    {
        $mac = Mac::where('IDCENTRO_MAC', $idcentro_mac)->first();


        return view('configuracion.reg_tablas', compact('mac'));
    }
}
