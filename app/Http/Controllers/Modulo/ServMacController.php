<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ServMacController extends Controller
{
    private function centro_mac(){
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac'=>$idmac, 'name_mac'=>$name_mac ];

        return (object) $resp;
    }

    public function index()
    {
        $entidad = DB::table('M_MAC_ENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                        ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $this->centro_mac()->idmac)
                        ->get();

        return view('serv_mac.index', compact('entidad'));    
    }

    public function tb_index(Request $request)
    {        
        // dd($this->centro_mac());
        $servicios = DB::table('M_ENTIDAD AS ME')
                            ->select('ME.NOMBRE_ENTIDAD', 'SERV.NOMBRE_SERVICIO', 'SERV.REQUISITO_SERVICIO', 'SERV.REQ_CITA', 'SERV.TIPO_SER', 'SERV.COSTO_SERV','SERV.NOMBRE_MAC','ME.IDENTIDAD', 'SERV.TRAMITE', 'SERV.ORIENTACION', 'SERV.IDENT_SERV', 'SERV.IDSERVICIOS')
                            ->join(DB::raw('(SELECT 
                                                D_ENTIDAD_SERVICIOS.IDSERVICIOS,
                                                D_ENTIDAD_SERVICIOS.NOMBRE_SERVICIO,
                                                D_ENTIDAD_SERVICIOS.REQUISITO_SERVICIO,
                                                D_ENTIDAD_SERVICIOS.REQ_CITA,
                                                D_ENTIDAD_SERVICIOS.TIPO_SER,
                                                D_ENTIDAD_SERVICIOS.TRAMITE,
                                                D_ENTIDAD_SERVICIOS.ORIENTACION,
                                                D_ENT_SERV.IDENTIDAD,
                                                D_ENTIDAD_SERVICIOS.`COSTO_SERV`,
                                                MCM.IDCENTRO_MAC,
                                                MCM.NOMBRE_MAC,
                                                D_ENT_SERV.IDENT_SERV,
                                                D_ENTIDAD_SERVICIOS.FLAG 
                                            FROM
                                                D_ENT_SERV 
                                                JOIN D_ENTIDAD_SERVICIOS 
                                                ON D_ENTIDAD_SERVICIOS.IDSERVICIOS = D_ENT_SERV.IDSERVICIOS 
                                                JOIN M_CENTRO_MAC MCM 
                                                ON MCM.IDCENTRO_MAC = D_ENT_SERV.IDMAC) SERV'), function ($join) {
                                $join->on('SERV.IDENTIDAD', '=', 'ME.IDENTIDAD');
                            })
                            ->where('SERV.FLAG', '=', '1')
                            ->where('SERV.IDCENTRO_MAC', '=', $this->centro_mac()->idmac)
                            ->where(function($que) use ($request) {
                                if($request->entidad != '' ){
                                    $que->where('ME.IDENTIDAD', $request->entidad);
                                }
                            })
                            ->get();

        return view('serv_mac.tablas.tb_index', compact('servicios'));
    }

    public function md_add_servicios(Request $request)
    {                   
        $servicios = DB::table('M_ENTIDAD AS ME')
                            ->select('ME.NOMBRE_ENTIDAD', 'SERV.NOMBRE_SERVICIO', 'SERV.REQUISITO_SERVICIO', 'SERV.REQ_CITA', 'SERV.TIPO_SER', 'SERV.COSTO_SERV','SERV.NOMBRE_MAC','ME.IDENTIDAD', 'SERV.TRAMITE', 'SERV.ORIENTACION', 'SERV.IDENT_SERV', 'SERV.IDSERVICIOS')
                            ->join(DB::raw('(SELECT 
                                                D_ENTIDAD_SERVICIOS.IDSERVICIOS,
                                                D_ENTIDAD_SERVICIOS.NOMBRE_SERVICIO,
                                                D_ENTIDAD_SERVICIOS.REQUISITO_SERVICIO,
                                                D_ENTIDAD_SERVICIOS.REQ_CITA,
                                                D_ENTIDAD_SERVICIOS.TIPO_SER,
                                                D_ENTIDAD_SERVICIOS.TRAMITE,
                                                D_ENTIDAD_SERVICIOS.ORIENTACION,
                                                D_ENT_SERV.IDENTIDAD,
                                                D_ENTIDAD_SERVICIOS.`COSTO_SERV`,
                                                MCM.IDCENTRO_MAC,
                                                MCM.NOMBRE_MAC,
                                                D_ENT_SERV.IDENT_SERV,
                                                D_ENTIDAD_SERVICIOS.FLAG 
                                            FROM
                                                D_ENT_SERV 
                                                JOIN D_ENTIDAD_SERVICIOS 
                                                ON D_ENTIDAD_SERVICIOS.IDSERVICIOS = D_ENT_SERV.IDSERVICIOS 
                                                JOIN M_CENTRO_MAC MCM 
                                                ON MCM.IDCENTRO_MAC = D_ENT_SERV.IDMAC) SERV'), function ($join) {
                                $join->on('SERV.IDENTIDAD', '=', 'ME.IDENTIDAD');
                            })
                            ->where('SERV.FLAG', '=', '1')
                            ->get();

        $view = view('serv_mac.modals.md_add_servicios', compact('servicios'))->render();

        return response()->json(['html' => $view]); 
    }

    public function store_servicio(Request $request)
    {
        try{

            $data = $request->all();
            $missingValues = [];

            foreach ($data as $key => $value) {
                if ($value === NULL || $value === "undefined") {
                    $missingValues[] = $key;
                }
            }
            // dd($missingValues);
            if (!empty($missingValues)) {
                // Enviar un mensaje con los valores faltantes.
                // return response()->json(['message' => 'Falta llenar los siguientes valos :  ' ."\n". implode(', ', $missingValues)], 400);

                $errorMessage = 'Falta llenar los siguientes campos : ' . "\n";

                foreach ($missingValues as $value) {
                    $errorMessage .= "* " . str_replace(
                        [
                            'nombre_servicio', 'tramite', 'orientacion', 'costo_serv', 'requisito_servicio', 'req_cita',
                        ],
                        [
                            'Falta nombre de servicio', 'Seleccionar tipo de trámite', 'Seleccionar tipo de orientación', 'Falta costo de servicio', 'Falta requisito de servicio', 'Ingresar si requiere cita',
                        ], 
                        $value) . "\n";
                }
                // dd($errorMessage);

                $response_ = response()->json([
                    'data' => null,
                    'message' => $errorMessage,
                    'status' => 201,
                ], 200);

                return $response_; 
            }

            $servicio = new Servicio;
            $servicio->NOMBRE_SERVICIO = $request->nombre_servicio;
            $servicio->TRAMITE = $request->tramite;
            $servicio->ORIENTACION = $request->orientacion;
            $servicio->COSTO_SERV = $request->costo_serv;
            $servicio->REQUISITO_SERVICIO = $request->requisito_servicio;
            $servicio->REQ_CITA = $request->req_cita;
            $servicio->save();

            $enl_serv = DB::table('D_ENT_SERV')->insert([
                'IDENTIDAD'     =>  $request->entidad,
                'IDSERVICIOS'   =>  $servicio->IDSERVICIOS,
                'IDMAC'         =>  $this->centro_mac()->idmac,
            ]);

            return $servicio;


        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function delete_servicio(Request $request)
    {
        $delete_ent_ser = DB::table('D_ENT_SERV')->where('IDENT_SERV', $request->ident_serv)->delete();

        return $delete_ent_ser;
    }

    public function export_serv_entidad(Request $request)
    {
        // dd($request->all());

        $entidad = Entidad::where('IDENTIDAD', $request->entidad)->first();

        $servicios = DB::table('M_ENTIDAD AS ME')
                            ->select('ME.NOMBRE_ENTIDAD', 'SERV.NOMBRE_SERVICIO', 'SERV.REQUISITO_SERVICIO', 'SERV.REQ_CITA', 'SERV.TIPO_SER', 'SERV.COSTO_SERV','SERV.NOMBRE_MAC','ME.IDENTIDAD', 'SERV.TRAMITE', 'SERV.ORIENTACION', 'SERV.IDENT_SERV', 'SERV.IDSERVICIOS')
                            ->join(DB::raw('(SELECT 
                                                D_ENTIDAD_SERVICIOS.IDSERVICIOS,
                                                D_ENTIDAD_SERVICIOS.NOMBRE_SERVICIO,
                                                D_ENTIDAD_SERVICIOS.REQUISITO_SERVICIO,
                                                D_ENTIDAD_SERVICIOS.REQ_CITA,
                                                D_ENTIDAD_SERVICIOS.TIPO_SER,
                                                D_ENTIDAD_SERVICIOS.TRAMITE,
                                                D_ENTIDAD_SERVICIOS.ORIENTACION,
                                                D_ENT_SERV.IDENTIDAD,
                                                D_ENTIDAD_SERVICIOS.`COSTO_SERV`,
                                                MCM.IDCENTRO_MAC,
                                                MCM.NOMBRE_MAC,
                                                D_ENT_SERV.IDENT_SERV,
                                                D_ENTIDAD_SERVICIOS.FLAG 
                                            FROM
                                                D_ENT_SERV 
                                                JOIN D_ENTIDAD_SERVICIOS 
                                                ON D_ENTIDAD_SERVICIOS.IDSERVICIOS = D_ENT_SERV.IDSERVICIOS 
                                                JOIN M_CENTRO_MAC MCM 
                                                ON MCM.IDCENTRO_MAC = D_ENT_SERV.IDMAC) SERV'), function ($join) {
                                $join->on('SERV.IDENTIDAD', '=', 'ME.IDENTIDAD');
                            })
                            ->where('SERV.FLAG', '=', '1')
                            ->where('SERV.IDCENTRO_MAC', '=', $this->centro_mac()->idmac)
                            ->where('ME.IDENTIDAD', $entidad->IDENTIDAD)
                            ->get();

        $export = Excel::download(new SeviciosEntidadExport($entidad, $servicios), 'CENTRO MAC '.$this->centro_mac()->name_mac.' - Servicios, Tasas y Requisitos de '.$entidad->NOMBRE_ENTIDAD.'.xlsx');

        return $export;

    }
}
