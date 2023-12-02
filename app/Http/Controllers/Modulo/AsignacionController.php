<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personal;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Asignacion;
use App\Models\Almacen;

class AsignacionController extends Controller
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
        return view('asignacion.index');
    }

    public function tb_index(Request $request)
    {
        $query = Personal::select([
                                'M_PERSONAL.IDPERSONAL',
                                DB::raw("CONCAT(M_PERSONAL.NOMBRE, ' ', M_PERSONAL.APE_PAT, ' ', M_PERSONAL.APE_MAT) as NOMBREU"),
                                DB::raw("CONCAT(D_PERSONAL_TIPODOC.TIPODOC_ABREV, ': ', M_PERSONAL.NUM_DOC) as NUM_DOCUMENTO"),
                                'M_ENTIDAD.NOMBRE_ENTIDAD',
                                DB::raw('IFNULL(CONT.CONT_ASIG, 0) as CONT_ASIG')
                            ])
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                            ->join('D_PERSONAL_TIPODOC', 'D_PERSONAL_TIPODOC.IDTIPO_DOC', '=', 'M_PERSONAL.IDTIPO_DOC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                            ->leftJoin(DB::raw('(SELECT M_ASIGNACION_BIEN.IDPERSONAL, COUNT(*) AS CONT_ASIG
                                                FROM M_ASIGNACION_BIEN
                                                GROUP BY M_ASIGNACION_BIEN.IDPERSONAL) CONT'), 'CONT.IDPERSONAL', '=', 'M_PERSONAL.IDPERSONAL')
                            ->where('M_PERSONAL.FLAG', 1)
                            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $this->centro_mac()->idmac)
                            ->get();

        return view('asignacion.tablas.tb_index', compact('query'));
    }

    public function asignacion_inventario(Request $request, $idpersonal)
    {
        $personal = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                ->join('D_PERSONAL_TIPODOC', 'D_PERSONAL_TIPODOC.IDTIPO_DOC', '=', 'M_PERSONAL.IDTIPO_DOC')
                                ->where('IDPERSONAL', $idpersonal)->first();

        return view('asignacion.asignacion_inventario', compact('personal'));
    }

    public function tb_asignacion(Request $request)
    {
        $query = Asignacion::join('M_ALMACEN', 'M_ALMACEN.IDALMACEN', '=', 'M_ASIGNACION_BIEN.IDALMACEN')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_ASIGNACION_BIEN.IDCENTRO_MAC')
                            ->get();

        return view('asignacion.tablas.tb_asignacion', compact('query'));
    }

    public function store_item(Request $request)
    {
        try{
            // dd($request->all());
            $save = new Asignacion;
            $save->IDCENTRO_MAC = $this->centro_mac()->idmac;
            $save->IDPERSONAL = $request->idpersonal;
            $save->IDALMACEN = $request->idalmacen;
            $save->IDESTADO_ASIG = 1;
            $save->save();

            return $save;


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

    public function almacen_select(Request $request)
    {
        // dd($request->all());
        $ids_encontrados = DB::select("SELECT GROUP_CONCAT(IDALMACEN SEPARATOR ', ') as NOMBRES FROM M_ASIGNACION_BIEN WHERE IDCENTRO_MAC = " . $this->centro_mac()->idmac . "");
        
        if (!empty($ids_encontrados)) {
            $nombres = $ids_encontrados[0]->NOMBRES;
            // dd($nombres);
            if($request->term){
                
                // Asegúrate de que $nombres sea un array
                $nombresArray = explode(', ', $nombres);

                $almacen = Almacen::whereNotIn('IDALMACEN', $nombresArray)
                    ->where(function ($query) use ($request) {
                        $term = '%' . $request->term . '%';
                        $query->where('COD_INTERNO_PCM', 'LIKE', $term)
                            ->orWhere('DESCRIPCION', 'LIKE', $term)
                            ->orWhere('SERIE_MEDIDA', 'LIKE', $term)
                            ->orWhere('UBICACION_EQUIPOS', 'LIKE', $term);
                    })
                    ->get();
                // dd($almacen);
            }else{
                $almacen = DB::select("SELECT * FROM M_ALMACEN WHERE IDALMACEN NOT IN ($nombres) ");
            }            
        } else {
            // Manejar el caso cuando $ids_encontrados es un array vacío
            $almacen = Almacen::get();
        }

        return response()->json($almacen);
    }

    public function eliminar_item(Request $request)
    {
        $delete = Asignacion::where('IDASIGNACION', $request->idasignacion)->delete();

        return $delete;
    }

    public function md_add_estado(Request $request)
    {
        $idasginacion = $request->idasignacion;

        $view = view('asignacion.modals.md_add_estado', compact('idasginacion'))->render();

        return response()->json(['html' => $view]);
    }

    public function store_estado(Request $request)
    {
        $save = 
    }
}
