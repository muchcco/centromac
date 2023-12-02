<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Almacen;
use App\Models\User;
use App\Imports\AlmacenImport;
use Maatwebsite\Excel\Facades\Excel;

class AlmacenController extends Controller
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
        return view('almacen.index');
    }

    public function tb_index(Request $requet)
    {
        $query = Almacen::from('M_ALMACEN as MA')
                            ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
                            ->where('MA.FLAG', 1)
                            ->where('MCM.IDCENTRO_MAC', $this->centro_mac()->idmac)
                            ->get();

        return view('almacen.tablas.tb_index', compact('query'));
    }

    public function md_add_datos(Request $requet)
    {
        $view = view('almacen.modals.md_add_datos')->render();

        return response()->json(['html' => $view]);
    }

    public function store_datos(Request $request)
    {
        try {

            $id = $this->centro_mac()->idmac;

            $file = $request->file('excel_file');

            $upload = Excel::import(new AlmacenImport($id), $file);

            return response()->json($upload);

        }catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }
}
