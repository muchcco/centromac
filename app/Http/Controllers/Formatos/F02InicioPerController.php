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

    public function formulario(Request $request)
    {

        $centro_mac = $this->centro_mac()->name_mac;
        $id_mac = $this->centro_mac()->idmac;

        $personal = Personal::where('IDPERSONAL', auth()->user()->idpersonal)->first();

        $fecha = date("Y-m-d");
        
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
                                WHERE FECHA = '$fecha' AND IDCENTRO_MAC = $id_mac) as DFI"), 'DFI.IDDESC_FORM', '=', 'DDF.IDDESC_FORM')
            ->get();

        return view('formatos.f_02_inicio_oper.formulario', compact('centro_mac', 'personal', 'resultado'));   
    }

    public function store_form(Request $request)
    {
        try {
            // Recorre cada fila y guarda los datos
            foreach ($request->iddesc_form as $key => $iddesc_form) {
                // Verifica si los valores no son null antes de acceder a ellos
                $conformidad_i = $request->apertura[$key] ?? null;
                $conformidad_f = $request->cierre[$key] ?? null;
                $observacion_f02 = $request->observacion[$key] ?? null;
    
                // Crear una nueva instancia del modelo y llenarla con los datos de la fila
                $nuevoRegistro = new FInicioOperacion([
                    'IDDESC_FORM' => $iddesc_form,
                    'CONFORMIDAD_I' => $conformidad_i,
                    'CONFORMIDAD_F' => $conformidad_f,
                    'OBSERVACION_F02' => $observacion_f02,
                    'DIA' => Carbon::now()->format('d'),
                    'MES' => Carbon::now()->format('m'),
                    'AÑO' => Carbon::now()->format('Y'),
                    'FECHA' => Carbon::now()->format('Y-m-d'),
                    'HORA' => Carbon::now()->format('H:i:s'),
                    'IDCENTRO_MAC' => $this->centro_mac()->idmac,
                ]);
    
                // Guardar el registro en la base de datos
                $nuevoRegistro->save();
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
    

}
