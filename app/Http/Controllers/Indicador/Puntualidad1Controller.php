<?php

namespace App\Http\Controllers\Indicador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mac;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IndicadorPuntualidadExport;
use Carbon\Carbon;  // Importar la clase Carbon

class Puntualidad1Controller extends Controller
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

    public function index(Request $request)
    {
        $mac = DB::table('M_CENTRO_MAC')
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->orderBy('NOMBRE_MAC', 'ASC')
            ->get();

        return view('indicador.puntualidad.index', compact('mac'));
    }

    public function tb_index(Request $request)
    {
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;

        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', $idmac)
            ->select('NOMBRE_MAC')
            ->first();

        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible';

        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = $request->mes ?: date('m');

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
            '12' => 'Diciembre'
        ];

        $mesNombre = $meses[$fecha_mes];

        $fecha_inicio = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->format('Y-m-d');
        $fecha_fin = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->endOfMonth()->format('Y-m-d');
        $numeroDias = Carbon::create($fecha_año, $fecha_mes, 1)->daysInMonth;

        $hoy = Carbon::today()->format('Y-m-d');

        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->where('m_modulo.idcentro_mac', $idmac)
            ->where('m_modulo.es_administrativo', 'NO')
            ->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                $q->where('m_modulo.fechainicio', '<=', $fecha_fin)
                    ->where('m_modulo.fechafin', '>=', $fecha_inicio);
            })
            ->select(
                'm_modulo.idmodulo',
                'm_modulo.n_modulo',
                'm_entidad.nombre_entidad',
                'm_modulo.fechainicio',
                'm_modulo.fechafin'
            )
            ->get();

        $feriados = DB::table('feriados')
            ->whereYear('fecha', $fecha_año)
            ->whereMonth('fecha', $fecha_mes)
            ->where(function ($q) use ($idmac) {
                $q->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->pluck('fecha')
            ->toArray();

        $diasCerrados = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereBetween('fecha_asistencia', [$fecha_inicio, $fecha_fin])
            ->pluck(DB::raw('DAY(fecha_asistencia)'))
            ->toArray();

        $spRaw = DB::select(
            "CALL db_centro_mac_reporte.SP_OCUPABILIDAD_MENSUAL_PIVOT(?,?,?)",
            [$idmac, $fecha_año, $fecha_mes]
        );

        $sp = [];

        foreach ($spRaw as $row) {
            $idModulo = $row->idmodulo ?? $row->IDMODULO;

            for ($d = 1; $d <= $numeroDias; $d++) {
                $col = 'd' . str_pad($d, 2, '0', STR_PAD_LEFT);
                if (isset($row->$col)) {
                    $sp[$d][$idModulo] = $row->$col;
                }
            }
        }

        $dias = [];

        for ($dia = 1; $dia <= $numeroDias; $dia++) {

            if (in_array($dia, $diasCerrados)) {
                continue;
            }

            $fecha = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $dia);

            // no consultar dias futuros
            if ($fecha > $hoy) {
                continue;
            }

            $esFeriado = in_array($fecha, $feriados);

            $resultados = DB::select("
        WITH CTE AS (
            SELECT 
                pm.IDMODULO,
                p.NUM_DOC,
                a.HORA,
                pm.status
            FROM m_personal_modulo pm
            JOIN m_personal p ON pm.NUM_DOC = p.NUM_DOC
            JOIN m_modulo m ON pm.IDMODULO = m.IDMODULO
            JOIN m_asistencia a 
                ON pm.NUM_DOC = a.NUM_DOC
               AND a.FECHA = ?
            WHERE pm.status IN ('itinerante','fijo')
              AND ? BETWEEN pm.fechainicio AND pm.fechafin
              AND a.IDCENTRO_MAC = ?
        )
        SELECT 
            IDMODULO,
            CASE
                WHEN MIN(CASE WHEN status='itinerante' THEN HORA END) IS NOT NULL
                THEN MIN(CASE WHEN status='itinerante' THEN HORA END)
                ELSE MIN(CASE WHEN status='fijo' THEN HORA END)
            END AS hora_minima
        FROM CTE
        GROUP BY IDMODULO
        HAVING hora_minima IS NOT NULL
        ", [$fecha, $fecha, $idmac]);

            foreach ($resultados as $r) {
                $dias[$dia][$r->IDMODULO] = [
                    'hora_minima' => $r->hora_minima,
                    'es_feriado' => $esFeriado
                ];
            }
        }

        $final = [];

        for ($dia = 1; $dia <= $numeroDias; $dia++) {
            foreach ($modulos as $m) {

                if (isset($sp[$dia][$m->idmodulo])) {
                    $final[$dia][$m->idmodulo] = $sp[$dia][$m->idmodulo];
                } elseif (isset($dias[$dia][$m->idmodulo])) {
                    $final[$dia][$m->idmodulo] = $dias[$dia][$m->idmodulo]['hora_minima'];
                } else {
                    $final[$dia][$m->idmodulo] = null;
                }
            }
        }

        return view(
            'indicador.puntualidad.tablas.tb_index',
            compact(
                'mesNombre',
                'nombreMac',
                'final',
                'modulos',
                'numeroDias',
                'fecha_año',
                'fecha_mes',
                'diasCerrados',
                'feriados'
            )
        );
    }

    public function export_excel(Request $request)
    {
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;

        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', $idmac)
            ->select('NOMBRE_MAC')
            ->first();

        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible';

        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = $request->mes ?: date('m');

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
            '12' => 'Diciembre'
        ];

        $mesNombre = $meses[$fecha_mes];

        $fecha_inicio = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->format('Y-m-d');
        $fecha_fin = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->endOfMonth()->format('Y-m-d');

        $numeroDias = Carbon::create($fecha_año, $fecha_mes, 1)->daysInMonth;

        $hoy = Carbon::today()->format('Y-m-d');

        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->where('m_modulo.idcentro_mac', $idmac)
            ->where('m_modulo.es_administrativo', 'NO')
            ->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                $q->where('m_modulo.fechainicio', '<=', $fecha_fin)
                    ->where('m_modulo.fechafin', '>=', $fecha_inicio);
            })
            ->select(
                'm_modulo.idmodulo',
                'm_modulo.n_modulo',
                'm_entidad.nombre_entidad',
                'm_modulo.fechainicio',
                'm_modulo.fechafin'
            )
            ->orderByRaw('CAST(m_modulo.n_modulo AS UNSIGNED)')
            ->get();

        $feriados = DB::table('feriados')
            ->whereYear('fecha', $fecha_año)
            ->whereMonth('fecha', $fecha_mes)
            ->where(function ($q) use ($idmac) {
                $q->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->pluck('fecha')
            ->toArray();

        $diasCerrados = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('idmac', $idmac)
            ->whereBetween('fecha_asistencia', [$fecha_inicio, $fecha_fin])
            ->pluck(DB::raw('DAY(fecha_asistencia)'))
            ->toArray();

        $spRaw = DB::select(
            "CALL db_centro_mac_reporte.SP_OCUPABILIDAD_MENSUAL_PIVOT(?,?,?)",
            [$idmac, $fecha_año, $fecha_mes]
        );

        $sp = [];

        foreach ($spRaw as $row) {

            $idModulo = $row->idmodulo ?? $row->IDMODULO;

            for ($d = 1; $d <= $numeroDias; $d++) {

                $col = 'd' . str_pad($d, 2, '0', STR_PAD_LEFT);

                if (isset($row->$col)) {
                    $sp[$d][$idModulo] = $row->$col;
                }
            }
        }

        $dias = [];

        for ($dia = 1; $dia <= $numeroDias; $dia++) {

            if (in_array($dia, $diasCerrados)) {
                continue;
            }

            $fecha = sprintf('%04d-%02d-%02d', $fecha_año, $fecha_mes, $dia);

            if ($fecha > $hoy) {
                continue;
            }

            $esFeriado = in_array($fecha, $feriados);

            $resultados = DB::select("
            WITH asistencia AS (
                SELECT
                        mm.IDMODULO,
                        mm.N_MODULO,
                        MIN(ma.HORA) AS hora_asesor

                FROM m_asistencia ma

                JOIN m_personal mp 
                    ON mp.NUM_DOC = ma.NUM_DOC

                LEFT JOIN m_personal_modulo mpm
                    ON mpm.NUM_DOC = ma.NUM_DOC
                AND mpm.IDCENTRO_MAC = ma.IDCENTRO_MAC
                AND mpm.STATUS IN ('itinerante','fijo')
                AND ? BETWEEN mpm.FECHAINICIO AND mpm.FECHAFIN

                LEFT JOIN m_modulo mm 
                    ON mm.IDMODULO = mpm.IDMODULO

                WHERE ma.FECHA = ?
                AND ma.IDCENTRO_MAC = ?

                GROUP BY
                    mm.IDMODULO,
                    mp.NUM_DOC
                )

                SELECT
                    IDMODULO,
                    N_MODULO,
                    MIN(hora_asesor) AS hora_modulo
                FROM asistencia
                GROUP BY
                    IDMODULO,
                    N_MODULO
                ORDER BY
                N_MODULO;", [$fecha, $fecha, $idmac]);

            foreach ($resultados as $r) {

                $dias[$dia][$r->IDMODULO] = [
                    'hora_minima' => $r->hora_modulo,
                    'es_feriado' => $esFeriado
                ];
            }
        }

        $final = [];

        for ($dia = 1; $dia <= $numeroDias; $dia++) {

            foreach ($modulos as $m) {

                if (isset($sp[$dia][$m->idmodulo])) {

                    $final[$dia][$m->idmodulo] = $sp[$dia][$m->idmodulo];
                } elseif (isset($dias[$dia][$m->idmodulo])) {

                    $final[$dia][$m->idmodulo] = $dias[$dia][$m->idmodulo]['hora_minima'];
                } else {

                    $final[$dia][$m->idmodulo] = null;
                }
            }
        }

        return Excel::download(

            new IndicadorPuntualidadExport(
                $mesNombre,
                $nombreMac,
                $final,
                $modulos,
                $numeroDias,
                $fecha_año,
                $fecha_mes,
                $diasCerrados,
                $feriados
            ),

            'INDICADOR_DE_PUNTUALIDAD_' . $nombreMac . '_' . $fecha_año . '_' . $mesNombre . '.xlsx'
        );
    }
}
