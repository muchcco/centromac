<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personal;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Asignacion;
use App\Models\Almacen;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AsigPersonal;
use Carbon\Carbon;

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
        $asignacion_estado = AsigPersonal::where('IDPERSONAL', $idpersonal)->first();

        if($asignacion_estado){
            $datos_acepta = AsigPersonal::join('A_ASIGNACION_BIEN', 'A_ASIGNACION_BIEN.IDASIG_PERSONAL', '=', 'M_ASIG_PERSONAL.IDASIG_PERSONAL')
                    ->join('D_ESTADO_ASIGNACION', 'D_ESTADO_ASIGNACION.IDESTADO_ASIG', '=', 'M_ASIG_PERSONAL.IDESTADO_ASIG')
                    ->where('A_ASIGNACION_BIEN.IDASIG_PERSONAL', $asignacion_estado->IDASIG_PERSONAL)
                    ->get();  
        }else{
            $datos_acepta = NULL;
        }
        
              

        $personal = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                ->join('D_PERSONAL_TIPODOC', 'D_PERSONAL_TIPODOC.IDTIPO_DOC', '=', 'M_PERSONAL.IDTIPO_DOC')
                                ->where('IDPERSONAL', $idpersonal)->first();

        return view('asignacion.asignacion_inventario', compact('personal', 'asignacion_estado', 'datos_acepta'));
    }

    public function tb_asignacion(Request $request)
    {
        $query = Asignacion::join('M_ALMACEN', 'M_ALMACEN.IDALMACEN', '=', 'M_ASIGNACION_BIEN.IDALMACEN')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_ASIGNACION_BIEN.IDCENTRO_MAC')
                            ->join('M_ASIG_PERSONAL', 'M_ASIG_PERSONAL.IDASIG_PERSONAL', '=', 'M_ASIGNACION_BIEN.IDASIG_PERSONAL')
                            ->get();

        return view('asignacion.tablas.tb_asignacion', compact('query'));
    }

    public function store_item(Request $request)
    {
        try{
            // dd($request->all());
            $verificar = AsigPersonal::where('IDPERSONAL', $request->idpersonal)->first();

            if($verificar){
                $idasi_per = $verificar->IDASIG_PERSONAL;
            }else{
                $dat = new AsigPersonal;
                $dat->IDPERSONAL = $request->idpersonal;
                $dat->IDESTADO_ASIG = 1;
                $dat->save();

                $idasi_per = $dat->IDASIG_PERSONAL;
            }

            $save = new Asignacion;
            $save->IDCENTRO_MAC = $this->centro_mac()->idmac;
            $save->IDASIG_PERSONAL = $idasi_per;
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
        // dd($request->all());

        $save = Asignacion::findOrFail($request->idasignacion);
        $save->ESTADO_BIEN = $request->estado;
        $save->save();

        return $save;
    }

    public function md_add_observacion(Request $request)
    {
        $idasginacion = $request->idasignacion;

        $asignacion = Asignacion::where('IDASIGNACION', $idasginacion)->first();
        
        $view = view('asignacion.modals.md_add_observacion', compact('idasginacion', 'asignacion'))->render();

        return response()->json(['html' => $view]);
    }

    public function store_observacion(Request $request)
    {
        // dd($request->all());

        $save = Asignacion::findOrFail($request->idasignacion);
        $save->OBSERVACION = $request->observacion;
        $save->save();

        return $save;
    }

    public function borrador_pdf(Request $request, $idpersonal)
    {
        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD' ,'=','M_PERSONAL.IDENTIDAD')->where('M_PERSONAL.IDPERSONAL', $idpersonal)->first();

        $centro_mac = $this->centro_mac()->name_mac;

        $query = Asignacion::select('*')
                    ->join('m_almacen as ma', 'ma.IDALMACEN', '=', 'm_asignacion_bien.IDALMACEN')
                    ->where('m_asignacion_bien.IDPERSONAL', $idpersonal)
                    ->get();
        
        $count = DB::select("SELECT COUNT(*) AS NUM_C FROM M_ASIGNACION_BIEN WHERE IDPERSONAL = $idpersonal ");

        // dd($query);

        $pdf = Pdf::loadView('asignacion.pdf.borrador_pdf', compact('query', 'datos_persona', 'count', 'centro_mac'))->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function estado_borrador(Request $request)
    {
        try{

            $save = AsigPersonal::findOrFail($request->asignacion_estado);
            $save->IDESTADO_ASIG = 2;
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

    public function orginal_pdf(Request $request, $idpersonal)
    {
        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD' ,'=','M_PERSONAL.IDENTIDAD')->where('M_PERSONAL.IDPERSONAL', $idpersonal)->first();

        $centro_mac = $this->centro_mac()->name_mac;

        $query = Asignacion::select('*')
                    ->join('m_almacen as ma', 'ma.IDALMACEN', '=', 'm_asignacion_bien.IDALMACEN')
                    ->where('m_asignacion_bien.IDPERSONAL', $idpersonal)
                    ->get();
        
        $count = DB::select("SELECT COUNT(*) AS NUM_C FROM M_ASIGNACION_BIEN WHERE IDPERSONAL = $idpersonal ");

        // dd($query);

        $pdf = Pdf::loadView('asignacion.pdf.orginal_pdf', compact('query', 'datos_persona', 'count', 'centro_mac'))->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function cargar_documento_acept(Request $request)
    {
        try{

            $personal = AsigPersonal::join('M_PERSONAL', 'M_PERSONAL.IDPERSONAL', '=', 'M_ASIG_PERSONAL.IDPERSONAL')
                                        ->where('M_ASIG_PERSONAL.IDASIG_PERSONAL', $request->asignacion_estado)
                                        ->first();

            $estructura_carp = 'archivos\\'.$personal->NUM_DOC.'\\';            

            if (!file_exists($estructura_carp)) {
                mkdir($estructura_carp, 0777, true);
            }

            if($request->hasFile('file_aprobado'))
            {
                $archivoPDF = $request->file('file_aprobado');
                $nombrePDF = 'SUBIDA_'.$archivoPDF->getClientOriginalName();
                //$nameruta = '/archivo/'; // RUTA DONDE SE VA ALMACENAR EL DOCUMENTO PDF
                $nameruta = $estructura_carp;  // GUARDAR EN UN SERVIDOR
                $archivoPDF->move($nameruta, $nombrePDF);
            }

            $save = DB::table('A_ASIGNACION_BIEN')->insert([
                'IDASIG_PERSONAL'       =>  $request->asignacion_estado,
                'NOMBRE_RUTA'           =>  $estructura_carp.$nombrePDF,
                'NOMBRE_DOCUMENTO'      =>  $nombrePDF,
                'CREATED_AT'            =>  Carbon::now(),
                'UPDATED_AT'            =>  Carbon::now(),
            ]);

            $update = AsigPersonal::findOrFail($request->asignacion_estado);
            $update->IDESTADO_ASIG = 3;
            $update->save();

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
}
