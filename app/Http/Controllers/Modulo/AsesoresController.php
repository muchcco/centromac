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
use Carbon\Carbon;

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
        $query = DB::table('M_PERSONAL as MP')->distinct()
            ->leftJoin(DB::raw('(
                            SELECT *, ROW_NUMBER() OVER (PARTITION BY NUM_DOC ORDER BY FIELD(STATUS, "Itinerante", "Fijo")) as rn
                            FROM M_PERSONAL_MODULO
                            WHERE FECHAINICIO <= NOW() AND FECHAFIN >= NOW()
                        ) as MPM'), function ($join) {
                $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
                    ->where('MPM.rn', '=', 1);
            })
            ->leftJoin('M_MODULO as MMOD', function ($join) {
                $join->on('MMOD.IDMODULO', '=', 'MPM.IDMODULO');
            })
            ->leftJoin('M_ENTIDAD as ME', function ($join) {
                $join->on('ME.IDENTIDAD', '=', 'MMOD.IDENTIDAD');
            })
            ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MP.IDMAC')
            ->join('D_PERSONAL_TIPODOC as DPT', 'DPT.IDTIPO_DOC', '=', 'MP.IDTIPO_DOC')
            ->leftJoin('d_personal_mac', 'd_personal_mac.idpersonal', '=', 'MP.idpersonal')
            ->join(DB::raw('(
                            SELECT 
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
                            FROM M_PERSONAL
                        ) as CONT'), 'CONT.IDPERSONAL', '=', 'MP.IDPERSONAL')
            ->select(
                'MP.IDPERSONAL',
                'MCM.NOMBRE_MAC as PRINCIPAL_MAC',
                'MP.IDMAC',
                DB::raw('(SELECT COUNT(*) FROM d_personal_mac WHERE d_personal_mac.idpersonal = MP.idpersonal AND d_personal_mac.status = 1 ) AS COUNT_DPM'),
                DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'),
                'DPT.TIPODOC_ABREV',
                'MP.NUM_DOC',
                DB::raw('COALESCE(ME.NOMBRE_ENTIDAD, (SELECT NOMBRE_ENTIDAD FROM M_ENTIDAD WHERE M_ENTIDAD.IDENTIDAD = MP.IDENTIDAD)) AS NOMBRE_ENTIDAD'),
                DB::raw('COALESCE(MMOD.N_MODULO, (SELECT N_MODULO FROM M_MODULO WHERE M_MODULO.IDMODULO = MP.IDMODULO)) AS N_MODULO'),
                DB::raw('COALESCE(
                                (
                                    SELECT GROUP_CONCAT(DISTINCT MCM2.NOMBRE_MAC SEPARATOR ", ")
                                    FROM d_personal_mac AS DPM
                                    JOIN M_CENTRO_MAC AS MCM2 
                                        ON MCM2.IDCENTRO_MAC = DPM.idcentro_mac
                                    WHERE DPM.idpersonal = MP.idpersonal
                                ),
                                MCM.NOMBRE_MAC
                            ) AS NOMBRE_MAC'),
                DB::raw('COALESCE(
                                (
                                    SELECT GROUP_CONCAT(DISTINCT MCM2.NOMBRE_MAC SEPARATOR ", ")
                                    FROM d_personal_mac AS DPM
                                    JOIN M_CENTRO_MAC AS MCM2 
                                        ON MCM2.IDCENTRO_MAC = DPM.idcentro_mac
                                    WHERE DPM.idpersonal = MP.idpersonal
                                ),
                                MCM.NOMBRE_MAC
                            ) AS CENTRO_MAC'),
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
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('d_personal_mac.idcentro_mac', '=', $this->centro_mac()->idmac)
                        ->whereIn('d_personal_mac.status', [1, 2]);  // Se agregan los dos valores de status
                }
            })

            ->whereIn('MP.FLAG', [1, 2, 3])
            ->where('MP.IDENTIDAD', '!=', 17)
            ->orderBy('NOMBRE_ENTIDAD', 'asc')
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

    public function md_cambio_mac(Request $request)
    {
        $centromac = DB::table('M_CENTRO_MAC')
            ->orderBy('NOMBRE_MAC', 'ASC')
            ->get();

        $idmac = $request->mac;

        $detalle_mac = DB::table('d_personal_mac')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'd_personal_mac.idcentro_mac')
            ->where('d_personal_mac.idpersonal', $request->idpersonal)
            ->get();

        $personal = Personal::where('IDPERSONAL', $request->idpersonal)->first();

        $view = view('personal.modals.md_cambio_mac', compact('centromac', 'idmac', 'personal', 'detalle_mac'))->render();

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

    public function update_mac(Request $request)
    {
        try {

            $personal = Personal::findOrFail($request->idpersonal);
            $personal->IDMAC = $request->mac;
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

    public function delete_mac_mod(Request $request)
    {
        try {

            $delete = DB::table('d_personal_mac')->where('id', $request->id)->delete();

            return response()->json(['success' => true, 'message' => 'Registro eliminado.']);
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

            $bajapersonal = DB::table('d_personal_mac')->where('idpersonal', $request->idpersonal)->where('idcentro_mac', $this->centro_mac()->idmac)->update([
                'status'      => 2,
                'updated_at'    => Carbon::now()
            ]);

            if ($request->baja == 3) {
                $personal = Personal::findOrFail($request->idpersonal);
                $personal->IDMAC = NULL;
                $personal->save();
            }

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

                $per_mac = DB::table('d_personal_mac')->where('idcentro_mac', $this->centro_mac()->idmac)->where('idpersonal', $persona_existe->IDPERSONAL)->where('status', 1)->first();
                // dd($persona_existe->IDPERSONAL);
                if ($per_mac) {
                    return response()->json([
                        'data' => null,
                        'message' => "El personal esta registrado en tu centro mac",
                        'status' => 206
                    ]);
                }

                if ($persona_existe->IDMAC === NULL) {
                    $usuario = DB::table('M_PERSONAL')->where('NUM_DOC', $persona_existe->NUM_DOC)->first();
                    if (!$usuario) {
                        return response()->json([
                            'data' => null,
                            'error' => 'Usuario no encontrado.',
                            'message' => 'Error al registrar el asesor.',
                            'status' => 400
                        ]);
                    }

                    $personal = Personal::findOrFail($persona_existe->IDPERSONAL);
                    $personal->IDMAC = $this->centro_mac()->idmac;
                    $personal->FLAG = 1;
                    $personal->save();

                    // Extraer idmodulo e identidad
                    $modulo_entidad = DB::table('M_MODULO')
                        ->where('IDMODULO', $request->modulos_entidades)
                        ->select('IDMODULO', 'IDENTIDAD')
                        ->first();

                    if (!$modulo_entidad) {
                        return response()->json(['message' => 'Módulo no encontrado.'], 400);
                    }

                    $us_id = auth()->user()->id;

                    $insertedId = DB::table('d_personal_mac')->insertGetId([
                        'idcentro_mac' => $this->centro_mac()->idmac,
                        'idpersonal'    => $usuario->IDPERSONAL,
                        'idus_reg'      => $us_id,
                        'status'      => 1,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now()
                    ]);

                    // Guardar en `m_personal_modulo`
                    DB::table('M_PERSONAL_MODULO')->insert([
                        'NUM_DOC' => $usuario->NUM_DOC,
                        'IDMODULO' => $modulo_entidad->IDMODULO,
                        'IDCENTRO_MAC' => $this->centro_mac()->idmac,
                        'FECHAINICIO' => $request->fechainicio,
                        'FECHAFIN' => $request->fechafin
                    ]);

                    return response()->json([
                        'data'    => $insertedId,
                        'message' => 'Asesor registrado correctamente.'
                    ], 200);
                } else {
                    $persona_e_m = Personal::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')->where('NUM_DOC', $request->dni)->first();
                    return response()->json([
                        'data' => null,
                        'message' => "El personal esta registrado en el centro MAC " . $persona_e_m->NOMBRE_MAC,
                        'status' => 201
                    ]);
                }
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

            $us_id = auth()->user()->id;

            $insertedId = DB::table('d_personal_mac')->insertGetId([
                'idcentro_mac' => $this->centro_mac()->idmac,
                'idpersonal'    => $save->IDPERSONAL,
                'idus_reg'      => $us_id,
                'status'      => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ]);

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
    public function store_asesores_more(Request $request)
    {
        try {
            $usuario = DB::table('M_PERSONAL')->where('NUM_DOC', $request->dni)->first();
            if (!$usuario) {
                return response()->json([
                    'data' => null,
                    'error' => 'Usuario no encontrado.',
                    'message' => 'Error al registrar el asesor.',
                    'status' => 400
                ]);
            }

            // Extraer idmodulo e identidad
            $modulo_entidad = DB::table('M_MODULO')
                ->where('IDMODULO', $request->modulos_entidades)
                ->select('IDMODULO', 'IDENTIDAD')
                ->first();

            if (!$modulo_entidad) {
                return response()->json(['message' => 'Módulo no encontrado.'], 400);
            }

            $us_id = auth()->user()->id;

            $insertedId = DB::table('d_personal_mac')->insertGetId([
                'idcentro_mac' => $this->centro_mac()->idmac,
                'idpersonal'    => $usuario->IDPERSONAL,
                'idus_reg'      => $us_id,
                'status'      => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ]);

            // Guardar en `m_personal_modulo`
            DB::table('M_PERSONAL_MODULO')->insert([
                'NUM_DOC' => $usuario->NUM_DOC,
                'IDMODULO' => $modulo_entidad->IDMODULO,
                'IDCENTRO_MAC' => $this->centro_mac()->idmac,
                'FECHAINICIO' => $request->fechainicio,
                'FECHAFIN' => $request->fechafin
            ]);

            return response()->json([
                'data'    => $insertedId,
                'message' => 'Asesor registrado correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data'    => null,
                'error'   => $e->getMessage(),
                'message' => 'Error al registrar el asesor.',
                'status'  => 400
            ]);
        }
    }


    public function exportasesores_excel(Request $request)
    {
        // 1. Obtener los datos con el mismo query que tenías
        $rows = DB::table('db_centros_mac.m_personal_modulo as MPM')
            ->distinct()
            ->selectRaw(
                <<<'SQL'
                MCM.nombre_mac         AS NOMBRE_MAC,
                ME.nombre_entidad      AS NOMBRE_ENTIDAD,
                CONCAT(MP.APE_PAT,' ',MP.APE_MAT,', ',MP.NOMBRE) AS NOMBREU,
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
            SQL
            )
            ->join('db_centros_mac.m_personal as MP',   'MP.num_doc',          '=', 'MPM.num_doc')
            ->leftJoin('db_centros_mac.d_personal_cargo as DPC', 'DPC.idcargo_personal', '=', 'MP.idcargo_personal')
            ->join('db_centros_mac.m_modulo as MMOD',   'MMOD.idmodulo',       '=', 'MPM.idmodulo')
            ->leftJoin('db_centros_mac.m_entidad as ME', 'ME.identidad',        '=', 'MMOD.identidad')
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
            ->where('MP.FLAG', 1)
            ->where('MP.IDENTIDAD', '!=', 17)
            ->orderBy('ME.nombre_entidad', 'asc')
            ->get();

        // 2. Devolver Excel usando FromCollection + WithHeadings
        return Excel::download(new AsesoresExport($rows), 'reporte_asesores.xlsx');
    }
}
