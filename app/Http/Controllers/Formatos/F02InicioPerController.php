<?php

namespace App\Http\Controllers\Formatos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Personal;
use App\Models\DescripcionFormato;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\FInicioOperacion;

class F02InicioPerController extends Controller
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
        return view('formatos.f_02_inicio_oper.index');
    }

    public function formulario(Request $request, $fecha)
    {

        $centro_mac = $this->centro_mac()->name_mac;
        $id_mac = $this->centro_mac()->idmac;

        $personal = Personal::where('IDPERSONAL', auth()->user()->idpersonal)->first();

        $fecha_sel = date("Y-m-d");
        
        $resultado = DescripcionFormato::from('D_DESCRIPCION_FORMATOS as DDF')->select(
                'DDF.IDDESC_FORM',
                'DDF.IDPADRE_F',
                'DDF.DESCRIPCION_F',
                'DFI.CONFORMIDAD_I',
                'DFI.CONFORMIDAD_F',
                'DFI.OBSERVACION_F02'
            )
            ->leftJoin(DB::raw("(SELECT IDDESC_FORM, CONFORMIDAD_I, CONFORMIDAD_F, OBSERVACION_F02
                                FROM F_MAC_03_VER_OPERACION
                                WHERE FECHA = '$fecha_sel' AND IDCENTRO_MAC = $id_mac) as DFI"), 'DFI.IDDESC_FORM', '=', 'DDF.IDDESC_FORM')
            ->get();

        return view('formatos.f_02_inicio_oper.formulario', compact('centro_mac', 'personal', 'resultado', 'fecha'));   
    }

    public function store_form(Request $request)
    {
        try {
            // Recorre cada fila y guarda los datos

            // dd($request->all());
            // Log the entire request data
            logger()->info('Request Data:', $request->all());

            // Access the data using the input method
            $iddesc_form = $request->input('iddesc_form');

            // Log the specific data
            logger()->info('iddesc_form Data:', $iddesc_form);

            $hoy = Carbon::parse($request->fecha)->format('Y-m-d');
            $año = Carbon::parse($request->fecha)->format('Y');
            $mes = Carbon::parse($request->fecha)->format('m');
            $dia = Carbon::parse($request->fecha)->format('d');

            // dd($año);
            

            // Check if $request->iddesc_form is not null and is an array
            if (!is_null($request->iddesc_form) && is_array($request->iddesc_form)) {
                foreach ($request->iddesc_form as $key => $iddesc_form) {
                    // Verifica si los valores no son null antes de acceder a ellos
                    $conformidad_i = $request->apertura[$key] ?? null;
                    $conformidad_f = $request->cierre[$key] ?? null;
                    $observacion_f02 = $request->observacion[$key] ?? null;

                    // Utiliza el método updateOrCreate para insertar o actualizar el registro
                    FInicioOperacion::updateOrCreate(
                        [
                            'FECHA' => $hoy,
                            'IDCENTRO_MAC' => $this->centro_mac()->idmac,
                            'IDDESC_FORM' => $iddesc_form,
                        ],
                        [
                            'CONFORMIDAD_I' => $conformidad_i,
                            'CONFORMIDAD_F' => $conformidad_f,
                            'OBSERVACION_F02' => $observacion_f02,
                            'DIA' => $dia,
                            'MES' => $mes,
                            'AÑO' => $año,
                            'HORA' => Carbon::now()->format('H:i:s'),
                            'LOG_REGISTRO' => auth()->user()->id,
                        ]
                    );
                }
            } else {
                // Handle the case when $request->iddesc_form is null or not an array
                return response()->json(['error' => 'iddesc_form is null or not an array'], 400);
            }

            // Puedes retornar una respuesta o redireccionar según tus necesidades
            return response()->json(['message' => 'Registros guardados correctamente']);


        } catch (\Exception $e) {
            // Manejar la excepción y retornar una respuesta de error
            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);
        }
    }

    public function tb_index(Request $request)
    {
        // Definir fecha de inicio y fecha de fin
        // $fechaInicio = '2023-12-07';
        // $fechaFin = '2023-12-12';

        // Calcular fecha de inicio como la fecha actual menos 4 días
        // $fechaFin = now()->toDateString(); // Fecha actual
        // $fechaInicio = now()->subDays(4)->toDateString(); // Fecha actual menos 4 días

        // $fecha_i = date("Y-m-d");
        if ($request->fecha_inicio != '') {
            $fechaInicio = $request->fecha_inicio;
        } else {
            $fechaInicio = now()->subDays(4)->toDateString();
        }

        // $fecha_i = date("Y-m-d");
        if ($request->fecha_fin != '') {
            $fechaFin = $request->fecha_fin;
        } else {
            $fechaFin = now()->toDateString();
        }

        // Inicializar una instancia del modelo Eloquent correspondiente
        $modelo = new FInicioOperacion();

        // Inicializar una instancia del constructor de consultas
        $queryBuilder = $modelo->from('D_DESCRIPCION_FORMATOS as DDF')
            //->leftJoin('F_MAC_03_VER_OPERACION AS DFI', 'DFI.IDDESC_FORM', '=', 'DDF.IDDESC_FORM')
            ->leftJoin(DB::raw("
                                (SELECT
                                    IDDESC_FORM,
                                    CONFORMIDAD_I,
                                    CONFORMIDAD_F,
                                    OBSERVACION_F02,
                                    FECHA
                                FROM
                                    F_MAC_03_VER_OPERACION
                                WHERE
                                    FECHA BETWEEN '$fechaInicio' AND '$fechaFin'
                                    AND IDCENTRO_MAC = " . $this->centro_mac()->idmac . ") DFI
                            "), 'DFI.IDDESC_FORM', '=', 'DDF.IDDESC_FORM')
            ->select([
                'DDF.IDDESC_FORM',
                'DDF.IDPADRE_F',
                'DDF.DESCRIPCION_F',
            ]);

        // Construir dinámicamente las partes de la consulta
        $currentDate = $fechaInicio;
        $fechas = []; // Inicializar el arreglo de fechas
        while (strtotime($currentDate) <= strtotime($fechaFin)) {
            // Excluir los domingos (día de la semana = 0 en PHP)
            if (date('w', strtotime($currentDate)) != 0) {
                $formattedDate = str_replace('-', '', $currentDate);
                $queryBuilder->addSelect([
                    DB::raw("MAX(CASE WHEN DFI.FECHA = '$currentDate' THEN DFI.CONFORMIDAD_I END) AS '$currentDate'"),
                    DB::raw("MAX(CASE WHEN DFI.FECHA = '$currentDate' THEN DFI.CONFORMIDAD_F END) AS '$currentDate'"),
                ]);

                $fechas[] = $currentDate; // Agregar la fecha al arreglo
            }

            // Avanzar a la siguiente fecha
            $currentDate = date("Y-m-d", strtotime($currentDate . '+1 day'));
        }

        // Agregar el resto de la consulta
        $queryBuilder
            //->whereBetween('DFI.FECHA', [$fechaInicio, $fechaFin])
            // ->where('DFI.IDCENTRO_MAC', $this->centro_mac()->idmac)
            ->groupBy('DDF.IDDESC_FORM', 'DDF.IDPADRE_F', 'DDF.DESCRIPCION_F');

        // Ejecutar la consulta
        $resultado = $queryBuilder->get();

        // Mostrar el resultado (o realizar otras operaciones según tus necesidades)
        //dd($resultado);

        // Pasar las fechas y el resultado a la vista
        return view('formatos.f_02_inicio_oper.tablas.tb_index', compact('resultado', 'fechas'));
    }
    

}
