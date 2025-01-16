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
            ->where(function ($query) {
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

        if (!$request->filled('mes') || !$request->filled('año') || !is_numeric($request->mes) || !is_numeric($request->año)) {
            return response()->json(['error' => 'Por favor, proporciona un mes y un año válidos.'], 422);
        }

        $mes = $request->mes;
        $año = $request->año;
        $fechaInicio = Carbon::create($año, $mes, 1);
        $fechaActual = Carbon::now();
        $fechaFin = ($fechaActual->month == $mes && $fechaActual->year == $año) ? $fechaActual->startOfDay() : $fechaInicio->copy()->endOfMonth();

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

        // Ahora, obtenemos el NOMBRE_MAC utilizando el idmac
        $mac = DB::table('M_CENTRO_MAC')
            ->where('IDCENTRO_MAC', '=', $idmac) // Filtrar por el idmac
            ->select('NOMBRE_MAC') // Seleccionamos solo el campo NOMBRE_MAC
            ->first(); // Usamos first() porque esperamos solo un resultado

        // Verificar si se encontró el nombre del centro MAC
        $nombreMac = $mac ? $mac->NOMBRE_MAC : 'Nombre no disponible'; // Si $mac es null, retorna un valor predeterminado

        // Ahora puedes usar $nombreMac para mostrar el nombre del centro MAC

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
        // Inicializar un array para almacenar los módulos y entidades
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.identidad', '=', 'm_entidad.identidad')
            ->where('m_modulo.idcentro_mac', $idmac) // Filtrar por el idcentro_mac recibido
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('m_modulo.fechainicio', '<=', $fechaFin)
                    ->where('m_modulo.fechafin', '>=', $fechaInicio);
            })
            ->select('m_modulo.idmodulo', 'm_modulo.n_modulo', 'm_entidad.nombre_entidad', 'm_modulo.fechainicio', 'm_modulo.fechafin')
            ->orderBy('m_modulo.n_modulo') // Ordena por el nombre del módulo
            ->get();

        // Obtener feriados del mes y año especificados
        $feriados = DB::table('feriados')
            ->whereYear('fecha', $fecha_año)
            ->whereMonth('fecha', $fecha_mes)
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
        return view('ocupabilidad.tablas.tb_index', compact('mesNombre', 'diasHabiles', 'nombreMac', 'dias', 'modulos', 'numeroDias', 'fecha_año', 'fecha_mes', 'feriados'));
    }
}
