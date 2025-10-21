<?php

namespace App\Http\Controllers\Modulo;

use App\Exports\AsistenciaDetalleExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\Asistenciatest;
use App\Models\Entidad;
use App\Models\Mac;
use Illuminate\Support\Facades\DB;
use App\Imports\AsistenciaImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Personal;
use Carbon\Carbon;
use App\Exports\AsistenciaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AsistenciaGroupExport;
use App\Models\Configuracion;
use Carbon\CarbonPeriod;
use PDO;
use mysqli;
use PDOException;
use App\Models\AuditoriaGeneral;
use Illuminate\Database\Query\JoinClause;

class AsistenciaController extends Controller
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

    public function asistencia()
    {
        // $da = User::first()->locales;
        // dd($da);
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
            ->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $entidad = DB::table('M_MAC_ENTIDAD')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $idmac)
            ->get();

        // SOLO Administrador o Moderador pueden ver todos los MACs
        $macs = [];
        if (auth()->user()->hasRole(['Administrador', 'Moderador'])) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('idcentro_mac as id', 'nombre_mac as nom')
                ->orderBy('nombre_mac')
                ->get();
        }

        return view('asistencia.asistencia', compact('entidad', 'idmac', 'name_mac', 'macs'));
    }

    public function cerrarDia(Request $request)
    {
        try {
            $fecha = $request->input('fecha');
            $idmac = $request->input('idmac');
            $user  = auth()->user();

            // Ejecutar el SP de cierre/migración
            DB::statement("CALL guardar_resumen_asistencia_dia(?, ?)", [$fecha, $idmac]);

            // Guardar en log
            DB::table('db_centros_mac.cierre_asistencia_log')->insert([
                'tipo_cierre'   => 'DIA',
                'fecha'         => $fecha,
                'anio'          => Carbon::parse($fecha)->year,
                'mes'           => Carbon::parse($fecha)->month,
                'idmac'         => $idmac,
                'user_id'       => $user->id,
                'user_nombre'   => $user->name,
                'fecha_registro' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "El día $fecha en el MAC $idmac se cerró correctamente."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error al cerrar el día: " . $e->getMessage()
            ], 500);
        }
    }

    public function cerrarMes(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $idmac = $request->input('idmac');

        if (!$anio || !$mes) {
            return response()->json(['success' => false, 'message' => 'Faltan parámetros (año o mes).']);
        }

        try {
            DB::beginTransaction();

            // Determinar qué MACs cerrar
            if (auth()->user()->hasRole('Administrador')) {
                $macs = $idmac ? [$idmac] : DB::table('m_centro_mac')->pluck('idcentro_mac')->toArray();
            } else {
                $macs = [auth()->user()->idcentro_mac];
            }

            foreach ($macs as $mac) {
                // Ejecutar SP de cierre
                DB::statement("CALL guardar_resumen_asistencia(?, ?, ?)", [$anio, $mes, $mac]);

                // Insertar log
                DB::table('cierre_asistencia_log')->insert([
                    'tipo_cierre'    => 'MES',
                    'fecha'          => now()->toDateString(),
                    'anio'           => $anio,
                    'mes'            => $mes,
                    'idmac'          => $mac,
                    'user_id'        => auth()->id(),
                    'user_nombre'    => auth()->user()->name,
                    'fecha_registro' => now(),
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => "Mes {$mes}-{$anio} cerrado correctamente."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function md_cerrar_mes()
    {
        $html = view('asistencia.modals.md_cerrar_mes')->render();
        return response()->json(['html' => $html]);
    }

    public function store_agregar_asistencia(Request $request)
    {
        $userIdMac = auth()->user()->idcentro_mac;

        if ($request->has('DNI') && !$request->has('fecha')) {
            try {
                $allowedCentrosMac = [10, 12, 13, 14, 19];
                $userCentroMac = auth()->user()->idcentro_mac;

                $personal = DB::table('m_personal')
                    ->when(!in_array($userCentroMac, $allowedCentrosMac), function ($q) use ($userIdMac) {
                        return $q->where('IDMAC', $userIdMac);
                    }, function ($q) use ($allowedCentrosMac) {
                        return $q->whereIn('IDMAC', $allowedCentrosMac);
                    })
                    ->where('NUM_DOC', $request->input('DNI'))
                    ->first();

                if ($personal) {
                    $nombreCompleto = $personal->NOMBRE . ' ' . $personal->APE_PAT . ' ' . $personal->APE_MAT;
                    return response()->json(['success' => true, 'nombreCompleto' => $nombreCompleto]);
                }

                return response()->json(['success' => false, 'message' => 'DNI no encontrado o no pertenece a este centro MAC']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error al buscar el nombre: ' . $e->getMessage()]);
            }
        }

        $request->validate([
            'DNI'    => 'required|string|max:15',
            'fecha'  => 'required|date',
            'id'     => 'required|integer',
            'hora1'  => 'nullable|date_format:H:i',
            'hora2'  => 'nullable|date_format:H:i',
            'hora3'  => 'nullable|date_format:H:i',
            'hora4'  => 'nullable|date_format:H:i',
        ]);

        try {
            $allowedCentrosMac = [10, 12, 13, 14, 19];
            $userCentroMac = auth()->user()->idcentro_mac;

            $personal = DB::table('m_personal as p')
                ->join('d_personal_mac as dpm', 'dpm.idpersonal', '=', 'p.idpersonal')
                ->when(!in_array($userCentroMac, $allowedCentrosMac), function ($q) use ($userIdMac) {
                    return $q->where('dpm.idcentro_mac', $userIdMac);
                }, function ($q) use ($allowedCentrosMac) {
                    return $q->whereIn('dpm.idcentro_mac', $allowedCentrosMac);
                })
                ->where('p.NUM_DOC', $request->input('DNI'))
                ->first();

            if (!$personal) {
                return response()->json([
                    'success' => false,
                    'message' => 'El DNI proporcionado no corresponde a ningún personal registrado en este centro MAC.'
                ]);
            }

            $nombreCompleto = $personal->NOMBRE . ' ' . $personal->APE_PAT . ' ' . $personal->APE_MAT;
            $fecha = $request->input('fecha');
            $mes   = date('m', strtotime($fecha));
            $anio  = date('Y', strtotime($fecha));

            $horas = [
                $request->input('hora1'),
                $request->input('hora2'),
                $request->input('hora3'),
                $request->input('hora4')
            ];

            $lastCorrelativo = DB::table('m_asistencia')
                ->where('num_doc', $request->input('DNI'))
                ->where('fecha', $fecha)
                ->max('CORRELATIVO');
            $nextCorrelativo = $lastCorrelativo ? ((int)$lastCorrelativo + 1) : 1;

            foreach ($horas as $hora) {
                if (!empty($hora)) {
                    $punchTime = $fecha . ' ' . $hora;

                    $exists = DB::table('m_asistencia')
                        ->where('IDCENTRO_MAC', $userIdMac)
                        ->where('NUM_DOC', $request->input('DNI'))
                        ->where('FECHA_BIOMETRICO', $punchTime)
                        ->exists();

                    if ($exists) continue;

                    // Insertar y recuperar ID
                    $idAsistencia = DB::table('m_asistencia')->insertGetId([
                        'IDTIPO_ASISTENCIA' => 1,
                        'NUM_DOC'           => $request->input('DNI'),
                        'IDCENTRO_MAC'      => $userIdMac,
                        'MES'               => $mes,
                        'AÑO'               => $anio,
                        'FECHA'             => $fecha,
                        'HORA'              => date('H:i:s', strtotime($punchTime)),
                        'FECHA_BIOMETRICO'  => $punchTime,
                        'NUM_BIOMETRICO'    => '',
                        'CORRELATIVO'       => $nextCorrelativo,
                        'CORRELATIVO_DIA'   => ''
                    ]);

                    // Guardar auditoría
                    AuditoriaGeneral::create([
                        'idUsuario'           => auth()->user()->id,
                        'modelo_afectado'     => 'm_asistencia',
                        'idRegistroAfectado'  => $idAsistencia,
                        'accion'              => 'INSERT',
                        'valores_anteriores'  => null,
                        'valores_nuevos'      => json_encode([
                            'NUM_DOC' => $request->input('DNI'),
                            'FECHA_BIOMETRICO' => $punchTime,
                            'IDCENTRO_MAC' => $userIdMac
                        ]),
                        'fecha_accion'        => now(),
                        'ip_usuario'          => $request->ip(),
                        'descripcion'         => 'Registro de hora manual en asistencia',
                        'tabla_id_nombre'     => 'IDASISTENCIA'
                    ]);

                    $nextCorrelativo++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Registro(s) guardado(s) exitosamente para ' . $nombreCompleto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el registro: ' . $e->getMessage()
            ]);
        }
    }

    public function tb_asistencia(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        // VERIFICAMOS LA HORA DE INGRESO PARA INDICAR SI ESTAN EN HORA O TARDANZA TOMAMOS REF DE LUN A VIER YA QUE ES EL MISMO HORARIO DEL SABADO
        $conf = Configuracion::where('IDCONFIGURACION', 2)->first();

        $entidad = Entidad::select('NOMBRE_ENTIDAD', 'ABREV_ENTIDAD', 'IDENTIDAD');
        $fecha = $request->fecha ?? date('Y-m-d');

        $datos = DB::table('M_ASISTENCIA as MA')
            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')

            ->leftJoin('M_PERSONAL_MODULO as MPM', function (JoinClause $join) use ($idmac, $fecha) {
                $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
                    ->where('MPM.STATUS', '!=', 'eliminado')
                    ->where('MPM.IDCENTRO_MAC', '=', $idmac)
                    ->where(function ($q) use ($fecha) {
                        $q->where('MPM.STATUS', 'itinerante')
                            ->where('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                            ->where('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                            ->orWhere(function ($q2) use ($fecha) {
                                $q2->where('MPM.STATUS', 'fijo')
                                    ->where('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                                    ->where('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                                    ->whereNotExists(function ($q3) {
                                        $q3->select(DB::raw(1))
                                            ->from('M_PERSONAL_MODULO as MPM2')
                                            ->whereRaw('MPM2.NUM_DOC = MPM.NUM_DOC')
                                            ->where('MPM2.STATUS', 'itinerante')
                                            ->whereColumn('MA.FECHA', '>=', 'MPM2.FECHAINICIO')
                                            ->whereColumn('MA.FECHA', '<=', 'MPM2.FECHAFIN');
                                    });
                            });
                    });
            })

            ->leftJoin('M_MODULO as MM', 'MM.IDMODULO', '=', 'MPM.IDMODULO')

            ->leftJoin('M_ENTIDAD as ME', function (JoinClause $join) {
                $case = <<<'SQL'
                    CASE
                    WHEN MPM.NUM_DOC IS NOT NULL THEN MM.IDENTIDAD
                    ELSE MP.IDENTIDAD
                    END
                    SQL;
                $join->on(DB::raw('ME.IDENTIDAD'), '=', DB::raw($case));
            })

            ->leftJoin('D_ASISTENCIA_OBSERVACION as DAO', function (JoinClause $join) {
                $join->on('DAO.NUM_DOC', '=', 'MA.NUM_DOC')
                    ->on('DAO.FECHA',     '=', 'MA.FECHA')
                    ->on('DAO.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC');
            })

            ->select([
                'MA.FECHA as fecha_asistencia',
                'MA.IDCENTRO_MAC as idmac',
                'MP.IDPERSONAL as idpersonal',
                DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
                DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT(MA.HORA, '%H:%i:%s') ORDER BY MA.HORA) AS fecha_biometrico"),
                'MA.NUM_DOC as n_dni',
                DB::raw('UPPER(CONCAT(MP.APE_PAT," ",MP.APE_MAT,", ",MP.NOMBRE)) AS nombreu'),
                'ME.ABREV_ENTIDAD',
                'MC.NOMBRE_MAC',
                'MPM.STATUS as status_modulo',
                'MM.N_MODULO as nombre_modulo',
                DB::raw('COUNT(IF(DAO.flag = 1, DAO.id_asistencia_obv, NULL)) as contador_obs'),
            ])

            ->where('MA.IDCENTRO_MAC', $idmac)
            // ->where('ME.IDENTIDAD', $request->entidad)
            ->when($request->filled('entidad'), function ($query) use ($request) {
                $query->where('ME.IDENTIDAD', $request->entidad);
            })
            ->whereDate('MA.FECHA', $fecha)

            ->groupBy(
                'MA.FECHA',
                'MA.IDCENTRO_MAC',
                'MP.IDPERSONAL',
                'MA.NUM_DOC',
                'ME.ABREV_ENTIDAD',
                'MC.NOMBRE_MAC',
                'MPM.STATUS',
                'MM.N_MODULO'
            )
            ->orderBy('MM.N_MODULO', 'asc')
            ->get();

        foreach ($datos as $q) {
            $horas = explode(',', $q->fecha_biometrico); // Separa las horas por coma
            $num_horas = count($horas);

            // Asigna las horas con segundos
            if ($num_horas == 1) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = null;
            } elseif ($num_horas == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[1];
            } elseif ($num_horas == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[2];
            } elseif ($num_horas >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
        }
        // dd($datos);
        return view('asistencia.tablas.tb_asistencia', compact('datos', 'conf'));
    }
    public function verificarCierre(Request $request)
    {
        $fecha = $request->input('fecha');
        $idmac = auth()->user()->idcentro_mac;

        $existe = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereDate('fecha_asistencia', $fecha)
            ->exists();

        return response()->json([
            'cerrado' => $existe
        ]);
    }
    public function revertirDia(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'idmac' => 'required|integer',
        ]);

        $user = auth()->user();

        // 🔒 Validación de permisos según el rol
        if ($user->hasRole('Especialista TIC')) {
            // Solo puede revertir su propio MAC
            if ($user->idcentro_mac != $request->idmac) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Solo puede revertir asistencias de su propio MAC.'
                ], 403);
            }
        }

        //  Permisos generales
        if (!($user->hasAnyRole(['Administrador', 'Monitor', 'Especialista TIC', 'Moderador']))) {
            return response()->json([
                'ok' => false,
                'msg' => 'No tiene permisos para realizar esta acción.'
            ], 403);
        }

        try {
            DB::statement("CALL SP_REVERTIR_ASISTENCIA_DIA(?, ?)", [
                $request->fecha,
                $request->idmac
            ]);

            return response()->json([
                'ok' => true,
                'msg' => " Se revirtió la asistencia del {$request->fecha} en el MAC #{$request->idmac}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => "Error: " . $e->getMessage()
            ], 500);
        }
    }

    public function mdRevertir(Request $request)
    {
        $user = auth()->user();

        // Si es Administrador o Monitor → ver todos los MACs
        if ($user->hasRole(['Administrador', 'Monitor','Moderador'])) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->orderBy('NOMBRE_MAC')
                ->get();
        }
        // Si es Especialista_TIC → solo su propio MAC
        elseif ($user->hasRole('Especialista TIC|Especialista_TIC')) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->where('IDCENTRO_MAC', $user->idcentro_mac)
                ->get();
        } else {
            // Otros roles no deberían poder abrir este modal
            return response()->json([
                'html' => "<div class='p-3 text-center text-danger'>No tiene permisos para esta acción.</div>"
            ]);
        }

        return response()->json([
            'html' => view('asistencia.modals.md_revertir', compact('macs'))->render()
        ]);
    }
    public function tb_asistencia_resumen(Request $request)
    {
        // 1. Verificar a qué MAC pertenece el usuario
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
            ->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;

        // 2. Tomar la fecha solicitada (o actual)
        $fecha = $request->fecha ?? date('Y-m-d');

        // 3. Consultar directamente asistencia_resumen
        $datos = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereDate('fecha_asistencia', $fecha)
            ->orderBy('nombre_modulo', 'asc')
            ->get();

        // 4. Procesar las horas (igual que en tb_asistencia)
        foreach ($datos as $q) {
            $horas = explode(',', $q->fecha_biometrico); // Separa las horas por coma
            $num_horas = count($horas);

            if ($num_horas == 1) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = null;
            } elseif ($num_horas == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[1];
            } elseif ($num_horas == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[2];
            } elseif ($num_horas >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
        }

        // 5. Retornar la vista del resumen
        return view('asistencia.tablas.tb_asistencia_resumen', compact('datos'));
    }

    public function md_moficicar_modulo(Request $request)
    {
        $num_doc = $request->input('num_doc');
        $nombre_modulo = $request->input('nombre_modulo');
        $fecha_asistencia = $request->input('fecha_asistencia');

        // Obtener el nombre del asesor completo
        $asesor = DB::table('M_PERSONAL')->where('NUM_DOC', $num_doc)->first();
        $nombre_asesor = $asesor ? $asesor->APE_PAT . " " . $asesor->APE_MAT . ", " . $asesor->NOMBRE : '';

        // Obtener la entidad del asesor desde la tabla M_PERSONAL_MODULO según el rango de fechas
        $entidad_id = DB::table('M_PERSONAL_MODULO as MPM')
            ->join('M_MODULO as MM', 'MPM.IDMODULO', '=', 'MM.IDMODULO')
            ->join('M_ENTIDAD as ME', 'MM.IDENTIDAD', '=', 'ME.IDENTIDAD')
            ->where('MPM.NUM_DOC', $num_doc)
            ->whereDate('MPM.FECHAINICIO', '<=', $fecha_asistencia)
            ->whereDate('MPM.FECHAFIN', '>=', $fecha_asistencia)
            ->value('ME.IDENTIDAD');

        // Obtener los módulos disponibles que están relacionados con la entidad del asesor
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
            ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD')
            ->where('m_modulo.IDENTIDAD', $entidad_id)  // Filtrar por la entidad del asesor
            ->where('m_modulo.IDCENTRO_MAC', auth()->user()->idcentro_mac) // Filtrar por el centro MAC del usuario autenticado
            ->get();

        $idcentro_mac = auth()->user()->idcentro_mac;

        // Renderiza la vista del modal con los datos, incluyendo los módulos y el ID del centro MAC
        $html = view('asistencia.modals.md_moficicar_modulo', compact('num_doc', 'nombre_modulo', 'fecha_asistencia', 'nombre_asesor', 'modulos', 'idcentro_mac'))->render();

        return response()->json(['html' => $html]);
    }
    public function md_add_dni_asistencia(Request $request)
    {
        $dni = $request->input('DNI');
        $nombre = $request->input('nombre');
        $fecha_asistencia = $request->input('fecha_asistencia');

        // Cargar la vista del modal con los datos
        $html = view('asistencia.modals.md_add_dni_asistencia', compact('dni', 'nombre', 'fecha_asistencia'))->render();

        return response()->json(['html' => $html]);
    }

    /********** OBSERVACIONES ASISTENCIA  *************************/

    public function md_add_comment_user(Request $request)
    {
        $personal = Personal::select(DB::raw('UPPER(CONCAT(APE_PAT," ",APE_MAT,", ",NOMBRE)) AS nombreu'))->where('IDPERSONAL', $request->IDPERSONAL)->first();

        $observacion = DB::table('M_ASISTENCIA as MA')
            ->leftJoin('D_ASISTENCIA_OBSERVACION as DAO', function (JoinClause $join) {
                $join->on('DAO.NUM_DOC', '=', 'MA.NUM_DOC')
                    ->on('DAO.FECHA',     '=', 'MA.FECHA')
                    ->on('DAO.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC');
            })
            ->where('DAO.NUM_DOC', $request->NUM_DOC)
            ->where('DAO.FECHA', $request->FECHA)
            ->where('DAO.IDCENTRO_MAC', $request->IDCENTRO_MAC)
            ->where('DAO.flag', 1)
            ->get();

        $fecha_d = $request->FECHA;
        $mac_d = $request->IDCENTRO_MAC;
        $num_doc = $request->NUM_DOC;

        $html = view('asistencia.modals.md_add_comment_user', compact('personal', 'observacion', 'fecha_d', 'mac_d', 'num_doc'))->render();

        return response()->json(['html' => $html]);
    }

    public function store_agregar_observacion(Request $request)
    {
        $request->validate([
            'NUM_DOC'     => 'required',
            'FECHA'       => 'required|date',
            'OBSERVACION' => 'required|string',
        ]);

        $inserted = DB::table('D_ASISTENCIA_OBSERVACION')->insert([
            'num_doc'      => $request->NUM_DOC,
            'fecha'        => $request->FECHA,
            'idcentro_mac' => $this->centro_mac()->idmac,
            'observacion'  => $request->OBSERVACION,
            'us_reg'       => auth()->id(),
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);

        return response()->json([
            'success' => $inserted,
        ], $inserted ? 201 : 500);
    }

    public function eliminarObservacion(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:D_ASISTENCIA_OBSERVACION,id_asistencia_obv',
        ]);

        $updated = DB::table('D_ASISTENCIA_OBSERVACION')
            ->where('id_asistencia_obv', $request->id)
            ->update([
                'flag'       => 0,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'success' => (bool) $updated,
        ], $updated ? 200 : 404);
    }


    /********** FIN OBSERVACIONES ASISTENCIA  *************************/

    public function md_add_asistencia(Request $request)
    {
        $view = view('asistencia.modals.md_add_asistencia')->render();

        return response()->json(['html' => $view]);
    }
    public function md_add_asistencia_callao(Request $request)
    {
        $view = view('asistencia.modals.md_add_asistencia_callao')->render();

        return response()->json(['html' => $view]);
    }
    public function md_agregar_asistencia(Request $request)
    {
        $view = view('asistencia.modals.md_agregar_asistencia')->render();

        return response()->json(['html' => $view]);
    }

    public function store_asistencia(Request $request)
    {
        // Obtener el archivo de la solicitud
        $file = $request->file('txt_file');

        // Convertir el archivo a un array de líneas
        $lines = file($file->getRealPath());

        // Inicializar arrays vacíos
        $num_docs = [];
        $fechas_biometrico = [];
        $horas = [];
        $anios = [];
        $meses = [];

        // Recorrer las líneas y procesar datos
        foreach ($lines as $line) {
            // Usar tabulación como separador
            $data = explode("\t", $line);

            // Verificar que se tengan al menos 7 columnas (ya que la columna 6 contiene la fecha y hora)
            if (count($data) >= 7) {
                // Extraer los valores que necesitamos
                $num_docs[] = trim($data[2]); // DNI o NUM_DOC
                $fechas_biometrico[] = trim($data[6]); // FECHA_BIOMETRICO

                // Separar la fecha y la hora
                $fechaHora = explode(' ', trim($data[6]));
                if (count($fechaHora) == 2) {
                    $fecha = $fechaHora[0]; // Fecha
                    $hora = $fechaHora[1]; // Hora
                    $horas[] = $hora;

                    // Extraer año y mes
                    $fechaParts = explode('/', $fecha);
                    if (count($fechaParts) == 3) {
                        $anios[] = $fechaParts[0]; // Año
                        $meses[] = $fechaParts[1]; // Mes
                    }
                }
            } else {
                echo "<pre>Línea con formato incorrecto: " . print_r($line, true) . "</pre>";
            }
        }

        // Obtener el ID del Centro MAC usando el método
        $idCentroMac = $this->centro_mac()->idmac;

        // Ahora insertamos los datos en la base de datos
        foreach ($num_docs as $index => $num_doc) {
            // Verificar si ya existe un registro con los mismos valores de NUM_DOC, IDCENTRO_MAC y FECHA_BIOMETRICO
            $existingRecord = Asistencia::where('NUM_DOC', $num_doc)
                ->where('IDCENTRO_MAC', $idCentroMac)
                ->where('FECHA_BIOMETRICO', $fechas_biometrico[$index])
                ->first();

            // Si no existe el registro, crear uno nuevo
            if (!$existingRecord) {
                Asistencia::create([
                    'IDTIPO_ASISTENCIA' => 1, // Puedes ajustar este valor según tus necesidades
                    'NUM_DOC' => $num_doc,
                    'IDCENTRO_MAC' => $idCentroMac,
                    'MES' => $meses[$index],
                    'AÑO' => $anios[$index],
                    'FECHA' => $fechas_biometrico[$index], // Fecha completa (biométrico)
                    'HORA' => $horas[$index],
                    'FECHA_BIOMETRICO' => $fechas_biometrico[$index],
                    'NUM_BIOMETRICO' => '', // Si no hay valor, se puede dejar vacío
                    'CORRELATIVO' => $index + 1, // Puedes ajustar el correlativo según tu lógica
                    'CORRELATIVO_DIA' => '' // Puedes agregar el valor según lo que necesites
                ]);
            } else {
                // Si ya existe el registro, lo omitimos y continuamos con el siguiente
                continue;
            }
        }

        return response()->json(['success' => true, 'message' => 'Asistencias cargadas exitosamente.']);
    }

    public function store_asistencia_callao(Request $request)
    {
        // Inicializar el progreso a 0%
        Cache::put('upload_progress', 0);

        if ($request->hasFile('txt_file') && $request->file('txt_file')->isValid()) {
            $fileTmpPath = $request->file('txt_file')->getPathName();
            $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$fileTmpPath;";

            try {
                $accessDb = new PDO($dsn);
                $mysqli = new mysqli('localhost', 'root', '', 'asistencia_callao');
                if ($mysqli->connect_error) {
                    return response()->json(['success' => false, 'message' => 'Error de conexión a MySQL: ' . $mysqli->connect_error], 500);
                }
                $mysqli->set_charset('utf8mb4');

                // Actualiza el progreso al 10%
                Cache::put('upload_progress', 10);

                $tablesQuery = $accessDb->query("SELECT Name FROM MSysObjects WHERE Type=1 AND Name NOT LIKE 'MSys%'");
                $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

                // Actualiza el progreso al 20%
                Cache::put('upload_progress', 20);

                foreach ($tables as $table) {
                    if ($table === 'Switchboard Items') {
                        continue;
                    }

                    try {
                        $dataQuery = $accessDb->query("SELECT * FROM [$table]");
                        $rows = $dataQuery->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        continue;
                    }

                    if (!empty($rows)) {
                        $columns = array_keys($rows[0]);
                        $tableExistsQuery = $mysqli->query("SHOW TABLES LIKE '$table'");

                        if ($tableExistsQuery->num_rows > 0) {
                            $mysqli->query("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            foreach ($columns as $column) {
                                $mysqli->query("ALTER TABLE `$table` MODIFY `$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            }
                        } else {
                            $columnsSQL = [];
                            foreach ($columns as $column) {
                                $columnsSQL[] = "`$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                            }
                            $createTableSQL = "CREATE TABLE `$table` (" . implode(', ', $columnsSQL) . ")";
                            if (!$mysqli->query($createTableSQL)) {
                                continue;
                            }
                        }

                        $mysqli->query("DELETE FROM `$table`");

                        foreach ($rows as &$row) {
                            array_walk_recursive($row, function (&$value) {
                                if (!mb_check_encoding($value, 'UTF-8')) {
                                    $value = utf8_encode($value);
                                }
                            });

                            $values = array_map(function ($value) use ($mysqli) {
                                if (is_null($value)) {
                                    return 'NULL';
                                }
                                $escapedValue = $mysqli->real_escape_string($value);
                                return "'" . $escapedValue . "'";
                            }, $row);

                            $insertSQL = "INSERT INTO `$table` (" . implode(',', array_keys($row)) . ") VALUES (" . implode(',', $values) . ")";
                            $mysqli->query($insertSQL);
                        }
                    }

                    // Simula actualizar el progreso para cada tabla procesada
                    // (Esto es solo un ejemplo; en un caso real, el porcentaje dependerá de tu lógica de procesamiento)
                    $currentProgress = Cache::get('upload_progress', 0);
                    // Incrementa el progreso en 10% por cada tabla procesada (ajusta según el número de tablas y la lógica)
                    Cache::put('upload_progress', min($currentProgress + 10, 90));
                }

                $idmac_callao = $this->centro_mac()->idmac;
                $fecha_inicio = $request->fecha_inicio;
                $fecha_fin    = $request->fecha_fin;

                $call_centro = DB::select("INSERT INTO M_ASISTENCIA (
                                                    IDTIPO_ASISTENCIA,
                                                    NUM_DOC,
                                                    IDCENTRO_MAC,
                                                    MES,
                                                    AÑO,
                                                    FECHA,
                                                    HORA,
                                                    FECHA_BIOMETRICO,
                                                    NUM_BIOMETRICO,
                                                    CORRELATIVO,
                                                    CORRELATIVO_DIA
                                                )
                                                SELECT 
                                                    2,
                                                    ui.ssn AS DNI,
                                                    $idmac_callao AS nom_mac,
                                                    LPAD(MONTH(chk.CHECKTIME), 2, '0') AS mes,
                                                    YEAR(chk.CHECKTIME) AS anio,
                                                    DATE(chk.CHECKTIME) AS fecha,
                                                    TIME_FORMAT(chk.CHECKTIME, '%H:%i:%s') AS hora,
                                                    chk.CHECKTIME AS FECHA_BIOMETRICO,
                                                    '', -- NUM_BIOMETRICO.
                                                    '', -- CORRELATIVO.
                                                    ''  -- CORRELATIVO_DIA.
                                                FROM asistencia_callao.checkinout chk
                                                JOIN asistencia_callao.userinfo ui ON ui.userid = chk.userid
                                                WHERE ui.ssn IS NOT NULL
                                                AND ui.ssn > 0
                                                AND DATE(chk.CHECKTIME) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                                                AND NOT EXISTS (
                                                        SELECT 2 
                                                        FROM M_ASISTENCIA ma
                                                        WHERE 
                                                            ma.NUM_DOC = ui.ssn COLLATE utf8mb4_unicode_ci
                                                            AND ma.IDCENTRO_MAC = $idmac_callao
                                                            AND ma.FECHA = DATE(chk.CHECKTIME)
                                                            AND ma.HORA = TIME_FORMAT(chk.CHECKTIME, '%H:%i:%s')
                                                );");

                // Finalmente, actualiza el progreso al 100% cuando termine todo el procesamiento
                Cache::put('upload_progress', 100);

                $responseData = ['success' => true, 'message' => 'Asistencias cargadas exitosamente.'];
                array_walk_recursive($responseData, function (&$item) {
                    if (!mb_check_encoding($item, 'UTF-8')) {
                        $item = utf8_encode($item);
                    }
                });

                return response()->json($responseData);
            } catch (PDOException $e) {
                return response()->json(['success' => false, 'message' => 'Error al procesar el archivo Access: ' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No se envió ningún archivo o hubo un error en la carga.'], 400);
        }
    }

    public function getUploadProgress(Request $request)
    {
        // Retorna el progreso actual (si no existe, se asume 0)
        $progress = Cache::get('upload_progress', 0);
        return response()->json(['progress' => $progress]);
    }

    public function md_detalle(Request $request)
    {
        // Obtener la fecha y el DNI del request
        $fecha_ = $request->fecha_;
        $dni_ = $request->dni_;

        // Obtener el ID del centro MAC del usuario autenticado
        $centroMac = $this->centro_mac();  // Llamar al método para obtener el centro MAC
        $idcentroMac = $centroMac->idmac;  // Obtener el ID del centro MAC

        // Consultar los datos de asistencia con el ID del centro MAC
        $query = DB::select("
        SELECT 
            FECHA,
            NUM_DOC,
            DATE_FORMAT(HORA, '%H:%i:%s') AS HORAS,
            MAX(IDASISTENCIA) as IDASISTENCIA  
        FROM
            M_ASISTENCIA
        WHERE 
            FECHA = '$fecha_'
            AND NUM_DOC = '$dni_'
            AND IDCENTRO_MAC = '$idcentroMac'  -- Filtrar por el centro MAC
        GROUP BY 
            NUM_DOC, FECHA, HORA
        ORDER BY 
            HORA ASC");  // Ordenar las horas de menor a mayor

        // Pasar los resultados de la consulta a la vista
        $view = view('asistencia.modals.md_detalle', compact('query', 'fecha_'))->render();

        // Retornar la vista con los datos de la consulta
        return response()->json(['html' => $view]);
    }

    public function eliminarHora(Request $request)
    {

        try {
            $idAsistencia = $request->idAsistencia;

            // Buscar y eliminar la asistencia original
            $asistencia = Asistencia::findOrFail($idAsistencia);
            $dni = $asistencia->NUM_DOC;
            $marcacion = $asistencia->FECHA_BIOMETRICO;

            // Captura antes de eliminar
            $valoresAnteriores = $asistencia->toArray();

            $asistencia->delete();

            // Registrar en la tabla de auditoría
            try {
                AuditoriaGeneral::create([
                    'idUsuario'           => auth()->user()->id,
                    'modelo_afectado'     => 'asistencia',
                    'idRegistroAfectado'  => $idAsistencia,
                    'accion'              => 'DELETE',
                    'valores_anteriores'  => json_encode($valoresAnteriores),
                    'valores_nuevos'      => null,
                    'fecha_accion'        => now(),
                    'ip_usuario'          => $request->ip(),
                    'descripcion'         => 'Eliminación de hora registrada en asistencia',
                    'tabla_id_nombre'     => 'IDASISTENCIA'
                ]);
            } catch (\Exception $ex) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar auditoría: ' . $ex->getMessage()
                ]);
            }


            // Eliminar también de asistenciatest si existe un registro con mismo DNI y marcación
            Asistenciatest::where('DNI', $dni)
                ->where('marcacion', $marcacion)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hora eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la hora: ' . $e->getMessage()
            ], 500);
        }
    }




    public function det_us(Request $request, $id)
    {
        $idPersonal = $id;

        $personal = Personal::where('NUM_DOC', $idPersonal)->first();

        // dd($personal);

        return view('asistencia.det_us', compact('idPersonal', 'personal'));
    }

    public function tb_det_us(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
            ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL')
            ->where('NUM_DOC', $request->num_doc)
            ->first();

        $query = DB::table('M_ASISTENCIA')
            ->select('FECHA', 'NUM_DOC')
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora1', ['1'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora2', ['2'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora3', ['3'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora4', ['4'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora5', ['5'])
            ->selectRaw('COUNT(NUM_DOC) AS N_NUM_DOC')
            ->where('NUM_DOC', $request->num_doc)
            ->where(function ($que) use ($request) {
                $fecha_mes_actual = Carbon::now()->format('m');
                if ($request->mes != '') {
                    $que->where('MES', $request->mes);
                } else {
                    $que->where('MES', $fecha_mes_actual);
                }
            })
            ->where(function ($que) use ($request) {
                $fecha_año_actual = Carbon::now()->format('Y');
                if ($request->año != '') {
                    $que->where('AÑO', $request->año);
                } else {
                    $que->where('AÑO', $fecha_año_actual);
                }
            })
            ->groupBy('NUM_DOC', 'FECHA')
            ->orderBy('FECHA', 'ASC')
            ->get();
        // dd($query);
        return view('asistencia.tablas.tb_det_us', compact('query', 'datos_persona'));
    }

    public function asistencia_excel(Request $request)
    {
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');

        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();

        // dd($nombreMES);

        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
            ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL')
            ->where('NUM_DOC', $request->num_doc)
            ->first();

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $query = DB::table('M_ASISTENCIA')
            ->select('FECHA', 'NUM_DOC')
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora1', ['1'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora2', ['2'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora3', ['3'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora4', ['4'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora5', ['5'])
            ->selectRaw('COUNT(NUM_DOC) AS N_NUM_DOC')
            ->where('NUM_DOC', $request->num_doc)
            ->where(function ($que) use ($request) {
                $fecha_mes_actual = Carbon::now()->format('m');
                if ($request->mes != '') {
                    $que->where('MES', $request->mes);
                } else {
                    $que->where('MES', $fecha_mes_actual);
                }
            })
            ->where(function ($que) use ($request) {
                $fecha_año_actual = Carbon::now()->format('Y');
                if ($request->año != '') {
                    $que->where('AÑO', $request->año);
                } else {
                    $que->where('AÑO', $fecha_año_actual);
                }
            })
            ->groupBy('NUM_DOC', 'FECHA')
            ->orderBy('FECHA', 'ASC')
            ->get();

        foreach (auth()->user()->locales as $local) {
            $MAC = $local->IDCENTRO_MAC;
        }

        // dd($datos_persona);

        $export = Excel::download(new AsistenciaExport($query, $datos_persona, $nombreMES, $hora_1, $hora_2, $hora_3, $hora_4), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $MAC . ' ' . $datos_persona->ABREV_ENTIDAD . '_' . $nombreMES . '.xlsx');

        return $export;
    }

    public function asistencia_pdf(Request $request)
    {
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');

        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        // dd($hora_1->VALOR);

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
            ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL')
            ->where('NUM_DOC', $request->num_doc)
            ->first();

        $query = DB::table('M_ASISTENCIA')
            ->select('FECHA', 'NUM_DOC')
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora1', ['1'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora2', ['2'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora3', ['3'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora4', ['4'])
            ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora5', ['5'])
            ->selectRaw('COUNT(NUM_DOC) AS N_NUM_DOC')
            ->where('NUM_DOC', $request->num_doc)
            ->where(function ($que) use ($request) {
                $fecha_mes_actual = Carbon::now()->format('m');
                if ($request->mes != '') {
                    $que->where('MES', $request->mes);
                } else {
                    $que->where('MES', $fecha_mes_actual);
                }
            })
            ->where(function ($que) use ($request) {
                $fecha_año_actual = Carbon::now()->format('Y');
                if ($request->año != '') {
                    $que->where('AÑO', $request->año);
                } else {
                    $que->where('AÑO', $fecha_año_actual);
                }
            })
            ->groupBy('NUM_DOC', 'FECHA')
            ->orderBy('FECHA', 'ASC')
            ->get();


        $pdf = Pdf::loadView('asistencia.asistencia_pdf', compact('nombreMES', 'query', 'datos_persona', 'hora_1', 'hora_2', 'hora_3', 'hora_4'))->setPaper('a4', 'landscape');
        return $pdf->stream();

        // return view('asistencia.asistencia_pdf', compact('nombreMES', 'query', 'datos_persona'));
    }

    // >>>>>>>>>>>>>>>>>>>>>>>>    ASISTENCIA POR ENTIDAD
    /** ***************************************************************************************************************************************************** **/

    public function det_entidad(Request $request)
    {

        $mac = DB::table('M_CENTRO_MAC')
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->orderBy('NOMBRE_MAC', 'ASC')
            ->get();


        return view('asistencia.det_entidad', compact('mac'));
    }

    public function tb_det_entidad(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $mes = $request->input('mes', Carbon::now()->month);
        $año = $request->input('año', Carbon::now()->year);

        $mac = $request->mac;

        $data = DB::table('M_PERSONAL')
            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
            ->select(
                'M_ENTIDAD.IDENTIDAD',
                'M_ENTIDAD.NOMBRE_ENTIDAD',
                'M_ENTIDAD.ABREV_ENTIDAD',
                DB::raw('COUNT(DISTINCT M_PERSONAL.IDPERSONAL) AS COUNT_PER')
            )
            ->when(auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador'), function ($query) use ($idmac) {
                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', $idmac);
            })
            ->when(!auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador') && $mac != 0, function ($query) use ($mac) {
                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', $mac);
            })
            ->groupBy('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_ENTIDAD.ABREV_ENTIDAD') // ❗️ no se agrupa por MAC
            ->orderBy('M_ENTIDAD.ABREV_ENTIDAD', 'ASC')
            ->get();
        $data_spcm = DB::table('M_PERSONAL')
            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
            ->select('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC', DB::raw('COUNT(M_ENTIDAD.IDENTIDAD) AS COUNT_PER'))
            ->groupBy('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC')
            // ->where('M_CENTRO_MAC.IDCENTRO_MAC', $idmac)
            ->where(function ($query) use ($request) {
                $mac = $request->mac;
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                } else {
                    $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $mac);
                }
            })
            ->where('M_PERSONAL.FLAG', 1)
            ->whereNot('M_ENTIDAD.IDENTIDAD', 17) //QUITAMOS DEL REGISTRO A PERSONAL DE PCM
            ->get();

        return view('asistencia.tablas.tb_det_entidad', compact('data', 'data_spcm'));
    }

    public function exportgroup_excel(Request $request)
    {
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        // dd($request->all());
        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        } else {
            $idmac = $request->mac;
        }

        $esTodosLosMacs = ($idmac == 0);

        $name_mac = ($idmac == 0)
            ? 'TODOS LOS MACs'
            : Mac::where('IDCENTRO_MAC', $idmac)->value('NOMBRE_MAC');
        // DEFINIMOS EL TIPO DE DESCA
        $tipo_desc = '1';
        $fecha_inicial = '';
        $fecha_fin = '';
        $identidad = $request->identidad;
        //dd($identidad);

        if ($identidad == '17') {

            // Obtener datos del encabezado (asesores con identidad 17)
            $nom_ = Personal::from('M_PERSONAL as MP')
                ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                ->select(
                    'DPC.NOMBRE_CARGO',
                    DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'),
                    'MP.NUM_DOC',
                    'MP.IDENTIDAD'
                )
                ->where('MP.IDENTIDAD', 17)
                ->where('MP.IDMAC', $this->centro_mac()->idmac)
                ->get();

            // Primer y último día del mes
            $primerDia = Carbon::createFromDate($request->año, $request->mes, 1)->startOfDay();
            $ultimoDia = $primerDia->copy()->endOfMonth();

            // Rango de fechas entre primer y último día del mes
            $fechas = CarbonPeriod::create($primerDia, $ultimoDia);
            $fechasArray = [];
            foreach ($fechas as $fecha) {
                $fechasArray[] = $fecha->toDateString();
            }

            // Array para almacenar cada asesor con su detalle
            $datosAgrupados = [];

            foreach ($nom_ as $encabezado) {
                // Consulta principal: obtiene la primera y última marcación por día
                $detalle = Asistencia::select([
                    DB::raw('DATE(M_ASISTENCIA.FECHA) AS FECHA'),
                    'M_ASISTENCIA.NUM_DOC',
                    DB::raw('MIN(TIME(M_ASISTENCIA.HORA)) AS HORA_1'), //  menor hora del día (primer marcaje)
                    DB::raw('MAX(TIME(M_ASISTENCIA.HORA)) AS HORA_4'), //  mayor hora del día (último marcaje)
                    DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                ])
                    ->where('M_ASISTENCIA.IDCENTRO_MAC', $this->centro_mac()->idmac)
                    ->where('M_ASISTENCIA.NUM_DOC', $encabezado->NUM_DOC)
                    ->whereMonth('M_ASISTENCIA.FECHA', $request->mes)
                    ->whereYear('M_ASISTENCIA.FECHA', $request->año)
                    ->groupBy('M_ASISTENCIA.NUM_DOC', DB::raw('DATE(M_ASISTENCIA.FECHA)'))
                    ->orderByRaw('DATE(M_ASISTENCIA.FECHA) ASC') // compatible con ONLY_FULL_GROUP_BY
                    ->get();

                // Agregar encabezado + detalle al arreglo principal
                $datosAgrupados[] = [
                    'encabezado' => $encabezado,
                    'detalle' => $detalle
                ];
            }

            // Vaciar query general
            $query = [];
        } else {

            $query = DB::table('M_ASISTENCIA as MA')
                ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
                ->leftJoin('M_PERSONAL_MODULO as MPM', function ($join) use ($idmac) {
                    $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
                        ->where('MPM.STATUS', '!=', 'eliminado');

                    if ($idmac != 0) {
                        $join->where('MPM.IDCENTRO_MAC', '=', $idmac);
                    } else {
                        // Usamos el ID del MAC de la asistencia
                        $join->whereColumn('MPM.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC');
                    }
                })

                ->leftJoin('M_MODULO as MM', 'MM.IDMODULO', '=', 'MPM.IDMODULO')
                ->leftJoin('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MM.IDENTIDAD')
                ->leftJoin('D_ASISTENCIA_OBSERVACION as DAO', function (JoinClause $join) {
                    $join->on('DAO.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->on('DAO.FECHA',     '=', 'MA.FECHA')
                        ->on('DAO.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC');
                })
                ->select(
                    'MA.FECHA as FECHA',
                    DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
                    DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT(MA.HORA, '%H:%i:%s') ORDER BY MA.HORA) AS fecha_biometrico"),
                    'MA.NUM_DOC as NUM_DOC',
                    DB::raw('UPPER(CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE)) AS NOMBREU'),
                    'ME.ABREV_ENTIDAD', // Mostrar la abreviatura correcta de la entidad
                    'MC.NOMBRE_MAC',
                    'MPM.STATUS as status_modulo',
                    'MM.N_MODULO as N_MODULO',
                    DB::raw("GROUP_CONCAT(DISTINCT DAO.OBSERVACION ORDER BY DAO.ID_ASISTENCIA_OBV SEPARATOR ';') AS observaciones"),
                    DB::raw('COUNT(IF(DAO.flag = 1, DAO.id_asistencia_obv, NULL)) as contador_obs')
                )
                ->where(function ($query) use ($request) {
                    // Filtra por fecha (mes y año) y centro MAC
                    // $idmac = $this->centro_mac()->idmac;

                    // Si se proporcionan fechas de inicio y fin
                    if ($request->fecha_inicio && $request->fecha_fin) {
                        $fecha_inicio = date('Y-m-d', strtotime($request->fecha_inicio));  // Convierte a formato Y-m-d
                        $fecha_fin = date('Y-m-d', strtotime($request->fecha_fin));  // Convierte a formato Y-m-d
                        $query->whereBetween('MA.FECHA', [$fecha_inicio, $fecha_fin]);
                    } else {
                        // Si no se proporcionaron fechas, filtrar por mes y año
                        $query->whereMonth('MA.FECHA', $request->mes)
                            ->whereYear('MA.FECHA', $request->año);
                    }

                    // Aseguramos que siempre se filtre por IDCENTRO_MAC
                    // $query->where('MA.IDCENTRO_MAC', $idmac);
                })
                ->where(function ($query) use ($request, $idmac) {
                    if ($request->identidad) {
                        $query->where('ME.IDENTIDAD', $request->identidad);
                    }
                    if ($idmac != 0) {
                        $query->where('MA.IDCENTRO_MAC', '=', $idmac);
                    }
                })
                ->where(function ($query) use ($idmac) {
                    if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador') && $idmac != 0) {
                        $query->where('MA.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                    }
                })

                ->where(function ($query) use ($request) {
                    // Primero tratamos de obtener los módulos itinerantes dentro del rango de fechas
                    $query->where('MPM.STATUS', 'itinerante')
                        ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                        ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                        ->orWhere(function ($query) {
                            // Si no tiene módulo itinerante, tomamos el módulo fijo dentro del rango de fechas
                            $query->where('MPM.STATUS', 'fijo')
                                ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                                ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                                ->whereNotExists(function ($query) {
                                    // Nos aseguramos de que no tenga un módulo itinerante para esa fecha
                                    $query->select(DB::raw(1))
                                        ->from('M_PERSONAL_MODULO as MPM2')
                                        ->whereRaw('MPM2.NUM_DOC = MPM.NUM_DOC')
                                        ->where('MPM2.STATUS', 'itinerante')
                                        ->whereDate('MA.FECHA', '>=', DB::raw('MPM2.FECHAINICIO'))
                                        ->whereDate('MA.FECHA', '<=', DB::raw('MPM2.FECHAFIN'));
                                });
                        })
                        // Incluir los asesores que no tienen módulo (sin itinerante ni fijo)
                        ->orWhereNull('MPM.NUM_DOC');
                })
                ->orderBy('MC.NOMBRE_MAC', 'asc')
                ->orderBy('MA.FECHA', 'asc')  // Ordenar por FECHA primero, en orden ascendente
                ->orderBy('MM.N_MODULO', 'asc') // Luego por N_MODULO, también en orden ascendente
                ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ME.ABREV_ENTIDAD', 'MC.NOMBRE_MAC', 'MPM.STATUS', 'MM.N_MODULO')
                ->get();


            // $query = DB::table('M_ASISTENCIA as MA')
            //     ->select('PERS.ABREV_ENTIDAD', 'PERS.NOMBREU', 'PERS.NOMBRE_CARGO', 'MA.FECHA', 'MA.NUM_DOC', DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'))
            //     ->selectRaw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
            //     ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
            //                     FROM M_PERSONAL
            //                     LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
            //                     JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
            //                     JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.NOMBRE_CARGO')
            //     ->where('PERS.IDENTIDAD', $request->identidad)
            //     ->where('MA.IDCENTRO_MAC', $idmac)
            //     ->whereMonth('MA.FECHA', $request->mes)
            //     ->whereYear('MA.FECHA', $request->año)
            //     ->orderBy('FECHA', 'ASC')
            //     ->get();
            // dd($query);

            foreach ($query as $q) {
                $horas = explode(',', $q->fecha_biometrico);
                $num_horas = count($horas);
                if ($num_horas == 1) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = null;
                    $q->HORA_3 = null;
                    $q->HORA_4 = null;
                } elseif ($num_horas == 2) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = null;
                    $q->HORA_3 = null;
                    $q->HORA_4 = $horas[1];
                } elseif ($num_horas == 3) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = null;
                    $q->HORA_4 = $horas[2];
                } elseif ($num_horas >= 4) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = $horas[2];
                    $q->HORA_4 = $horas[3];
                }
            }


            $datosAgrupados = '';
            $fechasArray = '';
        }

        // dd($query);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin, $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray,), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $name_mac . ' _' . $nombreMES . '.xlsx');

        return $export;
    }

    public function md_det_entidad_perso(Request $request)
    {
        $identidad = $request->identidad;

        $mac = $request->mac;

        $view = view('asistencia.modals.md_det_entidad_perso', compact('identidad', 'mac'))->render();

        return response()->json(['html' => $view]);
    }

    public function exportgroup_excel_pr(Request $request)
    {

        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        // dd($request->all());
        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        } else {
            $idmac = $request->mac;
        }

        $esTodosLosMacs = ($idmac == 0);
        $name_mac = ($idmac == 0)
            ? 'TODOS LOS MACs'
            : Mac::where('IDCENTRO_MAC', $idmac)->value('NOMBRE_MAC');
        // DEFINIMOS EL TIPO DE DESCA

        $fecha_ini_desc = strftime('%d de %B del %Y', strtotime($request->fecha_inicio));
        $fecha_fin_desc = strftime('%d de %B del %Y', strtotime($request->fecha_fin));

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();


        $tipo_desc = '2';
        $fecha_inicial = $fecha_ini_desc;
        $fecha_fin = $fecha_fin_desc;
        $identidad = $request->identidad;
        // dd($identidad);

        if ($identidad == '17') {

            // Obtener datos del encabezado
            $nom_ = Personal::from('M_PERSONAL as MP')
                ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                ->select('DPC.NOMBRE_CARGO', DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'), 'MP.NUM_DOC', 'MP.IDENTIDAD')
                ->where('MP.IDENTIDAD', 17)
                ->where('MP.IDMAC', $this->centro_mac()->idmac)
                ->get();

            $primerDia = $request->fecha_inicio;
            $ultimoDia = $request->fecha_fin;

            // Obtén el rango de fechas entre el primer y último día del mes
            $fechas = CarbonPeriod::create($primerDia, $ultimoDia);

            // Convierte las fechas a un array
            $fechasArray = [];
            foreach ($fechas as $fecha) {
                $fechasArray[] = $fecha->toDateString();
            }

            // Obtener datos del detalle
            $querys = DB::table('M_ASISTENCIA as MA')
                ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
                                        FROM M_PERSONAL
                                        LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                        JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                        JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                ->select([
                    'PERS.ABREV_ENTIDAD1',
                    'PERS.NOMBRE_CARGO',
                    'PERS.NOMBREU',
                    'MA.FECHA',
                    'MA.NUM_DOC',
                    DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
                    DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
                    'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
                    'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
                ])
                ->where('PERS.IDENTIDAD', $request->identidad)
                ->where('MA.IDCENTRO_MAC', $idmac)
                ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
                ->orderBy('MA.FECHA', 'asc')
                ->get();

            foreach ($querys as $q) {
                $horas = explode(',', $q->HORAS);
                $num_horas = count($horas);
                if ($num_horas == 1) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = null;
                    $q->HORA_3 = null;
                    $q->HORA_4 = null;
                } elseif ($num_horas == 2) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = null;
                    $q->HORA_3 = null;
                    $q->HORA_4 = $horas[1];
                } elseif ($num_horas == 3) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = null;
                    $q->HORA_4 = $horas[2];
                } elseif ($num_horas >= 4) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = $horas[2];
                    $q->HORA_4 = $horas[3];
                }
            }


            $query = [];
            foreach ($fechasArray as $fecha) {
                $query[$fecha] = $querys->filter(function ($row) use ($fecha) {
                    return $row->FECHA == $fecha;
                })->toArray();
            }
            // Agrupar por NUM_DOC
            $datosAgrupados = [];

            foreach ($nom_ as $encabezado) {
                // Utilizando la relación definida en los modelos
                $detalle = $encabezado->asistencias()
                    ->select([
                        'M_ASISTENCIA.FECHA',
                        'M_ASISTENCIA.NUM_DOC',
                        DB::raw('GROUP_CONCAT(DATE_FORMAT(HORA, "%H:%i:%s") ORDER BY HORA) AS HORAS'),
                        DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                    ])
                    ->whereBetween(DB::raw('DATE(M_ASISTENCIA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                    ->groupBy('M_ASISTENCIA.NUM_DOC', 'M_ASISTENCIA.FECHA')
                    ->orderBy('M_ASISTENCIA.FECHA', 'asc')
                    ->get();

                foreach ($detalle as $d) {
                    $horas = explode(',', $d->HORAS);
                    $num_horas = count($horas);
                    if ($num_horas == 1) {
                        $d->HORA_1 = $horas[0];
                        $d->HORA_2 = null;
                        $d->HORA_3 = null;
                        $d->HORA_4 = null;
                    } elseif ($num_horas == 2) {
                        $d->HORA_1 = $horas[0];
                        $d->HORA_2 = null;
                        $d->HORA_3 = null;
                        $d->HORA_4 = $horas[1];
                    } elseif ($num_horas == 3) {
                        $d->HORA_1 = $horas[0];
                        $d->HORA_2 = $horas[1];
                        $d->HORA_3 = null;
                        $d->HORA_4 = $horas[2];
                    } elseif ($num_horas >= 4) {
                        $d->HORA_1 = $horas[0];
                        $d->HORA_2 = $horas[1];
                        $d->HORA_3 = $horas[2];
                        $d->HORA_4 = $horas[3];
                    }
                }

                // dd($detalle);

                $datosAgrupados[] = ['encabezado' => $encabezado, 'detalle' => $detalle];
            }

            // dd($datosAgrupados);
            // Ahora, $datosAgrupados es un array asociativo donde cada elemento tiene la información del encabezado junto con su detalle correspondiente


        } else {

            $query = DB::table('M_ASISTENCIA as MA')
                ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
                ->leftJoin('M_PERSONAL_MODULO as MPM', function ($join) use ($idmac) {
                    $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
                        ->where('MPM.STATUS', '!=', 'eliminado')
                        ->when($idmac > 0, function ($q) use ($idmac) {
                            $q->where('MPM.IDCENTRO_MAC', '=', $idmac);
                        });
                })
                ->leftJoin('M_MODULO as MM', 'MM.IDMODULO', '=', 'MPM.IDMODULO')
                ->leftJoin('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MM.IDENTIDAD')
                ->leftJoin('D_ASISTENCIA_OBSERVACION as DAO', function (JoinClause $join) {
                    $join->on('DAO.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->on('DAO.FECHA',     '=', 'MA.FECHA')
                        ->on('DAO.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC');
                })
                ->select(
                    'MA.FECHA as FECHA',
                    DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
                    DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT(MA.HORA, '%H:%i:%s') ORDER BY MA.HORA) AS fecha_biometrico"),
                    'MA.NUM_DOC as NUM_DOC',
                    DB::raw('UPPER(CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE)) AS NOMBREU'),
                    'ME.ABREV_ENTIDAD', // Mostrar la abreviatura correcta de la entidad
                    'MC.NOMBRE_MAC',
                    'MPM.STATUS as status_modulo',
                    'MM.N_MODULO as N_MODULO',
                    DB::raw("GROUP_CONCAT(DISTINCT DAO.OBSERVACION ORDER BY DAO.ID_ASISTENCIA_OBV SEPARATOR ';') AS observaciones"),
                    DB::raw('COUNT(IF(DAO.flag = 1, DAO.id_asistencia_obv, NULL)) as contador_obs')
                )
                ->where(function ($query) use ($request, $esTodosLosMacs, $idmac) {
                    $fecha_inicio = date('Y-m-d', strtotime($request->fecha_inicio));
                    $fecha_fin    = date('Y-m-d', strtotime($request->fecha_fin));
                    $query->whereBetween('MA.FECHA', [$fecha_inicio, $fecha_fin]);

                    if (!$esTodosLosMacs) {
                        $query->where('MA.IDCENTRO_MAC', $idmac);
                    }
                })

                ->where(function ($query) use ($request, $idmac, $esTodosLosMacs) {
                    if ($request->identidad) {
                        $query->where('ME.IDENTIDAD', $request->identidad);
                    }
                    if (!$esTodosLosMacs) {
                        $query->where('MA.IDCENTRO_MAC', '=', $idmac);
                    }
                })

                ->where(function ($query) use ($idmac, $esTodosLosMacs) {
                    if (!$esTodosLosMacs) {
                        $query->where('MA.IDCENTRO_MAC', '=', $idmac);
                    } elseif (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                        $query->where('MA.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                    }
                })

                ->where(function ($query) use ($request) {
                    // Primero tratamos de obtener los módulos itinerantes dentro del rango de fechas
                    $query->where('MPM.STATUS', 'itinerante')
                        ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                        ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                        ->orWhere(function ($query) {
                            // Si no tiene módulo itinerante, tomamos el módulo fijo dentro del rango de fechas
                            $query->where('MPM.STATUS', 'fijo')
                                ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                                ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                                ->whereNotExists(function ($query) {
                                    // Nos aseguramos de que no tenga un módulo itinerante para esa fecha
                                    $query->select(DB::raw(1))
                                        ->from('M_PERSONAL_MODULO as MPM2')
                                        ->whereRaw('MPM2.NUM_DOC = MPM.NUM_DOC')
                                        ->where('MPM2.STATUS', 'itinerante')
                                        ->whereDate('MA.FECHA', '>=', DB::raw('MPM2.FECHAINICIO'))
                                        ->whereDate('MA.FECHA', '<=', DB::raw('MPM2.FECHAFIN'));
                                });
                        })
                        // Incluir los asesores que no tienen módulo (sin itinerante ni fijo)
                        ->orWhereNull('MPM.NUM_DOC');
                })
                ->orderBy('MC.NOMBRE_MAC', 'asc')
                ->orderBy('MA.FECHA', 'asc')  // Ordenar por FECHA primero, en orden ascendente
                ->orderBy('MM.N_MODULO', 'asc') // Luego por N_MODULO, también en orden ascendente
                ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ME.ABREV_ENTIDAD', 'MC.NOMBRE_MAC', 'MPM.STATUS', 'MM.N_MODULO')
                ->get();
            // $query =  DB::table('M_ASISTENCIA as MA')
            //     ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
            //                             FROM M_PERSONAL
            //                             LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
            //                             JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
            //                             JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->select([
            //         'PERS.ABREV_ENTIDAD',
            //         'PERS.NOMBRE_CARGO',
            //         'PERS.NOMBREU',
            //         'MA.FECHA',
            //         'MA.NUM_DOC',
            //         DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
            //         DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
            //         'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
            //         'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
            //     ])
            //     ->where('PERS.IDENTIDAD', $request->identidad)
            //     ->where('MA.IDCENTRO_MAC', $idmac)
            //     ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
            //     ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
            //     ->orderBy('MA.FECHA', 'asc')
            //     ->get();

            foreach ($query as $q) {
                $horas = explode(',', $q->fecha_biometrico);
                $num_horas = count($horas);
                if ($num_horas == 1) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = null;
                    $q->HORA_3 = null;
                    $q->HORA_4 = null;
                } elseif ($num_horas == 2) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = null;
                    $q->HORA_3 = null;
                    $q->HORA_4 = $horas[1];
                } elseif ($num_horas == 3) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = null;
                    $q->HORA_4 = $horas[2];
                } elseif ($num_horas >= 4) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = $horas[2];
                    $q->HORA_4 = $horas[3];
                }
            }

            $datosAgrupados = '';
            $fechasArray = '';
        }



        // dd($fecha_inicial);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin, $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $name_mac . ' _' . $nombreMES . '.xlsx');

        return $export;
    }

    public function exportgroup_excel_general(Request $request)
    {

        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        // dd($request->all());
        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        } else {
            $idmac = $request->mac;
        }

        if ($idmac == 0) {
            $name_mac = 'TODOS LOS MACs';
        } else {
            $dec_mac = Mac::where('IDCENTRO_MAC', $idmac)->first();
            $name_mac = $dec_mac?->NOMBRE_MAC ?? 'MAC DESCONOCIDO';
        }
        // DEFINIMOS EL TIPO DE DESCA
        $anio = $request->input('año');
        $mes = $request->input('mes');

        $fechaInicio = Carbon::create($anio, $mes, 1);

        $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();

        $meses = [
            '01' => 'enero',
            '02' => 'febrero',
            '03' => 'marzo',
            '04' => 'abril',
            '05' => 'mayo',
            '06' => 'junio',
            '07' => 'julio',
            '08' => 'agosto',
            '09' => 'septiembre',
            '10' => 'octubre',
            '11' => 'noviembre',
            '12' => 'diciembre'
        ];

        $fecha_ini_desc = $fechaInicio->format('d') . ' de ' . $meses[$mes] . ' del ' . $fechaInicio->format('Y');
        $fecha_fin_desc = $fechaFin->format('d') . ' de ' . $meses[$mes] . ' del ' . $fechaFin->format('Y');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();


        $tipo_desc = '2';
        $fecha_inicial = $fecha_ini_desc;
        $fecha_fin = $fecha_fin_desc;
        // $identidad = $request->identidad;
        // dd($fecha_ini_desc);
        $identidadArray = DB::table('M_MAC_ENTIDAD')->select('IDENTIDAD')->where('IDCENTRO_MAC', $idmac)->whereNot('IDENTIDAD', 17)->pluck('IDENTIDAD')->toArray();

        $identidadString = '(' . implode(', ', $identidadArray) . ')';

        // dd($identidadString);
        $identidad = $identidadString;

        $query = DB::table('M_ASISTENCIA as MA')
            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
            ->leftJoin('M_PERSONAL_MODULO as MPM', function ($join) use ($idmac) {
                $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
                    ->where('MPM.STATUS', '!=', 'eliminado');

                if ($idmac != 0) {
                    $join->where('MPM.IDCENTRO_MAC', '=', $idmac);
                }
            })
            ->leftJoin('M_MODULO as MM', 'MM.IDMODULO', '=', 'MPM.IDMODULO')
            ->leftJoin('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MM.IDENTIDAD')
            ->leftJoin('D_ASISTENCIA_OBSERVACION as DAO', function (JoinClause $join) {
                $join->on('DAO.NUM_DOC', '=', 'MA.NUM_DOC')
                    ->on('DAO.FECHA',     '=', 'MA.FECHA')
                    ->on('DAO.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC');
            })
            ->select(
                'MA.FECHA as FECHA',
                DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
                DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT(MA.HORA, '%H:%i:%s') ORDER BY MA.HORA) AS fecha_biometrico"),
                'MA.NUM_DOC as NUM_DOC',
                DB::raw('UPPER(CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE)) AS NOMBREU'),
                'ME.ABREV_ENTIDAD', // Mostrar la abreviatura correcta de la entidad
                'MC.NOMBRE_MAC',
                'MPM.STATUS as status_modulo',
                'MM.N_MODULO as N_MODULO',
                DB::raw("GROUP_CONCAT(DISTINCT DAO.OBSERVACION ORDER BY DAO.ID_ASISTENCIA_OBV SEPARATOR ';') AS observaciones"),
                DB::raw('COUNT(IF(DAO.flag = 1, DAO.id_asistencia_obv, NULL)) as contador_obs')
            )
            ->where(function ($query) use ($request) {
                // Filtra por fecha (mes y año) y centro MAC
                // $idmac = $this->centro_mac()->idmac;

                // Si se proporcionan fechas de inicio y fin
                if ($request->fecha_inicio && $request->fecha_fin) {
                    $fecha_inicio = date('Y-m-d', strtotime($request->fecha_inicio));  // Convierte a formato Y-m-d
                    $fecha_fin = date('Y-m-d', strtotime($request->fecha_fin));  // Convierte a formato Y-m-d
                    $query->whereBetween('MA.FECHA', [$fecha_inicio, $fecha_fin]);
                } else {
                    // Si no se proporcionaron fechas, filtrar por mes y año
                    $query->whereMonth('MA.FECHA', $request->mes)
                        ->whereYear('MA.FECHA', $request->año);
                }

                // Aseguramos que siempre se filtre por IDCENTRO_MAC
                // $query->where('MA.IDCENTRO_MAC', $idmac);
            })
            ->where(function ($query) use ($request, $idmac) {
                // Filtra por entidad si es necesario
                //dd($request->identidad);
                if ($request->identidad) {
                    $query->where('ME.IDENTIDAD', $request->identidad);
                }
                if ($idmac != 0) {
                    $query->where('MA.IDCENTRO_MAC', '=', $idmac);
                }
            })
            ->where(function ($query) {
                // Filtra por el centro MAC del usuario
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('MA.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->where(function ($query) use ($request) {
                // Primero tratamos de obtener los módulos itinerantes dentro del rango de fechas
                $query->where('MPM.STATUS', 'itinerante')
                    ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                    ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                    ->orWhere(function ($query) {
                        // Si no tiene módulo itinerante, tomamos el módulo fijo dentro del rango de fechas
                        $query->where('MPM.STATUS', 'fijo')
                            ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
                            ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
                            ->whereNotExists(function ($query) {
                                // Nos aseguramos de que no tenga un módulo itinerante para esa fecha
                                $query->select(DB::raw(1))
                                    ->from('M_PERSONAL_MODULO as MPM2')
                                    ->whereRaw('MPM2.NUM_DOC = MPM.NUM_DOC')
                                    ->where('MPM2.STATUS', 'itinerante')
                                    ->whereDate('MA.FECHA', '>=', DB::raw('MPM2.FECHAINICIO'))
                                    ->whereDate('MA.FECHA', '<=', DB::raw('MPM2.FECHAFIN'));
                            });
                    })
                    // Incluir los asesores que no tienen módulo (sin itinerante ni fijo)
                    ->orWhereNull('MPM.NUM_DOC');
            })
            ->orderBy('MC.NOMBRE_MAC', 'asc')
            ->orderBy('MA.FECHA', 'asc')  // Ordenar por FECHA primero, en orden ascendente
            ->orderBy('MM.N_MODULO', 'asc') // Luego por N_MODULO, también en orden ascendente
            ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ME.ABREV_ENTIDAD', 'MC.NOMBRE_MAC', 'MPM.STATUS', 'MM.N_MODULO')
            ->get();

        // $query = DB::table('M_ASISTENCIA as MA')
        //     ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
        //     ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
        //     ->leftJoin('M_PERSONAL_MODULO as MPM', function ($join) use ($idmac) {
        //         $join->on('MP.NUM_DOC', '=', 'MPM.NUM_DOC')
        //             ->where('MPM.STATUS', '!=', 'eliminado')  // Aseguramos que solo módulos válidos sean considerados
        //             ->where('MPM.IDCENTRO_MAC', '=', $idmac); // Filtrar por el IDCENTRO_MAC del centro actual
        //     })
        //     ->leftJoin('M_MODULO as MM', 'MM.IDMODULO', '=', 'MPM.IDMODULO')
        //     ->leftJoin('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'MM.IDENTIDAD')
        //     ->select(
        //         'MA.FECHA as FECHA',
        //         DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
        //         DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT(MA.HORA, '%H:%i:%s') ORDER BY MA.HORA) AS fecha_biometrico"),
        //         'MA.NUM_DOC as NUM_DOC',
        //         DB::raw('UPPER(CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE)) AS NOMBREU'),
        //         'ME.ABREV_ENTIDAD', // Mostrar la abreviatura correcta de la entidad
        //         'MC.NOMBRE_MAC',
        //         'MPM.STATUS as status_modulo',
        //         'MM.N_MODULO as N_MODULO' // Asegurándonos de mostrar solo el módulo correspondiente al Centro MAC
        //     )

        //     ->where(function ($query) use ($request) {
        //         // Filtra por fecha (mes y año) y centro MAC
        //         $idmac = $this->centro_mac()->idmac;
        //         $query->where('MA.IDCENTRO_MAC', $idmac)
        //             ->whereMonth('MA.FECHA', $request->mes)
        //             ->whereYear('MA.FECHA', $request->año);
        //     })
        //     ->where(function ($query) use ($request) {
        //         // Filtra por entidad si es necesario
        //         if ($request->entidad) {
        //             $query->where('ME.IDENTIDAD', $request->entidad);
        //         }
        //     })
        //     ->where(function ($query) {
        //         // Filtra por el centro MAC del usuario
        //         if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
        //             $query->where('MA.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
        //         }
        //     })
        //     ->where(function ($query) use ($request) {
        //         // Primero tratamos de obtener los módulos itinerantes dentro del rango de fechas
        //         $query->where('MPM.STATUS', 'itinerante')
        //             ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
        //             ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
        //             ->orWhere(function ($query) {
        //                 // Si no tiene módulo itinerante, tomamos el módulo fijo dentro del rango de fechas
        //                 $query->where('MPM.STATUS', 'fijo')
        //                     ->whereDate('MA.FECHA', '>=', DB::raw('MPM.FECHAINICIO'))
        //                     ->whereDate('MA.FECHA', '<=', DB::raw('MPM.FECHAFIN'))
        //                     ->whereNotExists(function ($query) {
        //                         // Nos aseguramos de que no tenga un módulo itinerante para esa fecha
        //                         $query->select(DB::raw(1))
        //                             ->from('M_PERSONAL_MODULO as MPM2')
        //                             ->whereRaw('MPM2.NUM_DOC = MPM.NUM_DOC')
        //                             ->where('MPM2.STATUS', 'itinerante')
        //                             ->whereDate('MA.FECHA', '>=', DB::raw('MPM2.FECHAINICIO'))
        //                             ->whereDate('MA.FECHA', '<=', DB::raw('MPM2.FECHAFIN'));
        //                     });
        //             })
        //             // Incluir los asesores que no tienen módulo (sin itinerante ni fijo)
        //             ->orWhereNull('MPM.NUM_DOC');
        //     })
        //     ->orderBy('MA.FECHA', 'asc')  // Ordenar por FECHA primero, en orden ascendente
        //     ->orderBy('MM.N_MODULO', 'asc') // Luego por N_MODULO, también en orden ascendente
        //     ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ME.ABREV_ENTIDAD', 'MC.NOMBRE_MAC', 'MPM.STATUS', 'MM.N_MODULO')
        //     ->get();


        foreach ($query as $q) {
            // Asumiendo que el valor de 'fecha_biometrico' contiene la hora en formato 'H:i:s'
            $horas = explode(',', $q->fecha_biometrico); // Separa las horas por coma
            $num_horas = count($horas);

            // Asigna las horas con segundos
            if ($num_horas == 1) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = null;
            } elseif ($num_horas == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[1];
            } elseif ($num_horas == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[2];
            } elseif ($num_horas >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
        }

        $datosAgrupados = '';
        $fechasArray = '';

        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin, $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray,), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $name_mac . ' _' . $nombreMES . '.xlsx');

        return $export;
    }

    public function dow_asistencia()
    {
        try {

            $insert = DB::select("CALL  SP_CARGA_ASISTENCIA();");


            return response()->json([
                'status' => true,
                'message' => 'Se cargaron las asistencia con éxito.',
                'data' => $insert,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Hubo un error al actualizar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function migrarDatos(Request $request)
    {
        try {
            $dbCentroMac = DB::connection('mysql');
            $dbZK = DB::connection('zk'); // conexión a la base zk

            // Obtener registros NO migrados
            $asistencias = $dbZK->table('iclock_transaction')
                ->where('migrado', 0)
                ->orderBy('punch_time')
                ->get();

            $lastId = $dbCentroMac->table('m_asistencia')->max('IDASISTENCIA') ?? 0;
            $processedCount = 0;

            foreach ($asistencias as $asistencia) {
                $lastId++;

                $fechaHora = Carbon::parse($asistencia->punch_time)->second(0);
                $fecha     = $fechaHora->toDateString();
                $hora      = $fechaHora->toTimeString();
                $mes       = (int)$fechaHora->format('m');
                $anio      = (int)$fechaHora->format('Y');

                $correlativoDia = $dbCentroMac->table('m_asistencia')
                    ->whereDate('FECHA', $fecha)
                    ->count() + 1;

                try {
                    $dbCentroMac->table('m_asistencia')->insert([
                        'IDASISTENCIA'     => $lastId,
                        'IDTIPO_ASISTENCIA' => 1,
                        'NUM_DOC'          => $asistencia->emp_code,
                        'IDCENTRO_MAC'     => 11,
                        'MES'              => $mes,
                        'AÑO'              => $anio,
                        'FECHA'            => $fecha,
                        'HORA'             => $hora,
                        'FECHA_BIOMETRICO' => "$fecha $hora",
                        'NUM_BIOMETRICO'   => null,
                        'CORRELATIVO'      => $lastId,
                        'CORRELATIVO_DIA'  => $correlativoDia,
                    ]);

                    // Marcar como migrado
                    $dbZK->table('iclock_transaction')
                        ->where('id', $asistencia->id)
                        ->update(['migrado' => 1]);

                    $processedCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->getCode() == '23000') {
                        continue; // clave duplicada
                    } else {
                        throw $e;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "$processedCount registros migrados correctamente desde iclock_transaction."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error al migrar desde iclock_transaction: " . $e->getMessage()
            ], 500);
        }
    }
    public function exportgroup_excel_resumen(Request $request)
    {
        $mes  = $request->mes;
        $anio = $request->año;
        $idmac = $request->mac;

        $query = DB::table('asistencia_resumen')
            ->when($mes, fn($q) => $q->whereMonth('fecha_asistencia', $mes))
            ->when($anio, fn($q) => $q->whereYear('fecha_asistencia', $anio))
            ->when($idmac, fn($q) => $q->where('idmac', $idmac))
            ->orderBy('fecha_asistencia', 'asc')
            ->orderBy('nombreu', 'asc')
            ->get();

        foreach ($query as $q) {
            $horas = explode(',', $q->fecha_biometrico ?? '');
            $q->HORA_1 = $horas[0] ?? null;
            $q->HORA_2 = $horas[1] ?? null;
            $q->HORA_3 = $horas[2] ?? null;
            $q->HORA_4 = $horas[3] ?? null;
        }

        $name_mac = ($idmac == 0)
            ? 'TODOS LOS MACs'
            : DB::table('m_centro_mac')->where('idcentro_mac', $idmac)->value('nombre_mac');

        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        $fecha = \Carbon\Carbon::create(null, $mes, 1);
        $nombreMES = $fecha->formatLocalized('%B');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AsistenciaResumenExport($query, $name_mac, $nombreMES),
            'REPORTE_RESUMEN_ASISTENCIA_' . $name_mac . '_' . $nombreMES . '.xlsx'
        );
    }
}
