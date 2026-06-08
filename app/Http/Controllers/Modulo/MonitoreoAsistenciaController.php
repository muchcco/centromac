<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class MonitoreoAsistenciaController extends Controller
{
    /**
     * Obtiene el centro MAC del usuario autenticado
     */
    private function centro_mac()
    {
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('m_centro_mac.IDCENTRO_MAC', $us_id)
            ->select('m_centro_mac.IDCENTRO_MAC', 'm_centro_mac.NOMBRE_MAC')
            ->first();

        return (object) [
            'idmac' => $user->IDCENTRO_MAC,
            'name_mac' => $user->NOMBRE_MAC,
        ];
    }

    /**
     * Vista principal del monitoreo de asistencia
     */
    public function index()
    {
        $usuario = auth()->user();

        /**
         *  Solo Administrador o Monitor pueden ver todos los MAC
         * Los demás (Especialista TIC, Orientador, Asesor, Supervisor, Coordinador)
         * solo ven su propio MAC.
         */
        if ($usuario->hasRole('Administrador|Monitor|Moderador')) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
                ->orderBy('NOMBRE_MAC', 'ASC')
                ->get();
        }

        if ($usuario->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->where('IDCENTRO_MAC', $this->centro_mac()->idmac)
                ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
                ->get();
        }

        return view('monitoreo.asistencia.index', compact('macs'));
    }

    /**
     * Carga la tabla de monitoreo
     */
    public function tabla(Request $request)
    {
        $anio = $request->anio ?? date('Y');
        $mes  = (int) ($request->mes ?? date('m'));

        $usuario = auth()->user();
        $idmacUsuario = $usuario->idcentro_mac;

        $query = DB::table('db_centros_mac.cierre_asistencia_log as c')
            ->join('db_centros_mac.m_centro_mac as m', 'm.IDCENTRO_MAC', '=', 'c.idmac')
            ->where('c.anio', $anio)
            ->where(function ($q) use ($mes) {
                // 🔄 Incluye cierres del mes o el 1er día del siguiente (cierres mensuales)
                $q->where('c.mes', $mes)
                    ->orWhere(function ($q2) use ($mes) {
                        $mesSig = $mes + 1;
                        if ($mesSig > 12) $mesSig = 1;
                        $q2->where('c.mes', $mesSig)
                            ->whereDay('c.fecha', 1)
                            ->where('c.tipo_cierre', 'Cierre Mensual');
                    });
            });

        /**
         * 🔒 Lógica de restricción:
         * - Administrador y Monitor: pueden ver todos los MAC y filtrar.
         * - Otros roles (Especialista TIC, Asesor, etc.): solo su centro MAC.
         */
        if ($usuario->hasRole('Administrador|Monitor|Moderador')) {
            if ($request->filled('idmac')) {
                $query->where('m.IDCENTRO_MAC', $request->idmac);
            }
        }

        if ($usuario->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $query->where('m.IDCENTRO_MAC', $idmacUsuario);
        }

        $cierres = $query
            ->select(
                'm.IDCENTRO_MAC',
                'm.NOMBRE_MAC as nombre_mac',
                'c.fecha',
                'c.fecha_registro',
                'c.user_nombre',
                'c.tipo_cierre'
            )
            ->orderBy('m.NOMBRE_MAC', 'ASC')
            ->orderBy('c.fecha', 'DESC')
            ->get();


        return view('monitoreo.asistencia.tablas.tb_index', compact('cierres', 'anio', 'mes'));
    }

    /**
     * Detalle del cierre
     */
    public function detalle($idmac)
    {
        $anio = date('Y');
        $mes  = date('m');

        $detalles = DB::table('db_centros_mac.cierre_asistencia_log as c')
            ->join('db_centros_mac.m_centro_mac as m', 'm.IDCENTRO_MAC', '=', 'c.idmac')
            ->where('c.idmac', $idmac)
            ->where('c.anio', $anio)
            ->where('c.mes', $mes)
            ->select('c.fecha', 'c.tipo_cierre', 'c.user_nombre', 'c.fecha_registro')
            ->orderBy('c.fecha', 'DESC')
            ->get();

        return response()->json($detalles);
    }
    public function pivot_index()
    {
        $usuario = auth()->user();
        $idmacUsuario = $usuario->idcentro_mac;

        // ✅ 1. MACs visibles según rol
        if ($usuario->hasRole('Administrador|Monitor|Moderador')) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
                ->orderBy('NOMBRE_MAC')
                ->get();

            $feriados = DB::table('db_centros_mac.feriados')
                ->select('id', 'name', 'fecha', 'id_centromac')
                ->orderBy('fecha')
                ->get();
        } else {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->where('IDCENTRO_MAC', $idmacUsuario)
                ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
                ->get();

            $feriados = DB::table('db_centros_mac.feriados')
                ->select('id', 'name', 'fecha', 'id_centromac')
                ->whereNull('id_centromac')
                ->orWhere('id_centromac', $idmacUsuario)
                ->orderBy('fecha')
                ->get();
        }

        return view('monitoreo.asistencia.pivot_index', compact('macs', 'feriados'));
    }


    public function tb_pivot(Request $request)
    {
        $anio = $request->anio ?? date('Y');
        $mes  = (int) ($request->mes ?? date('m'));
        $idmac = $request->idmac ?? null;

        $usuario = auth()->user();
        $idmacUsuario = $usuario->idcentro_mac;

        $diasMes = Carbon::createFromDate($anio, $mes, 1)->daysInMonth;

        // ✅ 1. MACs según rol
        $macsQuery = DB::table('db_centros_mac.m_centro_mac')
            ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
            ->orderBy('NOMBRE_MAC')
            ->whereNotIn('IDCENTRO_MAC', [5, 6]); // excluir MAC 5 y 6

        if ($usuario->hasRole('Administrador|Monitor|Moderador')) {
            if ($idmac) {
                $macsQuery->where('IDCENTRO_MAC', $idmac);
            }
        } else {
            $macsQuery->where('IDCENTRO_MAC', $idmacUsuario);
        }

        $macs = $macsQuery->get();

        // ✅ 2. Traer asistencia real (no cierres)
        $asistencias = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->select('idmac', 'fecha_asistencia')
            ->whereYear('fecha_asistencia', $anio)
            ->whereMonth('fecha_asistencia', $mes)
            ->whereNotIn('idmac', [5, 6])
            ->get();

        // ✅ 3. Feriados nacionales + locales
        $feriados = DB::table('db_centros_mac.feriados')
            ->select('fecha', 'id_centromac', 'name')
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->get();

        // ✅ 4. Construcción del PIVOT
        $pivotData = [];
        foreach ($macs as $mac) {
            $fila = [
                'mac' => $mac->NOMBRE_MAC,
                'dias' => []
            ];

            for ($d = 1; $d <= $diasMes; $d++) {
                $fecha = Carbon::createFromDate($anio, $mes, $d)->toDateString();
                $carbonFecha = Carbon::parse($fecha);
                $nombreDia = strtolower($carbonFecha->locale('es')->dayName);

                $esDomingo = $nombreDia === 'domingo';
                $esFeriado = $feriados->contains(function ($f) use ($fecha, $mac) {
                    return $f->fecha == $fecha && (is_null($f->id_centromac) || $f->id_centromac == $mac->IDCENTRO_MAC);
                });

                if ($esDomingo || $esFeriado) {
                    $titulo = $esFeriado
                        ? ($feriados->firstWhere('fecha', $fecha)->name ?? 'Feriado')
                        : 'Domingo';

                    $estado = $esDomingo
                        ? "<div class='celda-domingo' title='{$titulo}'></div>"
                        : "<div class='celda-feriado' title='{$titulo}'></div>";

                    $fila['dias'][$d] = $estado;
                    continue;
                }

                // ✅ Verificar si existe asistencia en esa fecha y MAC
                $tieneAsistencia = $asistencias->contains(function ($a) use ($mac, $fecha) {
                    return $a->idmac == $mac->IDCENTRO_MAC && $a->fecha_asistencia == $fecha;
                });

                if ($fecha > now()->toDateString()) {
                    $estado = '<span class="text-secondary">–</span>'; // futuro
                } elseif ($tieneAsistencia) {
                    $estado = '<span class="text-success fw-bold">✅</span>'; // hay registros
                } else {
                    $estado = '<span class="text-danger fw-bold">❌</span>'; // no hay registros
                }

                $fila['dias'][$d] = $estado;
            }

            $pivotData[] = $fila;
        }

        return view('monitoreo.asistencia.tablas.tb_pivot', compact('pivotData', 'anio', 'mes', 'diasMes'));
    }
}
