<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Modulo;
use App\Models\Feriado;
use App\Models\Asistencia;
use App\Models\Personal;
use App\Models\Itinerante;
use App\Models\User;

class PuntualidadController extends Controller
{
    private function centro_mac()
    {
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;

        return (object) ['idmac' => $idmac, 'name_mac' => $name_mac];
    }

    public function index()
    {
        return view('puntualidad.index');
    }
    public function tb_index(Request $request)
    {
        if (!$request->filled('fechainicio') || !$request->filled('fechafin')) {
            return response()->json(['error' => 'Por favor, proporciona un rango de fechas válido.'], 422);
        }
    
        $fechaInicio = Carbon::parse($request->fechainicio);
        $fechaFin = Carbon::parse($request->fechafin);
    
        $centroMac = $this->centro_mac();
    
        $modulos = Modulo::with('entidad')->where('IDCENTRO_MAC', $centroMac->idmac)->get();
    
        $data = $modulos->map(function ($modulo) use ($fechaInicio, $fechaFin) {
            // Combinamos las fechas marcadas por personal regular e itinerante
            $diasMarcados = DB::table('m_asistencia')
                ->join('m_personal', 'm_personal.NUM_DOC', '=', 'm_asistencia.NUM_DOC')
                ->leftJoin('m_itinerante', 'm_itinerante.NUM_DOC', '=', 'm_asistencia.NUM_DOC')
                ->where(function ($query) use ($modulo) {
                    $query->where('m_personal.IDMODULO', $modulo->IDMODULO)
                          ->orWhere('m_itinerante.IDMODULO', $modulo->IDMODULO);
                })
                ->whereBetween(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'), [$fechaInicio, $fechaFin])
                ->select(DB::raw('DISTINCT DATE(m_asistencia.FECHA_BIOMETRICO) as fecha_biometrico'))
                ->get()
                ->pluck('fecha_biometrico');
    
            // Calculamos los días puntuales
            $diasPuntuales = DB::table('m_asistencia')
                ->join('m_personal', 'm_personal.NUM_DOC', '=', 'm_asistencia.NUM_DOC')
                ->leftJoin('m_itinerante', 'm_itinerante.NUM_DOC', '=', 'm_asistencia.NUM_DOC')
                ->where(function ($query) use ($modulo) {
                    $query->where('m_personal.IDMODULO', $modulo->IDMODULO)
                          ->orWhere('m_itinerante.IDMODULO', $modulo->IDMODULO);
                })
                ->whereBetween(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'), [$fechaInicio, $fechaFin])
                ->whereTime('m_asistencia.FECHA_BIOMETRICO', '<', '08:16:00')
                ->select(DB::raw('DISTINCT DATE(m_asistencia.FECHA_BIOMETRICO) as fecha_biometrico'))
                ->get()
                ->pluck('fecha_biometrico');
    
            $diasMarcadosCount = $diasMarcados->count();
            $diasPuntualesCount = $diasPuntuales->count();
    
            $porcentaje = $diasMarcadosCount > 0 ? round(($diasPuntualesCount / $diasMarcadosCount) * 100, 2) : 0;
    
            return [
                'modulo' => $modulo->N_MODULO,
                'entidad' => $modulo->entidad ? $modulo->entidad->NOMBRE_ENTIDAD : 'Sin Entidad',
                'mac' => $modulo->NOMBRE_MAC,
                'dias_marcados' => $diasMarcadosCount,
                'dias_puntuales' => $diasPuntualesCount,
                'porcentaje' => $porcentaje
            ];
        });
    
        return view('puntualidad.tablas.tb_index', compact('data'));
    }
    
}
