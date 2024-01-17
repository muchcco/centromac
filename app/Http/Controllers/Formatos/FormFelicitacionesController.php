<?php

namespace App\Http\Controllers\Formatos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Personal;
use App\Models\User;
use App\Models\FLibroFelicitacion;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FormFelicitacionesController extends Controller
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
        return view('formatos.f_felicitaciones.index');
    }

    public function tb_index(Request $request)
    {
        $query = FLibroFelicitacion::from('F_LIBRO_FELICITACIONES as FLF')->select(
                                            'FLF.IDLIBRO_FELICITACION',
                                            'FLF.CORRELATVIO',
                                            'FLF.R_FECHA',
                                            'MCM.NOMBRE_MAC',
                                            \DB::raw('MPR.NOMBRE AS NOM_REGISTRA'),
                                            \DB::raw('CONCAT(FLF.R_NOMBRE, \', \', FLF.R_APE_PAT, \' \', FLF.R_APE_MAT) AS NOMBREU'),
                                            \DB::raw('CONCAT(DPT.TIPODOC_ABREV, \' - \', FLF.R_NUM_DOC) AS DOCUMENTO'),
                                            'FLF.R_DESCRIPCION',
                                            'ME.ABREV_ENTIDAD',
                                            \DB::raw('CONCAT(MP.NOMBRE, \', \', MP.APE_PAT, \' \', MP.APE_MAT) AS ASESOR')
                                        )
                                            ->leftJoin('M_PERSONAL AS MP', 'MP.IDPERSONAL', '=', 'FLF.IDPERSONAL')
                                            ->leftJoin('M_PERSONAL AS MPR', 'MPR.IDPERSONAL', '=', 'FLF.IDPER_REGISTRA')
                                            ->leftJoin('M_CENTRO_MAC AS MCM', 'MCM.IDCENTRO_MAC', '=', 'FLF.IDCENTRO_MAC')
                                            ->leftJoin('D_PERSONAL_TIPODOC AS DPT', 'DPT.IDTIPO_DOC', '=', 'FLF.IDTIPO_DOC')
                                            ->leftJoin('M_ENTIDAD AS ME', 'ME.IDENTIDAD', '=', 'FLF.IDENTIDAD')
                                            ->where('FLF.FLAG', 1)
                                            ->orderBy('FLF.CORRELATVIO', 'desc')
                                            ->get();

        return view('formatos.f_felicitaciones.tablas.tb_index', compact('query'));

    }

    public function md_add_felicitacion(Request $request)
    {
        $tip_doc = DB::table('D_PERSONAL_TIPODOC')->get();

        $entidad = DB::table('M_MAC_ENTIDAD')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $this->centro_mac()->idmac)
                            ->get();

        $asesor = Personal::where('FLAG' , 1)->where('IDMAC', $this->centro_mac()->idmac)->get();

        $view = view('formatos.f_felicitaciones.modals.md_add_felicitacion', compact('tip_doc', 'entidad', 'asesor'))->render();

        return response()->json(['html' => $view]);
    }

    public function store(Request $request)
    {
        try {
            // dd($request->all());
            $corr_ = FLibroFelicitacion::orderby('IDLIBRO_FELICITACION', 'DESC')->first();

            if(isset($corr_->CORRELATVIO)){
                $cont_ = $corr_->CORRELATVIO + 1;
                // dd($cont_);
                $codpadron = Str::padLeft($cont_, 7, '0');
            }else{
                $codpadron = '0000001';
            }


            $num_doc = $request->num_doc;

            $estructura_carp = 'formato_archivo\\felicitaciones\\'.$num_doc;

            if (!file_exists($estructura_carp)) {
                mkdir($estructura_carp, 0777, true);
            }

            $save = new FLibroFelicitacion;
            $save->IDPER_REGISTRA = auth()->user()->id;
            $save->IDCENTRO_MAC = $this->centro_mac()->idmac;
            $save->CORRELATVIO = $codpadron;
            $save->AÑO = Carbon::now()->format('Y');
            $save->MES = Carbon::now()->format('m');
            $save->R_FECHA = $request->fecha;
            $save->R_NOMBRE = $request->idtipo_dato;
            $save->R_APE_PAT = $request->ape_pat;
            $save->R_APE_MAT = $request->ape_mat;
            $save->IDTIPO_DOC = $request->tipo_doc;
            $save->R_NUM_DOC = $request->num_doc;
            $save->R_CORREO = $request->correo;
            $save->R_DESCRIPCION = $request->descripcion;
            $save->IDENTIDAD = $request->entidad;
            $save->IDPERSONAL = $request->asesor;
            if($request->hasFile('file_doc'))
            {
                $archivoPDF = $request->file('file_doc');
                $nombrePDF = $archivoPDF->getClientOriginalName();
                //$nameruta = '/img/fotoempresa/'; // RUTA DONDE SE VA ALMACENAR EL DOCUMENTO PDF
                $nameruta = $estructura_carp;  // GUARDAR EN UN SERVIDOR
                $archivoPDF->move($nameruta, $nombrePDF);

                $save->R_ARCHIVO_NOM = $nombrePDF;
                $save->R_ARCHIVO_RUT = $estructura_carp;
            }
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
}
