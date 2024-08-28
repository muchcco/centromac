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
    private function centro_mac(){
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac'=>$idmac, 'name_mac'=>$name_mac ];

        return (object) $resp;
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

        $period = CarbonPeriod::create($fechaInicio, $fechaFin);

        $diasTotales = $period->count();

        $feriados = Feriado::whereBetween('fecha', [$fechaInicio, $fechaFin])->pluck('fecha')->toArray();

        $domingos = 0;
        foreach ($period as $date) {
            if ($date->isSunday()) {
                $domingos++;
            }
        }

        $diasFeriados = count(array_filter($feriados, function ($feriado) {
            return !Carbon::parse($feriado)->isSunday();
        }));

        $diasHabiles = $diasTotales - $domingos - $diasFeriados;

         // Obtener todos los módulos con sus entidades
         $modulos = Modulo::with('entidad')
         ->join('m_centro_mac', 'm_centro_mac.idcentro_mac', '=', 'm_modulo.idcentro_mac')
         ->where(function ($query) use ($fechaInicio, $fechaFin) {
             $query->whereBetween('fechainicio', [$fechaInicio, $fechaFin])
                 ->orWhereBetween('fechafin', [$fechaInicio, $fechaFin])
                 ->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                     $query->where('fechainicio', '<=', $fechaInicio)
                         ->where('fechafin', '>=', $fechaFin);
                 });
         })
         ->where(function($query) {
            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
            }
        })  
         ->get();

        $data = $modulos->map(function ($modulo) use ($fechaInicio, $fechaFin, $diasHabiles) {
            $personalIds = Personal::where('IDMODULO', $modulo->IDMODULO)->pluck('NUM_DOC');

            // Calcular días marcados y puntuales para itinerantes
            $diasMarcadosItinerante = 0;
            $diasPuntualesItinerante = 0;

            $itinerantes = Itinerante::where('IDMODULO', $modulo->IDMODULO)
                ->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fechainicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fechafin', [$fechaInicio, $fechaFin])
                        ->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->where('fechainicio', '<', $fechaInicio)
                                ->where('fechafin', '>', $fechaFin);
                        });
                })
                ->get();

            foreach ($itinerantes as $itinerante) {
                $fechaMax = max($fechaInicio, Carbon::parse($itinerante->fechainicio));
                $fechaMin = min($fechaFin, Carbon::parse($itinerante->fechafin));

                // Calcular los días marcados dentro del rango de itinerancia
                $diasMarcadosItinerante += DB::table('m_asistencia')
                    ->where('NUM_DOC', $itinerante->NUM_DOC)
                    ->whereBetween(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'), [
                        max($fechaInicio, Carbon::parse($itinerante->fechainicio)),
                        min($fechaFin, Carbon::parse($itinerante->fechafin))
                    ])
                    ->distinct()
                    ->count(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'));


                $diasPuntualesItinerante += Asistencia::where('NUM_DOC', $itinerante->NUM_DOC)
                    ->whereBetween(DB::raw('DATE(FECHA_BIOMETRICO)'), [$fechaMax, $fechaMin])
                    ->whereTime('FECHA_BIOMETRICO', '<', '08:16:00')
                    ->select(DB::raw('DATE(FECHA_BIOMETRICO) as date'))
                    ->distinct()
                    ->count();
            }

            // Obtener los días marcados por el personal regular (fuera del rango de itinerancia) para su módulo original
            $diasMarcadosRegular = DB::table('m_asistencia')
                ->join('m_personal', 'm_asistencia.NUM_DOC', '=', 'm_personal.NUM_DOC')
                ->where('m_personal.IDMODULO', $modulo->IDMODULO)
                ->whereBetween(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'), [$fechaInicio, $fechaFin])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('m_itinerante')
                        ->whereColumn('m_itinerante.NUM_DOC', 'm_asistencia.NUM_DOC')
                        ->whereRaw('DATE(m_asistencia.FECHA_BIOMETRICO) BETWEEN m_itinerante.fechainicio AND m_itinerante.fechafin');
                })
                ->distinct()
                ->count(DB::raw('DATE(m_asistencia.FECHA_BIOMETRICO)'));


            $diasPuntualesRegular = Asistencia::whereIn('NUM_DOC', $personalIds)
                ->whereBetween(DB::raw('DATE(FECHA_BIOMETRICO)'), [$fechaInicio, $fechaFin])
                ->whereTime('FECHA_BIOMETRICO', '<', '08:16:00')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('m_itinerante')
                        ->whereColumn('m_itinerante.NUM_DOC', 'm_asistencia.NUM_DOC')
                        ->whereRaw('DATE(m_asistencia.FECHA_BIOMETRICO) BETWEEN m_itinerante.fechainicio AND m_itinerante.fechafin');
                })
                ->select(DB::raw('DATE(FECHA_BIOMETRICO) as date'))
                ->distinct()
                ->count();

            // Sumar días marcados y puntuales entre itinerantes y regulares
            // Verificar si hay días marcados por itinerantes o personal regular
            if ($diasMarcadosItinerante > 0 || $diasMarcadosRegular > 0) {
                // Sumar los días marcados por itinerantes y por personal regular si existe al menos uno de ellos
                $diasMarcados = $diasMarcadosItinerante + $diasMarcadosRegular;
            } else {
                // Si no hay días marcados, establecer en 0
                $diasMarcados = 0;
            }
            $diasPuntuales = $diasPuntualesItinerante + $diasPuntualesRegular;

            $porcentaje = $diasMarcados > 0 ? round(($diasPuntuales / $diasMarcados) * 100, 2) : 0;

            return [
                'modulo' => $modulo->N_MODULO,
                'entidad' => $modulo->entidad->NOMBRE_ENTIDAD,
                'mac' => $modulo->NOMBRE_MAC,
                'dias_marcados' => $diasMarcados,
                'dias_puntuales' => $diasPuntuales,
                'porcentaje' => $porcentaje
            ];
        });

        return view('puntualidad.tablas.tb_index', compact('data', 'diasHabiles'));
    }
}
