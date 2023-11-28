<?php

namespace App\Http\Controllers\Formatos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\EvaluacionMotivacional;

class EvalMotivacionalController extends Controller
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
        // dd(auth()->user()->idpersonal);
        return view('formatos.evaluacion_motivacional.index');
    }

    public function tb_index(Request $request)
    {
        $fecha_I = date("Y-m-d");
        if ($request->mes != '') {
            $fecha_mes = $request->mes;
        } else {
            $fecha_mes = date('m', strtotime($fecha_I));
        }

        $fecha_A = date("Y-m-d");
        if ($request->año != '') {
            $fecha_año = $request->año;
        } else {
            $fecha_año = date('Y', strtotime($fecha_A));
        }

        $query = DB::select("CALL SP_F_EVALUACIONMOT(:p_mes, :p_id_centro_mac, :p_anio, :idpersonalreg)", [
            'p_mes' => $fecha_mes,
            'p_id_centro_mac' => $this->centro_mac()->idmac,
            'p_anio' => $fecha_año,
            'idpersonalreg' => auth()->user()->idpersonal,
        ]);

        // dd($query);


        return view('formatos.evaluacion_motivacional.tablas.tb_index', compact('query', 'fecha_mes', 'fecha_año'));
    }

    public function store_datos(Request $request)
    {
        // dd($request->all());
        try {
            $personal_mot = EvaluacionMotivacional::where('IDPERSONAL', $request['idpersonal'])
                            ->where('IDCENTRO_MAC', $this->centro_mac()->idmac)
                            ->where('MES', $request['mes'])
                            ->where('AÑO', $request['año'])
                            ->where('IDPERSONA_REGISTRA', auth()->user()->idpersonal)
                            ->first();

            // $suma = 0;

            // if ($personal_mot !== null) {
            //     // Accede directamente a las propiedades del modelo
            //     $suma = $personal_mot->PROACTIVIDAD + $personal_mot->CALIDAD_SERVICIO + $personal_mot->COMPROMISO + $personal_mot->VESTIMENTA;
            // }

            $evaluaciones = json_decode($request->evaluaciones, true);
            // dd($evaluaciones['proactividad']);

            /************************************** SEPARAR LOS VALORES DE CADA BTON ************************************************/

            if($evaluaciones['proactividad']){
                $proactividad = $evaluaciones['proactividad'];
            }elseif(isset($personal_mot->PROACTIVIDAD)){
                $proactividad = $personal_mot->PROACTIVIDAD;
            }else{
                $proactividad = NULL;
            }
            if($evaluaciones['calidad_servicio']){
                $calidad_servicio = $evaluaciones['calidad_servicio'];
            }elseif(isset($personal_mot->CALIDAD_SERVICIO)){
                $calidad_servicio = $personal_mot->CALIDAD_SERVICIO;
            }else{
                $calidad_servicio = NULL;
            }
            if($evaluaciones['compromiso']){
                $compromiso = $evaluaciones['compromiso'];
            }elseif(isset($personal_mot->COMPROMISO)){
                $compromiso = $personal_mot->COMPROMISO;
            }else{
                $compromiso = NULL;
            }
            if($evaluaciones['vestimenta']){
                $vestimenta = $evaluaciones['vestimenta'];
            }elseif(isset($personal_mot->VESTIMENTA)){
                $vestimenta = $personal_mot->VESTIMENTA;
            }else{
                $vestimenta = NULL;
            }

            //dd($proactividad);
            /******************************************************* FIN ************************************************************/

            if($personal_mot){
                $save = EvaluacionMotivacional::findOrFail($personal_mot->IDEEVAL_MOTIVACIONAL);
                $save->IDENTIDAD = $request->entidad;
                $save->PROACTIVIDAD = $proactividad;
                $save->CALIDAD_SERVICIO = $calidad_servicio;
                $save->COMPROMISO = $compromiso;
                $save->VESTIMENTA = $vestimenta;
                // $save->TOTAL_P = $suma;
                $save->IDPERSONA_REGISTRA = auth()->user()->idpersonal;
                $save->save();
                
            }else{
                $save = EvaluacionMotivacional::create([
                    'IDPERSONAL' => $request['idpersonal'],
                    'IDENTIDAD' => $request['entidad'],
                    'IDCENTRO_MAC' => $this->centro_mac()->idmac,
                    'PROACTIVIDAD' => $proactividad,
                    'CALIDAD_SERVICIO' => $calidad_servicio,
                    'COMPROMISO' => $compromiso,
                    'VESTIMENTA' => $vestimenta,
                    // 'TOTAL_P' => $suma,
                    'MES' => $request['mes'],
                    'AÑO' => $request['año'],
                    'FLAG' => 1,
                    'IDPERSONA_REGISTRA' => auth()->user()->idpersonal,
                ]);
            }

            return $save;

        } catch (\Exception $e) {
             // Manejar la excepción y retornar una respuesta de error
             return response()->json([
                 'data' => null,
                 'error' => $e->getMessage(),
                 'message' => 'BAD'
             ], 400);
         }
        
    }

    public function delete_datos(Request $request)
    {
        // dd($request->all());

        $del = DB::table('F_EVAL_MOTIVACIONAL')->where('IDEEVAL_MOTIVACIONAL', $request->id)->update([
            'PROACTIVIDAD' => NULL,
            'CALIDAD_SERVICIO' => NULL,
            'COMPROMISO' => NULL,
            'VESTIMENTA' => NULL,
        ]);

        return $del;
    }

}