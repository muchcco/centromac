<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entidad;
use App\Models\Personal;
use App\Models\User;

class AsesoresController extends Controller
{
    public function asesores()
    {
        return view('personal.asesores');
    }

    public function tb_asesores(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/
        
        $query = DB::table('M_PERSONAL as MP')
                                    ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MP.IDMAC')
                                    ->join('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MP.IDENTIDAD')
                                    ->join('D_PERSONAL_TIPODOC as DPT', 'DPT.IDTIPO_DOC', '=', 'MP.IDTIPO_DOC')
                                    ->join(DB::raw('(SELECT 
                                                (CASE WHEN NOMBRE IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN APE_PAT IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN APE_MAT IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN IDTIPO_DOC IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN NUM_DOC IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN IDMAC IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN IDENTIDAD IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DIRECCION IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN SEXO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN FECH_NACIMIENTO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN IDDISTRITO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN IDDISTRITO_NAC IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN TELEFONO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN CELULAR IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN CORREO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN GRUPO_SANGUINEO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN E_NOMAPE IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN E_TELEFONO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN E_CELULAR IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN ESTADO_CIVIL IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DF_N_HIJOS IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN PD_PUESTO_TRABAJO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN PD_TIEMPO_PTRABAJO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN PD_CENTRO_ATENCION IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN PD_CODIGO_IDENTIFICACION IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_FECHA_INGRESO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_PUESTO_TRABAJO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_TIEMPO_PTRABAJO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_AREA_TRABAJO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_JEFE_INMEDIATO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_CARGO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN DLP_TELEFONO IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN TVL_ID IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN GI_ID IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN GI_CARRERA IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN GI_DESDE IS NULL THEN 1 ELSE 0 END +
                                                CASE WHEN GI_HASTA IS NULL THEN 1 ELSE 0 END) AS CAMPOS_NULL,
                                                M_PERSONAL.IDPERSONAL
                                            FROM db_centros_mac.M_PERSONAL) CONT'), 'CONT.IDPERSONAL', '=', 'MP.IDPERSONAL')
                                    ->select(
                                        'MP.IDPERSONAL',
                                        DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'),
                                        'DPT.TIPODOC_ABREV',
                                        'MP.NUM_DOC',
                                        'ME.ABREV_ENTIDAD',
                                        'MCM.NOMBRE_MAC',
                                        'MP.FLAG',
                                        'MP.CORREO',
                                        'CONT.CAMPOS_NULL',
                                        DB::raw("(
                                            SELECT COUNT(*) AS cantidad_campos
                                            FROM information_schema.columns
                                            WHERE table_schema = 'db_centros_mac'
                                              AND table_name = 'M_PERSONAL'
                                        ) AS TOTAL_CAMPOS"),
                                        DB::raw("( 
                                            (
                                                SELECT COUNT(*) AS cantidad_campos
                                                FROM information_schema.columns
                                                WHERE table_schema = 'db_centros_mac'
                                                  AND table_name = 'M_PERSONAL'
                                            ) - CONT.CAMPOS_NULL
                                        ) AS DIFERENCIA_CAMPOS")
                                    )
                                    ->where('MP.IDMAC', '=', $idmac)
                                    ->whereNot('MP.IDENTIDAD', 17) //QUITAMOS DEL REGISTRO A PERSONAL DE PCM
                                    ->orderBy('ME.NOMBRE_ENTIDAD', 'asc')
                                    ->get();


        return view('personal.tablas.tb_asesores', compact('query'));
    }

    public function md_add_asesores(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $entidad = DB::table('M_MAC_ENTIDAD')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $idmac)
                            ->get();

        $view = view('personal.modals.md_add_asesores', compact('entidad'))->render();

        return response()->json(['html' => $view]); 
    }

    public function store_asesores(Request $request)
    {
        try{
            $validated = $request->validate([
                'nombre' => 'required',
                'ap_pat' => 'required',
                'ap_mat' => 'required',
                'dni' => 'required',
                'entidad' => 'required',
                'sexo' => 'required',
            ]);

            $persona_existe = Personal::where('NUM_DOC', $request->dni)->first();
            // dd($persona_existe);
            if($persona_existe){
                $response_ = response()->json([
                    'data' => null,
                    'message' => "El personal ya fue registrado",
                    'status' => 201,
                ], 200);

                return $response_;
            }

            /*================================================================================================================*/
            $us_id = auth()->user()->idcentro_mac;
            $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

            $idmac = $user->IDCENTRO_MAC;
            $name_mac = $user->NOMBRE_MAC;
            /*================================================================================================================*/

            // $personal = 

            $save = new Personal;
            $save->NOMBRE = $request->nombre;
            $save->APE_PAT = $request->ap_pat;
            $save->APE_MAT = $request->ap_mat;
            $save->NUM_DOC = $request->dni;
            $save->IDENTIDAD = $request->entidad;
            $save->SEXO = $request->sexo;
            $save->CORREO = $request->correo;
            $save->IDMAC = $idmac;
            $save->IDTIPO_DOC = 1;
            $save->FECH_NACIMIENTO = $request->fech_nac;
            $save->CELULAR = $request->telefono;
            $save->save();

            //$per = new Personalinter;
            //$per->id_personal = $save->id;
            //$per->validez = 0;
            //$per->estado = 5;
            //$per->save();

            return $save;
            
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

}