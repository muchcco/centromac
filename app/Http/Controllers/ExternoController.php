<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mac;
use Illuminate\Support\Facades\DB;
use App\Models\Personal;
use Carbon\Carbon;

class ExternoController extends Controller
{
    public function cumpleaño(Request $request) 
    {
        $macs = Mac::get();

        $tip_doc = DB::table('D_PERSONAL_TIPODOC')->get();

        return view('externo.cumpleaños.cumpleaño', compact('macs', 'tip_doc'));

    }

    public function cumpleaño_validar(Request $request)
    {
        $dat = Personal::where('IDMAC', $request->idmac)->get();

        $mac = Mac::where('IDCENTRO_MAC', $request->idmac)->first();

        // Configura la configuración regional en español
        setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish');
        Carbon::setLocale('es');

        $personal = Personal::select(
                                DB::raw("CONCAT(APE_PAT, ' ', APE_MAT, ' - ', NOMBRE) AS NOMBRES"),
                                'FECH_NACIMIENTO',
                                'FOTO_RUTA',
                                'SEXO',
                                'NUM_DOC',
                                DB::raw("(
                                    SELECT AP2.NOMBRE_ARCHIVO 
                                    FROM A_PERSONAL AP2 
                                    WHERE AP2.IDPERSONAL = M_PERSONAL.IDPERSONAL 
                                        AND LOWER(AP2.FORMATO_DOC) IN ('jpg','jpeg','png')
                                    LIMIT 1
                                ) AS NOMBRE_ARCHIVO")
                            )
                            ->join('M_ENTIDAD', 'M_PERSONAL.IDENTIDAD', '=', 'M_ENTIDAD.IDENTIDAD')
                            ->addSelect('M_ENTIDAD.NOMBRE_ENTIDAD')
                            ->where('M_PERSONAL.FLAG', 1)
                            ->whereNotNull('M_PERSONAL.FECH_NACIMIENTO')
                            ->where('M_PERSONAL.IDMAC', $request->idmac)
                            ->get();

        $fecha_actual = Carbon::now();
        
        $proximos_cumpleaños = [];

        $fecha_limite_proximo_anio = $fecha_actual->copy()->addYear()->addMonths(6);

        foreach ($personal as $persona) {
            $fecha_nacimiento = Carbon::parse($persona->FECH_NACIMIENTO);
            $proximo_cumpleaños = $fecha_actual->copy()->year($fecha_actual->year)->month($fecha_nacimiento->month)->day($fecha_nacimiento->day);

            
            if ($proximo_cumpleaños->gte($fecha_actual) && $proximo_cumpleaños->lte($fecha_limite_proximo_anio)) {
                
                $edad = $proximo_cumpleaños->year - $fecha_nacimiento->year;

                if ($proximo_cumpleaños->lt($fecha_actual)) {
                    $edad++;
                }

                $persona->prox_cumpleanos = $proximo_cumpleaños;
                $persona->edad_proximo_cumpleanos = $edad;
                $persona->nombre_mes_proximo_cumpleanos = $proximo_cumpleaños->format('F'); // 'F' muestra el nombre completo del mes en español
                $proximos_cumpleaños[] = $persona;
            }
        }

        usort($proximos_cumpleaños, function ($a, $b) {
            return $a->prox_cumpleanos->gte($b->prox_cumpleanos) ? 1 : -1;
        });

        $cumpleanos_por_mes = [];

        // Agrupar los cumpleaños por mes
        foreach ($proximos_cumpleaños as $persona) {
            $nombre_mes = $persona->prox_cumpleanos->format('F'); // Obtener el nombre del mes
            
            // Verificar si ya existe una entrada para este mes en el array, si no, se crea
            if (!isset($cumpleanos_por_mes[$nombre_mes])) {
                $cumpleanos_por_mes[$nombre_mes] = [];
            }
            
            // Agregar la persona al array correspondiente al mes
            $cumpleanos_por_mes[$nombre_mes][] = $persona;
        }

        $meses_latino = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        uksort($cumpleanos_por_mes, function($a, $b) use ($meses_latino) {
            $meses_en_orden = array_flip(array_keys($meses_latino));
            return $meses_en_orden[$a] <=> $meses_en_orden[$b];
        });

        // foreach ($cumpleanos_por_mes as $mes => $personas) {
        //     $mes_latino = isset($meses_latino[$mes]) ? $meses_latino[$mes] : $mes;
        //     echo "<h2>$mes_latino</h2>"; 

        //     usort($personas, function ($a, $b) {
        //         return $a->prox_cumpleanos->timestamp - $b->prox_cumpleanos->timestamp;
        //     });

        //     foreach ($personas as $persona) {
        //         echo $persona->nombre . ' cumple ' . $persona->edad_proximo_cumpleanos . ' años el ' . $persona->prox_cumpleanos->format('d/m/Y') . '<br>';
        //     }
        //     echo "<br>";
        // }

        return view('externo.cumpleaños.cumpleaño_validar',  compact('dat', 'mac', 'proximos_cumpleaños', 'cumpleanos_por_mes', 'meses_latino'));
    }
}
