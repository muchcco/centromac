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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OcupabilidadExport;

class ReporteOcupabilidadController extends Controller
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
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->orderBy('NOMBRE_MAC', 'ASC')
            ->get();

        return view('reporte.ocupabilidad.index', compact('mac'));
    }

    public function tb_index(Request $request)
    {
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;

        // Obtener el nombre del Centro MAC
        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', '=', $idmac)
            ->select('NOMBRE_MAC')
            ->first();

        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible';

        // Obtener el año y mes
        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = $request->mes ?: date('m');

        // Llamar al procedimiento SP_INDICADOR_MES_MAC para obtener los días marcados
        $diasmesmacs = DB::select('CALL SP_INDICADOR_MES_MAC(?, ?)', [$fecha_año, $fecha_mes]);

        // Llamar al procedimiento SP_Feriado para obtener los días hábiles y otros detalles
        $feriadosInfo = DB::select('CALL SP_Feriado(?, ?)', [$fecha_mes, $fecha_año]);

        // Crear un array para asociar los feriados con cada IDCENTROMAC
        $feriadosPorCentro = [];
        foreach ($feriadosInfo as $feriado) {
            $feriadosPorCentro[$feriado->IDCENTROMAC] = [
                'dias_totales' => $feriado->DÍAS_TOTALES_DEL_MES,
                'domingos' => $feriado->DOMINGOS,
                'feriados' => $feriado->FERIADOS,
                'feriados_domingos' => $feriado->FERIADOS_DOMINGOS,
                'dias_habiles' => $feriado->DIAS_HABILES
            ];
        }

        // Obtener los módulos del centro MAC correspondiente en el rango de fechas
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'm_modulo.idcentro_mac') // Unir la tabla M_CENTRO_MAC
            ->where(function ($query) use ($fecha_año, $fecha_mes) {
                $fechaInicio = Carbon::createFromFormat('Y-m', "$fecha_año-$fecha_mes")->startOfMonth();
                $fechaFin = Carbon::createFromFormat('Y-m', "$fecha_año-$fecha_mes")->endOfMonth();
                $query->where('m_modulo.fechainicio', '<=', $fechaFin)
                    ->where('m_modulo.fechafin', '>=', $fechaInicio);
            })
            ->where('m_modulo.es_administrativo', 'NO')
            ->select('m_modulo.idmodulo', 'm_modulo.n_modulo', 'm_entidad.ABREV_ENTIDAD', 'm_modulo.fechainicio', 'm_modulo.fechafin', 'm_modulo.idcentro_mac', 'M_CENTRO_MAC.NOMBRE_MAC') // Seleccionamos NOMBRE_MAC
            ->orderBy('M_CENTRO_MAC.NOMBRE_MAC') // Ordenamos por nombre_mac
            ->orderBy('m_modulo.n_modulo') // Luego ordenamos por nombre del módulo
            ->get();

        // Crear un array para asociar los datos de días marcados y días hábiles a los módulos
        $dias = [];
        foreach ($modulos as $modulo) {
            // Buscar los días marcados para el IDCENTRO_MAC del módulo
            $diasmesmac = collect($diasmesmacs)->firstWhere('IDMODULO', $modulo->idmodulo);
            $centroMacId = $modulo->idcentro_mac;

            // Obtener el nombre del Centro MAC para este módulo
            $centroMac = DB::table('M_CENTRO_MAC')
                ->where('IDCENTRO_MAC', '=', $centroMacId)
                ->select('NOMBRE_MAC')
                ->first();

            // Verificar si el nombre del Centro MAC está disponible
            $nombre_mac = $centroMac ? $centroMac->NOMBRE_MAC : 'Centro MAC no disponible';

            // Obtener los días hábiles para el Centro MAC
            $diasHabiles = isset($feriadosPorCentro[$centroMacId]) ? $feriadosPorCentro[$centroMacId]['dias_habiles'] : 0;

            // Aquí calculamos el porcentaje de ocupabilidad
            $diasMarcados = $diasmesmac ? $diasmesmac->total_horas_minimas_no_nulas : 0; // Este es el campo de días marcados
            $porcentaje = $diasHabiles > 0 ? ($diasMarcados / $diasHabiles) * 100 : 0;

            // Agregar a la variable $dias
            $dias[$modulo->idmodulo] = [
                'centromac' => $nombre_mac,
                'modulo' => $modulo->n_modulo,
                'entidad' => $modulo->ABREV_ENTIDAD,
                'dias_marcados' => $diasMarcados,
                'dias_habiles' => $diasHabiles,
                'porcentaje' => number_format($porcentaje, 2),
            ];
        }

        // Retornar la vista con los datos calculados
        return view('reporte.ocupabilidad.tablas.tb_index', compact('nombreMac', 'dias', 'fecha_año', 'fecha_mes'));
    }
    public function export_excel(Request $request)
    {
        // Obtener el idcentromac desde el formulario (si está presente)
        $idmac = $request->input('mac') ?: auth()->user()->idcentro_mac ?: 11;

        // Obtener el nombre del Centro MAC
        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', '=', $idmac)
            ->select('NOMBRE_MAC')
            ->first();

        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible';

        // Obtener el año y mes
        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = $request->mes ?: date('m');

        // Llamar al procedimiento SP_INDICADOR_MES_MAC1 para obtener los días marcados
        $diasmesmacs = DB::select('CALL SP_INDICADOR_MES_MAC(?, ?)', [$fecha_año, $fecha_mes]);

        // Llamar al procedimiento SP_Feriado para obtener los días hábiles y otros detalles
        $feriadosInfo = DB::select('CALL SP_Feriado(?, ?)', [$fecha_mes, $fecha_año]);

        // Crear un array para asociar los feriados con cada IDCENTROMAC
        $feriadosPorCentro = [];
        foreach ($feriadosInfo as $feriado) {
            $feriadosPorCentro[$feriado->IDCENTROMAC] = [
                'dias_totales' => $feriado->DÍAS_TOTALES_DEL_MES,
                'domingos' => $feriado->DOMINGOS,
                'feriados' => $feriado->FERIADOS,
                'feriados_domingos' => $feriado->FERIADOS_DOMINGOS,
                'dias_habiles' => $feriado->DIAS_HABILES
            ];
        }

        // Obtener los módulos del centro MAC correspondiente en el rango de fechas
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'm_modulo.idcentro_mac') // Unir la tabla M_CENTRO_MAC
            ->where(function ($query) use ($fecha_año, $fecha_mes) {
                $fechaInicio = Carbon::createFromFormat('Y-m', "$fecha_año-$fecha_mes")->startOfMonth();
                $fechaFin = Carbon::createFromFormat('Y-m', "$fecha_año-$fecha_mes")->endOfMonth();
                $query->where('m_modulo.fechainicio', '<=', $fechaFin)
                    ->where('m_modulo.fechafin', '>=', $fechaInicio);
            })
            ->where('m_modulo.es_administrativo', 'NO')
            ->select('m_modulo.idmodulo', 'm_modulo.n_modulo', 'm_entidad.ABREV_ENTIDAD', 'm_modulo.fechainicio', 'm_modulo.fechafin', 'm_modulo.idcentro_mac', 'M_CENTRO_MAC.NOMBRE_MAC') // Seleccionamos NOMBRE_MAC
            ->orderBy('M_CENTRO_MAC.NOMBRE_MAC') // Ordenamos por nombre_mac
            ->orderBy('m_modulo.n_modulo') // Luego ordenamos por nombre del módulo
            ->get();

        // Crear un array para asociar los datos de días marcados y días hábiles a los módulos
        $dias = [];
        foreach ($modulos as $modulo) {
            // Buscar los días marcados para el IDCENTRO_MAC del módulo
            $diasmesmac = collect($diasmesmacs)->firstWhere('IDMODULO', $modulo->idmodulo);
            $centroMacId = $modulo->idcentro_mac;

            // Obtener el nombre del Centro MAC para este módulo
            $centroMac = DB::table('M_CENTRO_MAC')
                ->where('IDCENTRO_MAC', '=', $centroMacId)
                ->select('NOMBRE_MAC')
                ->first();

            // Verificar si el nombre del Centro MAC está disponible
            $nombre_mac = $centroMac ? $centroMac->NOMBRE_MAC : 'Centro MAC no disponible';

            // Obtener los días hábiles para el Centro MAC
            $diasHabiles = isset($feriadosPorCentro[$centroMacId]) ? $feriadosPorCentro[$centroMacId]['dias_habiles'] : 0;

            // Aquí calculamos el porcentaje de ocupabilidad
            $diasMarcados = $diasmesmac ? $diasmesmac->total_horas_minimas_no_nulas : 0; // Este es el campo de días marcados
            $porcentaje = $diasHabiles > 0 ? ($diasMarcados / $diasHabiles) * 100 : 0;

            // Agregar a la variable $dias
            $dias[$modulo->idmodulo] = [
                'centromac' => $nombre_mac,
                'modulo' => $modulo->n_modulo,
                'entidad' => $modulo->ABREV_ENTIDAD,
                'dias_marcados' => $diasMarcados,
                'dias_habiles' => $diasHabiles,
                'porcentaje' => number_format($porcentaje, 2),
            ];
        }
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $mesNombre = $meses[(int)$fecha_mes] ?? 'Mes no válido'; // Convertimos el mes numérico a nombre
        // Exportar los datos a un archivo Excel
        $export = Excel::download(
            new OcupabilidadExport($nombreMac, $dias, $fecha_año, $fecha_mes,$mesNombre),
            'Reporte_Ocupabilidad_' . $nombreMac . '_' . $fecha_año . '_' . $fecha_mes . '.xlsx'
        );
        return $export;
    }
}
