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
        $user = User::join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('m_centro_mac.IDCENTRO_MAC', $us_id)->first();

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
                                'm_personal.IDPERSONAL',
                                DB::raw("CONCAT(m_personal.NOMBRE, ' ', m_personal.APE_PAT, ' ', m_personal.APE_MAT) as NOMBREU"),
                                DB::raw("CONCAT(d_personal_tipodoc.TIPODOC_ABREV, ': ', m_personal.NUM_DOC) as NUM_DOCUMENTO"),
                                'm_entidad.NOMBRE_ENTIDAD',
                                DB::raw('IFNULL(CONT.CONT_ASIG, 0) as CONT_ASIG')
                            ])
                            ->join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'm_personal.IDMAC')
                            ->join('d_personal_tipodoc', 'd_personal_tipodoc.IDTIPO_DOC', '=', 'm_personal.IDTIPO_DOC')
                            ->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_personal.IDENTIDAD')
                            ->leftJoin(DB::raw('(SELECT m_asignacion_bien.IDPERSONAL, COUNT(*) AS CONT_ASIG
                                                FROM m_asignacion_bien
                                                GROUP BY m_asignacion_bien.IDPERSONAL) CONT'), 'CONT.IDPERSONAL', '=', 'm_personal.IDPERSONAL')
                            ->where('m_personal.FLAG', 1)
                            ->where('m_centro_mac.IDCENTRO_MAC', $this->centro_mac()->idmac)
                            ->get();

        return view('asignacion.tablas.tb_index', compact('query'));
    }

    public function asignacion_inventario(Request $request, $idpersonal)
    {
        $asignacion_estado = AsigPersonal::where('IDPERSONAL', $idpersonal)->first();

        if($asignacion_estado){
            $datos_acepta = AsigPersonal::join('a_asignacion_bien', 'a_asignacion_bien.IDASIG_PERSONAL', '=', 'm_asig_personal.IDASIG_PERSONAL')
                    ->join('d_estado_asignacion', 'd_estado_asignacion.IDESTADO_ASIG', '=', 'm_asig_personal.IDESTADO_ASIG')
                    ->where('a_asignacion_bien.IDASIG_PERSONAL', $asignacion_estado->IDASIG_PERSONAL)
                    ->get();  
        }else{
            $datos_acepta = NULL;
        }
        
              

        $personal = Personal::join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_personal.IDENTIDAD')
                                ->join('d_personal_tipodoc', 'd_personal_tipodoc.IDTIPO_DOC', '=', 'm_personal.IDTIPO_DOC')
                                ->where('IDPERSONAL', $idpersonal)
                                ->where('m_personal.FLAG', 1)
                                ->first();

        return view('asignacion.asignacion_inventario', compact('personal', 'asignacion_estado', 'datos_acepta'));
    }

    public function tb_asignacion(Request $request)
    {
        $query = Asignacion::join('m_almacen', 'm_almacen.IDALMACEN', '=', 'm_asignacion_bien.IDALMACEN')
                            ->join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'm_asignacion_bien.IDCENTRO_MAC')
                            ->join('m_asig_personal', 'm_asig_personal.IDASIG_PERSONAL', '=', 'm_asignacion_bien.IDASIG_PERSONAL')
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
        $ids_encontrados = DB::select("SELECT GROUP_CONCAT(IDALMACEN SEPARATOR ', ') as NOMBRES FROM m_asignacion_bien WHERE IDCENTRO_MAC = " . $this->centro_mac()->idmac . "");
        // dd($ids_encontrados);
        if (isset($ids_encontrados[0]->NOMBRES) ) {
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
                $almacen = DB::select("SELECT * FROM m_almacen WHERE IDALMACEN NOT IN ($nombres) ");
            }            
        } else {
            // Manejar el caso cuando $ids_encontrados es un array vacío
            if($request->term){
                $almacen = Almacen::where(function ($query) use ($request) {
                        $term = '%' . $request->term . '%';
                        $query->where('COD_INTERNO_PCM', 'LIKE', $term)
                            ->orWhere('DESCRIPCION', 'LIKE', $term)
                            ->orWhere('SERIE_MEDIDA', 'LIKE', $term)
                            ->orWhere('UBICACION_EQUIPOS', 'LIKE', $term);
                    })
                    ->get();
            }else{
                $almacen = Almacen::get();
            }            
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
        $datos_persona = Personal::join('m_entidad', 'm_entidad.IDENTIDAD' ,'=','m_personal.IDENTIDAD')->where('m_personal.IDPERSONAL', $idpersonal)->first();

        $centro_mac = $this->centro_mac()->name_mac;

        $query = Asignacion::select('*')
                    ->join('m_almacen as ma', 'ma.IDALMACEN', '=', 'm_asignacion_bien.IDALMACEN')
                    ->where('m_asignacion_bien.IDPERSONAL', $idpersonal)
                    ->get();
        
        $count = DB::select("SELECT COUNT(*) AS NUM_C FROM m_asignacion_bien WHERE IDPERSONAL = $idpersonal ");

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
        $datos_persona = Personal::join('m_entidad', 'm_entidad.IDENTIDAD' ,'=','m_personal.IDENTIDAD')->where('m_personal.IDPERSONAL', $idpersonal)->first();

        $centro_mac = $this->centro_mac()->name_mac;

        $query = Asignacion::select('*')
                    ->join('m_almacen as ma', 'ma.IDALMACEN', '=', 'm_asignacion_bien.IDALMACEN')
                    ->where('m_asignacion_bien.IDPERSONAL', $idpersonal)
                    ->get();
        
        $count = DB::select("SELECT COUNT(*) AS NUM_C FROM m_asignacion_bien WHERE IDPERSONAL = $idpersonal ");

        // dd($query);

        $pdf = Pdf::loadView('asignacion.pdf.orginal_pdf', compact('query', 'datos_persona', 'count', 'centro_mac'))->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function cargar_documento_acept(Request $request)
    {
        try{

            $personal = AsigPersonal::join('m_personal', 'm_personal.IDPERSONAL', '=', 'm_asig_personal.IDPERSONAL')
                                        ->where('m_asig_personal.IDASIG_PERSONAL', $request->asignacion_estado)
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

            $save = DB::table('a_asignacion_bien')->insert([
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
