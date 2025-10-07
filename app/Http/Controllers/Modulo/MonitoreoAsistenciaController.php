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
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
            ->select('M_CENTRO_MAC.IDCENTRO_MAC', 'M_CENTRO_MAC.NOMBRE_MAC')
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
         * ✅ Solo Administrador o Monitor pueden ver todos los MAC
         * Los demás (Especialista TIC, Orientador, Asesor, Supervisor, Coordinador)
         * solo ven su propio MAC.
         */
        if ($usuario->hasRole('Administrador|Monitor')) {
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
        if ($usuario->hasRole('Administrador|Monitor')) {
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
                DB::raw('MAX(c.fecha) as ultima_fecha_cerrada'),
                DB::raw('MAX(c.fecha_registro) as fecha_registro'),
                DB::raw('MAX(c.user_nombre) as usuario_cerro'),
                DB::raw('MAX(c.tipo_cierre) as tipo_cierre')
            )
            ->groupBy('m.IDCENTRO_MAC', 'm.NOMBRE_MAC')
            ->orderBy('m.NOMBRE_MAC', 'ASC')
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

        // Roles con acceso total
        if ($usuario->hasRole('Administrador|Monitor')) {
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
                ->orderBy('NOMBRE_MAC')
                ->get();
        } else {
            // Solo su MAC
            $macs = DB::table('db_centros_mac.m_centro_mac')
                ->where('IDCENTRO_MAC', $idmacUsuario)
                ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
                ->get();
        }

        return view('monitoreo.asistencia.pivot_index', compact('macs'));
    }


    public function tb_pivot(Request $request)
    {
        $anio = $request->anio ?? date('Y');
        $mes  = (int) ($request->mes ?? date('m'));
        $idmac = $request->idmac ?? null;

        $usuario = auth()->user();
        $idmacUsuario = $usuario->idcentro_mac;

        $diasMes = Carbon::createFromDate($anio, $mes, 1)->daysInMonth;

        // Base: lista de MACs según rol
        $macsQuery = DB::table('db_centros_mac.m_centro_mac')
            ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
            ->orderBy('NOMBRE_MAC');

        if ($usuario->hasRole('Administrador|Monitor')) {
            if ($idmac) {
                $macsQuery->where('IDCENTRO_MAC', $idmac);
            }
        } else {
            $macsQuery->where('IDCENTRO_MAC', $idmacUsuario);
        }

        $macs = $macsQuery->get();

        // Traer cierres
        $cierres = DB::table('db_centros_mac.cierre_asistencia_log')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->select('idmac', 'fecha', 'tipo_cierre')
            ->get();

        // Estructura del pivot
        $pivotData = [];
        foreach ($macs as $mac) {
            $fila = [
                'mac' => $mac->NOMBRE_MAC,
                'dias' => []
            ];

            for ($d = 1; $d <= $diasMes; $d++) {
                $fecha = Carbon::createFromDate($anio, $mes, $d)->toDateString();
                $cierre = $cierres->first(function ($c) use ($mac, $fecha) {
                    return $c->idmac == $mac->IDCENTRO_MAC && $c->fecha == $fecha;
                });

                if ($fecha > now()->toDateString()) {
                    $estado = '<span class="text-secondary">–</span>'; // futuro
                } elseif ($cierre) {
                    $estado = '<span class="text-success fw-bold">✅</span>';
                } else {
                    $estado = '<span class="text-danger fw-bold">❌</span>';
                }

                $fila['dias'][$d] = $estado;
            }

            $pivotData[] = $fila;
        }

        return view('monitoreo.asistencia.tablas.tb_pivot', compact('pivotData', 'anio', 'mes', 'diasMes'));
    }
}
