<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entidad;
use App\Models\Personal;
use App\Models\User;
use App\Exports\AsistenciaGroupExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsesoresExport;

class AsesoresController extends Controller
{

    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac' => $idmac, 'name_mac' => $name_mac];

        return (object) $resp;
    }

    public function asesores()
    {
        return view('personal.asesores');
    }

    public function tb_asesores(Request $request)
    {
        $query = DB::table('M_PERSONAL as MP')
            ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MP.IDMAC')
            ->join('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MP.IDENTIDAD')
            ->join('D_PERSONAL_TIPODOC as DPT', 'DPT.IDTIPO_DOC', '=', 'MP.IDTIPO_DOC')
            ->leftJoin('M_MODULO as MMOD', 'MMOD.IDMODULO', '=', 'MP.IDMODULO')
            ->join(DB::raw('(SELECT 
                                IDPERSONAL,
                                (
                                    CASE WHEN TIP_CAS IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN N_CONTRATO IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN NOMBRE IS NULL THEN 1 ELSE 0 END + 
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
                                    CASE WHEN TELEFONO IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN CELULAR IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN CORREO IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN ESTADO_CIVIL IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN DF_N_HIJOS IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN DLP_JEFE_INMEDIATO IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN DLP_CARGO IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN DLP_TELEFONO IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN TVL_ID IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN GI_ID IS NULL THEN 1 ELSE 0 END + 
                                    CASE WHEN GI_CARRERA IS NULL THEN 1 ELSE 0 END
                                ) AS CAMPOS_NULL
                                FROM M_PERSONAL) as CONT'), 'CONT.IDPERSONAL', '=', 'MP.IDPERSONAL')
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
                'MMOD.N_MODULO',
                DB::raw("(
                    SELECT COUNT(*) 
                    FROM information_schema.columns
                    WHERE table_schema = 'db_centros_mac'
                    AND table_name = 'M_PERSONAL'
                ) AS TOTAL_CAMPOS"),
                DB::raw("(
                    (
                        SELECT COUNT(*) 
                        FROM information_schema.columns
                        WHERE table_schema = 'db_centros_mac'
                        AND table_name = 'M_PERSONAL'
                    ) - CONT.CAMPOS_NULL
                ) AS DIFERENCIA_CAMPOS")
            )
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('MP.IDMAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->whereIn('MP.FLAG', [1, 2])
            ->where('MP.IDENTIDAD', '!=', 17) // Excluyendo personal de PCM
            ->orderBy('ME.NOMBRE_ENTIDAD', 'asc')
            ->get();

        return view('personal.tablas.tb_asesores', compact('query'));
    }

    public function md_add_asesores(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
            ->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        // Modificación: Obtener `n_modulo` y `n_entidad` juntos
        $modulos_entidades = DB::table('db_centros_mac.M_MODULO as MM')
            ->join('db_centros_mac.M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MM.IDENTIDAD')
            ->join('db_centros_mac.M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MM.IDCENTRO_MAC')
            ->where('MCM.IDCENTRO_MAC', $idmac)
            ->select(
                DB::raw('CONCAT(MM.N_MODULO, " - ", ME.NOMBRE_ENTIDAD) as nombre_completo'),
                'MM.IDMODULO'
            )
            ->orderBy('MM.N_MODULO', 'ASC')
            ->get();

        // Pasar la colección al modal
        $view = view('personal.modals.md_add_asesores', compact('modulos_entidades'))->render();

        return response()->json(['html' => $view]);
    }

    public function md_cambiar_entidad(Request $request)
    {
        $entidad = DB::table('M_MAC_ENTIDAD')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $this->centro_mac()->idmac)
            ->orderBy('M_ENTIDAD.NOMBRE_ENTIDAD', 'ASC')
            ->get();

        $personal = Personal::where('IDPERSONAL', $request->idpersonal)->first();

        $view = view('personal.modals.md_cambiar_entidad', compact('entidad', 'personal'))->render();

        return response()->json(['html' => $view]);
    }

    public function md_cambiar_modulo(Request $request)
    {
        $personal = Personal::where('IDPERSONAL', $request->idpersonal)->first();

        // dd($personal);

        $modulo = DB::table('M_MODULO')
            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MODULO.IDENTIDAD')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MODULO.IDCENTRO_MAC')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $this->centro_mac()->idmac)
            ->where(function ($query) {
                $query->whereDate('M_MODULO.FECHAINICIO', '<=', now()->format('Y-m-d')) // Comparar con la fecha actual en formato 'YYYY-MM-DD'
                    ->whereDate('M_MODULO.FECHAFIN', '>=', now()->format('Y-m-d'));    // Comparar con la fecha actual en formato 'YYYY-MM-DD'
            })
            ->orderBy('M_MODULO.N_MODULO', 'ASC')
            ->get();

        // dd($modulo);
        $view = view('personal.modals.md_cambiar_modulo', compact('personal', 'modulo'))->render();

        return response()->json(['html' => $view]);
    }

    public function md_baja_asesores(Request $request)
    {
        $personal = Personal::where('IDPERSONAL', $request->idpersonal)->first();

        // dd($personal);

        $view = view('personal.modals.md_baja_asesores', compact('personal'))->render();

        return response()->json(['html' => $view]);
    }

    public function update_entidad(Request $request)
    {
        try {

            $personal = Personal::findOrFail($request->idpersonal);
            $personal->IDENTIDAD = $request->entidad;
            $personal->save();

            return $personal;
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

    public function update_modulo(Request $request)
    {
        try {

            $personal = Personal::findOrFail($request->idpersonal);
            $personal->IDMODULO = $request->modulo;
            $personal->save();

            return $personal;
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

    public function baja_asesores(Request $request)
    {
        try {

            $personal = Personal::findOrFail($request->idpersonal);
            $personal->FLAG = $request->baja;
            $personal->save();

            return $personal;
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

    public function store_asesores(Request $request)
    {
        try {
            // Validación de campos
            $validated = $request->validate([
                'nombre' => 'required|string',
                'ap_pat' => 'required|string',
                'ap_mat' => 'required|string',
                'dni' => 'required|numeric',
                'modulos_entidades' => 'required',
                'fechainicio' => 'required|date',
                'fechafin' => 'required|date|after_or_equal:fechainicio'
            ]);

            // Extraer idmodulo e identidad
            $modulo_entidad = DB::table('M_MODULO')
                ->where('IDMODULO', $request->modulos_entidades)
                ->select('IDMODULO', 'IDENTIDAD')
                ->first();

            if (!$modulo_entidad) {
                return response()->json(['message' => 'Módulo no encontrado.'], 400);
            }

            // Verificar existencia del personal
            $persona_existe = Personal::where('NUM_DOC', $request->dni)->first();
            if ($persona_existe) {
                return response()->json([
                    'data' => null,
                    'message' => "El personal ya fue registrado.",
                    'status' => 201
                ]);
            }

            // Obtener centro MAC del usuario
            $us_id = auth()->user()->idcentro_mac;
            $user = User::where('idcentro_mac', $us_id)->first();

            // Guardar en `m_personal`
            $save = new Personal;
            $save->NOMBRE = $request->nombre;
            $save->APE_PAT = $request->ap_pat;
            $save->APE_MAT = $request->ap_mat;
            $save->NUM_DOC = $request->dni;
            $save->IDENTIDAD = $modulo_entidad->IDENTIDAD;
            $save->IDMAC = $user->idcentro_mac;
            $save->IDTIPO_DOC = 1;
            $save->IDMODULO = $modulo_entidad->IDMODULO;
            $save->save();

            // Guardar en `m_personal_modulo`
            DB::table('M_PERSONAL_MODULO')->insert([
                'NUM_DOC' => $request->dni,
                'IDMODULO' => $modulo_entidad->IDMODULO,
                'IDCENTRO_MAC' => $user->idcentro_mac,
                'FECHAINICIO' => $request->fechainicio,
                'FECHAFIN' => $request->fechafin
            ]);

            return response()->json([
                'data' => $save,
                'message' => 'Asesor registrado exitosamente',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'Error al registrar el asesor.',
                'status' => 400
            ]);
        }
    }


    public function exportasesores_excel()
    {

        $query = DB::table('db_centros_mac.M_PERSONAL as MP')
            ->join('db_centros_mac.M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MP.IDMAC')
            ->join('db_centros_mac.M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MP.IDENTIDAD')
            ->join('db_centros_mac.D_PERSONAL_TIPODOC as DPT', 'DPT.IDTIPO_DOC', '=', 'MP.IDTIPO_DOC')
            ->join('db_centros_mac.D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
            ->select(
                'MP.IDPERSONAL',
                DB::raw("CONCAT(MP.APE_PAT, ' ', MP.APE_MAT, ', ', MP.NOMBRE) AS NOMBREU"),
                'DPT.TIPODOC_ABREV',
                'MP.NUM_DOC',
                'ME.ABREV_ENTIDAD',
                'MCM.NOMBRE_MAC',
                'MP.FLAG',
                'MP.CORREO',
                'MP.FECH_NACIMIENTO',
                'MP.CELULAR',
                'DPC.NOMBRE_CARGO',
                'MP.PD_FECHA_INGRESO',
                'MP.PCM_TALLA',
                'MP.ESTADO_CIVIL',
                'MP.DF_N_HIJOS',
                'MP.NUMERO_MODULO',
                'MP.IDCARGO_PERSONAL',
                'MP.TVL_ID',
                'MP.N_CONTRATO',
                'MP.TIP_CAS',
                'MP.GI_ID',
                'MP.GI_CARRERA',
                'MP.GI_CURSO_ESP',
                'MP.DLP_JEFE_INMEDIATO',
                'MP.APE_MAT',
                'MP.DLP_CARGO',
                'MP.DLP_TELEFONO',
                'MP.I_INGLES',
                'MP.I_QUECHUA'
            )
            ->where('MP.FLAG', 1)
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('MP.IDMAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->where('MP.IDENTIDAD', '!=', 17)
            ->orderBy('ME.NOMBRE_ENTIDAD', 'asc')
            ->get();


        // dd($query);
        $export = Excel::download(new AsesoresExport($query), 'REPORTE DE PERSONAL ASESORES CENTROS MAC.xlsx');

        return $export;
    }
}
