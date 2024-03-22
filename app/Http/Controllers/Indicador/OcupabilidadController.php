<?php

namespace App\Http\Controllers\Indicador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IndicadorOcupabilidadExport;

class OcupabilidadController extends Controller
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

    public function index(Request $request)
    {
        return view('indicador.ocupabilidad.index');
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

        $name_mac = $this->centro_mac()->name_mac;


        $query = DB::select("CALL SP_I_OCUPABILIDAD(:idmac, :anio, :mes)", [
            'idmac' => $this->centro_mac()->idmac,
            'anio' => $fecha_año,
            'mes' => $fecha_mes,
        ]);

        return view('indicador.ocupabilidad.tablas.tb_index', compact('name_mac', 'query', 'fecha_mes', 'fecha_año'));
    }

    public function export_excel(Request $request)
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

        $name_mac = $this->centro_mac()->name_mac;


        $query = DB::select("CALL SP_I_OCUPABILIDAD(:idmac, :anio, :mes)", [
            'idmac' => $this->centro_mac()->idmac,
            'anio' => $fecha_año,
            'mes' => $fecha_mes,
        ]);

        setlocale(LC_TIME, 'es_PE', 'Spanish_Spain', 'Spanish');

        $numero_mes = (int)$fecha_mes; // Asegúrate de que $fecha_mes sea un entero
        $nombre_mes = '';

        $nombres_meses = [
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

        if (isset($nombres_meses[$numero_mes])) {
            $nombre_mes = $nombres_meses[$numero_mes];
        } else {
            // Manejar el caso en que el número de mes no sea válido
            $nombre_mes = 'Mes no válido';
        }

        $name_mac = $this->centro_mac()->name_mac;

        $export = Excel::download(new IndicadorOcupabilidadExport($query, $fecha_año, $fecha_mes, $name_mac, $nombre_mes), 'INDICADOR DE OCUPABILIDAD DEL  CENTRO MAC - '.$this->centro_mac()->name_mac.' _'.$fecha_año.' - '.$nombre_mes.'.xlsx');

        return $export;

    }
}
