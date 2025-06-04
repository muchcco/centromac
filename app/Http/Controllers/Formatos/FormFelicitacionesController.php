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
use App\Exports\FelicitacionExport;
use Maatwebsite\Excel\Facades\Excel;

class FormFelicitacionesController extends Controller
{
    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac' => $idmac, 'name_mac' => $name_mac];

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
            \DB::raw('CONCAT(MP.NOMBRE, \', \', MP.APE_PAT, \' \', MP.APE_MAT) AS ASESOR'),
            'FLF.R_ARCHIVO_RUT',
            'FLF.R_ARCHIVO_NOM',
            'FLF.AÑO',
            'FLF.CORRELATIVO_MAC',
        )
            ->leftJoin('M_PERSONAL AS MP', 'MP.IDPERSONAL', '=', 'FLF.IDPERSONAL')
            ->leftJoin('M_PERSONAL AS MPR', 'MPR.IDPERSONAL', '=', 'FLF.IDPER_REGISTRA')
            ->leftJoin('M_CENTRO_MAC AS MCM', 'MCM.IDCENTRO_MAC', '=', 'FLF.IDCENTRO_MAC')
            ->leftJoin('D_PERSONAL_TIPODOC AS DPT', 'DPT.IDTIPO_DOC', '=', 'FLF.IDTIPO_DOC')
            ->leftJoin('M_ENTIDAD AS ME', 'ME.IDENTIDAD', '=', 'FLF.IDENTIDAD')
            ->where('FLF.FLAG', 1)
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('FLF.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
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

        # $asesor = Personal::where('FLAG' , 1)->where('IDMAC', $this->centro_mac()->idmac)->get();
        // Obtención de los asesores filtrados por el status = 1 y el idmac del centro MAC actual
        $asesor = DB::table('D_PERSONAL_MAC')
            ->join('M_PERSONAL', 'D_PERSONAL_MAC.IDPERSONAL', '=', 'M_PERSONAL.IDPERSONAL')
            // ->where('D_PERSONAL_MAC.Status', 1) // Filtra por status = 1
            ->where('D_PERSONAL_MAC.IDCENTRO_MAC', $this->centro_mac()->idmac) // Filtra por el centro MAC actual
            ->select('M_PERSONAL.IDPERSONAL', 'M_PERSONAL.NOMBRE', 'M_PERSONAL.APE_PAT', 'M_PERSONAL.APE_MAT')
            ->get();
            
        $view = view('formatos.f_felicitaciones.modals.md_add_felicitacion', compact('tip_doc', 'entidad', 'asesor'))->render();

        return response()->json(['html' => $view]);
    }

    public function store(Request $request)
    {
        try {

            $currentYear = Carbon::now()->format('Y');

            $corr_ =  FLibroFelicitacion::where('AÑO', $currentYear)
                ->orderby('IDLIBRO_FELICITACION', 'DESC')
                ->first();

            if (isset($corr_->CORRELATVIO)) {
                $cont_ = $corr_->CORRELATVIO + 1;
                // dd($cont_);
                $codpadron = Str::padLeft($cont_, 7, '0');
            } else {
                $codpadron = '0000001';
            }


            $num_doc = $request->num_doc;

            $estructura_carp = 'formato_archivo\\felicitaciones\\' . $num_doc;

            if (!file_exists($estructura_carp)) {
                mkdir($estructura_carp, 0777, true);
            }

            $save = new FLibroFelicitacion;
            $save->IDPER_REGISTRA = auth()->user()->idpersonal;
            $save->IDCENTRO_MAC = $this->centro_mac()->idmac;
            $save->CORRELATVIO = $codpadron;
            $save->CORRELATIVO_MAC = $request->correlativo_mac;
            $save->AÑO = Carbon::now()->format('Y');
            $save->MES = Carbon::now()->format('m');
            $save->R_FECHA = $request->fecha;
            $save->R_NOMBRE = $request->nombre;
            $save->R_APE_PAT = $request->ape_pat;
            $save->R_APE_MAT = $request->ape_mat;
            $save->IDTIPO_DOC = $request->tipo_doc;
            $save->R_NUM_DOC = $request->num_doc;
            $save->R_CORREO = $request->correo;
            $save->R_DESCRIPCION = $request->descripcion;
            $save->IDENTIDAD = $request->entidad;
            $save->IDPERSONAL = $request->asesor;
            if ($request->hasFile('file_doc')) {
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
        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function md_edit_felicitacion(Request $request)
    {
        $tip_doc = DB::table('D_PERSONAL_TIPODOC')->get();

        $entidad = DB::table('M_MAC_ENTIDAD')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $this->centro_mac()->idmac)
            ->get();

        $asesor = DB::table('D_PERSONAL_MAC')
                    ->join('M_PERSONAL', 'D_PERSONAL_MAC.IDPERSONAL', '=', 'M_PERSONAL.IDPERSONAL')
                    // ->where('D_PERSONAL_MAC.Status', 1) // Filtra por status = 1
                    ->where('D_PERSONAL_MAC.IDCENTRO_MAC', $this->centro_mac()->idmac) // Filtra por el centro MAC actual
                    ->select('M_PERSONAL.IDPERSONAL', 'M_PERSONAL.NOMBRE', 'M_PERSONAL.APE_PAT', 'M_PERSONAL.APE_MAT')
                    ->get();

        $felicitacion = FLibroFelicitacion::where('IDLIBRO_FELICITACION', $request->idfelicitacion)->first();

        $view = view('formatos.f_felicitaciones.modals.md_edit_felicitacion', compact('tip_doc', 'entidad', 'asesor', 'felicitacion'))->render();

        return response()->json(['html' => $view]);
    }

    public function update(Request $request)
    {
        try {
            $num_doc = $request->num_doc;

            $estructura_carp = 'formato_archivo\\felicitaciones\\' . $num_doc;

            if (!file_exists($estructura_carp)) {
                mkdir($estructura_carp, 0777, true);
            }

            $save = FLibroFelicitacion::findOrFail($request->idfelicitacion);
            $save->R_NOMBRE = $request->nombre;
            $save->R_APE_PAT = $request->ape_pat;
            $save->R_APE_MAT = $request->ape_mat;
            $save->CORRELATIVO_MAC = $request->correlativo_mac;
            $save->R_FECHA = $request->fecha;
            $save->R_CORREO = $request->correo;
            $save->R_DESCRIPCION = $request->descripcion;
            $save->IDENTIDAD = $request->entidad;
            $save->IDPERSONAL = $request->asesor;
            if ($request->hasFile('file_doc')) {
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
        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function eliminar_archivo(Request $request)
    {
        $archivo = FLibroFelicitacion::where('IDLIBRO_FELICITACION', $request->idfelicitacion)->first();

        $del = FLibroFelicitacion::where('IDLIBRO_FELICITACION', $request->idfelicitacion)->update([
            'R_ARCHIVO_NOM' => null,
            'R_ARCHIVO_RUT' => null,
        ]);

        if (file_exists($archivo->R_ARCHIVO_RUT . '/' . $archivo->R_ARCHIVO_NOM)) {
            unlink($archivo->R_ARCHIVO_RUT . '/' . $archivo->R_ARCHIVO_NOM);
        }

        return $del;
    }

    public function delete(Request $request)
    {
        try {
            $archivo = FLibroFelicitacion::where('IDLIBRO_FELICITACION', $request->idfelicitacion)->first();

            if ($archivo) {
                $rutaCompleta = public_path($archivo->R_ARCHIVO_RUT . '/' . $archivo->R_ARCHIVO_NOM);

                // Verifica si el archivo existe antes de intentar eliminarlo
                if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }

            // Elimina el registro de la base de datos
            $del = FLibroFelicitacion::where('IDLIBRO_FELICITACION', $request->idfelicitacion)->delete();

            return response()->json(['status' => true, 'message' => 'Archivo eliminado correctamente.', 'data' => $del], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Ocurrió un error al eliminar el archivo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function export_excel(Request $request)
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
            \DB::raw('CONCAT(MP.NOMBRE, \', \', MP.APE_PAT, \' \', MP.APE_MAT) AS ASESOR'),
            'FLF.R_ARCHIVO_RUT',
            'FLF.R_ARCHIVO_NOM',
            'FLF.AÑO',
            'FLF.CORRELATIVO_MAC',
        )
            ->leftJoin('M_PERSONAL AS MP', 'MP.IDPERSONAL', '=', 'FLF.IDPERSONAL')
            ->leftJoin('M_PERSONAL AS MPR', 'MPR.IDPERSONAL', '=', 'FLF.IDPER_REGISTRA')
            ->leftJoin('M_CENTRO_MAC AS MCM', 'MCM.IDCENTRO_MAC', '=', 'FLF.IDCENTRO_MAC')
            ->leftJoin('D_PERSONAL_TIPODOC AS DPT', 'DPT.IDTIPO_DOC', '=', 'FLF.IDTIPO_DOC')
            ->leftJoin('M_ENTIDAD AS ME', 'ME.IDENTIDAD', '=', 'FLF.IDENTIDAD')
            ->where('FLF.FLAG', 1)
            ->orderBy('FLF.CORRELATVIO', 'desc')
            ->get();

        $name_mac = $this->centro_mac()->name_mac;

        $export = Excel::download(new FelicitacionExport($query, $name_mac), 'FORMATO LIBRO DE FELICITACIONES  CENTRO MAC - ' . $this->centro_mac()->name_mac . '.xlsx');

        return $export;
    }
}
