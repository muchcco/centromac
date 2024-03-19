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
        setlocale(LC_TIME, 'es_ES');
        Carbon::setLocale('es');

        $personal = Personal::select(DB::raw("CONCAT(APE_PAT, ' ', APE_MAT, ' - ', NOMBRE) AS NOMBRES"), 'FECH_NACIMIENTO', 'NOMBRE_ENTIDAD', 'FOTO_RUTA', 'SEXO')
                                        ->join('M_ENTIDAD', 'M_PERSONAL.IDENTIDAD', '=', 'M_ENTIDAD.IDENTIDAD')
                                        ->where('M_PERSONAL.FLAG', 1)
                                        ->whereNotNull('M_PERSONAL.FECH_NACIMIENTO')
                                        ->get();

        $fecha_actual = Carbon::now();
        $proximos_cumpleaños = [];

        // Calcular la fecha límite para el próximo año
        $fecha_limite_proximo_anio = $fecha_actual->copy()->addYear()->addMonths(6);

        foreach ($personal as $persona) {
            $fecha_nacimiento = Carbon::parse($persona->fech_nac);
            $proximo_cumpleaños = $fecha_actual->copy()->year($fecha_actual->year)->month($fecha_nacimiento->month)->day($fecha_nacimiento->day);

            // Compara si el próximo cumpleaños está dentro del rango actual y el próximo año
            if ($proximo_cumpleaños->gte($fecha_actual) && $proximo_cumpleaños->lte($fecha_limite_proximo_anio)) {
                // Calcula la diferencia de años, teniendo en cuenta si el cumpleaños ya ocurrió o no
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

        // Ahora $proximos_cumpleaños contiene la lista de personas con próximos cumpleaños en los próximos 6 meses del año actual y del próximo año
        // foreach ($proximos_cumpleaños as $persona) {
        //     echo $persona->nombre . ' cumple ' . $persona->edad_proximo_cumpleanos . ' años en ' . $persona->nombre_mes_proximo_cumpleanos . ' el día ' . $persona->prox_cumpleanos . '<br>';
        // }


        return view('externo.cumpleaños.cumpleaño_validar',  compact('dat', 'mac', 'proximos_cumpleaños'));
    }
}
