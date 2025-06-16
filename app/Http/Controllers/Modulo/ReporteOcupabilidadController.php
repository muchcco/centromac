<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Exports\ReporteOcupabilidadExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteOcupabilidadController extends Controller
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
        // listar todos los MAC, sin filtro de rol
        $mac = DB::table('M_CENTRO_MAC')
            ->orderBy('NOMBRE_MAC')
            ->get();

        return view('reporte.ocupabilidad.index', compact('mac'));
    }

    public function tb_index(Request $request)
    {
        $fecha_año  = $request->input('año', date('Y'));
        $fecha_mes  = str_pad($request->input('mes', date('m')), 2, '0', STR_PAD_LEFT);

        $meses      = [
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
        $mesNombre   = $meses[$fecha_mes];
        $fechaInicio = Carbon::create($fecha_año, $fecha_mes, 1)->startOfMonth();
        $fechaFin    = (Carbon::now()->year == $fecha_año && Carbon::now()->month == $fecha_mes)
            ? Carbon::now()->startOfDay()
            : $fechaInicio->copy()->endOfMonth();
        $numeroDias  = $fechaFin->day;

        // Obtener todos los módulos con su MAC asociado
        $modulos = DB::table('m_modulo as m')
            ->join('m_entidad as e', 'm.identidad', '=', 'e.identidad')
            ->join('M_CENTRO_MAC as cm', 'm.idcentro_mac', '=', 'cm.IDCENTRO_MAC')
            ->where('m.fechainicio', '<=', $fechaFin)
            ->where('m.fechafin', '>=', $fechaInicio)
            ->where('m.es_administrativo', 'NO')
            ->select(
                'm.idmodulo',
                'm.n_modulo',
                'e.nombre_entidad',
                'm.fechainicio',
                'm.fechafin',
                'cm.IDCENTRO_MAC as idmac',
                'cm.NOMBRE_MAC as nombre_mac'
            )
            ->orderBy('cm.NOMBRE_MAC')
            ->orderBy('m.n_modulo')
            ->get();

        // Precalcular feriados por MAC
        $feriadosPorMac = [];
        foreach ($modulos->pluck('idmac')->unique() as $macId) {
            $feriadosPorMac[$macId] = DB::table('feriados')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where(fn($q) => $q->where('id_centromac', $macId)->orWhereNull('id_centromac'))
                ->pluck('fecha')
                ->toArray();
        }

        // Calcular días hábiles por módulo
        $diasHabilesPorModulo = [];
        foreach ($modulos as $mod) {
            $domingos      = 0;
            for ($d = 1; $d <= $numeroDias; $d++) {
                if (Carbon::create($fecha_año, $fecha_mes, $d)->isSunday()) {
                    $domingos++;
                }
            }
            $feriados       = $feriadosPorMac[$mod->idmac];
            $diasFeriados   = count(array_filter($feriados, fn($f) => ! Carbon::parse($f)->isSunday()));
            $diasHabilesPorModulo[$mod->idmac][$mod->idmodulo] = $numeroDias - $domingos - $diasFeriados;
        }

        for ($d = 1; $d <= $numeroDias; $d++) {
            $f = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $d);
            $esFeriado = false; // ya no hace falta aquí
            $resultados =  DB::select("
            WITH CTE AS (
                SELECT 
                    pm.IDMODULO,
                    p.NUM_DOC,
                    a.HORA,
                    pm.status,
                    a.IDCENTRO_MAC
                FROM m_personal_modulo pm
                JOIN m_personal p ON pm.NUM_DOC = p.NUM_DOC
                JOIN m_modulo m ON pm.IDMODULO = m.IDMODULO
                JOIN m_asistencia a ON pm.NUM_DOC = a.NUM_DOC
                    AND a.FECHA = ?
                WHERE pm.status IN ('itinerante','fijo')
                  AND ? BETWEEN pm.fechainicio AND pm.fechafin
            )
            SELECT
                IDCENTRO_MAC,
                IDMODULO,
                CASE
                    WHEN MIN(CASE WHEN status='itinerante' THEN hora END) IS NOT NULL
                    THEN MIN(CASE WHEN status='itinerante' THEN hora END)
                    ELSE MIN(CASE WHEN status='fijo'
                        AND NUM_DOC NOT IN (
                            SELECT NUM_DOC FROM CTE WHERE status='itinerante'
                        ) THEN hora END)
                END AS hora_minima
            FROM CTE
            GROUP BY IDCENTRO_MAC, IDMODULO
            HAVING hora_minima IS NOT NULL
            ORDER BY IDCENTRO_MAC, IDMODULO
        ", [$f, $f]);

            foreach ($resultados as $r) {
                $dias[$d][$r->IDCENTRO_MAC][$r->IDMODULO] = [
                    'hora_minima' => $r->hora_minima,
                ];
            }
        }

        return view('reporte.ocupabilidad.tablas.tb_index', compact(
            'mesNombre',
            'modulos',
            'dias',
            'numeroDias',
            'fecha_año',
            'fecha_mes',
            'feriadosPorMac',
            'diasHabilesPorModulo'
        ));
    }
    public function tb_index_all(Request $request)
    {
        $fecha_año  = $request->input('año', date('Y'));
        $fecha_mes  = str_pad($request->input('mes', date('m')), 2, '0', STR_PAD_LEFT);

        $meses      = [
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
        $mesNombre   = $meses[$fecha_mes];
        $fechaInicio = Carbon::create($fecha_año, $fecha_mes, 1)->startOfMonth();
        $fechaFin    = (Carbon::now()->year == $fecha_año && Carbon::now()->month == $fecha_mes)
            ? Carbon::now()->startOfDay()
            : $fechaInicio->copy()->endOfMonth();
        $numeroDias  = $fechaFin->day;

        // Obtener todos los módulos con su MAC asociado
        $modulos = DB::table('m_modulo as m')
            ->join('m_entidad as e', 'm.identidad', '=', 'e.identidad')
            ->join('M_CENTRO_MAC as cm', 'm.idcentro_mac', '=', 'cm.IDCENTRO_MAC')
            ->where('m.fechainicio', '<=', $fechaFin)
            ->where('m.fechafin', '>=', $fechaInicio)
            ->where('m.es_administrativo', 'NO')
            ->select(
                'm.idmodulo',
                'm.n_modulo',
                'e.nombre_entidad',
                'm.fechainicio',
                'm.fechafin',
                'cm.IDCENTRO_MAC as idmac',
                'cm.NOMBRE_MAC as nombre_mac'
            )
            ->orderBy('cm.NOMBRE_MAC')
            ->orderBy('m.n_modulo')
            ->get();

        // Precalcular feriados por MAC
        $feriadosPorMac = [];
        foreach ($modulos->pluck('idmac')->unique() as $macId) {
            $feriadosPorMac[$macId] = DB::table('feriados')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where(fn($q) => $q->where('id_centromac', $macId)->orWhereNull('id_centromac'))
                ->pluck('fecha')
                ->toArray();
        }

        // --- AÑADIDO: llamar al SP para días hábiles por módulo y MAC ---
        $spData = collect(
            DB::select('CALL SP_DIASHABILES_MODULO_ALL(?, ?)', [
                (int)$fecha_año,
                (int)$fecha_mes,
            ])
        );
        // convertir en arreglo [mac][modulo] => DIAS_HABILES
        $diasHabilesPorModulo = [];
        foreach ($spData as $row) {
            $diasHabilesPorModulo[$row->IDCENTRO_MAC][$row->IDMODULO] = $row->DIAS_HÁBILES;
        }
        // dd($diasHabilesPorModulo);

        for ($d = 1; $d <= $numeroDias; $d++) {
            $f         = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $d);
            $esFeriado = in_array($f, $feriadosPorMac[$modulos->first()->idmac] ?? [], true);

            $resultados = DB::select("
            WITH CTE AS (
                SELECT 
                    pm.IDMODULO,
                    p.NUM_DOC,
                    a.HORA,
                    pm.status,
                    a.IDCENTRO_MAC
                FROM m_personal_modulo pm
                JOIN m_personal p ON pm.NUM_DOC = p.NUM_DOC
                JOIN m_modulo m ON pm.IDMODULO = m.IDMODULO
                JOIN m_asistencia a ON pm.NUM_DOC = a.NUM_DOC
                    AND a.FECHA = ?
                WHERE pm.status IN ('itinerante','fijo')
                  AND ? BETWEEN pm.fechainicio AND pm.fechafin
            )
            SELECT
                IDCENTRO_MAC,
                IDMODULO,
                CASE
                    WHEN MIN(CASE WHEN status='itinerante' THEN hora END) IS NOT NULL
                    THEN MIN(CASE WHEN status='itinerante' THEN hora END)
                    ELSE MIN(CASE WHEN status='fijo'
                        AND NUM_DOC NOT IN (
                            SELECT NUM_DOC FROM CTE WHERE status='itinerante'
                        ) THEN hora END)
                END AS hora_minima
            FROM CTE
            GROUP BY IDCENTRO_MAC, IDMODULO
            HAVING hora_minima IS NOT NULL
            ORDER BY IDCENTRO_MAC, IDMODULO
        ", [$f, $f]);

            foreach ($resultados as $r) {
                $dias[$d][$r->IDCENTRO_MAC][$r->IDMODULO] = [
                    'hora_minima' => $r->hora_minima,
                    'es_feriado'  => $esFeriado,
                ];
            }
        }

        return view('reporte.ocupabilidad.tablas.tb_index_all', compact(
            'mesNombre',
            'modulos',
            'dias',
            'numeroDias',
            'fecha_año',
            'fecha_mes',
            'feriadosPorMac',
            'diasHabilesPorModulo'  // <-- enviamos también los hábiles del SP
        ));
    }
    public function exportExcel(Request $request)
    {
        $fecha_año  = $request->input('año', date('Y'));
        $fecha_mes  = str_pad($request->input('mes', date('m')), 2, '0', STR_PAD_LEFT);

        $meses      = [
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
        $mesNombre   = $meses[$fecha_mes];
        $fechaInicio = Carbon::create($fecha_año, $fecha_mes, 1)->startOfMonth();
        $fechaFin    = (Carbon::now()->year == $fecha_año && Carbon::now()->month == $fecha_mes)
            ? Carbon::now()->startOfDay()
            : $fechaInicio->copy()->endOfMonth();
        $numeroDias  = $fechaFin->day;

        // Obtener todos los módulos con su MAC asociado
        $modulos = DB::table('m_modulo as m')
            ->join('m_entidad as e', 'm.identidad', '=', 'e.identidad')
            ->join('M_CENTRO_MAC as cm', 'm.idcentro_mac', '=', 'cm.IDCENTRO_MAC')
            ->where('m.fechainicio', '<=', $fechaFin)
            ->where('m.fechafin', '>=', $fechaInicio)
            ->where('m.es_administrativo', 'NO')
            ->select(
                'm.idmodulo',
                'm.n_modulo',
                'e.nombre_entidad',
                'm.fechainicio',
                'm.fechafin',
                'cm.IDCENTRO_MAC as idmac',
                'cm.NOMBRE_MAC as nombre_mac'
            )
            ->orderBy('cm.NOMBRE_MAC')
            ->orderBy('m.n_modulo')
            ->get();

        // Precalcular feriados por MAC
        $feriadosPorMac = [];
        foreach ($modulos->pluck('idmac')->unique() as $macId) {
            $feriadosPorMac[$macId] = DB::table('feriados')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where(fn($q) => $q->where('id_centromac', $macId)->orWhereNull('id_centromac'))
                ->pluck('fecha')
                ->toArray();
        }

        // Calcular días hábiles por módulo
        $diasHabilesPorModulo = [];
        foreach ($modulos as $mod) {
            $domingos      = 0;
            for ($d = 1; $d <= $numeroDias; $d++) {
                if (Carbon::create($fecha_año, $fecha_mes, $d)->isSunday()) {
                    $domingos++;
                }
            }
            $feriados       = $feriadosPorMac[$mod->idmac];
            $diasFeriados   = count(array_filter($feriados, fn($f) => ! Carbon::parse($f)->isSunday()));
            $diasHabilesPorModulo[$mod->idmac][$mod->idmodulo] = $numeroDias - $domingos - $diasFeriados;
        }

        for ($d = 1; $d <= $numeroDias; $d++) {
            $f = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $d);
            $esFeriado = false; // ya no hace falta aquí
            $resultados =  DB::select("
            WITH CTE AS (
                SELECT 
                    pm.IDMODULO,
                    p.NUM_DOC,
                    a.HORA,
                    pm.status,
                    a.IDCENTRO_MAC
                FROM m_personal_modulo pm
                JOIN m_personal p ON pm.NUM_DOC = p.NUM_DOC
                JOIN m_modulo m ON pm.IDMODULO = m.IDMODULO
                JOIN m_asistencia a ON pm.NUM_DOC = a.NUM_DOC
                    AND a.FECHA = ?
                WHERE pm.status IN ('itinerante','fijo')
                  AND ? BETWEEN pm.fechainicio AND pm.fechafin
            )
            SELECT
                IDCENTRO_MAC,
                IDMODULO,
                CASE
                    WHEN MIN(CASE WHEN status='itinerante' THEN hora END) IS NOT NULL
                    THEN MIN(CASE WHEN status='itinerante' THEN hora END)
                    ELSE MIN(CASE WHEN status='fijo'
                        AND NUM_DOC NOT IN (
                            SELECT NUM_DOC FROM CTE WHERE status='itinerante'
                        ) THEN hora END)
                END AS hora_minima
            FROM CTE
            GROUP BY IDCENTRO_MAC, IDMODULO
            HAVING hora_minima IS NOT NULL
            ORDER BY IDCENTRO_MAC, IDMODULO
        ", [$f, $f]);

            foreach ($resultados as $r) {
                $dias[$d][$r->IDCENTRO_MAC][$r->IDMODULO] = [
                    'hora_minima' => $r->hora_minima,
                ];
            }
        }
        $export = new ReporteOcupabilidadExport(
            $modulos,
            $dias,
            $numeroDias,
            $fecha_año,
            $fecha_mes,
            $mesNombre,
            $feriadosPorMac,
            $diasHabilesPorModulo
        );

        return Excel::download(
            $export,
            "reporte_ocupabilidad_{$fecha_año}_{$fecha_mes}.xlsx"
        );
    }
    public function exportExcelSP(Request $request)
    {
        $fecha_año  = $request->input('año', date('Y'));
        $fecha_mes  = str_pad($request->input('mes', date('m')), 2, '0', STR_PAD_LEFT);

        $meses      = [
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
        $mesNombre   = $meses[$fecha_mes];
        $fechaInicio = Carbon::create($fecha_año, $fecha_mes, 1)->startOfMonth();
        $fechaFin    = (Carbon::now()->year == $fecha_año && Carbon::now()->month == $fecha_mes)
            ? Carbon::now()->startOfDay()
            : $fechaInicio->copy()->endOfMonth();
        $numeroDias  = $fechaFin->day;

        // Obtener todos los módulos con su MAC asociado
        $modulos = DB::table('m_modulo as m')
            ->join('m_entidad as e', 'm.identidad', '=', 'e.identidad')
            ->join('M_CENTRO_MAC as cm', 'm.idcentro_mac', '=', 'cm.IDCENTRO_MAC')
            ->where('m.fechainicio', '<=', $fechaFin)
            ->where('m.fechafin', '>=', $fechaInicio)
            ->where('m.es_administrativo', 'NO')
            ->select(
                'm.idmodulo',
                'm.n_modulo',
                'e.nombre_entidad',
                'm.fechainicio',
                'm.fechafin',
                'cm.IDCENTRO_MAC as idmac',
                'cm.NOMBRE_MAC as nombre_mac'
            )
            ->orderBy('cm.NOMBRE_MAC')
            ->orderBy('m.n_modulo')
            ->get();

        // Precalcular feriados por MAC
        $feriadosPorMac = [];
        foreach ($modulos->pluck('idmac')->unique() as $macId) {
            $feriadosPorMac[$macId] = DB::table('feriados')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where(fn($q) => $q->where('id_centromac', $macId)->orWhereNull('id_centromac'))
                ->pluck('fecha')
                ->toArray();
        }

        // --- AÑADIDO: llamar al SP para días hábiles por módulo y MAC ---
        $spData = collect(
            DB::select('CALL SP_DIASHABILES_MODULO_ALL(?, ?)', [
                (int)$fecha_año,
                (int)$fecha_mes,
            ])
        );
        // convertir en arreglo [mac][modulo] => DIAS_HABILES
        $diasHabilesPorModulo = [];
        foreach ($spData as $row) {
            $diasHabilesPorModulo[$row->IDCENTRO_MAC][$row->IDMODULO] = $row->DIAS_HÁBILES;
        }
        // dd($diasHabilesPorModulo);

        for ($d = 1; $d <= $numeroDias; $d++) {
            $f         = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $d);
            $esFeriado = in_array($f, $feriadosPorMac[$modulos->first()->idmac] ?? [], true);

            $resultados = DB::select("
            WITH CTE AS (
                SELECT 
                    pm.IDMODULO,
                    p.NUM_DOC,
                    a.HORA,
                    pm.status,
                    a.IDCENTRO_MAC
                FROM m_personal_modulo pm
                JOIN m_personal p ON pm.NUM_DOC = p.NUM_DOC
                JOIN m_modulo m ON pm.IDMODULO = m.IDMODULO
                JOIN m_asistencia a ON pm.NUM_DOC = a.NUM_DOC
                    AND a.FECHA = ?
                WHERE pm.status IN ('itinerante','fijo')
                  AND ? BETWEEN pm.fechainicio AND pm.fechafin
            )
            SELECT
                IDCENTRO_MAC,
                IDMODULO,
                CASE
                    WHEN MIN(CASE WHEN status='itinerante' THEN hora END) IS NOT NULL
                    THEN MIN(CASE WHEN status='itinerante' THEN hora END)
                    ELSE MIN(CASE WHEN status='fijo'
                        AND NUM_DOC NOT IN (
                            SELECT NUM_DOC FROM CTE WHERE status='itinerante'
                        ) THEN hora END)
                END AS hora_minima
            FROM CTE
            GROUP BY IDCENTRO_MAC, IDMODULO
            HAVING hora_minima IS NOT NULL
            ORDER BY IDCENTRO_MAC, IDMODULO
        ", [$f, $f]);

            foreach ($resultados as $r) {
                $dias[$d][$r->IDCENTRO_MAC][$r->IDMODULO] = [
                    'hora_minima' => $r->hora_minima,
                    'es_feriado'  => $esFeriado,
                ];
            }
        }
        $export = new ReporteOcupabilidadExport(
            $modulos,
            $dias,
            $numeroDias,
            $fecha_año,
            $fecha_mes,
            $mesNombre,
            $feriadosPorMac,
            $diasHabilesPorModulo
        );

        return Excel::download(
            $export,
            "reporte_ocupabilidad_{$fecha_año}_{$fecha_mes}.xlsx"
        );
    }
}
