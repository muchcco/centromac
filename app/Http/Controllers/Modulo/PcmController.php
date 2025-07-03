<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entidad;
use App\Models\Personal;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsesoresExport;
use Carbon\Carbon;

class PcmController extends Controller
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

    public function pcm()
    {
        return view('personal.pcm');
    }

    public function tb_pcm(Request $request)
    {

        $query = DB::table('M_PERSONAL as MP')
            ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MP.IDMAC')
            ->join('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MP.IDENTIDAD')
            ->join('D_PERSONAL_TIPODOC as DPT', 'DPT.IDTIPO_DOC', '=', 'MP.IDTIPO_DOC')
            ->join('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
            ->join(DB::raw('(SELECT 
                                IDPERSONAL,
                                (
                                    CASE WHEN GI_CURSO_ESP IS NULL THEN 1 ELSE 0 END + 
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
                if (!auth()->user()->hasRole('Administrador')) {
                    if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                        $query->where('MP.IDMAC', '=', $this->centro_mac()->idmac);
                    }
                }
            })
            ->whereIn('MP.FLAG', [1, 2])
            ->whereIn('MP.IDENTIDAD', [17, 74, 98, 100, 119, 120])  // Cambié la condición para los ID de IDENTIDAD
            ->whereIn('DPC.IDCARGO_PERSONAL', [1, 2, 3, 4, 5])
            ->orderBy('ME.NOMBRE_ENTIDAD', 'asc')
            ->get();


        return view('personal.tablas.tb_pcm', compact('query'));
    }

    public function md_add_pcm(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $cargos = DB::table('D_PERSONAL_CARGO')->get();

        $entidad = DB::table('M_ENTIDAD')->where('IDENTIDAD', 17)->get();


        $view = view('personal.modals.md_add_pcm', compact('entidad', 'cargos'))->render();

        return response()->json(['html' => $view]);
    }

    public function store_pcm(Request $request)
    {
        try {
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
            if ($persona_existe) {
                $response_ = response()->json([
                    'data' => null,
                    'message' => "El personal ya fue registrado... Si no esta en la lista, completar el formulario de registro dando clic en el siguiente enlace <a href='" . route('validar') . "' target='_blank'><strong>(Hacer clic aqui)</strong></a>",
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
            $save->IDCARGO_PERSONAL = $request->cargo;
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
            $us_id = auth()->user()->id;
            $insertedId = DB::table('d_personal_mac')->insertGetId([
                'idcentro_mac' => $idmac,
                'idpersonal'   => $save->IDPERSONAL,
                'idus_reg'     => $us_id,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ]);
            return response()->json([
                'data' => $save,
                'message' => 'Personal registrado exitosamente',
                'status' => 200
            ]);
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

    public function md_baja_pcm(Request $request)
    {
        $personal = Personal::where('IDPERSONAL', $request->idpersonal)->first();

        // dd($personal);

        $view = view('personal.modals.md_baja_pcm', compact('personal'))->render();

        return response()->json(['html' => $view]);
    }

    public function baja_pcm(Request $request)
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

    public function exporta_excel(Request $request)
    {

        $tipo = $request->tipo;


        if ($tipo == 1) {
            $now = now();
            // 1) Consulta de m_personal_modulo
            $q1 = DB::table('db_centros_mac.m_personal_modulo as MPM')
                ->distinct()  // Asegurando que no haya filas duplicadas
                ->selectRaw(<<<'SQL'
                            MCM.nombre_mac         AS NOMBRE_MAC,
                            ME.nombre_entidad      AS NOMBRE_ENTIDAD,
                            CONCAT(MP.APE_PAT, ' ', MP.APE_MAT, ', ', MP.NOMBRE) AS NOMBREU,
                            DPT.TIPODOC_ABREV      AS TIPODOC_ABREV,
                            MP.NUM_DOC             AS NUM_DOC,
                            MP.FLAG                AS FLAG,
                            MP.CORREO              AS CORREO,
                            MP.FECH_NACIMIENTO     AS FECH_NACIMIENTO,
                            MP.CELULAR             AS CELULAR,
                            DPC.NOMBRE_CARGO       AS NOMBRE_CARGO,
                            MP.SEXO                AS SEXO,
                            MP.PD_FECHA_INGRESO    AS PD_FECHA_INGRESO,
                            MP.PCM_TALLA           AS PCM_TALLA,
                            MP.ESTADO_CIVIL        AS ESTADO_CIVIL,
                            MP.DF_N_HIJOS          AS DF_N_HIJOS,
                            MP.NUMERO_MODULO       AS NUMERO_MODULO,
                            MP.IDCARGO_PERSONAL    AS IDCARGO_PERSONAL,
                            MP.TVL_ID              AS TVL_ID,
                            MP.N_CONTRATO          AS N_CONTRATO,
                            MP.GI_ID               AS GI_ID,
                            MP.GI_CARRERA          AS GI_CARRERA,
                            MP.GI_CURSO_ESP        AS GI_CURSO_ESP,
                            MP.DLP_JEFE_INMEDIATO  AS DLP_JEFE_INMEDIATO,
                            MP.DLP_CARGO           AS DLP_CARGO,
                            MP.DLP_TELEFONO        AS DLP_TELEFONO,
                            MP.I_INGLES            AS I_INGLES,
                            MP.I_QUECHUA           AS I_QUECHUA
                        SQL)
                ->join('db_centros_mac.m_personal as MP', 'MP.num_doc', '=', 'MPM.num_doc')
                ->leftJoin('db_centros_mac.d_personal_cargo as DPC', 'DPC.idcargo_personal', '=', 'MP.idcargo_personal')
                ->join('db_centros_mac.m_modulo as MMOD', 'MMOD.idmodulo', '=', 'MPM.idmodulo')
                ->leftJoin('db_centros_mac.m_entidad as ME', 'ME.identidad', '=', 'MMOD.identidad')
                ->leftJoin('db_centros_mac.d_personal_mac as DPM', function ($j) {
                    $j->on('DPM.idpersonal', '=', 'MP.idpersonal')
                        ->whereIn('DPM.status', [1]);
                })
                ->join('db_centros_mac.m_centro_mac as MCM', 'MCM.idcentro_mac', '=', 'MPM.idcentro_mac')
                ->join('db_centros_mac.d_personal_tipodoc as DPT', 'DPT.idtipo_doc', '=', 'MP.idtipo_doc')
                ->whereDate('MPM.fechainicio', '<=', now())
                ->where(function ($q) {
                    $q->whereDate('MPM.fechafin', '>=', now())
                        ->orWhereNull('MPM.fechafin');
                })
                ->where('MPM.status', 'fijo')
                ->when(
                    auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador'),
                    fn($q) => $q->where('MPM.idcentro_mac', auth()->user()->idcentro_mac)
                )
                ->where('MP.FLAG', 1);

            // 2) Consulta para obtener los de IDENTIDAD = 17 en m_personal
            $q2 = DB::table('db_centros_mac.m_personal as MP')
                ->distinct()  // Asegurando que no haya filas duplicadas
                ->selectRaw(<<<'SQL'
                            MCM.nombre_mac         AS NOMBRE_MAC,
                            ME.nombre_entidad      AS NOMBRE_ENTIDAD,
                            CONCAT(MP.APE_PAT, ' ', MP.APE_MAT, ', ', MP.NOMBRE) AS NOMBREU,
                            DPT.TIPODOC_ABREV      AS TIPODOC_ABREV,
                            MP.NUM_DOC             AS NUM_DOC,
                            MP.FLAG                AS FLAG,
                            MP.CORREO              AS CORREO,
                            MP.FECH_NACIMIENTO     AS FECH_NACIMIENTO,
                            MP.CELULAR             AS CELULAR,
                            DPC.NOMBRE_CARGO       AS NOMBRE_CARGO,
                            MP.SEXO                AS SEXO,
                            MP.PD_FECHA_INGRESO    AS PD_FECHA_INGRESO,
                            MP.PCM_TALLA           AS PCM_TALLA,
                            MP.ESTADO_CIVIL        AS ESTADO_CIVIL,
                            MP.DF_N_HIJOS          AS DF_N_HIJOS,
                            MP.NUMERO_MODULO       AS NUMERO_MODULO,
                            MP.IDCARGO_PERSONAL    AS IDCARGO_PERSONAL,
                            MP.TVL_ID              AS TVL_ID,
                            MP.N_CONTRATO          AS N_CONTRATO,
                            MP.GI_ID               AS GI_ID,
                            MP.GI_CARRERA          AS GI_CARRERA,
                            MP.GI_CURSO_ESP        AS GI_CURSO_ESP,
                            MP.DLP_JEFE_INMEDIATO  AS DLP_JEFE_INMEDIATO,
                            MP.DLP_CARGO           AS DLP_CARGO,
                            MP.DLP_TELEFONO        AS DLP_TELEFONO,
                            MP.I_INGLES            AS I_INGLES,
                            MP.I_QUECHUA           AS I_QUECHUA
                        SQL)
                ->join('db_centros_mac.m_centro_mac as MCM', 'MCM.idcentro_mac', '=', 'MP.IDMAC')
                ->join('db_centros_mac.m_entidad as ME', 'ME.identidad', '=', 'MP.IDENTIDAD')
                ->join('db_centros_mac.d_personal_tipodoc as DPT', 'DPT.idtipo_doc', '=', 'MP.IDTIPO_DOC')
                ->leftJoin('db_centros_mac.d_personal_cargo as DPC', 'DPC.idcargo_personal', '=', 'MP.idcargo_personal')
                ->where('MP.IDENTIDAD', 17)  // Solo tomamos los de IDENTIDAD = 17
                ->where('MP.FLAG', 1)
                // Aplicar el filtro de roles solo si el usuario tiene uno de los roles especificados
                ->when(
                    auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador'),
                    fn($q) => $q->where('MP.IDMAC', auth()->user()->idcentro_mac)  // Aquí se aplica el filtro para el centro MAC
                );

            // Unir las dos consultas con UNION
            $query = $q1
                ->union($q2)  // Unimos las dos consultas
                ->orderBy('NOMBRE_ENTIDAD', 'asc')  // Ordenamos por NOMBRE_ENTIDAD
                ->get();
        } elseif ($tipo == 3) {
            $query = DB::table('db_centros_mac.M_PERSONAL as MP')
                ->leftJoin('db_centros_mac.M_PERSONAL_MODULO as MPM', function ($join) {
                    $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
                        ->whereDate('MPM.FECHAINICIO', '<=', now())
                        ->whereDate('MPM.FECHAFIN', '>=', now())
                        ->where('MPM.STATUS', '=', 'fijo') // Agregar condición para MPM.STATUS = 'fijo'
                        ->where('MP.IDMAC', '=', 'MPM.IDCENTRO_MAC');
                })
                ->leftJoin('db_centros_mac.M_MODULO as MMOD', 'MMOD.IDMODULO', '=', 'MPM.IDMODULO')
                ->leftJoin('db_centros_mac.M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MMOD.IDENTIDAD')
                ->leftJoin('db_centros_mac.D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                ->leftJoin('db_centros_mac.D_PERSONAL_MAC as DPM', function ($join) {
                    $join->on('DPM.IDPERSONAL', '=', 'MP.IDPERSONAL')
                        ->where('DPM.STATUS', '=', 1); // Agregar la condición de STATUS = 1
                })
                ->join('db_centros_mac.M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'DPM.IDCENTRO_MAC') // Se une con M_CENTRO_MAC
                ->join('db_centros_mac.D_PERSONAL_TIPODOC as DPT', 'DPT.IDTIPO_DOC', '=', 'MP.IDTIPO_DOC')
                ->select(
                    'MP.IDPERSONAL',
                    DB::raw("CONCAT(MP.APE_PAT, ' ', MP.APE_MAT, ', ', MP.NOMBRE) AS NOMBREU"),
                    'DPT.TIPODOC_ABREV',
                    'MP.NUM_DOC',
                    DB::raw('COALESCE(ME.NOMBRE_ENTIDAD, (SELECT NOMBRE_ENTIDAD FROM M_ENTIDAD WHERE M_ENTIDAD.IDENTIDAD = MP.IDENTIDAD)) AS NOMBRE_ENTIDAD'),
                    DB::raw('COALESCE(MMOD.N_MODULO, (SELECT N_MODULO FROM M_MODULO WHERE M_MODULO.IDMODULO = MP.IDMODULO)) AS N_MODULO'),
                    'MCM.NOMBRE_MAC',  // Se obtiene el NOMBRE_MAC desde MCM
                    'MP.FLAG',
                    'MP.CORREO',
                    'MP.FECH_NACIMIENTO',
                    'MP.CELULAR',
                    'DPC.NOMBRE_CARGO',
                    'MP.SEXO',
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
                    'MP.I_QUECHUA',
                    'DPM.IDCENTRO_MAC'
                )
                ->where('MP.FLAG', 1)
                ->where(function ($query) {
                    if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                        $query->where('DPM.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                    }
                })
                ->whereIn('MP.IDENTIDAD', [17, 74, 98, 100, 119, 120])  // Cambié la condición para los ID de IDENTIDAD
                ->whereIn('DPC.IDCARGO_PERSONAL', [1, 2, 3, 4, 5])
                ->orderBy('MCM.NOMBRE_MAC', 'asc')
                ->orderBy('ME.NOMBRE_ENTIDAD', 'asc')
                ->get();
        } elseif ($tipo == 2) {
        }




        // dd($query);
        $export = Excel::download(new AsesoresExport($query), 'REPORTE DE PERSONAL CENTROS MAC.xlsx');

        return $export;
    }
}
