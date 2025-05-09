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
        // Obtener el idcentromac desde el formulario (si está presente)
        $idmac = $request->input('mac');

        // Verificar si no se proporcionó el idcentromac
        if (empty($idmac)) {
            // Si no se proporcionó, tomar el idcentromac del usuario autenticado
            $idmac = auth()->user()->idcentro_mac;
        }

        // Verificar si aún no se ha asignado un idcentromac (en caso de que el usuario no esté autenticado o no tenga este campo)
        if (empty($idmac)) {
            // Si no se encontró, asignar el valor por defecto 11
            $idmac = 11;
        }

        // Obtener el NOMBRE_MAC utilizando el idmac
        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', '=', $idmac) // Filtrar por el idmac
            ->select('NOMBRE_MAC') // Seleccionamos solo el campo NOMBRE_MAC
            ->first(); // Usamos first() porque esperamos solo un resultado

        // Verificar si se encontró el nombre del centro MAC
        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible'; // Si $mac es null, retorna un valor predeterminado

        // Obtener el mes y año desde la solicitud
        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = $request->mes ?: date('m');

        // Crear un array con los nombres de los meses
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

        // Convertir el número del mes a su nombre correspondiente
        $mesNombre = $meses[$fecha_mes];  // Esto convertirá el número del mes al nombre en letras

        // Calcular el primer y último día del mes
        $fecha_inicio = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->startOfMonth()->format('Y-m-d');
        $fecha_fin = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->endOfMonth()->format('Y-m-d');

        // Inicializar un array para los días
        $dias = [];

        // Inicializar un array para almacenar los módulos y entidades
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->where('m_modulo.idcentro_mac', $idmac) // Filtrar por el idcentro_mac recibido
            // Añadir condiciones para filtrar módulos que tienen actividad dentro del rango de fechas
            ->where(function ($query) use ($fecha_inicio, $fecha_fin) {
                $query->where('m_modulo.fechainicio', '<=', $fecha_fin)
                    ->where('m_modulo.fechafin', '>=', $fecha_inicio);
            })
            ->where('m_modulo.es_administrativo', 'NO') // Filtrar por el campo es_administrativo == "NO"
            ->select('m_modulo.idmodulo', 'm_modulo.n_modulo', 'm_entidad.nombre_entidad', 'm_modulo.fechainicio', 'm_modulo.fechafin')
            ->get();


        // Obtener feriados del mes y año especificados, considerando también los feriados con idcentro_mac NULL
        $feriados = DB::table('feriados')
            ->whereYear('fecha', $fecha_año)
            ->whereMonth('fecha', $fecha_mes)
            ->where(function ($query) use ($idmac) {
                $query->where('id_centromac', $idmac)
                    ->orWhereNull('id_centromac');
            })
            ->pluck('fecha')
            ->toArray();

        // Crear un array para los días
        $numeroDias = Carbon::create($fecha_año, $fecha_mes, 1)->daysInMonth;

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
        return view('indicador.puntualidad.tablas.tb_index', compact('mesNombre', 'nombreMac', 'dias', 'modulos', 'numeroDias', 'fecha_año', 'fecha_mes', 'feriados'));
    }

    public function export_excel(Request $request)
    {
        // Obtener el idcentromac desde el formulario (si está presente)
        $idmac = $request->input('mac');

        // Verificar si no se proporcionó el idcentromac
        if (empty($idmac)) {
            // Si no se proporcionó, tomar el idcentromac del usuario autenticado
            $idmac = auth()->user()->idcentro_mac;
        }

        // Verificar si aún no se ha asignado un idcentromac (en caso de que el usuario no esté autenticado o no tenga este campo)
        if (empty($idmac)) {
            // Si no se encontró, asignar el valor por defecto 11
            $idmac = 11;
        }

        // Obtener el NOMBRE_MAC utilizando el idmac
        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', '=', $idmac) // Filtrar por el idmac
            ->select('NOMBRE_MAC') // Seleccionamos solo el campo NOMBRE_MAC
            ->first(); // Usamos first() porque esperamos solo un resultado

        // Verificar si se encontró el nombre del centro MAC
        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible'; // Si $mac es null, retorna un valor predeterminado

        // Obtener el mes y año desde la solicitud
        $fecha_año = $request->año ?: date('Y');
        $fecha_mes = $request->mes ?: date('m');

        // Crear un array con los nombres de los meses
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

        // Convertir el número del mes a su nombre correspondiente
        $mesNombre = $meses[$fecha_mes];  // Esto convertirá el número del mes al nombre en letras

        // Calcular el primer y último día del mes
        $fecha_inicio = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->startOfMonth()->format('Y-m-d');
        $fecha_fin = Carbon::createFromDate($fecha_año, $fecha_mes, 1)->endOfMonth()->format('Y-m-d');

        // Inicializar un array para los días
        $dias = [];

        // Inicializar un array para almacenar los módulos y entidades
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->where('m_modulo.idcentro_mac', $idmac) // Filtrar por el idcentro_mac recibido
            // Añadir condiciones para filtrar módulos que tienen actividad dentro del rango de fechas
            ->where(function ($query) use ($fecha_inicio, $fecha_fin) {
                $query->where('m_modulo.fechainicio', '<=', $fecha_fin)
                    ->where('m_modulo.fechafin', '>=', $fecha_inicio);
            })
            ->where('m_modulo.es_administrativo', 'NO')
            ->select('m_modulo.idmodulo', 'm_modulo.n_modulo', 'm_entidad.nombre_entidad', 'm_modulo.fechainicio', 'm_modulo.fechafin')
            ->orderBy('m_modulo.n_modulo', 'asc')
            ->get();


        // Obtener feriados del mes y año especificados
        $feriados = DB::table('feriados')
        ->whereYear('fecha', $fecha_año)
        ->whereMonth('fecha', $fecha_mes)
        ->where(function ($query) use ($idmac) {
            $query->where('id_centromac', $idmac)
                ->orWhereNull('id_centromac');
        })
        ->pluck('fecha')
        ->toArray();

        // Crear un array para los días
        $numeroDias = Carbon::create($fecha_año, $fecha_mes, 1)->daysInMonth;

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

        // Generar el archivo Excel
        $export = Excel::download(
            new IndicadorPuntualidadExport($mesNombre, $nombreMac, $dias, $modulos, $numeroDias, $fecha_año, $fecha_mes, $feriados),
            'INDICADOR_DE_PUNTUALIDAD_' . $nombreMac . '_' . $fecha_año . '_' . $mesNombre . '.xlsx'
        );

        return $export;
    }
}
