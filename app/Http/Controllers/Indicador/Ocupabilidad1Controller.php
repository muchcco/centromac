<?php

namespace App\Http\Controllers\Indicador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IndicadorOcupabilidadExport;
use Carbon\Carbon;

class Ocupabilidad1Controller extends Controller
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
        return view('indicador.ocupabilidad.index');
    }


    public function tb_index(Request $request)
    {
        $idmac = $this->centro_mac()->idmac;

        $fecha_año = $request->año ?: date('Y'); // Año actual si no se proporciona
        $fecha_mes = $request->mes ?: date('m'); // Mes actual si no se proporciona
        // Obtener los feriados del mes actual
        $feriados = DB::table('feriados')
            ->whereYear('fecha', $fecha_año)
            ->whereMonth('fecha', $fecha_mes)
            ->pluck('fecha') // Obtenemos solo las fechas
            ->toArray(); // Convertir en un array para fácil manipulación
        // Obtener el nombre del mes en español
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
            12 => 'Diciembre'
        ];

        $numero_mes = (int) $fecha_mes;
        $nombre_mes = $meses[$numero_mes];

        // Calcular el número de días en el mes
        $daysInMonth = Carbon::create($fecha_año, $fecha_mes, 1)->daysInMonth;

        // Obtener el primer y último día del mes actual
        $startOfMonth = Carbon::create($fecha_año, $fecha_mes, 1)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::create($fecha_año, $fecha_mes, 1)->endOfMonth()->format('Y-m-d');

        // Crear dinámicamente las columnas DIA_1, DIA_2, ..., DIA_N (según el número de días del mes)
        $selectRaw = [
            'M_MODULO.N_MODULO',
            'M_ENTIDAD.NOMBRE_ENTIDAD',
            'M_MODULO.FECHAINICIO',
            'M_MODULO.FECHAFIN'
        ];

        // Crear dinámicamente las columnas de días según las fechas disponibles
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $fecha_dia = Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d'); // Genera la fecha para el día

            // Comprobar que la fecha esté dentro del rango de FECHAINICIO y FECHAFIN
            $selectRaw[] = DB::raw("
            CASE 
                WHEN '$fecha_dia' BETWEEN M_MODULO.FECHAINICIO AND M_MODULO.FECHAFIN 
                THEN MIN(CASE WHEN DATE(M_ASISTENCIA.FECHA) = '$fecha_dia' THEN M_ASISTENCIA.HORA END) 
                ELSE NULL 
            END AS DIA_$i
        ");
        }

        // Consulta
        $query = DB::table('M_MODULO')
            ->join('M_PERSONAL', 'M_PERSONAL.IDMODULO', '=', 'M_MODULO.IDMODULO')
            ->join('M_ASISTENCIA', 'M_ASISTENCIA.NUM_DOC', '=', 'M_PERSONAL.NUM_DOC')
            ->join('M_ENTIDAD', 'M_MODULO.IDENTIDAD', '=', 'M_ENTIDAD.IDENTIDAD')
            ->select($selectRaw)
            // Asegurar que el módulo está activo al menos un día durante el mes actual
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('M_MODULO.FECHAINICIO', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('M_MODULO.FECHAFIN', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($query) use ($startOfMonth, $endOfMonth) {
                        // El módulo abarca todo el mes
                        $query->where('M_MODULO.FECHAINICIO', '<=', $startOfMonth)
                            ->where('M_MODULO.FECHAFIN', '>=', $endOfMonth);
                    });
            })
            ->where('M_MODULO.IDCENTRO_MAC', '=', $idmac)
            ->where('M_ASISTENCIA.IDCENTRO_MAC', '=', $idmac)
            ->groupBy('M_MODULO.IDMODULO', 'M_MODULO.N_MODULO', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_MODULO.FECHAINICIO', 'M_MODULO.FECHAFIN')
            ->orderBy('M_MODULO.N_MODULO')
            ->get();

        $name_mac = $this->centro_mac()->name_mac;

        // Pasar las variables a la vista
        return view('indicador.ocupabilidad.tablas.tb_index', compact('name_mac', 'query', 'fecha_mes', 'fecha_año', 'nombre_mes', 'daysInMonth', 'feriados'));
    }

    public function export_excel(Request $request)
    {
        $idmac = $this->centro_mac()->idmac;
        // Obtener fecha actual si no se proporciona año o mes
        $fecha_I = date("Y-m-d");
        $fecha_mes = $request->mes ?: date('m', strtotime($fecha_I));
        $fecha_año = $request->año ?: date('Y', strtotime($fecha_I));

        // Obtener el centro MAC
        $name_mac = $this->centro_mac()->name_mac;

        // Obtener los feriados del mes actual
        $feriados = DB::table('feriados')
            ->whereYear('fecha', $fecha_año)
            ->whereMonth('fecha', $fecha_mes)
            ->pluck('fecha')
            ->toArray();

        // Obtener el nombre del mes en español
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
            12 => 'Diciembre'
        ];

        $numero_mes = (int) $fecha_mes;
        $nombre_mes = $meses[$numero_mes] ?? 'Mes no válido';

        // Calcular el número de días en el mes
        $daysInMonth = Carbon::create($fecha_año, $fecha_mes, 1)->daysInMonth;

        // Crear dinámicamente las columnas DIA_1, DIA_2, ..., DIA_N (según el número de días del mes)
        $selectRaw = [
            'M_MODULO.N_MODULO',
            'M_ENTIDAD.NOMBRE_ENTIDAD',
            'M_MODULO.FECHAINICIO',
            'M_MODULO.FECHAFIN'
        ];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $fecha_dia = Carbon::create($fecha_año, $fecha_mes, $i)->format('Y-m-d');
            // Comprobar que la fecha esté dentro del rango de FECHAINICIO y FECHAFIN
            $selectRaw[] = DB::raw("
                CASE 
                    WHEN '$fecha_dia' BETWEEN M_MODULO.FECHAINICIO AND M_MODULO.FECHAFIN 
                    THEN MIN(CASE WHEN DATE(M_ASISTENCIA.FECHA) = '$fecha_dia' THEN M_ASISTENCIA.HORA END) 
                    ELSE NULL 
                END AS DIA_$i
            ");
        }

        // Consulta
        $query = DB::table('M_MODULO')
            ->join('M_PERSONAL', 'M_PERSONAL.IDMODULO', '=', 'M_MODULO.IDMODULO')
            ->join('M_ASISTENCIA', 'M_ASISTENCIA.NUM_DOC', '=', 'M_PERSONAL.NUM_DOC')
            ->join('M_ENTIDAD', 'M_MODULO.IDENTIDAD', '=', 'M_ENTIDAD.IDENTIDAD')
            ->select($selectRaw)
            ->where(function ($query) use ($fecha_año, $fecha_mes, $daysInMonth) {
                $startOfMonth = "$fecha_año-$fecha_mes-01";
                $endOfMonth = "$fecha_año-$fecha_mes-$daysInMonth";
                $query->whereBetween('M_MODULO.FECHAINICIO', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('M_MODULO.FECHAFIN', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->where('M_MODULO.FECHAINICIO', '<=', $startOfMonth)
                            ->where('M_MODULO.FECHAFIN', '>=', $endOfMonth);
                    });
            })
            ->where('M_MODULO.IDCENTRO_MAC', '=', $idmac)
            ->where('M_ASISTENCIA.IDCENTRO_MAC', '=', $idmac)
            ->groupBy('M_MODULO.IDMODULO', 'M_MODULO.N_MODULO', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_MODULO.FECHAINICIO', 'M_MODULO.FECHAFIN')
            ->orderBy('M_MODULO.N_MODULO')
            ->get();

        // Generar el archivo Excel
        $export = Excel::download(
            new IndicadorOcupabilidadExport($query, $fecha_año, $fecha_mes, $name_mac, $nombre_mes, $daysInMonth, $feriados),
            'INDICADOR_DE_OCUPABILIDAD_' . $name_mac . '_' . $fecha_año . '_' . $nombre_mes . '.xlsx'
        );

        return $export;
    }
}
