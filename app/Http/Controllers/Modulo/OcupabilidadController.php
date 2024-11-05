<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Modulo;
use App\Models\Asistencia;
use App\Models\Feriado;
use App\Models\Itinerante;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;

class OcupabilidadController extends Controller
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

    public function index()
    {
        $mac = DB::table('M_CENTRO_MAC')
                ->where(function($query) {
                    if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                        $query->where('IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                    }
                })
                ->orderBy('NOMBRE_MAC', 'ASC')
                ->get();

        return view('ocupabilidad.index', compact('mac'));
    }

    public function tb_index(Request $request)
    {
        // Validar que se hayan proporcionado los parámetros obligatorios
        if (!$request->filled('mac') || !$request->filled('mes') || !$request->filled('año')) {
            return response()->json(['error' => 'Por favor, proporciona los campos de MAC, Mes y Año.'], 422);
        }

        // Obtener los parámetros
        $mac = $request->input('mac');
        $mes = $request->input('mes');
        $año = $request->input('año');

        // Crear las fechas de inicio y fin basadas en el mes y el año proporcionados
        $fechaInicio = Carbon::create($año, $mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        // Obtener el período de fechas
        $period = CarbonPeriod::create($fechaInicio, $fechaFin);

        // Contar los días totales en el rango
        $diasTotales = $period->count();

        // Obtener los días feriados que caen en el rango
        $feriados = Feriado::whereBetween('fecha', [$fechaInicio, $fechaFin])->pluck('fecha')->toArray();

        // Calcular los domingos en el rango
        $domingos = 0;
        foreach ($period as $date) {
            if ($date->isSunday()) {
                $domingos++;
            }
        }

        // Contar los días feriados que no caen en domingo
        $diasFeriados = count(array_filter($feriados, function ($feriado) {
            return !Carbon::parse($feriado)->isSunday();
        }));

        // Calcular los días hábiles
        $diasHabiles = $diasTotales - $domingos - $diasFeriados;

        // Obtener todos los módulos del MAC especificado con sus entidades
        $modulos = Modulo::with('entidad')
            ->join('m_centro_mac', 'm_centro_mac.idcentro_mac', '=', 'm_modulo.idcentro_mac')
            ->where('m_centro_mac.idcentro_mac', $mac)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fechainicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fechafin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->where('fechainicio', '<=', $fechaInicio)
                            ->where('fechafin', '>=', $fechaFin);
                    });
            })
            ->orderBy('n_modulo', 'asc')
            ->get();

        // Preparar array para almacenar los resultados
        $resultados = [];

        foreach ($modulos as $modulo) {
            // Contar los días marcados por el personal itinerante y regular
            $diasMarcados = DB::table('m_asistencia')
                ->join('m_personal', 'm_asistencia.NUM_DOC', '=', 'm_personal.NUM_DOC')
                ->leftJoin('m_itinerante', function ($join) use ($fechaInicio, $fechaFin, $modulo) {
                    $join->on('m_asistencia.NUM_DOC', '=', 'm_itinerante.NUM_DOC')
                        ->where(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->whereBetween('m_itinerante.fechainicio', [$fechaInicio, $fechaFin])
                                ->orWhereBetween('m_itinerante.fechafin', [$fechaInicio, $fechaFin])
                                ->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                                    $query->where('m_itinerante.fechainicio', '<=', $fechaInicio)
                                        ->where('m_itinerante.fechafin', '>=', $fechaFin);
                                });
                        });
                })
                ->where(function ($query) use ($modulo) {
                    $query->where('m_personal.IDMODULO', $modulo->IDMODULO)
                        ->orWhere('m_itinerante.IDMODULO', $modulo->IDMODULO);
                })
                ->whereBetween(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'), [$fechaInicio, $fechaFin])
                ->distinct()
                ->count(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'));

            // Verificar si el módulo tiene una entidad, de lo contrario, asignar "Sin Entidad"
            $entidadNombre = $modulo->entidad ? $modulo->entidad->NOMBRE_ENTIDAD : 'Sin Entidad';

            // Calcular el porcentaje de ocupabilidad
            $porcentaje = $diasHabiles > 0 ? ($diasMarcados / $diasHabiles) * 100 : 0;

            // Almacenar los resultados
            $resultados[] = [
                'modulo' => $modulo,
                'entidad' => $entidadNombre,
                'mac' => $modulo->NOMBRE_MAC,
                'diasMarcados' => $diasMarcados,
                'diasHabiles' => $diasHabiles,
                'porcentaje' => $porcentaje
            ];
        }

        // Retornar la vista con los resultados
        return view('ocupabilidad.tablas.tb_index', compact('resultados'));
    }

    
}
