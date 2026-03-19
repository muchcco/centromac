<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class OcupabilidadController extends Controller
{
    private function centro_mac(): object
    {
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', auth()->user()->idcentro_mac)
            ->first();

        return (object)[
            'idmac'    => $user->IDCENTRO_MAC,
            'name_mac' => $user->NOMBRE_MAC,
        ];
    }

    public function index()
    {
        $mac = DB::table('M_CENTRO_MAC')
            ->when(
                auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador'),
                fn($q) => $q->where('IDCENTRO_MAC', $this->centro_mac()->idmac)
            )
            ->orderBy('NOMBRE_MAC')
            ->get();

        return view('ocupabilidad.index', compact('mac'));
    }

    public function tb_index(Request $request)
    {
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;

        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', '=', $idmac)
            ->select('NOMBRE_MAC')
            ->first();

        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible';

        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = str_pad($request->mes ?: date('m'), 2, '0', STR_PAD_LEFT);
        $meses = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];

        $mesNombre = $meses[$fecha_mes];
        $fechaInicio = Carbon::create($fecha_año, $fecha_mes, 1)->startOfMonth();
        $fechaActual = Carbon::now();
        $fechaFin = ($fechaActual->month == $fecha_mes && $fechaActual->year == $fecha_año)
            ? $fechaActual->startOfDay()
            : $fechaInicio->copy()->endOfMonth();

        $numeroDias = $fechaFin->day;

        $feriados = DB::table('feriados')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where(function ($query) use ($idmac) {
                $query->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->pluck('fecha')
            ->toArray();

        $domingos = 0;
        for ($dia = 1; $dia <= $numeroDias; $dia++) {
            $fecha = Carbon::create($fecha_año, $fecha_mes, $dia);
            if ($fecha->isSunday()) {
                $domingos++;
            }
        }

        $diasFeriados = count(array_filter($feriados, function ($feriado) {
            return !Carbon::parse($feriado)->isSunday();
        }));

        $diasHabiles = $numeroDias - $domingos - $diasFeriados;

        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->where('m_modulo.idcentro_mac', $idmac)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('m_modulo.fechainicio', '<=', $fechaFin)
                    ->where('m_modulo.fechafin', '>=', $fechaInicio);
            })
            ->where('m_modulo.es_administrativo', 'NO')
            ->select('m_modulo.idmodulo', 'm_modulo.n_modulo', 'm_modulo.identidad', 'm_entidad.nombre_entidad', 'm_modulo.fechainicio', 'm_modulo.fechafin')
            ->orderBy('m_modulo.n_modulo')
            ->get();

        for ($dia = 1; $dia <= $numeroDias; $dia++) {
            $fecha = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $dia);

            // Verificar si el día es un feriado
            $esFeriado = in_array($fecha, $feriados);

            // Realizar la consulta con el idcentromac
            $resultados = DB::select("
                WITH CTE AS (
                     SELECT 
                pm.IDMODULO,
                p.NUM_DOC, 
                a.HORA,
                pm.status
                    FROM 
                        m_personal_modulo pm
                    JOIN 
                        m_personal p ON pm.NUM_DOC = p.NUM_DOC 
                    JOIN 
                        m_modulo m ON pm.IDMODULO = m.IDMODULO 
                    INNER JOIN 
                        m_asistencia a ON pm.NUM_DOC = a.NUM_DOC 
                        AND a.FECHA = ? -- Comparación directa de fechas
                    WHERE 
                        pm.status IN ('itinerante', 'fijo') 
                        AND ? BETWEEN pm.fechainicio AND pm.fechafin 
                        AND a.IDCENTRO_MAC = ?
                )
                SELECT 
                    IDMODULO,
                    MIN(CASE WHEN status = 'itinerante' THEN hora END) AS hora_minima_itinerante,
                    CASE 
                        WHEN MIN(CASE WHEN status = 'itinerante' THEN hora END) IS NOT NULL THEN NULL
                        ELSE MIN(CASE WHEN status = 'fijo' THEN hora END) 
                    END AS hora_minima_fijo,
                    CASE 
                        WHEN MIN(CASE WHEN status = 'itinerante' THEN hora END) IS NOT NULL THEN 
                            MIN(CASE WHEN status = 'itinerante' THEN hora END) 
                        ELSE 
                            MIN(CASE WHEN status = 'fijo' AND 
                                      NUM_DOC NOT IN (SELECT NUM_DOC FROM CTE WHERE status = 'itinerante') THEN hora END) 
                    END AS hora_minima,
                    CASE 
                        WHEN MIN(CASE WHEN status = 'itinerante' THEN hora END) IS NOT NULL THEN 
                            MAX(CASE WHEN status = 'itinerante' THEN NUM_DOC END) 
                        ELSE 
                            MAX(CASE WHEN status = 'fijo' AND 
                                      NUM_DOC NOT IN (SELECT NUM_DOC FROM CTE WHERE status = 'itinerante') THEN NUM_DOC END) 
                    END AS num_doc
                FROM 
                    CTE
                GROUP BY 
                    IDMODULO
                HAVING 
                    hora_minima IS NOT NULL
                ORDER BY 
                    IDMODULO;
            ", [$fecha, $fecha, $idmac]); // Añadimos el idcentromac a la consulta

            // Agregar resultados al array de días
            foreach ($resultados as $resultado) {
                $dias[$dia][$resultado->IDMODULO] = [
                    'idmodulo' => $resultado->IDMODULO,
                    'hora_minima' => $resultado->hora_minima,
                    'es_feriado' => $esFeriado, // Añadir indicador de feriado
                ];
            }
        }

        // Retornar la vista con los días y sus módulos
        return view('ocupabilidad.tablas.tb_index', compact('mesNombre', 'diasHabiles', 'nombreMac', 'dias', 'modulos', 'numeroDias', 'fecha_año', 'fecha_mes', 'feriados'));
    }

    public function tb_index_sp(Request $request)
    {
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;

        $anio = $request->año ?: date('Y');
        $mes  = str_pad($request->mes ?: date('m'), 2, '0', STR_PAD_LEFT);

        // 🔥 FECHA INICIO
        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth();

        // 🔥 FECHA FIN (para consulta)
        // 🔥 FECHA FIN CORRECTA
        if ((int)$anio === (int)now()->year && (int)$mes === (int)now()->month) {
            $fechaFin = Carbon::yesterday(); // 🔥 clave
        } else {
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth();
        }

        $fechaInicioStr = $fechaInicio->toDateString();
        $fechaFinStr    = $fechaFin->toDateString();

        // 🔥 SP (OCUPABILIDAD)
        $data = collect(DB::select(
            "CALL db_centro_mac_reporte.SP_RESUMEN_ASIST_HABILES_MODULO(?, ?, ?)",
            [$idmac, $fechaInicioStr, $fechaFinStr]
        ))
            ->sortBy(fn($x) => (int)$x->N_MODULO)
            ->values();

        // =========================
        // 🔥 KPI GENERAL
        // =========================
        $total = $data->count();

        $totalMarcados = $data->sum('DIAS_ASISTENCIA');
        $totalHabiles  = $data->sum('DIAS_HABILES');

        $promedio = $totalHabiles > 0
            ? round(($totalMarcados / $totalHabiles) * 100, 1)
            : 0;

        $cumplen = $data->filter(function ($r) {
            return $r->DIAS_HABILES > 0
                ? ($r->DIAS_ASISTENCIA / $r->DIAS_HABILES) >= 0.95
                : false;
        })->count();

        $noCumplen = $data->filter(function ($r) {
            return $r->DIAS_HABILES > 0
                ? ($r->DIAS_ASISTENCIA / $r->DIAS_HABILES) < 0.85
                : true;
        })->count();

        // =========================
        // 🔥 NOMBRE MAC
        // =========================
        $nombreMac = optional(
            DB::table('M_CENTRO_MAC')
                ->where('IDCENTRO_MAC', $idmac)
                ->first()
        )->NOMBRE_MAC ?? 'MAC';

        // =========================
        // 🔥 MES ACTUAL
        // =========================
        $esMesActual = (
            (int)$anio === (int)now()->year &&
            (int)$mes  === (int)now()->month
        );

        // =========================
        // 🔥 1. FECHA CIERRE REAL (DATA)
        // =========================
        $fechaCierre = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereBetween('fecha_asistencia', [$fechaInicioStr, $fechaFinStr])
            ->whereRaw('DAYOFWEEK(fecha_asistencia) != 1') // excluir domingo
            ->max('fecha_asistencia');

        $fechaCierreFmt = $fechaCierre
            ? Carbon::parse($fechaCierre)->format('d/m/Y')
            : null;

        // =========================
        // 🔥 2. FECHA HÁBIL (SIEMPRE AYER)
        // =========================
        $fechaHabiles = Carbon::yesterday()->format('d/m/Y');

        // =========================
        // 🔥 RETURN
        // =========================
        return view('ocupabilidad.tablas.tb_index_sp', [
            'data'          => $data,
            'nombreMac'     => $nombreMac,
            'anio'          => $anio,
            'mes'           => $mes,

            // 🔥 CONTROL UX
            'esMesActual'   => $esMesActual,
            'fechaCierre'   => $fechaCierreFmt,
            'fechaHabiles'  => $fechaHabiles,

            // 🔥 KPIs
            'total'         => $total,
            'promedio'      => $promedio,
            'cumplen'       => $cumplen,
            'noCumplen'     => $noCumplen,
        ]);
    }
    public function tb_index_resumen(Request $request)
    {
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;
        $anio  = $request->anio ?: date('Y');
        $mes   = str_pad($request->mes ?: date('m'), 2, '0', STR_PAD_LEFT);

        $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString();
        $fechaFin    = Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString();

        $resultados = collect(
            DB::select("CALL db_centro_mac_reporte.SP_RESUMEN_ASIST_HABILES_MODULO(?, ?, ?)", [
                $idmac,
                $fechaInicio,
                $fechaFin
            ])
        )->sortBy('N_MODULO')->values();  // ordena y reindexa

        $nombreMac = optional(DB::table('M_CENTRO_MAC')->where('IDCENTRO_MAC', $idmac)->first())->NOMBRE_MAC ?? 'MAC';

        return view('ocupabilidad.tablas.tb_index_resumen', [
            'nombreMac'  => $nombreMac,
            'anio'       => $anio,
            'mes'        => $mes,
            'resultados' => $resultados,
        ]);
    }
}
