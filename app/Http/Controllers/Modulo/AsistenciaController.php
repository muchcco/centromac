<?php

namespace App\Http\Controllers\Modulo;

use App\Exports\AsistenciaDetalleExport;
use App\Exports\AsistenciaAsignacionExport;
use App\Exports\AsistenciaAsignacionReporteExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\Asistenciatest;
use App\Models\Entidad;
use App\Models\Mac;
use App\Jobs\ProcessAsistenciaTxt;
use App\Jobs\ProcessAsistenciaCallao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Queue;
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
            $user  = auth()->user();

            // 🔒 SOLO MAC DEL USUARIO
            $idmac = $user->idcentro_mac;

            $fechaCarbon = Carbon::parse($fecha);
            $hoy = Carbon::today();

            // 🔹 VALIDAR SI YA ESTÁ CERRADO
            $yaCerrado = DB::table('db_centro_mac_reporte.asistencia_resumen')
                ->where('idmac', $idmac)
                ->whereDate('fecha_asistencia', $fecha)
                ->exists();

            if ($yaCerrado) {
                return response()->json([
                    'success' => false,
                    'message' => 'El día ya se encuentra cerrado.'
                ], 400);
            }

            // 🔹 FERIADOS (OPTIMIZADO)
            $feriados = DB::table('feriados')->pluck('fecha')->toArray();

            // 🔹 DÍAS CERRADOS REALES
            $cerrados = DB::table('db_centro_mac_reporte.asistencia_resumen')
                ->where('idmac', $idmac)
                ->whereDate('fecha_asistencia', '<=', $fecha)
                ->pluck('fecha_asistencia')
                ->map(fn($f) => Carbon::parse($f)->format('Y-m-d'))
                ->unique()
                ->toArray();

            // 🔹 FECHA INICIO (PRIMER REGISTRO REAL)
            $fechaInicio = DB::table('db_centro_mac_reporte.asistencia_resumen')
                ->where('idmac', $idmac)
                ->min('fecha_asistencia');

            $fechaInicio = $fechaInicio
                ? Carbon::parse($fechaInicio)
                : $fechaCarbon->copy();

            $faltantes = [];
            $cursor = $fechaInicio->copy();

            // 🔴 CALCULAR DÍAS FALTANTES
            while ($cursor->lt($fechaCarbon)) {

                $fechaStr = $cursor->format('Y-m-d');

                $esDomingo = $cursor->isSunday();
                $esFeriado = in_array($fechaStr, $feriados);

                if (!$esDomingo && !$esFeriado) {

                    if (!in_array($fechaStr, $cerrados)) {
                        $faltantes[] = $cursor->format('d-m-Y');
                    }
                }

                $cursor->addDay();
            }

            // 🔴 VALIDACIÓN
            if (!empty($faltantes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cerrar en desorden.',
                    'detalle' => 'Faltan cerrar: <br>' . implode('<br>', $faltantes)
                ], 400);
            }

            // 🔹 CALCULAR DÍAS HÁBILES (REGLA DE 3 DÍAS)
            $diasHabiles = 0;
            $cursor = $fechaCarbon->copy()->addDay();

            while ($cursor->lte($hoy)) {

                $fechaStr = $cursor->format('Y-m-d');

                if (!$cursor->isSunday() && !in_array($fechaStr, $feriados)) {
                    $diasHabiles++;
                }

                $cursor->addDay();
            }

            $excepcion = DB::table('D_ASISTENCIA_EXCEPCION_CIERRE')
                ->where('IDCENTRO_MAC', $idmac)
                ->whereDate('FECHA_ASISTENCIA', $fecha)
                ->where('ESTADO', 'ACTIVO')
                ->where(function ($q) {
                    $q->whereNull('VALIDO_HASTA')
                        ->orWhere('VALIDO_HASTA', '>=', now());
                })
                ->first();

            if ($diasHabiles > 3 && !$excepcion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se superó el límite de 3 días hábiles.',
                    'detalle' => '
                Debe comunicarse con los encargados:<br><br>
                <strong>Para:</strong>
                <li>jretamozo@pcm.gob.pe</li>
                <strong>Cc:</strong>
                <li>mramirezr@pcm.gob.pe</li>
                <li>dalfaro@pcm.gob.pe</li>
                <li>yrojas@pcm.gob.pe</li>'
                ], 400);
            }

            // 🔹 HORARIO
            $esSabado = $fechaCarbon->isSaturday();
            $macSalida18 = [12, 13, 14, 10, 19];

            if ($esSabado) {
                $horaSalidaOficial = '13:00:00';
            } else {
                $horaSalidaOficial = in_array($idmac, $macSalida18)
                    ? '18:00:00'
                    : '17:00:00';
            }

            // 🔹 DATA ASISTENCIA
            $datos = DB::select(
                'CALL db_centros_mac.SP_ASISTENCIA_DIARIA_MAC(?, ?, ?)',
                [$idmac, $fecha, 0]
            );

            // 🔹 GUARDAR RESUMEN
            DB::statement(
                "CALL guardar_resumen_asistencia_dia(?, ?)",
                [$fecha, $idmac]
            );

            // 🔹 LOG (SOLO AUDITORÍA)
            DB::table('db_centros_mac.cierre_asistencia_log')->insert([
                'tipo_cierre'     => 'DIA',
                'fecha'           => $fecha,
                'anio'            => $fechaCarbon->year,
                'mes'             => $fechaCarbon->month,
                'idmac'           => $idmac,
                'user_id'         => $user->id,
                'user_nombre'     => $user->name,
                'fecha_registro'  => now(),
            ]);

            // 🔹 MARCAR EXCEPCIÓN
            if ($excepcion) {
                DB::table('D_ASISTENCIA_EXCEPCION_CIERRE')
                    ->where('IDEXCEPCION', $excepcion->IDEXCEPCION)
                    ->update(['ESTADO' => 'USADO']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cierre de asistencia realizado correctamente.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
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
                    'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
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
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ]);
        }
    }

    public function tb_asistencia(Request $request)
    {
        $userAuth = auth()->user();

        if ($userAuth->hasRole(['Administrador', 'Moderador'])) {
            $idmac = $request->mac ?: $userAuth->idcentro_mac;
        } else {
            $idmac = $userAuth->idcentro_mac;
        }

        $fecha     = $request->fecha ?? date('Y-m-d');
        $identidad = $request->entidad ?? 0;
        $esHoy = Carbon::parse($fecha)->isToday();
        $conf = Configuracion::where('IDCONFIGURACION', 2)->first();

        // 🔹 Determinar día de la semana
        $esSabado = Carbon::parse($fecha)->isSaturday();

        $macSalida18 = [12, 13, 14, 10, 19];

        if ($esSabado) {
            // 📅 SÁBADO → TODOS 13:00
            $horaSalidaOficial = '13:00:00';
        } else {
            // 📅 Lunes a Viernes
            $horaSalidaOficial = in_array($idmac, $macSalida18)
                ? '18:00:00'
                : '17:00:00';
        }
        $datos = DB::select(
            'CALL db_centros_mac.SP_ASISTENCIA_DIARIA_MAC(?, ?, ?)',
            [$idmac, $fecha, $identidad]
        );

        // 1️⃣ PREPROCESO
        foreach ($datos as $q) {

            $q->idpersonal    = $q->IDPERSONAL;
            $q->n_dni         = $q->NUM_DOC;
            $q->nombreu       = $q->nombre;
            $q->nombre_modulo = $q->N_MODULO;

            $horas = $q->horas ? array_map('trim', explode(',', $q->horas)) : [];
            sort($horas); // ordenar por seguridad
            $num = count($horas);
            // 🔹 Última marcación real del día
            $ultimaHora = $num > 0 ? max($horas) : null;
            $q->ultima_marcacion = $ultimaHora;

            $q->HORA_1 = $q->HORA_2 = $q->HORA_3 = $q->HORA_4 = null;

            if ($num == 1) {
                $q->HORA_1 = $horas[0];
            } elseif ($num == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_4 = $horas[1];
            } elseif ($num == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_4 = $horas[2];
            } elseif ($num >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }

            // 🔹 Marcaciones
            $q->flag_exceso     = ($num > 4 && $num % 2 == 0); // 6,8,10
            $q->flag_incompleto = ($num % 2 != 0);              // 1,3,5,7

            // inicializar
            $q->flag_tarde = false;
            $q->flag_tardanza_grupal = false;
            $q->hora_tardanza = null;
            $q->flag_retiro_anticipado = false;
            $q->flag_llegada_fuera_rango = false;
            $q->hora_salida_oficial = $horaSalidaOficial;
        }

        // 2️⃣ TARDANZA
        $modulos = collect($datos)->groupBy('nombre_modulo');

        foreach ($modulos as $modulo => $items) {

            $esPCM = $items->first()->ABREV_ENTIDAD === 'PCM';
            $horaMinimaIngresoModulo = $items->pluck('HORA_1')->filter()->min();
            // 🔹 Última marcación mínima del módulo
            $horaMaximaSalidaModulo = $items->pluck('ultima_marcacion')->filter()->max();

            // ==========================
            // ⏰ LLEGADA > 08:59
            // ==========================

            if ($esPCM) {

                // PCM → individual
                foreach ($items as $q) {
                    if ($q->HORA_1 && $q->HORA_1 > '08:59:59') {
                        $q->flag_llegada_fuera_rango = true;
                    }
                }
            } else {

                // NO PCM → grupal por módulo
                if ($horaMinimaIngresoModulo && $horaMinimaIngresoModulo > '08:59:59') {
                    foreach ($items as $q) {
                        $q->flag_llegada_fuera_rango = true;
                    }
                }
            }
            // ==========================
            // 🔴 RETIRO ANTICIPADO
            // ==========================

            if ($esPCM) {

                // PCM → retiro individual
                foreach ($items as $q) {
                    if ($q->ultima_marcacion && $q->ultima_marcacion < $horaSalidaOficial) {
                        $q->flag_retiro_anticipado = true;
                    }
                }
            } else {

                // NO PCM → retiro grupal por módulo
                if ($horaMaximaSalidaModulo && $horaMaximaSalidaModulo < $horaSalidaOficial) {
                    foreach ($items as $q) {
                        $q->flag_retiro_anticipado = true;
                    }
                }
            }

            // ==========================
            // 🟡 TARDANZA
            // ==========================

            if ($esPCM) {

                // PCM → individual
                foreach ($items as $q) {
                    if ($q->HORA_1 && $q->HORA_1 >= '08:16:00') {
                        $q->flag_tarde = true;
                        $q->hora_tardanza = $q->HORA_1;
                    }
                }
            } else {

                // NO PCM → por módulo
                $horaMinimaModulo = $items->pluck('HORA_1')->filter()->min();
                $totalPersonas = $items->count();

                if ($totalPersonas >= 2 && $horaMinimaModulo && $horaMinimaModulo >= '08:16:00') {

                    foreach ($items as $q) {
                        $q->flag_tardanza_grupal = true;
                        $q->flag_tarde = false;
                    }
                } elseif ($totalPersonas === 1) {

                    foreach ($items as $q) {
                        if ($q->HORA_1 && $q->HORA_1 >= '08:16:00') {
                            $q->flag_tarde = true;
                        }
                        $q->flag_tardanza_grupal = false;
                    }
                } else {

                    foreach ($items as $q) {
                        $q->flag_tarde = false;
                        $q->flag_tardanza_grupal = false;
                    }
                }
            }
        }

        return view('asistencia.tablas.tb_asistencia', compact('datos', 'conf'));
    }

    public function verificarCierre(Request $request)
    {
        $userAuth = auth()->user();

        if ($userAuth->hasRole(['Administrador', 'Moderador'])) {
            $idmac = $request->mac ?: $userAuth->idcentro_mac;
        } else {
            $idmac = $userAuth->idcentro_mac;
        }

        $fecha = $request->input('fecha');

        $existe = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereDate('fecha_asistencia', $fecha)
            ->exists();

        return response()->json([
            'cerrado' => $existe,
            'idmac'   => $idmac
        ]);
    }

    public function revertirDia(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'idmac' => 'required|integer',
        ]);

        $user = auth()->user();

        // ✅ SOLO Administrador y Moderador
        if (!$user->hasAnyRole(['Administrador', 'Moderador'])) {
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
                'msg' => "Se revirtió la asistencia del {$request->fecha} en el MAC #{$request->idmac}"
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

        // Administrador o Moderador → ver todos los MAC
        if ($user->hasRole(['Administrador', 'Moderador'])) {

            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->orderBy('NOMBRE_MAC')
                ->get();
        }

        // Especialista TIC → solo hasta 16-03-2026 y solo su MAC
        elseif ($user->hasRole(['Especialista TIC', 'Especialista_TIC'])) {

            $fechaLimite = Carbon::create(2026, 3, 16)->endOfDay();

            if (now()->gt($fechaLimite)) {
                return response()->json([
                    'html' => "<div class='p-3 text-center text-danger'>
                El permiso para revertir asistencias para Especialista TIC venció el 16-03-2026.
                </div>"
                ]);
            }

            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->where('IDCENTRO_MAC', $user->idcentro_mac)
                ->get();
        }

        // Otros roles
        else {

            return response()->json([
                'html' => "<div class='p-3 text-center text-danger'>
            No tiene permisos para esta acción.
            </div>"
            ]);
        }

        return response()->json([
            'html' => view('asistencia.modals.md_revertir', compact('macs'))->render()
        ]);
    }
    public function md_excepcion_cierre(Request $request)
    {

        $idmac = $request->mac ?? $request->idmac;
        $fecha = $request->fecha;
        $cerrado = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereDate('fecha_asistencia', $fecha)
            ->exists();

        if ($cerrado) {

            return response()->json([
                'success' => false,
                'msg' => 'Ese día ya se encuentra cerrado.'
            ]);
        }
        $macs = DB::table('m_centro_mac')
            ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
            ->orderBy('NOMBRE_MAC')
            ->get();

        $html = view(
            'asistencia.modals.md_excepcion_cierre',
            compact('macs')
        )->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    public function guardar_excepcion_cierre(Request $request)
    {
        try {
            $fecha = $request->fecha;
            $idmac = $request->mac ?? $request->idmac;
            $cerrado = DB::table('db_centro_mac_reporte.asistencia_resumen')
                ->where('idmac', $idmac)
                ->whereDate('fecha_asistencia', $fecha)
                ->exists();

            if ($cerrado) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Ese día ya se encuentra cerrado. No se puede registrar excepción.'
                ]);
            }
            $existe = DB::table('D_ASISTENCIA_EXCEPCION_CIERRE')
                ->where('IDCENTRO_MAC', $idmac)
                ->whereDate('FECHA_ASISTENCIA', $fecha)
                ->where('ESTADO', 'ACTIVO')
                ->exists();

            if ($existe) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Ya existe una excepción registrada para ese día.'
                ]);
            }
            DB::table('D_ASISTENCIA_EXCEPCION_CIERRE')->insert([
                'IDCENTRO_MAC' => $idmac,
                'FECHA_ASISTENCIA' => $fecha,
                'SOLICITADO_POR' => auth()->user()->id,
                'NOMBRE_SOLICITANTE' => auth()->user()->name,
                'MOTIVO' => $request->motivo,
                'VALIDO_HASTA' => $request->valido_hasta,
                'FECHA_SOLICITUD' => now(),
                'ESTADO' => 'ACTIVO'
            ]);
            return response()->json([
                'ok' => true,
                'msg' => 'Excepción registrada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Error al registrar excepción.',
                'error' => $e->getMessage()
            ]);
        }
    }
    public function tb_asistencia_resumen(Request $request)
    {
        $userAuth = auth()->user();

        if ($userAuth->hasRole(['Administrador', 'Moderador'])) {
            $idmac = $request->mac ?: $userAuth->idcentro_mac;
        } else {
            $idmac = $userAuth->idcentro_mac;
        }

        $fecha = $request->fecha ?? date('Y-m-d');

        $esSabado = Carbon::parse($fecha)->isSaturday();
        $macSalida18 = [12, 13, 14, 10, 19];

        if ($esSabado) {
            $horaSalidaOficial = '13:00:00';
        } else {
            $horaSalidaOficial = in_array($idmac, $macSalida18) ? '18:00:00' : '17:00:00';
        }

        $datos = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereDate('fecha_asistencia', $fecha)
            ->orderBy('nombre_modulo', 'asc')
            ->get();

        foreach ($datos as $q) {
            $horas = $q->fecha_biometrico ? array_map('trim', explode(',', $q->fecha_biometrico)) : [];
            sort($horas);
            $num_horas = count($horas);

            $q->ultima_marcacion = $num_horas > 0 ? max($horas) : null;
            $q->HORA_1 = $q->HORA_2 = $q->HORA_3 = $q->HORA_4 = null;

            if ($num_horas == 1) {
                $q->HORA_1 = $horas[0];
            } elseif ($num_horas == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_4 = $horas[1];
            } elseif ($num_horas == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_4 = $horas[2];
            } elseif ($num_horas >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }

            $q->flag_tarde = false;
            $q->flag_tardanza_grupal = false;
            $q->flag_retiro_anticipado = false;
            $q->flag_llegada_fuera_rango = false;
            $q->hora_salida_oficial = $horaSalidaOficial;

            $q->contador_obs = DB::table('D_ASISTENCIA_OBSERVACION')
                ->where('NUM_DOC', $q->n_dni)
                ->where('FECHA', $q->fecha_asistencia)
                ->where('IDCENTRO_MAC', $q->idmac)
                ->where('flag', 1)
                ->count();
        }

        $modulos = collect($datos)->groupBy('nombre_modulo');

        foreach ($modulos as $modulo => $items) {
            $filaBase = $items->first();
            $esPCM = strtoupper($filaBase->abrev_entidad ?? '') === 'PCM';

            $horaMinimaIngresoModulo = $items->pluck('HORA_1')->filter()->min();
            $horaMaximaSalidaModulo = $items->pluck('ultima_marcacion')->filter()->max();

            if ($esPCM) {
                foreach ($items as $q) {
                    if ($q->HORA_1 && $q->HORA_1 > '08:59:59') {
                        $q->flag_llegada_fuera_rango = true;
                    }
                }
            } else {
                if ($horaMinimaIngresoModulo && $horaMinimaIngresoModulo > '08:59:59') {
                    foreach ($items as $q) {
                        $q->flag_llegada_fuera_rango = true;
                    }
                }
            }

            if ($esPCM) {
                foreach ($items as $q) {
                    if ($q->ultima_marcacion && $q->ultima_marcacion < $horaSalidaOficial) {
                        $q->flag_retiro_anticipado = true;
                    }
                }
            } else {
                if ($horaMaximaSalidaModulo && $horaMaximaSalidaModulo < $horaSalidaOficial) {
                    foreach ($items as $q) {
                        $q->flag_retiro_anticipado = true;
                    }
                }
            }

            if ($esPCM) {
                foreach ($items as $q) {
                    if ($q->HORA_1 && $q->HORA_1 >= '08:16:00') {
                        $q->flag_tarde = true;
                    }
                }
            } else {
                $horaMinimaModulo = $items->pluck('HORA_1')->filter()->min();
                $totalPersonas = $items->count();

                if ($totalPersonas >= 2 && $horaMinimaModulo && $horaMinimaModulo >= '08:16:00') {
                    foreach ($items as $q) {
                        $q->flag_tardanza_grupal = true;
                        $q->flag_tarde = false;
                    }
                } elseif ($totalPersonas === 1) {
                    foreach ($items as $q) {
                        if ($q->HORA_1 && $q->HORA_1 >= '08:16:00') {
                            $q->flag_tarde = true;
                        }
                    }
                }
            }
        }

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
        // Datos del usuario
        $personal = Personal::select(DB::raw('UPPER(CONCAT(APE_PAT," ",APE_MAT,", ",NOMBRE)) AS nombreu'))
            ->where('IDPERSONAL', $request->IDPERSONAL)
            ->first();

        // 🔥 Consulta corregida — sin JOIN que duplica registros
        $observacion = DB::table('D_ASISTENCIA_OBSERVACION')
            ->where('NUM_DOC', $request->NUM_DOC)
            ->where('FECHA', $request->FECHA)
            ->where('IDCENTRO_MAC', $request->IDCENTRO_MAC)
            ->where('flag', 1)
            ->orderBy('id_asistencia_obv')
            ->get();

        // Conteo real
        $count_observaciones = $observacion->count();

        // Variables para el modal
        $fecha_d = $request->FECHA;
        $mac_d   = $request->IDCENTRO_MAC;
        $num_doc = $request->NUM_DOC;

        // Render de la vista
        $html = view('asistencia.modals.md_add_comment_user', compact(
            'personal',
            'observacion',
            'count_observaciones',
            'fecha_d',
            'mac_d',
            'num_doc'
        ))->render();

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
        $request->validate([
            'txt_file' => 'required|file',
        ]);

        $file = $request->file('txt_file');
        $idCentroMac = $this->centro_mac()->idmac;

        $filename = 'asistencia_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.txt';
        $storedPath = $file->storeAs('asistencia-txt', $filename);
        $uploadToken = bin2hex(random_bytes(8));
        Cache::put('upload_progress:' . $uploadToken, 0);
        Cache::put('upload_status:' . $uploadToken, 'queued');

        $jobId = Queue::connection('database')->push(
            new ProcessAsistenciaTxt($storedPath, $idCentroMac, $uploadToken),
            '',
            'asistencia'
        );
        Cache::put('upload_job_id:' . $uploadToken, $jobId);

        return response()->json([
            'success' => true,
            'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            'upload_token' => $uploadToken,
        ]);
    }
    public function store_asistencia_callao(Request $request)
    {
        $request->validate([
            'txt_file' => 'required|file',
        ]);

        $file = $request->file('txt_file');
        $idCentroMac = $this->centro_mac()->idmac;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $filename = 'asistencia_callao_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('asistencia-accdb', $filename);
        $uploadToken = bin2hex(random_bytes(8));
        Cache::put('upload_progress:' . $uploadToken, 0);
        Cache::put('upload_status:' . $uploadToken, 'queued');

        $jobId = Queue::connection('database')->push(
            new ProcessAsistenciaCallao($storedPath, $idCentroMac, $uploadToken, $fechaInicio, $fechaFin),
            '',
            'asistencia'
        );
        Cache::put('upload_job_id:' . $uploadToken, $jobId);

        return response()->json([
            'success' => true,
            'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            'upload_token' => $uploadToken,
        ]);
    }

    public function getUploadProgress(Request $request)
    {
        $token = $request->query('token');
        $key = $token ? 'upload_progress:' . $token : 'upload_progress';
        $progress = Cache::get($key, 0);
        $status = $token ? Cache::get('upload_status:' . $token, 'queued') : 'queued';
        $position = null;

        if ($token) {
            $jobId = Cache::get('upload_job_id:' . $token);
            if ($jobId) {
                $position = DB::table('jobs')
                    ->where('queue', 'asistencia')
                    ->where('id', '<', $jobId)
                    ->count();
            }
        }

        return response()->json([
            'progress' => $progress,
            'status' => $status,
            'position' => $position,
        ]);
    }

    public function cancelUpload(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        Cache::put('upload_cancelled:' . $token, true);
        Cache::put('upload_status:' . $token, 'cancelled');

        $jobId = Cache::get('upload_job_id:' . $token);
        if ($jobId) {
            DB::table('jobs')->where('id', $jobId)->delete();
        }

        return response()->json(['success' => true]);
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
                    'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
                ]);
            }


            // Eliminar también de asistenciatest si existe un registro con mismo DNI y marcación
            Asistenciatest::where('DNI', $dni)
                ->where('marcacion', $marcacion)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
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


    public function md_det_entidad_perso(Request $request)
    {
        $identidad = $request->identidad;

        $mac = $request->mac;

        $view = view('asistencia.modals.md_det_entidad_perso', compact('identidad', 'mac'))->render();

        return response()->json(['html' => $view]);
    }

    public function exportgroup_excel_pr(Request $request)
    {
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');

        $fecha = Carbon::create(null, $request->mes, 1);
        $nombreMES = $fecha->formatLocalized('%B');

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        } else {
            $idmac = $request->mac ?? 0;
        }

        $esTodosLosMacs = ($idmac == 0);
        $name_mac = $esTodosLosMacs ? 'TODOS LOS MACs' : Mac::where('IDCENTRO_MAC', $idmac)->value('NOMBRE_MAC');

        $fecha_inicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
        $fecha_fin = Carbon::parse($request->fecha_fin)->format('Y-m-d');

        $fecha_ini_desc = strftime('%d de %B del %Y', strtotime($fecha_inicio));
        $fecha_fin_desc = strftime('%d de %B del %Y', strtotime($fecha_fin));

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();

        $tipo_desc = '2';
        $fecha_inicial = $fecha_ini_desc;
        $fecha_fin_txt = $fecha_fin_desc;
        $identidad = $request->identidad;
        $datosAgrupados = [];
        $fechasArray = [];
        if ($identidad == '17') {
            // 🔥 SE MANTIENE TU LÓGICA ORIGINAL (NO TOCAMOS PR)
            $nom_ = Personal::from('M_PERSONAL as MP')
                ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                ->select('DPC.NOMBRE_CARGO', DB::raw('CONCAT(MP.APE_PAT," ",MP.APE_MAT,", ",MP.NOMBRE) AS NOMBREU'), 'MP.NUM_DOC', 'MP.IDENTIDAD')
                ->where('MP.IDENTIDAD', 17)
                ->where('MP.IDMAC', $this->centro_mac()->idmac)
                ->get();

            $fechas = CarbonPeriod::create($fecha_inicio, $fecha_fin);
            $fechasArray = [];
            foreach ($fechas as $f) {
                $fechasArray[] = $f->toDateString();
            }

            $datosAgrupados = [];

            foreach ($nom_ as $encabezado) {
                $detalle = $encabezado->asistencias()
                    ->select([
                        'M_ASISTENCIA.FECHA',
                        'M_ASISTENCIA.NUM_DOC',
                        DB::raw('GROUP_CONCAT(DATE_FORMAT(HORA,"%H:%i:%s") ORDER BY HORA) AS HORAS'),
                        DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                    ])
                    ->whereBetween(DB::raw('DATE(M_ASISTENCIA.FECHA)'), [$fecha_inicio, $fecha_fin])
                    ->groupBy('M_ASISTENCIA.NUM_DOC', 'M_ASISTENCIA.FECHA')
                    ->orderBy('M_ASISTENCIA.FECHA', 'asc')
                    ->get();

                foreach ($detalle as $d) {
                    $h = explode(',', $d->HORAS);
                    $d->HORA_1 = $h[0] ?? null;
                    $d->HORA_2 = $h[1] ?? null;
                    $d->HORA_3 = $h[2] ?? null;
                    $d->HORA_4 = $h[3] ?? null;
                }

                $datosAgrupados[] = ['encabezado' => $encabezado, 'detalle' => $detalle];
            }

            $query = [];
        } else {

            // 🔥 USAR MISMO MODELO QUE EXPORT ORIGINAL

            $cerrados = DB::select(
                'CALL db_centro_mac_reporte.SP_REPORTE_ASISTENCIA_DETALLADO(?, ?, ?, ?)',
                [$fecha_inicio, $fecha_fin, $idmac, $identidad ?? 0]
            );

            $abiertos = DB::select(
                'CALL db_centros_mac.SP_ASISTENCIA_DIARIA_MAC_RANGO(?, ?, ?, ?, ?)',
                [$idmac, $fecha_inicio, $fecha_fin, $identidad ?? 0, 1]
            );

            $mapear = function ($q, $estado) {

                // 🔥 MISMA LÓGICA QUE EL ORIGINAL
                if (!empty($q->hora_ingreso) || !empty($q->hora_salida)) {

                    $horas = array_filter([
                        $q->hora_ingreso,
                        $q->salida_refrigerio,
                        $q->ingreso_refrigerio,
                        $q->hora_salida
                    ]);

                    $horas = array_values($horas);
                } else {

                    $horas_raw = explode(',', $q->horas ?? ($q->fecha_biometrico ?? ''));

                    $horas = array_values(array_filter(array_map('trim', $horas_raw)));

                    sort($horas);
                }

                // 🔥 NORMALIZAR
                $horas = array_slice($horas, 0, 4);

                $q->HORA_1 = null;
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = null;

                $count = count($horas);

                if ($count == 1) {
                    $q->HORA_1 = $horas[0];
                } elseif ($count == 2) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_4 = $horas[1];
                } elseif ($count == 3) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_4 = $horas[2];
                } elseif ($count >= 4) {
                    $q->HORA_1 = $horas[0];
                    $q->HORA_2 = $horas[1];
                    $q->HORA_3 = $horas[2];
                    $q->HORA_4 = $horas[3];
                }

                // 🔥 NORMALIZAR CAMPOS (IMPORTANTE)
                $q->NOMBRE_MAC = $q->NOMBRE_MAC ?? $q->centro_mac ?? '';
                $q->ABREV_ENTIDAD = $q->ABREV_ENTIDAD ?? $q->entidad ?? '';
                $q->NOMBREU = $q->NOMBREU ?? $q->colaborador ?? $q->nombre ?? '';
                $q->NUM_DOC = $q->NUM_DOC ?? $q->dni ?? '';
                $q->FECHA = $q->FECHA ?? $q->fecha ?? $q->fecha_asistencia ?? null;
                $q->N_MODULO = $q->N_MODULO ?? $q->nombre_modulo ?? null;

                $q->observaciones = $q->observaciones ?? '';

                $q->contador_obs = isset($q->observaciones) && $q->observaciones != ''
                    ? count(array_filter(explode(';', $q->observaciones)))
                    : 0;

                $q->ESTADO = $estado;

                return $q;
            };

            // 🔥 MAPEAR IGUAL QUE EL ORIGINAL
            $cerrados = array_map(fn($q) => $mapear($q, 'CERRADO'), $cerrados);
            $abiertos = array_map(fn($q) => $mapear($q, 'ABIERTO'), $abiertos);

            // 🔥 UNIR
            $query = array_merge($cerrados, $abiertos);

            // 🔥 ORDENAR
            usort($query, function ($a, $b) {

                if ($a->FECHA != $b->FECHA) {
                    return strcmp($a->FECHA, $b->FECHA);
                }

                if ($a->N_MODULO != $b->N_MODULO) {
                    return strcmp($a->N_MODULO ?? '', $b->N_MODULO ?? '');
                }

                return strcmp($a->NOMBREU ?? '', $b->NOMBREU ?? '');
            });
        }

        return Excel::download(
            new AsistenciaGroupExport(
                $query,
                $name_mac,
                $nombreMES,
                $tipo_desc,
                $fecha_inicial,
                $fecha_fin_txt,
                $hora_1,
                $hora_2,
                $hora_3,
                $hora_4,
                $hora_5,
                $identidad,
                $datosAgrupados,
                $fechasArray
            ),
            'REPORTE ASISTENCIA COMPLETO - ' . $name_mac . ' - ' . strtoupper($nombreMES) . '.xlsx'
        );
    }

    public function exportgroup_excel_general(Request $request)
    {
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        $fecha = Carbon::create(null, $request->mes, 1);
        $nombreMES = $fecha->formatLocalized('%B');
        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();
        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        } else {
            $idmac = $request->mac ?? 0;
        }
        $name_mac = ($idmac == 0) ? 'TODOS LOS MACs' : Mac::where('IDCENTRO_MAC', $idmac)->value('NOMBRE_MAC');
        if ($request->fecha_inicio && $request->fecha_fin) {
            $fecha_inicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fecha_fin = Carbon::parse($request->fecha_fin)->format('Y-m-d');
            $nombreMES = Carbon::parse($fecha_inicio)->formatLocalized('%B');
        } else {
            $fecha_inicio = Carbon::create($request->año, $request->mes, 1)->startOfMonth()->format('Y-m-d');
            $fecha_fin = Carbon::create($request->año, $request->mes, 1)->endOfMonth()->format('Y-m-d');
            $nombreMES = Carbon::create(null, $request->mes, 1)->formatLocalized('%B');
        }
        $identidad = $request->identidad ?? 0;
        $cerrados = DB::select('CALL db_centro_mac_reporte.SP_REPORTE_ASISTENCIA_DETALLADO(?, ?, ?, ?)', [$fecha_inicio, $fecha_fin, $idmac, $identidad]);
        $abiertos = DB::select('CALL db_centros_mac.SP_ASISTENCIA_DIARIA_MAC_RANGO(?, ?, ?, ?, ?)', [$idmac, $fecha_inicio, $fecha_fin, $identidad, 1]);
        $mapear = function ($q, $estado) {
            // =======================
            // 1. SI ES DÍA CERRADO
            // =======================
            if (!empty($q->hora_ingreso) || !empty($q->hora_salida)) {

                $horas = array_filter([
                    $q->hora_ingreso,
                    $q->salida_refrigerio,
                    $q->ingreso_refrigerio,
                    $q->hora_salida
                ]);

                $horas = array_values($horas);

                // =======================
                // 2. SI ES DÍA ABIERTO
                // =======================
            } else {

                $horas_raw = explode(',', $q->horas ?? ($q->fecha_biometrico ?? ''));

                $horas = array_values(array_filter(array_map('trim', $horas_raw)));

                sort($horas); // 🔥 clave
            }

            // =======================
            // 3. NORMALIZAR (AMBOS CASOS)
            // =======================
            $horas = array_slice($horas, 0, 4);

            $q->HORA_1 = null;
            $q->HORA_2 = null;
            $q->HORA_3 = null;
            $q->HORA_4 = null;

            $count = count($horas);

            if ($count == 1) {
                $q->HORA_1 = $horas[0];
            } elseif ($count == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_4 = $horas[1];
            } elseif ($count == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_4 = $horas[2];
            } elseif ($count >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
            $q->NOMBRE_MAC = $q->NOMBRE_MAC ?? $q->centro_mac ?? '';
            $q->ABREV_ENTIDAD = $q->ABREV_ENTIDAD ?? $q->entidad ?? '';
            $q->NOMBREU = $q->NOMBREU ?? $q->colaborador ?? $q->nombre ?? '';
            $q->NUM_DOC = $q->NUM_DOC ?? $q->dni ?? '';
            $q->FECHA = $q->FECHA ?? $q->fecha ?? $q->fecha_asistencia ?? null;
            $q->N_MODULO = $q->N_MODULO ?? $q->nombre_modulo ?? null;
            $q->observaciones = $q->observaciones ?? '';
            $q->contador_obs = isset($q->observaciones) && $q->observaciones != ''
                ? count(array_filter(explode(';', $q->observaciones)))
                : 0;
            $q->ESTADO = $estado;
            return $q;
        };
        $cerrados = array_map(fn($q) => $mapear($q, 'CERRADO'), $cerrados);
        $abiertos = array_map(fn($q) => $mapear($q, 'ABIERTO'), $abiertos);
        $data = array_merge($cerrados, $abiertos);
        usort($data, function ($a, $b) {
            if ($a->FECHA != $b->FECHA) {
                return strcmp($a->FECHA, $b->FECHA);
            }
            if ($a->N_MODULO != $b->N_MODULO) {
                return strcmp($a->N_MODULO ?? '', $b->N_MODULO ?? '');
            }
            return strcmp($a->NOMBREU ?? '', $b->NOMBREU ?? '');
        });
        return Excel::download(
            new AsistenciaGroupExport(
                $data,
                $name_mac,
                $nombreMES,
                '1',
                $fecha_inicio,
                $fecha_fin,
                $hora_1,
                $hora_2,
                $hora_3,
                $hora_4,
                $hora_5,
                $identidad,
                '',
                ''
            ),
            'REPORTE ASISTENCIA COMPLETO - ' . $name_mac . ' - ' . strtoupper($nombreMES) . '.xlsx'
        );
    }
    public function dow_asistencia()
    {
        try {

            $insert = DB::select("CALL  SP_CARGA_ASISTENCIA();");


            return response()->json([
                'status' => true,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
                'data' => $insert,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /*   public function migrarDatos(Request $request)
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
            'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ], 500);
        }
    } */
    public function migrarDatos(Request $request)
    {
        $dbSqlServer = null;
        $dbMysql = null;

        try {
            /* =====================================================
         * CONEXIONES (SOLO B y C)
         * ===================================================== */
            $dbSqlServer = DB::connection('sqlserver'); // B
            $dbMysql     = DB::connection('mysql');     // C (local)

            $dbSqlServer->getPdo();
            $dbMysql->getPdo();

            /* =====================================================
         * LEER PENDIENTES DE SQL SERVER
         * ===================================================== */
            $pendientes = $dbSqlServer->table('MAC_HUANUCO')
                ->where('estado', 0)
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->get();

            if ($pendientes->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
                ]);
            }

            $procesados = 0;

            /* =====================================================
         * INSERTAR EN MYSQL FINAL
         * ===================================================== */
            foreach ($pendientes as $p) {

                $idAsistencia = ($dbMysql->table('m_asistencia')->max('IDASISTENCIA') ?? 0) + 1;

                $correlativoDia = $dbMysql->table('m_asistencia')
                    ->whereDate('FECHA', $p->fecha)
                    ->count() + 1;

                $dbMysql->table('m_asistencia')->insert([
                    'IDASISTENCIA'      => $idAsistencia,
                    'IDTIPO_ASISTENCIA' => 1,
                    'NUM_DOC'           => $p->num_doc,
                    'IDCENTRO_MAC'      => $p->idcentro_mac,
                    'MES'               => $p->mes,
                    'AÑO'               => $p->anio,
                    'FECHA'             => $p->fecha,
                    'HORA'              => $p->hora,
                    'FECHA_BIOMETRICO'  => "{$p->fecha} {$p->hora}",
                    'NUM_BIOMETRICO'    => null,
                    'CORRELATIVO'       => $idAsistencia,
                    'CORRELATIVO_DIA'   => $correlativoDia,
                    'estado'            => 0,
                ]);

                // Marcar SQL Server como procesado
                $dbSqlServer->table('MAC_HUANUCO')
                    ->where('id_staging', $p->id_staging)
                    ->update([
                        'estado' => 1,
                        'fecha_procesado' => now()
                    ]);

                $procesados++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo en cola. El proceso continuara en segundo plano.',
            ], 500);
        }
    }

    public function exportgroup_excel(Request $request)
    {
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        $fecha = Carbon::create(null, $request->mes, 1);
        $nombreMES = $fecha->formatLocalized('%B');
        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();
        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        } else {
            $idmac = $request->mac ?? 0;
        }
        $name_mac = ($idmac == 0) ? 'TODOS LOS MACs' : Mac::where('IDCENTRO_MAC', $idmac)->value('NOMBRE_MAC');
        if ($request->fecha_inicio && $request->fecha_fin) {
            $fecha_inicio = Carbon::parse($request->fecha_inicio)->format('Y-m-d');
            $fecha_fin = Carbon::parse($request->fecha_fin)->format('Y-m-d');
            $nombreMES = Carbon::parse($fecha_inicio)->formatLocalized('%B');
        } else {
            $fecha_inicio = Carbon::create($request->año, $request->mes, 1)->startOfMonth()->format('Y-m-d');
            $fecha_fin = Carbon::create($request->año, $request->mes, 1)->endOfMonth()->format('Y-m-d');
            $nombreMES = Carbon::create(null, $request->mes, 1)->formatLocalized('%B');
        }
        $identidad = $request->identidad ?? 0;
        $cerrados = DB::select('CALL db_centro_mac_reporte.SP_REPORTE_ASISTENCIA_DETALLADO(?, ?, ?, ?)', [$fecha_inicio, $fecha_fin, $idmac, $identidad]);
        $abiertos = DB::select('CALL db_centros_mac.SP_ASISTENCIA_DIARIA_MAC_RANGO(?, ?, ?, ?, ?)', [$idmac, $fecha_inicio, $fecha_fin, $identidad, 1]);
        $mapear = function ($q, $estado) {
            // =======================
            // 1. SI ES DÍA CERRADO
            // =======================
            if (!empty($q->hora_ingreso) || !empty($q->hora_salida)) {

                $horas = array_filter([
                    $q->hora_ingreso,
                    $q->salida_refrigerio,
                    $q->ingreso_refrigerio,
                    $q->hora_salida
                ]);

                $horas = array_values($horas);

                // =======================
                // 2. SI ES DÍA ABIERTO
                // =======================
            } else {

                $horas_raw = explode(',', $q->horas ?? ($q->fecha_biometrico ?? ''));

                $horas = array_values(array_filter(array_map('trim', $horas_raw)));

                sort($horas); // 🔥 clave
            }

            // =======================
            // 3. NORMALIZAR (AMBOS CASOS)
            // =======================
            $horas = array_slice($horas, 0, 4);

            $q->HORA_1 = null;
            $q->HORA_2 = null;
            $q->HORA_3 = null;
            $q->HORA_4 = null;

            $count = count($horas);

            if ($count == 1) {
                $q->HORA_1 = $horas[0];
            } elseif ($count == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_4 = $horas[1];
            } elseif ($count == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_4 = $horas[2];
            } elseif ($count >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
            $q->NOMBRE_MAC = $q->NOMBRE_MAC ?? $q->centro_mac ?? '';
            $q->ABREV_ENTIDAD = $q->ABREV_ENTIDAD ?? $q->entidad ?? '';
            $q->NOMBREU = $q->NOMBREU ?? $q->colaborador ?? $q->nombre ?? '';
            $q->NUM_DOC = $q->NUM_DOC ?? $q->dni ?? '';
            $q->FECHA = $q->FECHA ?? $q->fecha ?? $q->fecha_asistencia ?? null;
            $q->N_MODULO = $q->N_MODULO ?? $q->nombre_modulo ?? null;
            $q->observaciones = $q->observaciones ?? '';
            $q->contador_obs = isset($q->observaciones) && $q->observaciones != ''
                ? count(array_filter(explode(';', $q->observaciones)))
                : 0;
            $q->ESTADO = $estado;
            return $q;
        };
        $cerrados = array_map(fn($q) => $mapear($q, 'CERRADO'), $cerrados);
        $abiertos = array_map(fn($q) => $mapear($q, 'ABIERTO'), $abiertos);
        $data = array_merge($cerrados, $abiertos);
        usort($data, function ($a, $b) {
            if ($a->FECHA != $b->FECHA) {
                return strcmp($a->FECHA, $b->FECHA);
            }
            if ($a->N_MODULO != $b->N_MODULO) {
                return strcmp($a->N_MODULO ?? '', $b->N_MODULO ?? '');
            }
            return strcmp($a->NOMBREU ?? '', $b->NOMBREU ?? '');
        });
        return Excel::download(
            new AsistenciaGroupExport(
                $data,
                $name_mac,
                $nombreMES,
                '1',
                $fecha_inicio,
                $fecha_fin,
                $hora_1,
                $hora_2,
                $hora_3,
                $hora_4,
                $hora_5,
                $identidad,
                '',
                ''
            ),
            'REPORTE ASISTENCIA COMPLETO - ' . $name_mac . ' - ' . strtoupper($nombreMES) . '.xlsx'
        );
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

    private function asignacionIdMac(Request $request): int
    {
        $user = auth()->user();

        if ($user->hasRole(['Administrador', 'Moderador'])) {
            return (int) ($request->input('mac') ?: $user->idcentro_mac);
        }

        return (int) $user->idcentro_mac;
    }

    private function asignacionFechas(Request $request): array
    {
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        if ($fechaInicio > $fechaFin) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        return [$fechaInicio, $fechaFin];
    }

    private function asignacionNombreMac(int $idmac): string
    {
        return DB::table('m_centro_mac')
            ->where('IDCENTRO_MAC', $idmac)
            ->value('NOMBRE_MAC') ?: 'Centro MAC';
    }

    private function asignacionMacs()
    {
        $user = auth()->user();

        if ($user->hasRole(['Administrador', 'Moderador'])) {
            return DB::table('m_centro_mac')
                ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
                ->orderBy('NOMBRE_MAC')
                ->get();
        }

        return DB::table('m_centro_mac')
            ->select('IDCENTRO_MAC as id', 'NOMBRE_MAC as nom')
            ->where('IDCENTRO_MAC', $user->idcentro_mac)
            ->get();
    }

    private function aplicarFiltroPersonalMac($query, int $idmac)
    {
        return $query->where(function ($q) use ($idmac) {
            $q->where('p.IDMAC', $idmac)
                ->orWhereExists(function ($sq) use ($idmac) {
                    $sq->select(DB::raw(1))
                        ->from('d_personal_mac as dpm')
                        ->whereColumn('dpm.idpersonal', 'p.IDPERSONAL')
                        ->where('dpm.idcentro_mac', $idmac)
                        ->whereIn('dpm.STATUS', [1, 2]);
                });
        });
    }

    private function personalPerteneceMac(int $idpersonal, int $idmac): bool
    {
        $query = DB::table('m_personal as p')
            ->where('p.IDPERSONAL', $idpersonal);

        return $this->aplicarFiltroPersonalMac($query, $idmac)->exists();
    }

    private function personalParaAsignacion(int $idmac, ?string $term = null, int $limit = 25)
    {
        $query = DB::table('m_personal as p')
            ->leftJoin('m_entidad as e', 'e.IDENTIDAD', '=', 'p.IDENTIDAD')
            ->select(
                'p.IDPERSONAL',
                'p.NUM_DOC',
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo'),
                DB::raw('COALESCE(e.ABREV_ENTIDAD, e.NOMBRE_ENTIDAD, "-") as entidad')
            )
            ->whereIn('p.FLAG', [1, 2, 3])
            ->when($term, function ($q) use ($term) {
                $like = '%' . trim($term) . '%';

                $q->where(function ($sub) use ($like) {
                    $sub->where('p.NUM_DOC', 'like', $like)
                        ->orWhere('p.NOMBRE', 'like', $like)
                        ->orWhere('p.APE_PAT', 'like', $like)
                        ->orWhere('p.APE_MAT', 'like', $like)
                        ->orWhereRaw('CONCAT(p.APE_PAT, " ", p.APE_MAT, " ", p.NOMBRE) like ?', [$like]);
                });
            })
            ->orderBy('p.APE_PAT')
            ->orderBy('p.APE_MAT')
            ->orderBy('p.NOMBRE')
            ->limit($limit);

        return $this->aplicarFiltroPersonalMac($query, $idmac)->get();
    }

    private function horariosAsignadosData(int $idmac)
    {
        $query = DB::table('d_personal_asistencia as dpa')
            ->join('m_personal as p', 'p.IDPERSONAL', '=', 'dpa.idpersonal')
            ->leftJoin('m_entidad as e', 'e.IDENTIDAD', '=', 'p.IDENTIDAD')
            ->select(
                'dpa.id',
                'dpa.idpersonal',
                'dpa.hora_ingreso',
                'dpa.hora_salida',
                'dpa.fecha_inicio',
                'dpa.fecha_fin',
                'dpa.sin_fin',
                'p.NUM_DOC',
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo'),
                DB::raw('COALESCE(e.ABREV_ENTIDAD, e.NOMBRE_ENTIDAD, "-") as entidad')
            )
            ->orderBy('dpa.fecha_inicio', 'desc')
            ->orderBy('nombre_completo');

        return $this->aplicarFiltroPersonalMac($query, $idmac)->get();
    }

    private function personasAsignadasData(int $idmac)
    {
        $query = DB::table('d_personal_asistencia as dpa')
            ->join('m_personal as p', 'p.IDPERSONAL', '=', 'dpa.idpersonal')
            ->select(
                'p.IDPERSONAL',
                'p.NUM_DOC',
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo')
            )
            ->groupBy(
                'p.IDPERSONAL',
                'p.NUM_DOC',
                'p.APE_PAT',
                'p.APE_MAT',
                'p.NOMBRE'
            )
            ->orderBy('p.APE_PAT')
            ->orderBy('p.APE_MAT')
            ->orderBy('p.NOMBRE');

        return $this->aplicarFiltroPersonalMac($query, $idmac)->get();
    }

    private function asignacionDiasEspecialesDisponible(): bool
    {
        return Schema::hasTable('d_personal_asistencia_dia');
    }

    private function asignacionFeriadosDisponible(): bool
    {
        return Schema::hasTable('feriados');
    }

    private function asignacionFeriadosData(int $idmac, string $fechaInicio, string $fechaFin)
    {
        if (!$this->asignacionFeriadosDisponible()) {
            return collect();
        }

        return DB::table('feriados')
            ->select('id', 'name', 'fecha', 'id_centromac')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where(function ($q) use ($idmac) {
                $q->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->orderByRaw('CASE WHEN id_centromac = ? THEN 1 ELSE 0 END', [$idmac])
            ->get()
            ->keyBy(function ($feriado) {
                return Carbon::parse($feriado->fecha)->format('Y-m-d');
            });
    }

    private function asignacionEsFeriado(int $idmac, string $fecha): bool
    {
        if (!$this->asignacionFeriadosDisponible()) {
            return false;
        }

        return DB::table('feriados')
            ->whereDate('fecha', $fecha)
            ->where(function ($q) use ($idmac) {
                $q->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->exists();
    }

    private function horarioAsignacionEnMac(int $idAsignacion, int $idmac)
    {
        $horario = DB::table('d_personal_asistencia')
            ->where('id', $idAsignacion)
            ->first();

        if (!$horario || !$this->personalPerteneceMac((int) $horario->idpersonal, $idmac)) {
            return null;
        }

        return $horario;
    }

    private function diasEspecialesAsignacionData(int $idAsignacion, int $idmac)
    {
        if (!$this->asignacionDiasEspecialesDisponible()) {
            return collect();
        }

        if (!$this->horarioAsignacionEnMac($idAsignacion, $idmac)) {
            return collect();
        }

        return DB::table('d_personal_asistencia_dia')
            ->where('id_asignacion', $idAsignacion)
            ->where('activo', 1)
            ->orderBy('fecha', 'desc')
            ->get();
    }

    private function calendarioAsignacionData($horario, int $idmac, string $mes): array
    {
        $month = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $inicioMes = $month->copy()->startOfMonth();
        $finMes = $month->copy()->endOfMonth();
        $inicioAsignacion = Carbon::parse($horario->fecha_inicio)->startOfDay();
        $finAsignacion = ((int) $horario->sin_fin === 1 || !$horario->fecha_fin)
            ? $finMes->copy()
            : Carbon::parse($horario->fecha_fin)->endOfDay();

        $inicio = $inicioAsignacion->greaterThan($inicioMes) ? $inicioAsignacion : $inicioMes;
        $fin = $finAsignacion->lessThan($finMes) ? $finAsignacion : $finMes;

        if ($inicio->greaterThan($fin)) {
            return [
                'normales' => [],
                'sabados' => [],
                'feriados' => [],
            ];
        }

        $feriados = $this->asignacionFeriadosData($idmac, $inicio->format('Y-m-d'), $fin->format('Y-m-d'));
        $diasEspeciales = $this->asignacionDiasEspecialesDisponible()
            ? DB::table('d_personal_asistencia_dia')
                ->where('id_asignacion', $horario->id)
                ->where('activo', 1)
                ->whereBetween('fecha', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
                ->get()
                ->keyBy(function ($dia) {
                    return Carbon::parse($dia->fecha)->format('Y-m-d');
                })
            : collect();

        $nombresDia = [
            Carbon::MONDAY => 'Lun',
            Carbon::TUESDAY => 'Mar',
            Carbon::WEDNESDAY => 'Mie',
            Carbon::THURSDAY => 'Jue',
            Carbon::FRIDAY => 'Vie',
            Carbon::SATURDAY => 'Sab',
        ];
        $normales = [];
        $sabados = [];

        foreach (CarbonPeriod::create($inicio, $fin) as $dia) {
            $fecha = $dia->format('Y-m-d');
            $feriado = $feriados->get($fecha);
            $base = [
                'fecha' => $fecha,
                'dia' => $dia->format('d'),
                'label' => $dia->format('d/m'),
                'nombre_dia' => $nombresDia[$dia->dayOfWeek] ?? '',
                'es_feriado' => (bool) $feriado,
                'feriado' => $feriado ? $feriado->name : null,
            ];

            if ($dia->isWeekday()) {
                $normales[] = $base;
                continue;
            }

            if ($dia->isSaturday()) {
                $especial = $diasEspeciales->get($fecha);
                $sabados[] = array_merge($base, [
                    'seleccionado' => (bool) $especial,
                    'hora_ingreso' => $especial ? substr($especial->hora_ingreso, 0, 5) : '08:15',
                    'hora_salida' => $especial ? substr($especial->hora_salida, 0, 5) : '13:30',
                ]);
            }
        }

        return [
            'normales' => $normales,
            'sabados' => $sabados,
            'feriados' => $feriados->values(),
        ];
    }

    private function formatoMinutos(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return sprintf('%02d:%02d', $horas, $resto);
    }

    private function asignacionConsumosAprobadosData(int $idmac, string $fechaInicio, string $fechaFin, ?int $idpersonal = null)
    {
        if (!Schema::hasTable('d_personal_asistencia_consumo') || !Schema::hasTable('d_personal_asistencia_consumo_det')) {
            return collect();
        }

        return DB::table('d_personal_asistencia_consumo_det as det')
            ->join('d_personal_asistencia_consumo as c', 'c.id', '=', 'det.id_consumo')
            ->where('c.idcentro_mac', $idmac)
            ->where('c.estado', 'APROBADO')
            ->where('c.motivo', 'COMPENSACION DE HORAS / DIA')
            ->whereBetween('det.fecha_origen', [$fechaInicio, $fechaFin])
            ->when($idpersonal, function ($q) use ($idpersonal) {
                $q->where('c.idpersonal', $idpersonal);
            })
            ->select(
                'c.idpersonal',
                'det.fecha_origen',
                DB::raw('SUM(det.minutos_usados) as minutos_usados')
            )
            ->groupBy('c.idpersonal', 'det.fecha_origen')
            ->get()
            ->keyBy(function ($row) {
                return $row->idpersonal . '|' . Carbon::parse($row->fecha_origen)->format('Y-m-d');
            });
    }

    private function asignacionReporteData(int $idmac, string $fechaInicio, string $fechaFin, ?int $idpersonal = null)
    {
        $usaDiasEspeciales = $this->asignacionDiasEspecialesDisponible();
        $ingresoProgramado = $usaDiasEspeciales
            ? 'COALESCE(dpd.hora_ingreso, dpa.hora_ingreso)'
            : 'dpa.hora_ingreso';
        $salidaProgramada = $usaDiasEspeciales
            ? 'COALESCE(dpd.hora_salida, dpa.hora_salida)'
            : 'dpa.hora_salida';
        $tipoProgramacion = $usaDiasEspeciales
            ? 'CASE WHEN dpd.id IS NOT NULL THEN "Dia especial" ELSE "Lunes a viernes" END'
            : '"Lunes a viernes"';

        $query = DB::table('d_personal_asistencia as dpa')
            ->join('m_personal as p', 'p.IDPERSONAL', '=', 'dpa.idpersonal')
            ->join('m_asistencia as a', function ($join) {
                $join->on('a.NUM_DOC', '=', 'p.NUM_DOC')
                    ->whereColumn('a.FECHA', '>=', 'dpa.fecha_inicio')
                    ->whereRaw('(dpa.sin_fin = 1 OR dpa.fecha_fin IS NULL OR a.FECHA <= dpa.fecha_fin)');
            })
            ->leftJoin('m_centro_mac as cm', 'cm.IDCENTRO_MAC', '=', 'a.IDCENTRO_MAC')
            ->leftJoin('m_entidad as e', 'e.IDENTIDAD', '=', 'p.IDENTIDAD')
            ->leftJoin('m_modulo as m', 'm.IDMODULO', '=', 'p.IDMODULO')
            ->where('a.IDCENTRO_MAC', $idmac)
            ->whereBetween('a.FECHA', [$fechaInicio, $fechaFin])
            ->whereRaw('DAYOFWEEK(a.FECHA) <> 1')
            ->when($idpersonal, function ($q) use ($idpersonal) {
                $q->where('p.IDPERSONAL', $idpersonal);
            });

        if ($this->asignacionFeriadosDisponible()) {
            $query->whereNotExists(function ($sq) use ($idmac) {
                $sq->select(DB::raw(1))
                    ->from('feriados as fer')
                    ->whereColumn('fer.fecha', 'a.FECHA')
                    ->where(function ($q) use ($idmac) {
                        $q->where('fer.id_centromac', $idmac)
                            ->orWhereNull('fer.id_centromac');
                    });
            });
        }

        if ($usaDiasEspeciales) {
            $query->leftJoin('d_personal_asistencia_dia as dpd', function ($join) {
                $join->on('dpd.id_asignacion', '=', 'dpa.id')
                    ->whereColumn('dpd.fecha', 'a.FECHA')
                    ->where('dpd.activo', 1);
            })
                ->whereRaw('(DAYOFWEEK(a.FECHA) BETWEEN 2 AND 6 OR dpd.id IS NOT NULL)');
        } else {
            $query->whereRaw('DAYOFWEEK(a.FECHA) BETWEEN 2 AND 6');
        }

        $query->select(
                'dpa.id as id_asignacion',
                'dpa.idpersonal',
                DB::raw($ingresoProgramado . ' as ingreso_programado'),
                DB::raw($salidaProgramada . ' as salida_programada'),
                DB::raw($tipoProgramacion . ' as tipo_programacion'),
                'dpa.fecha_inicio as asignacion_inicio',
                'dpa.fecha_fin as asignacion_fin',
                'dpa.sin_fin',
                'p.NUM_DOC',
                'a.FECHA',
                'a.IDCENTRO_MAC',
                DB::raw('DAYOFWEEK(a.FECHA) as dia_semana'),
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo'),
                DB::raw('COALESCE(cm.NOMBRE_MAC, "-") as nombre_mac'),
                DB::raw('COALESCE(e.ABREV_ENTIDAD, e.NOMBRE_ENTIDAD, "-") as entidad'),
                DB::raw('COALESCE(m.N_MODULO, p.NUMERO_MODULO, "-") as modulo'),
                DB::raw('MIN(a.HORA) as asistencia_ingreso'),
                DB::raw('CASE WHEN COUNT(a.IDASISTENCIA) > 1 THEN MAX(a.HORA) ELSE NULL END as asistencia_salida'),
                DB::raw('COUNT(a.IDASISTENCIA) as total_marcaciones'),
                DB::raw('CASE WHEN COUNT(a.IDASISTENCIA) > 1 THEN GREATEST(TIMESTAMPDIFF(MINUTE, CONCAT(a.FECHA, " ", ' . $salidaProgramada . '), CONCAT(a.FECHA, " ", MAX(a.HORA))), 0) ELSE 0 END as minutos_extra')
            )
            ->groupBy(
                'dpa.id',
                'dpa.idpersonal',
                'dpa.hora_ingreso',
                'dpa.hora_salida',
                'dpa.fecha_inicio',
                'dpa.fecha_fin',
                'dpa.sin_fin',
                'p.NUM_DOC',
                'p.NOMBRE',
                'p.APE_PAT',
                'p.APE_MAT',
                'p.NUMERO_MODULO',
                'a.FECHA',
                'a.IDCENTRO_MAC',
                'cm.NOMBRE_MAC',
                'e.ABREV_ENTIDAD',
                'e.NOMBRE_ENTIDAD',
                'm.N_MODULO'
            );

        if ($usaDiasEspeciales) {
            $query->groupBy('dpd.id', 'dpd.hora_ingreso', 'dpd.hora_salida');
        }

        $rows = $query
            ->orderBy('a.FECHA', 'desc')
            ->orderBy('nombre_completo')
            ->get();
        $consumosAprobados = $this->asignacionConsumosAprobadosData($idmac, $fechaInicio, $fechaFin, $idpersonal);

        foreach ($rows as $row) {
            $row->minutos_extra = (int) $row->minutos_extra;
            $fecha = Carbon::parse($row->FECHA)->format('Y-m-d');
            $consumo = $consumosAprobados->get($row->idpersonal . '|' . $fecha);
            $row->minutos_usados_aprobados = (int) ($consumo->minutos_usados ?? 0);
            $row->minutos_saldo_disponible = max(0, $row->minutos_extra - $row->minutos_usados_aprobados);
            $row->horas_extra = $this->formatoMinutos($row->minutos_extra);
            $row->horas_usadas_aprobadas = $this->formatoMinutos($row->minutos_usados_aprobados);
            $row->horas_saldo_disponible = $this->formatoMinutos($row->minutos_saldo_disponible);
        }

        return $rows;
    }

    private function resumenAsignacionReporte($rows): array
    {
        $totalMinutos = (int) collect($rows)->sum('minutos_extra');
        $minutosUsados = (int) collect($rows)->sum('minutos_usados_aprobados');
        $minutosSaldo = (int) collect($rows)->sum('minutos_saldo_disponible');

        return [
            'total_minutos' => $totalMinutos,
            'total_horas' => $this->formatoMinutos($totalMinutos),
            'usadas_minutos' => $minutosUsados,
            'usadas_horas' => $this->formatoMinutos($minutosUsados),
            'saldo_minutos' => $minutosSaldo,
            'saldo_horas' => $this->formatoMinutos($minutosSaldo),
            'registros_extra' => collect($rows)->where('minutos_extra', '>', 0)->count(),
            'personas' => collect($rows)->pluck('idpersonal')->unique()->count(),
            'registros' => collect($rows)->count(),
        ];
    }

    // ASIGNACION PERSONAL PCM ASISTENCIA
    public function asignacion(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        $name_mac = $this->asignacionNombreMac($idmac);
        $macs = $this->asignacionMacs();
        [$fecha_inicio, $fecha_fin] = $this->asignacionFechas($request);

        return view('asistencia.asignacion.index', compact(
            'idmac',
            'name_mac',
            'macs',
            'fecha_inicio',
            'fecha_fin'
        ));
    }

    public function tb_asignacion_horarios(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        $horarios = $this->horariosAsignadosData($idmac);

        return view('asistencia.asignacion.tablas.tb_horarios', compact('horarios'));
    }

    public function tb_asignacion_dias_especiales(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|integer',
        ]);

        $idmac = $this->asignacionIdMac($request);
        $idAsignacion = (int) $request->input('id_asignacion');
        $disponible = $this->asignacionDiasEspecialesDisponible();
        $dias = collect();

        if ($disponible) {
            $dias = $this->diasEspecialesAsignacionData($idAsignacion, $idmac);
        }

        return view('asistencia.asignacion.tablas.tb_dias_especiales', compact(
            'dias',
            'disponible'
        ));
    }

    public function calendario_asignacion_horario(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|integer',
            'mes' => 'required|date_format:Y-m',
        ]);

        $idmac = $this->asignacionIdMac($request);
        $horario = $this->horarioAsignacionEnMac((int) $request->input('id_asignacion'), $idmac);

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la asignacion del horario.',
            ], 404);
        }

        return response()->json(array_merge([
            'success' => true,
        ], $this->calendarioAsignacionData($horario, $idmac, $request->input('mes'))));
    }

    public function personas_asignadas(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        $personas = $this->personasAsignadasData($idmac)
            ->map(function ($p) {
                return [
                    'id' => $p->IDPERSONAL,
                    'text' => $p->NUM_DOC . ' - ' . $p->nombre_completo,
                ];
            });

        return response()->json(['results' => $personas]);
    }

    public function tb_asignacion_reporte(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        [$fechaInicio, $fechaFin] = $this->asignacionFechas($request);
        $idpersonal = $request->input('idpersonal') ? (int) $request->input('idpersonal') : null;

        $rows = $this->asignacionReporteData($idmac, $fechaInicio, $fechaFin, $idpersonal);
        $summary = $this->resumenAsignacionReporte($rows);

        return view('asistencia.asignacion.tablas.tb_reporte', compact(
            'rows',
            'summary',
            'fechaInicio',
            'fechaFin'
        ));
    }

    private function asignacionRegimenLaboral($tvlId, ?string $otro = null): string
    {
        return match ((int) $tvlId) {
            1 => 'Decreto legislativo 1057-CAS',
            2 => 'Decreto Legislativo N°276',
            3 => 'Decreto Legislativo N°728',
            4 => 'Servicios no Personales-SNP',
            5 => 'OS',
            6 => 'Tercerización',
            7 => $otro ?: 'OTRO',
            default => '-',
        };
    }

    private function asignacionNombreDia(Carbon $fecha): string
    {
        return [
            Carbon::MONDAY => 'LUNES',
            Carbon::TUESDAY => 'MARTES',
            Carbon::WEDNESDAY => 'MIERCOLES',
            Carbon::THURSDAY => 'JUEVES',
            Carbon::FRIDAY => 'VIERNES',
            Carbon::SATURDAY => 'SABADO',
            Carbon::SUNDAY => 'DOMINGO',
        ][$fecha->dayOfWeek] ?? '';
    }

    private function asignacionFechaLarga(string $fecha): string
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Setiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $date = Carbon::parse($fecha);

        return $date->format('d') . ' de ' . $meses[$date->month] . ' ' . $date->format('Y');
    }

    private function minutosJornadaNeta(?string $horaInicio, ?string $horaFin, string $fecha): int
    {
        if (!$horaInicio || !$horaFin) {
            return 0;
        }

        $inicio = Carbon::parse($fecha . ' ' . $horaInicio);
        $fin = Carbon::parse($fecha . ' ' . $horaFin);

        if ($fin->lessThanOrEqualTo($inicio)) {
            return 0;
        }

        $minutos = $inicio->diffInMinutes($fin);

        if ($minutos >= 360 && !Carbon::parse($fecha)->isSaturday()) {
            $minutos -= 60;
        }

        return max(0, $minutos);
    }

    private function asignacionPermisosAprobadosData(int $idmac, string $fechaInicio, string $fechaFin, ?int $idpersonal = null)
    {
        if (!Schema::hasTable('d_personal_asistencia_consumo')) {
            return collect();
        }

        $tieneRango = Schema::hasColumn('d_personal_asistencia_consumo', 'fecha_inicio_consumo')
            && Schema::hasColumn('d_personal_asistencia_consumo', 'fecha_fin_consumo');
        $select = [
            'c.idpersonal',
            'c.fecha_consumo',
            'c.tipo_consumo',
            'c.hora_inicio',
            'c.hora_fin',
            'c.motivo',
        ];

        if ($tieneRango) {
            $select[] = DB::raw('COALESCE(c.fecha_inicio_consumo, c.fecha_consumo) as fecha_inicio_consumo');
            $select[] = DB::raw('COALESCE(c.fecha_fin_consumo, c.fecha_consumo) as fecha_fin_consumo');
        }

        if (Schema::hasColumn('d_personal_asistencia_consumo', 'observacion')) {
            $select[] = 'c.observacion';
        }

        $permisos = DB::table('d_personal_asistencia_consumo as c')
            ->select($select)
            ->where('c.idcentro_mac', $idmac)
            ->where('c.estado', 'APROBADO')
            ->when($tieneRango, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereRaw('COALESCE(c.fecha_inicio_consumo, c.fecha_consumo) <= ?', [$fechaFin])
                    ->whereRaw('COALESCE(c.fecha_fin_consumo, c.fecha_consumo) >= ?', [$fechaInicio]);
            }, function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('c.fecha_consumo', [$fechaInicio, $fechaFin]);
            })
            ->when($idpersonal, function ($q) use ($idpersonal) {
                $q->where('c.idpersonal', $idpersonal);
            })
            ->get();

        if (!$tieneRango) {
            return $permisos->groupBy(function ($row) {
                return $row->idpersonal . '|' . Carbon::parse($row->fecha_consumo)->format('Y-m-d');
            });
        }

        $expandido = collect();

        foreach ($permisos as $permiso) {
            $inicio = Carbon::parse(max($fechaInicio, Carbon::parse($permiso->fecha_inicio_consumo)->format('Y-m-d')));
            $fin = Carbon::parse(min($fechaFin, Carbon::parse($permiso->fecha_fin_consumo)->format('Y-m-d')));

            foreach (CarbonPeriod::create($inicio, $fin) as $dia) {
                $item = clone $permiso;
                $item->fecha_consumo = $dia->format('Y-m-d');
                $expandido->push($item);
            }
        }

        return $expandido->groupBy(function ($row) {
            return $row->idpersonal . '|' . Carbon::parse($row->fecha_consumo)->format('Y-m-d');
        });
    }

    private function asignacionReporteAsistenciaData(int $idmac, string $fechaInicio, string $fechaFin, ?int $idpersonal = null)
    {
        $query = DB::table('d_personal_asistencia as dpa')
            ->join('m_personal as p', 'p.IDPERSONAL', '=', 'dpa.idpersonal')
            ->leftJoin('D_PERSONAL_CARGO as dpc', 'dpc.IDCARGO_PERSONAL', '=', 'p.IDCARGO_PERSONAL')
            ->leftJoin('m_modulo as m', 'm.IDMODULO', '=', 'p.IDMODULO')
            ->select(
                'dpa.id',
                'dpa.idpersonal',
                'dpa.hora_ingreso',
                'dpa.hora_salida',
                'dpa.fecha_inicio',
                'dpa.fecha_fin',
                'dpa.sin_fin',
                'p.NUM_DOC',
                'p.TVL_ID',
                'p.TVL_OTRO',
                DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo'),
                DB::raw('COALESCE(dpc.NOMBRE_CARGO, p.DLP_CARGO, m.N_MODULO, p.NUMERO_MODULO, "-") as cargo')
            )
            ->where('dpa.fecha_inicio', '<=', $fechaFin)
            ->whereRaw('(dpa.sin_fin = 1 OR dpa.fecha_fin IS NULL OR dpa.fecha_fin >= ?)', [$fechaInicio])
            ->when($idpersonal, function ($q) use ($idpersonal) {
                $q->where('dpa.idpersonal', $idpersonal);
            })
            ->orderBy('p.APE_PAT')
            ->orderBy('p.APE_MAT')
            ->orderBy('p.NOMBRE');

        $asignaciones = $this->aplicarFiltroPersonalMac($query, $idmac)->get();

        if ($asignaciones->isEmpty()) {
            return collect();
        }

        $docs = $asignaciones->pluck('NUM_DOC')->filter()->unique()->values();
        $asistencias = DB::table('m_asistencia')
            ->where('IDCENTRO_MAC', $idmac)
            ->whereIn('NUM_DOC', $docs->all())
            ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
            ->select(
                'NUM_DOC',
                'FECHA',
                DB::raw('MIN(HORA) as ingreso_real'),
                DB::raw('CASE WHEN COUNT(IDASISTENCIA) > 1 THEN MAX(HORA) ELSE NULL END as salida_real'),
                DB::raw('COUNT(IDASISTENCIA) as total_marcaciones')
            )
            ->groupBy('NUM_DOC', 'FECHA')
            ->get()
            ->keyBy(function ($row) {
                return $row->NUM_DOC . '|' . Carbon::parse($row->FECHA)->format('Y-m-d');
            });

        $diasEspeciales = collect();
        if ($this->asignacionDiasEspecialesDisponible()) {
            $diasEspeciales = DB::table('d_personal_asistencia_dia')
                ->whereIn('id_asignacion', $asignaciones->pluck('id')->all())
                ->where('activo', 1)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get()
                ->keyBy(function ($row) {
                    return $row->id_asignacion . '|' . Carbon::parse($row->fecha)->format('Y-m-d');
                });
        }

        $feriados = $this->asignacionFeriadosData($idmac, $fechaInicio, $fechaFin);
        $permisos = $this->asignacionPermisosAprobadosData($idmac, $fechaInicio, $fechaFin, $idpersonal);
        $nombreMac = $this->asignacionNombreMac($idmac);
        $rows = collect();

        foreach ($asignaciones as $asignacion) {
            $inicio = Carbon::parse(max($fechaInicio, Carbon::parse($asignacion->fecha_inicio)->format('Y-m-d')));
            $finAsignacion = ((int) $asignacion->sin_fin === 1 || !$asignacion->fecha_fin)
                ? $fechaFin
                : Carbon::parse($asignacion->fecha_fin)->format('Y-m-d');
            $fin = Carbon::parse(min($fechaFin, $finAsignacion));

            if ($inicio->greaterThan($fin)) {
                continue;
            }

            foreach (CarbonPeriod::create($inicio, $fin) as $dia) {
                $fecha = $dia->format('Y-m-d');

                if ($dia->isSunday() || $feriados->has($fecha)) {
                    continue;
                }

                $especial = $diasEspeciales->get($asignacion->id . '|' . $fecha);

                if ($dia->isSaturday() && !$especial) {
                    continue;
                }

                $ingresoProgramado = $especial ? $especial->hora_ingreso : $asignacion->hora_ingreso;
                $salidaProgramada = $especial ? $especial->hora_salida : $asignacion->hora_salida;
                $asistencia = $asistencias->get($asignacion->NUM_DOC . '|' . $fecha);
                $permisosDia = $permisos->get($asignacion->idpersonal . '|' . $fecha, collect());
                $observaciones = [];

                if (!$asistencia) {
                    $observaciones[] = 'SIN REGISTRO';
                } elseif (!$asistencia->salida_real) {
                    $observaciones[] = 'SIN MARCACION DE SALIDA';
                }

                if ($permisosDia->isNotEmpty()) {
                    foreach ($permisosDia as $permiso) {
                        $texto = $permiso->motivo ?: 'PERMISO';
                        if (!empty($permiso->observacion)) {
                            $texto .= ': ' . $permiso->observacion;
                        }
                        $observaciones[] = $texto;
                    }
                }

                $minutosProgramados = $this->minutosJornadaNeta($ingresoProgramado, $salidaProgramada, $fecha);
                $minutosTrabajados = $asistencia && $asistencia->salida_real
                    ? $this->minutosJornadaNeta($asistencia->ingreso_real, $asistencia->salida_real, $fecha)
                    : 0;

                $rows->push((object) [
                    'idpersonal' => $asignacion->idpersonal,
                    'dni' => $asignacion->NUM_DOC,
                    'nombre_completo' => $asignacion->nombre_completo,
                    'regimen_laboral' => $this->asignacionRegimenLaboral($asignacion->TVL_ID, $asignacion->TVL_OTRO),
                    'centro_mac' => $nombreMac,
                    'cargo' => $asignacion->cargo,
                    'dia' => $this->asignacionNombreDia($dia),
                    'fecha' => $fecha,
                    'fecha_excel' => $dia->format('d/m/Y'),
                    'ingreso_programado' => substr($ingresoProgramado, 0, 5),
                    'ingreso_real' => $asistencia && $asistencia->ingreso_real ? substr($asistencia->ingreso_real, 0, 5) : '',
                    'salida_programada' => substr($salidaProgramada, 0, 5),
                    'salida_real' => $asistencia && $asistencia->salida_real ? substr($asistencia->salida_real, 0, 5) : '',
                    'minutos_programados' => $minutosProgramados,
                    'minutos_trabajados' => $minutosTrabajados,
                    'horas_programadas' => $this->formatoMinutos($minutosProgramados),
                    'horas_trabajadas' => $this->formatoMinutos($minutosTrabajados),
                    'observaciones' => implode(' | ', array_unique($observaciones)),
                    'total_marcaciones' => $asistencia ? (int) $asistencia->total_marcaciones : 0,
                    'tiene_permiso' => $permisosDia->isNotEmpty(),
                ]);
            }
        }

        return $rows
            ->sortBy(fn($row) => $row->fecha . '|' . $row->nombre_completo)
            ->values();
    }

    private function resumenReporteAsistencias($rows): array
    {
        $rows = collect($rows);

        return [
            'registros' => $rows->count(),
            'personas' => $rows->pluck('idpersonal')->unique()->count(),
            'sin_registro' => $rows->filter(fn($row) => $row->total_marcaciones === 0)->count(),
            'sin_salida' => $rows->filter(fn($row) => $row->total_marcaciones === 1)->count(),
            'con_permiso' => $rows->where('tiene_permiso', true)->count(),
            'horas_programadas' => $this->formatoMinutos((int) $rows->sum('minutos_programados')),
            'horas_trabajadas' => $this->formatoMinutos((int) $rows->sum('minutos_trabajados')),
        ];
    }

    public function reporte_asistencias_asignacion(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        $name_mac = $this->asignacionNombreMac($idmac);
        $macs = $this->asignacionMacs();
        [$fechaInicio, $fechaFin] = $this->asignacionFechas($request);
        $idpersonal = $request->input('idpersonal') ? (int) $request->input('idpersonal') : null;
        $personalAsignado = $this->personasAsignadasData($idmac);
        $rows = $this->asignacionReporteAsistenciaData($idmac, $fechaInicio, $fechaFin, $idpersonal);
        $summary = $this->resumenReporteAsistencias($rows);

        return view('asistencia.asignacion.reporte_asistencias.index', compact(
            'idmac',
            'name_mac',
            'macs',
            'fechaInicio',
            'fechaFin',
            'idpersonal',
            'personalAsignado',
            'rows',
            'summary'
        ));
    }

    public function export_reporte_asistencias_asignacion(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        [$fechaInicio, $fechaFin] = $this->asignacionFechas($request);
        $idpersonal = $request->input('idpersonal') ? (int) $request->input('idpersonal') : null;
        $rows = $this->asignacionReporteAsistenciaData($idmac, $fechaInicio, $fechaFin, $idpersonal);
        $nameMac = $this->asignacionNombreMac($idmac);
        $fileMac = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nameMac);

        return Excel::download(
            new AsistenciaAsignacionReporteExport($rows, $nameMac, $fechaInicio, $fechaFin),
            'CONTROL_ASISTENCIA_' . $fileMac . '_' . $fechaInicio . '_' . $fechaFin . '.xlsx'
        );
    }

    public function md_asignacion_horario(Request $request)
    {
        if ($request->boolean('editar') && !$request->filled('id')) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibio el ID del horario para editar.',
            ], 422);
        }

        $idmac = $this->asignacionIdMac($request);
        $nameMac = $this->asignacionNombreMac($idmac);
        $horario = null;
        $selectedPersonal = null;
        $diasEspecialesDisponible = $this->asignacionDiasEspecialesDisponible();
        $mesCalendario = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->format('Y-m')
            : Carbon::now()->format('Y-m');

        if ($request->filled('id')) {
            $horario = DB::table('d_personal_asistencia as dpa')
                ->join('m_personal as p', 'p.IDPERSONAL', '=', 'dpa.idpersonal')
                ->select(
                    'dpa.*',
                    'p.NUM_DOC',
                    DB::raw('UPPER(CONCAT(p.APE_PAT, " ", p.APE_MAT, ", ", p.NOMBRE)) as nombre_completo')
                )
                ->where('dpa.id', $request->input('id'))
                ->first();

            if (!$horario || !$this->personalPerteneceMac((int) $horario->idpersonal, $idmac)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontro la asignacion solicitada.',
                ], 404);
            }

            $selectedPersonal = [
                'id' => $horario->idpersonal,
                'text' => $horario->NUM_DOC . ' - ' . $horario->nombre_completo,
            ];

            $ultimoDiaEspecial = $diasEspecialesDisponible
                ? DB::table('d_personal_asistencia_dia')
                    ->where('id_asignacion', $horario->id)
                    ->where('activo', 1)
                    ->orderBy('fecha', 'desc')
                    ->value('fecha')
                : null;

            if ($ultimoDiaEspecial) {
                $mesCalendario = Carbon::parse($ultimoDiaEspecial)->format('Y-m');
            } elseif ($request->filled('fecha_inicio')) {
                $mesCalendario = Carbon::parse($request->input('fecha_inicio'))->format('Y-m');
            } elseif (Carbon::parse($horario->fecha_inicio)->greaterThan(Carbon::now())) {
                $mesCalendario = Carbon::parse($horario->fecha_inicio)->format('Y-m');
            }
        }

        return response()->json([
            'html' => view('asistencia.asignacion.modals.md_asignacion_horario', compact(
                'idmac',
                'nameMac',
                'horario',
                'selectedPersonal',
                'diasEspecialesDisponible',
                'mesCalendario'
            ))->render()
        ]);
    }

    public function buscar_personal_asignacion(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        $term = trim((string) $request->input('q', ''));

        if (strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $personal = $this->personalParaAsignacion($idmac, $term, 20)
            ->map(function ($p) {
                return [
                    'id' => $p->IDPERSONAL,
                    'text' => $p->NUM_DOC . ' - ' . $p->nombre_completo,
                ];
            });

        return response()->json(['results' => $personal]);
    }

    public function store_asignacion_horario(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id' => 'nullable|integer',
            'mac' => 'nullable|integer',
            'idpersonal' => 'required|integer',
            'hora_ingreso' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date',
            'sin_fin' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise los campos obligatorios.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $idmac = $this->asignacionIdMac($request);
        $id = $request->input('id') ? (int) $request->input('id') : null;
        $esEdicion = (bool) $id;
        $idpersonal = (int) $request->input('idpersonal');

        if ($id) {
            $horarioActual = DB::table('d_personal_asistencia')->where('id', $id)->first();

            if (!$horarioActual || !$this->personalPerteneceMac((int) $horarioActual->idpersonal, $idmac)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para editar esta asignacion.',
                ], 403);
            }
        }

        if (!$this->personalPerteneceMac($idpersonal, $idmac)) {
            return response()->json([
                'success' => false,
                'message' => 'El personal seleccionado no pertenece al Centro MAC filtrado.',
            ], 403);
        }

        if ($request->input('hora_ingreso') >= $request->input('hora_salida')) {
            return response()->json([
                'success' => false,
                'message' => 'La hora de salida debe ser mayor a la hora de ingreso.',
            ], 422);
        }

        $sinFin = $request->boolean('sin_fin');
        $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d');
        $fechaFin = $sinFin ? null : ($request->input('fecha_fin') ? Carbon::parse($request->input('fecha_fin'))->format('Y-m-d') : null);

        if (!$sinFin && !$fechaFin) {
            return response()->json([
                'success' => false,
                'message' => 'Ingrese fecha fin o marque Sin fin.',
            ], 422);
        }

        if ($fechaFin && $fechaFin < $fechaInicio) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha fin no puede ser menor a la fecha inicio.',
            ], 422);
        }

        $fechaFinComparacion = $fechaFin ?: '9999-12-31';
        $cruce = DB::table('d_personal_asistencia')
            ->where('idpersonal', $idpersonal)
            ->when($id, function ($q) use ($id) {
                $q->where('id', '<>', $id);
            })
            ->whereRaw('fecha_inicio <= ?', [$fechaFinComparacion])
            ->whereRaw('COALESCE(fecha_fin, "9999-12-31") >= ?', [$fechaInicio])
            ->exists();

        if ($cruce) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una asignacion de horario que se cruza con ese rango.',
            ], 409);
        }

        $data = [
            'idpersonal' => $idpersonal,
            'hora_ingreso' => $request->input('hora_ingreso') . ':00',
            'hora_salida' => $request->input('hora_salida') . ':00',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'sin_fin' => $sinFin ? 1 : 0,
        ];

        if ($id) {
            DB::table('d_personal_asistencia')->where('id', $id)->update($data);
        } else {
            $id = DB::table('d_personal_asistencia')->insertGetId($data);
        }

        return response()->json([
            'success' => true,
            'id' => $id,
            'message' => $esEdicion ? 'Horario actualizado correctamente.' : 'Horario asignado correctamente.',
        ]);
    }

    public function store_asignacion_dia_especial(Request $request)
    {
        if (!$this->asignacionDiasEspecialesDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'Primero cree la tabla d_personal_asistencia_dia.',
            ], 409);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_asignacion' => 'required|integer',
            'mac' => 'nullable|integer',
            'fecha' => 'required|date',
            'hora_ingreso' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise la fecha y las horas del dia especial.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $idmac = $this->asignacionIdMac($request);
        $idAsignacion = (int) $request->input('id_asignacion');
        $horario = $this->horarioAsignacionEnMac($idAsignacion, $idmac);

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la asignacion del horario.',
            ], 404);
        }

        if ($request->input('hora_ingreso') >= $request->input('hora_salida')) {
            return response()->json([
                'success' => false,
                'message' => 'La hora de salida debe ser mayor a la hora de ingreso.',
            ], 422);
        }

        $fecha = Carbon::parse($request->input('fecha'))->format('Y-m-d');

        if (Carbon::parse($fecha)->dayOfWeek === Carbon::SUNDAY) {
            return response()->json([
                'success' => false,
                'message' => 'Los domingos no se contabilizan para horas compensables.',
            ], 422);
        }

        if ($this->asignacionEsFeriado($idmac, $fecha)) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha seleccionada esta registrada como feriado o dia no laborable.',
            ], 422);
        }

        $fechaFin = ((int) $horario->sin_fin === 1 || !$horario->fecha_fin)
            ? null
            : Carbon::parse($horario->fecha_fin)->format('Y-m-d');

        if ($fecha < Carbon::parse($horario->fecha_inicio)->format('Y-m-d') || ($fechaFin && $fecha > $fechaFin)) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha especial esta fuera de la vigencia del horario base.',
            ], 422);
        }

        $existe = DB::table('d_personal_asistencia_dia')
            ->where('id_asignacion', $idAsignacion)
            ->where('fecha', $fecha)
            ->first();

        $data = [
            'hora_ingreso' => $request->input('hora_ingreso') . ':00',
            'hora_salida' => $request->input('hora_salida') . ':00',
            'activo' => 1,
            'updated_at' => now(),
        ];

        if ($existe) {
            DB::table('d_personal_asistencia_dia')->where('id', $existe->id)->update($data);
        } else {
            $data['id_asignacion'] = $idAsignacion;
            $data['fecha'] = $fecha;
            $data['created_at'] = now();
            DB::table('d_personal_asistencia_dia')->insert($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dia especial guardado correctamente.',
        ]);
    }

    public function sync_asignacion_sabados(Request $request)
    {
        if (!$this->asignacionDiasEspecialesDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'Primero cree la tabla d_personal_asistencia_dia.',
            ], 409);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_asignacion' => 'required|integer',
            'mac' => 'nullable|integer',
            'mes' => 'required|date_format:Y-m',
            'fechas' => 'nullable|array',
            'fechas.*' => 'date_format:Y-m-d',
            'hora_ingreso' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Revise el mes, las fechas y el horario de sabado.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->input('hora_ingreso') >= $request->input('hora_salida')) {
            return response()->json([
                'success' => false,
                'message' => 'La hora de salida debe ser mayor a la hora de ingreso.',
            ], 422);
        }

        $idmac = $this->asignacionIdMac($request);
        $idAsignacion = (int) $request->input('id_asignacion');
        $horario = $this->horarioAsignacionEnMac($idAsignacion, $idmac);

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la asignacion del horario.',
            ], 404);
        }

        $mes = $request->input('mes');
        $calendario = $this->calendarioAsignacionData($horario, $idmac, $mes);
        $permitidas = collect($calendario['sabados'])
            ->reject(fn($dia) => $dia['es_feriado'])
            ->pluck('fecha')
            ->values();
        $fechasSolicitadas = collect($request->input('fechas', []))
            ->filter()
            ->unique()
            ->values();
        $fechasInvalidas = $fechasSolicitadas->diff($permitidas);

        if ($fechasInvalidas->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Hay sabados fuera de vigencia o registrados como feriados.',
            ], 422);
        }

        $inicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth()->format('Y-m-d');
        $finMes = Carbon::createFromFormat('Y-m', $mes)->endOfMonth()->format('Y-m-d');

        DB::transaction(function () use ($idAsignacion, $inicioMes, $finMes, $fechasSolicitadas, $request) {
            DB::table('d_personal_asistencia_dia')
                ->where('id_asignacion', $idAsignacion)
                ->whereBetween('fecha', [$inicioMes, $finMes])
                ->whereRaw('DAYOFWEEK(fecha) = 7')
                ->delete();

            foreach ($fechasSolicitadas as $fecha) {
                DB::table('d_personal_asistencia_dia')->insert([
                    'id_asignacion' => $idAsignacion,
                    'fecha' => $fecha,
                    'hora_ingreso' => $request->input('hora_ingreso') . ':00',
                    'hora_salida' => $request->input('hora_salida') . ':00',
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Sabados programados actualizados.',
        ]);
    }

    public function delete_asignacion_dia_especial(Request $request)
    {
        if (!$this->asignacionDiasEspecialesDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'La tabla de dias especiales no existe.',
            ], 409);
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $idmac = $this->asignacionIdMac($request);
        $dia = DB::table('d_personal_asistencia_dia as dpd')
            ->join('d_personal_asistencia as dpa', 'dpa.id', '=', 'dpd.id_asignacion')
            ->select('dpd.*', 'dpa.idpersonal')
            ->where('dpd.id', $request->input('id'))
            ->first();

        if (!$dia) {
            return response()->json([
                'success' => false,
                'message' => 'El dia especial no existe.',
            ], 404);
        }

        if (!$this->personalPerteneceMac((int) $dia->idpersonal, $idmac)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para eliminar este dia especial.',
            ], 403);
        }

        DB::table('d_personal_asistencia_dia')->where('id', $dia->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dia especial eliminado.',
        ]);
    }

    public function delete_asignacion_horario(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $idmac = $this->asignacionIdMac($request);
        $horario = DB::table('d_personal_asistencia')->where('id', $request->input('id'))->first();

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'La asignacion no existe.',
            ], 404);
        }

        if (!$this->personalPerteneceMac((int) $horario->idpersonal, $idmac)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para eliminar esta asignacion.',
            ], 403);
        }

        if ($this->asignacionDiasEspecialesDisponible()) {
            DB::table('d_personal_asistencia_dia')
                ->where('id_asignacion', $horario->id)
                ->delete();
        }

        DB::table('d_personal_asistencia')->where('id', $horario->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asignacion eliminada.',
        ]);
    }

    public function export_asignacion_excel(Request $request)
    {
        $idmac = $this->asignacionIdMac($request);
        [$fechaInicio, $fechaFin] = $this->asignacionFechas($request);
        $idpersonal = $request->input('idpersonal') ? (int) $request->input('idpersonal') : null;

        $rows = $this->asignacionReporteData($idmac, $fechaInicio, $fechaFin, $idpersonal);
        $summary = $this->resumenAsignacionReporte($rows);
        $nameMac = $this->asignacionNombreMac($idmac);
        $fileMac = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nameMac);

        return Excel::download(
            new AsistenciaAsignacionExport($rows, $summary, $nameMac, $fechaInicio, $fechaFin),
            'REPORTE_HORAS_COMPENSABLES_' . $fileMac . '_' . $fechaInicio . '_' . $fechaFin . '.xlsx'
        );
    }
}
