<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Personal;
use App\Models\Mac;
use App\Models\Entidad;
use App\Models\Archivoper;
use App\Models\Servicio;
use App\Models\User;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\ConfiguracionMAc;

class PagesController extends Controller
{
    private function centro_mac_id(){
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
        $count_asesores = Personal::where('IDMAC', $this->centro_mac_id()->idmac)->where('FLAG', 1)->whereNot('IDENTIDAD', 17)->get()->count();

        $count_entidad = DB::table('M_MAC_ENTIDAD')->where('IDCENTRO_MAC', $this->centro_mac_id()->idmac)->whereNot('IDENTIDAD', 17)->get()->count();

        return view('inicio', compact('count_asesores', 'count_entidad'));
    }

    public function validar(Request $request)
    {
        $macs = Mac::get();

        $tip_doc = DB::table('D_PERSONAL_TIPODOC')->get();

        return view('validar', compact('tip_doc', 'macs'));
    }

    public function validar_dato(Request $request)
    {

        //VALIDAMOS SI EL USUARIO EXISTE
        /* ========================================================================================================= */

        $per_mac = Personal::where('NUM_DOC', $request->num_doc)->first();       
        
        /* ========================================================================================================= */

        /** PARA EL TIPO DE ESTATUS DEL PERSONAL **/
        ///  FLAG -> 1 = EL PERSONAL ESTA ACTIVO 
        ///  FLAG -> 2 = EL PERSONAL ESTA INACTIVO PERO PERTENECE AL CENTRO MAC
        ///  FLAG -> 3 = EL PERSONAL EXISTE PERO NO PERTENECE ALGÚN CENTRO MAC


        if(isset($per_mac)){
            $mac = Mac::where('IDCENTRO_MAC', $per_mac->IDMAC)->first();
            if($per_mac->FLAG == '1'){

                if($per_mac->IDMAC == $request->idmac){
                    return $per_mac;                    
                }else{
                    $response_ = response()->json([
                        'message' => "El personal pertenece al Centro MAC ". $mac->NOMBRE_MAC,
                        'status' => 201,
                    ], 200);
        
                    return $response_;
                }

            }elseif($per_mac->FLAG == '2'){

                if($per_mac->IDMAC == $request->idmac){
                    return $per_mac;                    
                }else{
                    $response_ = response()->json([
                        'message' => "El personal pertenece al Centro MAC ". $mac->NOMBRE_MAC . ", contacte con su especialista TIC del centro MAC para que se desvincule de su cuenta",
                        'status' => 202,
                    ], 200);
        
                    return $response_;
                }                

            }elseif($per_mac->FLAG == '3'){
                // dd($per_mac->NUM_DOC);
                $update = Personal::FindOrFail($per_mac->IDPERSONAL);
                $update->IDENTIDAD = $request->entidad;
                $update->FLAG = 1;
                $update->save();

                return $update;
                
            }            

        }else{
            
            $personal = new Personal;
            $personal->IDENTIDAD = $request->entidad;
            $personal->IDTIPO_DOC = $request->idtipo_doc;
            $personal->NUM_DOC = $request->num_doc;
            $personal->IDMAC = $request->idmac;
            $personal->save();

            return $personal;
        }
    }

    public function formdata($num_doc)
    {
        $departamentos = DB::table('DEPARTAMENTO')->get();

        $personal = Personal::leftJoin('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                ->where('M_PERSONAL.NUM_DOC', $num_doc)
                                ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                                ->first();
        // dd($personal);

        $dis_act = DB::table('DISTRITO AS D')
                        ->join('PROVINCIA AS P' , 'P.IDPROVINCIA', '=', 'D.PROVINCIA_ID')
                        ->join('DEPARTAMENTO  AS DP' , 'DP.IDDEPARTAMENTO', '=', 'D.DEPARTAMENTO_ID')
                        ->join('M_PERSONAL AS M', 'M.IDDISTRITO', '=', 'D.IDDISTRITO')
                        ->where('M.IDPERSONAL', $personal->IDPERSONAL)
                        ->first();
        // dd($dis_act);

        $dis_nac = DB::table('DISTRITO AS D')
                        ->join('PROVINCIA AS P' , 'P.IDPROVINCIA', '=', 'D.PROVINCIA_ID')
                        ->join('DEPARTAMENTO AS DP' , 'DP.IDDEPARTAMENTO', '=', 'D.DEPARTAMENTO_ID')
                        ->join('M_PERSONAL AS M', 'M.IDDISTRITO_NAC', '=', 'D.IDDISTRITO')
                        ->where('M.IDPERSONAL', $personal->IDPERSONAL)
                        ->first();
                                
        $entidad = DB::table('M_MAC_ENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                        ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $personal->IDMAC)
                        ->whereNot('M_ENTIDAD.IDENTIDAD', 17)
                        ->orderBy('M_ENTIDAD.NOMBRE_ENTIDAD', 'ASC')
                        ->get();

        $detall_fam = DB::table('D_PERSONAL_FAM')->join('M_PERSONAL', 'M_PERSONAL.IDPERSONAL', '=', 'D_PERSONAL_FAM.IDPERSONAL')->where('M_PERSONAL.NUM_DOC', $num_doc)->get();

        return view('formdata', compact('personal', 'entidad', 'departamentos', 'detall_fam', 'dis_act', 'dis_nac'));
    }

    public function add_datosfamiliares(Request $request)
    {
        $save = DB::table('D_PERSONAL_FAM')->where('IDPERSONAL', $request->idpersonal)->insert([
            'IDPERSONAL' => $request->idpersonal,
            'DATOS_NOMBRES' => $request->datos_nombre,
            'DATOS_PARENTESCO' => $request->datos_parentesco,
            'DATOS_ACTIVIDAD' => $request->datos_actividad,
            'DATOS_EDAD' => $request->datos_edad,
        ]);

        return $save;
    }

    public function delete_datosfamiliares(Request $request)
    {
        $delete = DB::table('D_PERSONAL_FAM')->where('IDDATOS_PERSONAL', $request->iddatos_personal)->delete();

        return $delete;
    }

    public function store_data(Request $request)
    {
        if($request->identidad == '17' ){

            // dd($request->all());
            $data = $request->except(['gi_otro', 'dni'/*, 'cv'*/]);

            foreach ($data as $key => $value) {
                if ($value === NULL || $value === "undefined") {
                    $missingValues[] = $key;
                }
            }

            if (!empty($missingValues)) {

                $errorMessage = 'Falta llenar los siguientes campos : ' . "\n";

                foreach ($missingValues as $value) {
                    $errorMessage .= "* " . str_replace(
                        [
                            'nombre', 'ape_pat', 'ape_mat', 'idtipo_doc', 'num_doc', 'identidad', 'direccion', 'sexo', 'fech_nacimiento', 'distrito',  'distrito2', 'telefono', 'celular', 'correo', 'grupo_sanguineo', 'pcm_talla', 'cargo_pcm', 'finicio' , 
                        ],
                        [
                            'Ingrese su nombre', 'Ingrese su Apellido Paterno', 'Ingrese su Apellido Materno', 'Ingrese su Tipo de Documento', 'Ingrese su Número de Documento', 'Ingrese su Entidad', 'Ingrese su Dirección', 'Ingrese su sexo', 'Ingrese su Fecha de Nacimiento', 'Ingrese su Distrito Actual', 'Ingrese el lugar de nacimiento', 'Ingrese su númerp de teléfono', 'Ingrese su número de celular', 'Ingrese su Correo', 'Ingrese su grupo sanguineo','Ingrese su talla' , 'Ingrese su Cargo en la PCM', 'Ingrese su fecha de Inicio de labores',
                        ], 
                        $value) . "\n";
                }
                // dd($errorMessage);

                $response_ = response()->json([
                    'data' => null,
                    'message' => $errorMessage,
                    'status' => 201,
                ], 200);

                return $response_;                

            }

            $IDPERSONAL = $request->idpersonal;

            $update = Personal::findOrFail($IDPERSONAL);
            $update->NOMBRE = $request->nombre;
            $update->APE_PAT = $request->ape_pat;
            $update->APE_MAT = $request->ape_mat;
            $update->IDTIPO_DOC = $request->idtipo_doc;
            $update->NUM_DOC = $request->num_doc;        
            $update->IDENTIDAD = $request->identidad;
            $update->DIRECCION = $request->direccion;
            $update->SEXO = $request->sexo;
            $update->FECH_NACIMIENTO = $request->fech_nacimiento;
            $update->IDDISTRITO = $request->distrito;
            $update->IDDISTRITO_NAC = $request->distrito2;
            $update->TELEFONO = $request->telefono;
            $update->CELULAR = $request->celular;
            $update->CORREO = $request->correo;
            $update->GRUPO_SANGUINEO = $request->grupo_sanguineo;
            $update->PCM_TALLA = $request->pcm_talla;

            $update->IDCARGO_PERSONAL = $request->cargo_pcm;
            $update->PCM_OS_FINICIO = $request->finicio;
            $update->GI_ID = $request->gi_id;
            $update->GI_OTRO = $request->gi_otro;
            $update->GI_CARRERA = $request->gi_carrera;
            $update->GI_DESDE = $request->gi_desde;
            $update->GI_HASTA = $request->gi_hasta;
            $update->save();

            $estructura_carp = 'personal\\num_doc\\'.$request->num_doc;
            if (!file_exists($estructura_carp)) {
                mkdir($estructura_carp, 0777, true);
            }

            $dni = new Archivoper;
            $dni->IDPERSONAL = $IDPERSONAL;
            if($request->hasFile('dni'))
            {
                $archivoDNI = $request->file('dni');
                $nombreDNI = $archivoDNI->getClientOriginalName();
                $formatoDNI = $archivoDNI->getClientOriginalExtension(); // Obtener el formato (extensión) del archivo
                $tamañoEnKBDNI = $archivoDNI->getSize() / 1024; // Tamaño en kilobytes
                //$nameruta = '/DNI/fotoempresa/'; // RUTA DONDE SE VA ALMACENAR EL DOCUMENTO PDF
                $namerutaDNI = $estructura_carp;  // GUARDAR EN UN SERVIDOR
                $archivoDNI->move($namerutaDNI, $nombreDNI);

                $dni->NOMBRE_ARCHIVO = $nombreDNI;
                $dni->NOMBRE_RUTA = $estructura_carp.'\\'.$nombreDNI;
                $dni->FORMATO_DOC = $formatoDNI;
                $dni->PESO_DOC = $tamañoEnKBDNI;
            }
            $dni->save();

            return $update;

        }else{
            $data = $request->except(['tvl_otro', 'gi_otro', 'dni', 'cv']);
            $missingValues = [];

            foreach ($data as $key => $value) {
                if ($value === NULL || $value === "undefined") {
                    $missingValues[] = $key;
                }
            }
            if (!empty($missingValues)) {

                $errorMessage = 'Falta llenar los siguientes campos : ' . "\n";

                foreach ($missingValues as $value) {
                    $errorMessage .= "* " . str_replace(
                        [
                            'nombre', 'ape_pat', 'ape_mat', 'idtipo_doc', 'num_doc', 'identidad', 'direccion', 'sexo', 'fech_nacimiento', 'distrito',  'distrito2', 'telefono', 'celular', 'correo', 'grupo_sanguineo', 'pcm_talla', 'e_nomape', 'e_telefono', 'e_celular', 'ecivil', 'df_n_hijos', 'dp_fecha_ingreso', 'dp_puesto_trabajo', 'dp_tiempo_ptrabajo', 'dp_centro_atencion', 'dp_codigo_identificacion', 'dlp_fecha_ingreso', 'dlp_puesto_trabajo', 'dlp_tiempo_puesto', 'dlp_area_trabajo', 'dlp_jefe_inmediato', 'dlp_cargo', 'dlp_telefono', 'tlv_id', 'gi_id',  'gi_carrera', 'gi_desde', 'gi_hasta', 'num_modulo', 
                        ],
                        [
                            'Ingrese su nombre', 'Ingrese su Apellido Paterno', 'Ingrese su Apellido Materno', 'Ingrese su Tipo de Documento', 'Ingrese su Número de Documento', 'Ingrese su Entidad', 'Ingrese su Dirección', 'Ingrese su sexo', 'Ingrese su Fecha de Nacimiento', 'Ingrese su Distrito Actual', 'Ingrese el lugar de nacimiento', 'Ingrese su númerp de teléfono', 'Ingrese su número de celular', 'Ingrese su Correo', 'Ingrese su grupo sanguineo','Ingrese su talla' ,'Ingrese Nombre y apellido de su contacto de emergencia', 'Ingrese el número de teléfono de su contacto de emergencia', 'Ingrese el número celular de su contacto de emergencia', 'Ingrese su estado civil', 'Ingrese el número de hijos si da el caso que no tiene colocar "0"', 'Ingrese fecha de Ingreso al centro MAC', 'Ingrese su puesto de trabajo', 'Ingrese el tiempo en el puesto de trabajo', 'Seleccione tipo de atención en el centro MAC', 'Ingrese su código de identificación que le remiten de su entidad si no lo tiene colocar (-)', 'Ingrese la fecha de ingreso a su entidad','Ingrese el puesto que tiene a su entidad', 'Ingrese el tiempo que tiene en su entidad ejem: x años y meses d dias ', 'Ingrese al área que pertenece en su entidad', 'Ingrese el nombre y apellido de su jefe inmediato', 'Ingrese el cargo al que corresponde su jefe inmediato', 'ingrese el número de telefono de su jefe inmediato', 'Seleccione el tipo de vinculación laboral que tiene con su entidad', 'Seleccione su grado de instruccón', 'Ingrese su carrera o profesión', 'Ingrese la fecha cuando inicio su carrera o profesión', 'Ingrese la fecha cuando culmino su carrera o profesión', 'Ingrese el número de módulo asignado'
                        ], 
                        $value) . "\n";
                }
                // dd($errorMessage);

                $response_ = response()->json([
                    'data' => null,
                    'message' => $errorMessage,
                    'status' => 201,
                ], 200);

                return $response_;
                

            }

            $IDPERSONAL = $request->idpersonal;

            $update = Personal::findOrFail($IDPERSONAL);
            $update->NOMBRE = $request->nombre;
            $update->APE_PAT = $request->ape_pat;
            $update->APE_MAT = $request->ape_mat;
            $update->IDTIPO_DOC = $request->idtipo_doc;
            $update->NUM_DOC = $request->num_doc;        
            $update->IDENTIDAD = $request->identidad;
            $update->DIRECCION = $request->direccion;
            $update->SEXO = $request->sexo;
            $update->FECH_NACIMIENTO = $request->fech_nacimiento;
            $update->IDDISTRITO = $request->distrito;
            $update->IDDISTRITO_NAC = $request->distrito2;
            $update->TELEFONO = $request->telefono;
            $update->CELULAR = $request->celular;
            $update->CORREO = $request->correo;
            $update->GRUPO_SANGUINEO = $request->grupo_sanguineo;
            $update->PCM_TALLA = $request->pcm_talla;
            $update->NUMERO_MODULO = $request->num_modulo;

            // $update->FOTO_RUTA = $request->

            $update->E_NOMAPE = $request->e_nomape;
            $update->E_TELEFONO = $request->e_telefono;
            $update->E_CELULAR = $request->e_celular;
            $update->ESTADO_CIVIL = $request->ecivil;
            $update->DF_N_HIJOS = $request->df_n_hijos;
            $update->PD_FECHA_INGRESO = $request->dp_fecha_ingreso;
            $update->PD_PUESTO_TRABAJO = $request->dp_puesto_trabajo;
            $update->PD_TIEMPO_PTRABAJO = $request->dp_tiempo_ptrabajo;
            $update->PD_CENTRO_ATENCION = $request->dp_centro_atencion;
            $update->PD_CODIGO_IDENTIFICACION = $request->dp_codigo_identificacion;
            $update->DLP_FECHA_INGRESO = $request->dlp_fecha_ingreso;
            $update->DLP_PUESTO_TRABAJO = $request->dlp_puesto_trabajo;
            $update->DLP_TIEMPO_PTRABAJO = $request->dlp_tiempo_puesto;
            $update->DLP_AREA_TRABAJO = $request->dlp_area_trabajo;
            $update->DLP_JEFE_INMEDIATO = $request->dlp_jefe_inmediato;
            $update->DLP_CARGO = $request->dlp_cargo;
            $update->DLP_TELEFONO = $request->dlp_telefono;
            $update->TVL_ID = $request->tlv_id;
            $update->TVL_OTRO = $request->tvl_otro;
            $update->GI_ID = $request->gi_id;
            $update->GI_OTRO = $request->gi_otro;
            $update->GI_CARRERA = $request->gi_carrera;
            $update->GI_DESDE = $request->gi_desde;
            $update->GI_HASTA = $request->gi_hasta;
            $update->save();

            // GUARDAMOS EL ARCHIVO DNI

            

            $estructura_carp = 'personal\\num_doc\\'.$request->num_doc;
            if (!file_exists($estructura_carp)) {
                mkdir($estructura_carp, 0777, true);
            }

            $dni = new Archivoper;
            $dni->IDPERSONAL = $IDPERSONAL;
            if($request->hasFile('dni'))
            {
                $archivoDNI = $request->file('dni');
                $nombreDNI = $archivoDNI->getClientOriginalName();
                $formatoDNI = $archivoDNI->getClientOriginalExtension(); // Obtener el formato (extensión) del archivo
                $tamañoEnKBDNI = $archivoDNI->getSize() / 1024; // Tamaño en kilobytes
                //$nameruta = '/DNI/fotoempresa/'; // RUTA DONDE SE VA ALMACENAR EL DOCUMENTO PDF
                $namerutaDNI = $estructura_carp;  // GUARDAR EN UN SERVIDOR
                $archivoDNI->move($namerutaDNI, $nombreDNI);

                $dni->NOMBRE_ARCHIVO = $nombreDNI;
                $dni->NOMBRE_RUTA = $estructura_carp.'\\'.$nombreDNI;
                $dni->FORMATO_DOC = $formatoDNI;
                $dni->PESO_DOC = $tamañoEnKBDNI;
            }
            $dni->save();

            $cv = new Archivoper;
            $cv->IDPERSONAL = $IDPERSONAL;
            if($request->hasFile('cv'))
            {
                $archivoCV = $request->file('cv');
                $nombreCV = $archivoCV->getClientOriginalName();
                $formatoCV = $nombreCV->getClientOriginalExtension(); // Obtener el formato (extensión) del archivo
                $tamañoEnKBCV = $archivo->getSize() / 1024; // Tamaño en kilobytes
                //$nameruta = '/CV/fotoempresa/'; // RUTA DONDE SE VA ALMACENAR EL DOCUMENTO PDF
                $namerutaCV = $estructura_carp;  // GUARDAR EN UN SERVIDOR
                $archivoCV->move($namerutaCV, $nombreCV);

                $cv->NOMBRE_ARCHIVO = $nombreCV;
                $cv->NOMBRE_RUTA = $estructura_carp.'\\'.$nombreCV;
                $cv->FORMATO_DOC = $formatoCV;
                $cv->PESO_DOC = $tamañoEnKBCV;
            }
            $cv->save();


            

            return $update;
        }

    }

    public function formdata_pcm(Request $request, $num_doc)
    {
        $departamentos = DB::table('DEPARTAMENTO')->get();

        $personal = Personal::leftJoin('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                ->where('M_PERSONAL.NUM_DOC', $num_doc)
                                ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                                ->first();
        // dd($personal);

        $dis_act = DB::table('DISTRITO AS D')
                        ->join('PROVINCIA AS P' , 'P.IDPROVINCIA', '=', 'D.PROVINCIA_ID')
                        ->join('DEPARTAMENTO  AS DP' , 'DP.IDDEPARTAMENTO', '=', 'D.DEPARTAMENTO_ID')
                        ->join('M_PERSONAL AS M', 'M.IDDISTRITO', '=', 'D.IDDISTRITO')
                        ->where('M.IDPERSONAL', $personal->IDPERSONAL)
                        ->first();
        // dd($dis_act);

        $dis_nac = DB::table('DISTRITO AS D')
                        ->join('PROVINCIA AS P' , 'P.IDPROVINCIA', '=', 'D.PROVINCIA_ID')
                        ->join('DEPARTAMENTO AS DP' , 'DP.IDDEPARTAMENTO', '=', 'D.DEPARTAMENTO_ID')
                        ->join('M_PERSONAL AS M', 'M.IDDISTRITO_NAC', '=', 'D.IDDISTRITO')
                        ->where('M.IDPERSONAL', $personal->IDPERSONAL)
                        ->first();
                                
        $entidad = DB::table('M_MAC_ENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                        ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $personal->IDMAC)
                        ->get();

        $detall_fam = DB::table('D_PERSONAL_FAM')->join('M_PERSONAL', 'M_PERSONAL.IDPERSONAL', '=', 'D_PERSONAL_FAM.IDPERSONAL')->where('M_PERSONAL.NUM_DOC', $num_doc)->get();

        $cargo = DB::table('D_PERSONAL_CARGO')->get();

        return view('formdata_pcm', compact('personal', 'entidad', 'departamentos', 'detall_fam', 'dis_act', 'dis_nac', 'cargo'));
    }

    /********************************************************** SERVICIOS ************************************************************************/

    public function servicios(Request $request)
    {
        
        $macs = Mac::get();

        $tip_doc = DB::table('D_PERSONAL_TIPODOC')->get();

        return view('servicios', compact('tip_doc', 'macs'));
    }

    public function centro_mac($idcentro_mac)
    {
        $entidades = DB::table('M_MAC_ENTIDAD')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $idcentro_mac)
                            ->get();

        $options = '<option value="">-- Seleccione una entidad --</option>';
        foreach ($entidades as $ent) {
            $options .= '<option value="' . $ent->IDENTIDAD . '">' . $ent->NOMBRE_ENTIDAD . '</option>';
        }        

        return $options;
    }

    public function validar_servicio(Request $request)
    {
        // dd($request->all());

        $servicios = DB::table('D_ENT_SERV as DES')
                        ->join('D_ENTIDAD_SERVICIOS as SERV', 'SERV.IDSERVICIOS', '=', 'DES.IDSERVICIOS')
                        ->join('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'DES.IDENTIDAD')
                        ->join('M_CENTRO_MAC as MAC', 'MAC.IDCENTRO_MAC', '=', 'DES.IDMAC')
                        ->select('DES.*', 'SERV.*', 'ME.*', 'MAC.*')
                        ->where('MAC.IDCENTRO_MAC', $request->idmac)
                        ->where('ME.IDENTIDAD', $request->identidad)
                        ->first();

        return $servicios;
    }

    public function list_serv(Request $request, $idcentro_mac, $identidad)
    {
        // dd($request->all());

        $entidad = Entidad::where('IDENTIDAD', $identidad)->first();

        $mac = Mac::where('IDCENTRO_MAC', $idcentro_mac)->first();


        $servicios = DB::table('D_ENT_SERV as DES')
                        ->join('D_ENTIDAD_SERVICIOS as SERV', 'SERV.IDSERVICIOS', '=', 'DES.IDSERVICIOS')
                        ->join('M_ENTIDAD as ME', 'ME.IDENTIDAD', '=', 'DES.IDENTIDAD')
                        ->join('M_CENTRO_MAC as MAC', 'MAC.IDCENTRO_MAC', '=', 'DES.IDMAC')
                        ->select('DES.*', 'SERV.*', 'ME.*', 'MAC.*')
                        ->where('MAC.IDCENTRO_MAC', $idcentro_mac)
                        ->where('ME.IDENTIDAD', $identidad)
                        ->get();

        return view('list_serv', compact('servicios', 'mac', 'entidad'));
    }

    public function md_edit_servicios_ext(Request $request)
    {
        $servicios = Servicio::where('IDSERVICIOS', $request->idservicios)->first();

        // dd($request->all());

        $view = view('md_edit_servicios_ext', compact('servicios'))->render();

        return response()->json(['html' => $view]);
    }

    public function update_obsev(Request $request)
    {
        try{

            $update = Servicio::findOrFail($request->idservicios);
            $update->OBSERVACION = $request->observacion;
            $update->save();

            return $update;

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


    /********************************************************** RECURSOS ***************************************************************************/

    public function buscar_dni(Request $request)
    {
        $token = '';

        $client = new Client(['base_uri' => 'https://api.apis.net.pe', 'verify' => false]);

        $parameters = [
            'http_errors' => false,
            'connect_timeout' => 5,
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Referer' => 'https://apis.net.pe/api-consulta-dni',
                'User-Agent' => 'laravel/guzzle',
                'Accept' => 'application/json',
            ],
            'query' => ['numero' => $request->dni]
        ];
        // Para usar la versión 1 de la api, cambiar a /v1/ruc
        $res = $client->request('GET', '/v1/dni', $parameters);
        $response = json_decode($res->getBody()->getContents(), true);
        // var_dump($response);
        return $response;
    }

    public function provincias($departamento_id)
    {
        $provincias = DB::table('PROVINCIA')->where('DEPARTAMENTO_ID', $departamento_id)->get();

        $options = '<option value="">Selecciona una opción</option>';
        foreach ($provincias as $prov) {
            $options .= '<option value="' . $prov->IDPROVINCIA . '">' . $prov->NAME_PROVINCIA . '</option>';
        }

        return $options;
    }

    public function distritos($provincia_id)
    {        
        $distritos = DB::table('DISTRITO')->where('PROVINCIA_ID', $provincia_id)->get();

        $options = '<option value="">Selecciona una opción</option>';
        foreach ($distritos as $dist) {
            $options .= '<option value="' . $dist->IDDISTRITO . '">' . $dist->NAME_DISTRITO . '</option>';
        }

        return $options;
    }

    public function entidad($idcentro_mac)
    {
        $idcentro_mac = DB::table('M_MAC_ENTIDAD')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                            ->leftJoin('CONFIGURACION_SIST', 'CONFIGURACION_SIST.IDCONFIGURACION', '=', 'M_MAC_ENTIDAD.TIPO_REFRIGERIO')
                            ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $idcentro_mac)
                            ->orderBy('M_ENTIDAD.NOMBRE_ENTIDAD', 'ASC')
                            ->get();
        
        $options = '<option value="">Selecciona una opción</option>';
        foreach ($idcentro_mac as $sede) {
            $options .= '<option value="' . $sede->IDENTIDAD . '">' . $sede->NOMBRE_ENTIDAD . '</option>';
        }

        return $options;
    }

    /******************************************************** DATOS DE LOGIN ***********************************************************************************/

    public function login_verificacion(Request $request)
    {
        $usuario = User::where('email',$request->email)->first();
        $models = DB::select("select * from modelhasrolestemp where model_id = '".$usuario->id."' and estado = '1'");
        // dd($models);
        if($models){
            foreach($models as $model){

                // dd($model);
                DB::insert("insert into model_has_roles (role_id,model_type,model_id) values('".$model->role_id."','App\\Models\\User','".$model->model_id."')");
            }
        }
        //DB::delete("delete from modelhasrolestemp where model_id = '".$usuario->id."'");
        DB::update("update modelhasrolestemp set estado = '0' where model_id = '".$usuario->id."'");
        $select_roles = DB::select("select * from model_has_roles join roles on role_id = id where model_id = '".$usuario->id."'");
        // dd($select_roles)
        return Response()->json($select_roles);
    }

    /******************************************************** CONSUMO NOVOSGA***********************************************************************************/

    public function vista(Request $request)
    {
        $macs = Mac::get();

        $tip_doc = DB::table('D_PERSONAL_TIPODOC')->get();

        return view('vista', compact('tip_doc', 'macs'));
    }

    public function validar_entidad(Request $request)
    {
        $servicios = DB::table('M_ENTIDAD AS ME')
                        ->join('M_MAC_ENTIDAD AS MME', 'MME.IDENTIDAD', '=', 'ME.IDENTIDAD')
                        ->where('MME.IDCENTRO_MAC', $request->idmac)
                        ->where('ME.IDENTIDAD', $request->identidad)
                        ->first();
        // dd($servicios);
        return $servicios;
    }

    public function entidad_cola(Request $request, $identidad)
    {
        // $horaActual = Carbon::now();
        // dd($horaActual);

        // dd($identidad);
        $query = ConfiguracionMAc::select('SELECT 
                                                        DATE(dt_cheg) Fecha,
                                                        CONCAT(sigla_senha, num_senha) Ticket,
                                                        ss.nome Entidad,
                                                        ss.`id`,
                                                        att.`prioridade_id`,
                                                        IFNULL(ss2.nome, "No atendido") "tipo_servicio",
                                                        TIME_FORMAT(
                                                        IFNULL(dt_cheg, "00:00:00"),
                                                        "%H:%i:%s"
                                                        ) "hora_llegada",
                                                        TIME_FORMAT(
                                                        IFNULL(dt_cha, "00:00:00"),
                                                        "%H:%i:%s"
                                                        ) "hora_llamado",
                                                        IFNULL(
                                                        SEC_TO_TIME(
                                                            TIMESTAMPDIFF(SECOND, dt_cheg, dt_cha)
                                                        ),
                                                        "00:00:00"
                                                        ) "Tiempo de espera",
                                                        TIME_FORMAT(
                                                        IFNULL(dt_ini, "00:00:00"),
                                                        "%H:%i:%s"
                                                        ) "Hora Inicio de atencion",
                                                        IFNULL(
                                                        SEC_TO_TIME(
                                                            TIMESTAMPDIFF(SECOND, dt_ini, dt_fim)
                                                        ),
                                                        "00:00:00"
                                                        ) "Tiempo de atención",
                                                        TIME_FORMAT(
                                                        IFNULL(dt_fim, "00:00:00"),
                                                        "%H:%i:%s"
                                                        ) "Fin de Atención",
                                                        IFNULL(
                                                        SEC_TO_TIME(
                                                            (
                                                            (
                                                                TIMESTAMPDIFF(SECOND, dt_ini, dt_fim)
                                                            ) + (
                                                                TIMESTAMPDIFF(SECOND, dt_cheg, dt_cha)
                                                            )
                                                            )
                                                        ),
                                                        "00:00:00"
                                                        ) "Tiempo total",
                                                        IFNULL(
                                                        CONCAT(uu.nome, " ", uu.sobrenome),
                                                        "No Atendido"
                                                        ) Asesor,
                                                        IFNULL(SEC_TO_TIME(TIMESTAMPDIFF(SECOND, dt_cheg, NOW())), "00:00:00") "Tiempo_espera",
                                                        (
                                                        CASE
                                                            WHEN (att.status = 1) 
                                                            THEN "En espera" 
                                                            WHEN (att.status = 2) 
                                                            THEN "Llamando" 
                                                            WHEN (att.status = 3) 
                                                            THEN "Atención Iniciada" 
                                                            WHEN (att.status = 4) 
                                                            THEN "Atención Cerrada" 
                                                            WHEN (att.status = 5) 
                                                            THEN "Abandono" 
                                                            WHEN (att.status = 6) 
                                                            THEN "Cancelado" 
                                                            WHEN (att.status = 7) 
                                                            THEN "Error de selección" 
                                                            WHEN (att.status = 8) 
                                                            THEN "Terminado" 
                                                            ELSE att.status 
                                                        END
                                                        ) AS Estado,
                                                        CONCAT(uu2.nome, " ", uu2.sobrenome) "Derivado de",
                                                        att.nm_cli Ciudadano,
                                                        att.ident_cli num_docu 
                                                    FROM
                                                        atendimentos att 
                                                        LEFT JOIN atend_codif sss 
                                                        ON sss.atendimento_id = att.id 
                                                        LEFT JOIN servicos ss 
                                                        ON ss.id = att.servico_id 
                                                        LEFT JOIN servicos ss2 
                                                        ON ss2.id = sss.servico_id 
                                                        LEFT JOIN usuarios uu 
                                                        ON uu.id = att.usuario_id 
                                                        LEFT JOIN usuarios uu2 
                                                        ON uu2.id = att.usuario_tri_id 
                                                    WHERE ss.`id` IN ('.$identidad.')
                                                    AND att.status = 1
                                                    ORDER BY att.dt_cheg ASC ');


        // Supongamos que estás obteniendo los resultados así
        $resultados = $query;

        // Define las duraciones de tiempo para cada ID específico
        $duraciones = [
            65 => 7,   // TRAMITES => 65
            157 => 5,  // ENTREGAS => 157 
            336 => 4,  // CERTIFICACIONES => 336
            // Agrega más IDs y duraciones según sea necesario
        ];

        // Inicializa las variables para contar y sumar la duración
        $cantidadTotal = 0;
        $sumaTotalTiempo = 0;

        // Define las horas límite
        $horaInicio = Carbon::parse('13:30:00');
        $horaFin = Carbon::parse('16:30:00');

        // Itera sobre los resultados
        foreach ($resultados as $registro) {
            // Accede a las propiedades del objeto
            $id = $registro->id;

            // Verifica si el ID está definido en las duraciones
            if (isset($duraciones[$id])) {
                $cantidadTotal++;
                $sumaTotalTiempo += $duraciones[$id];
            }
        }

        // Convierte la suma total de tiempo en minutos
        $sumaTotalEnMinutos = $sumaTotalTiempo * 60;
        // dd($sumaTotalTiempo);
        // Obtiene la hora actua    l
        $horaActual = Carbon::now();
        $horaActualFormato = $horaActual->format('H:i:s');

        // Suma los minutos
        // $sumaTotalEnMinutos = $sumaTotalTiempo; // Aquí tu valor real de suma de minutos
        $horaProgramada = $horaActual->copy()->addMinutes($sumaTotalTiempo);
        $horaProgramadaFormato = $horaProgramada->format('H:i:s');

        // dd("Hora actual: $horaActualFormato", "Suma de tiempo: $sumaTotalEnMinutos minutos", "Hora programada en minutos: $horaProgramadaFormato");


        // Calcula la hora final sumando la hora de inicio y la suma total de tiempo
        $horaFinal = $horaInicio->copy()->addMinutes($sumaTotalEnMinutos);

        // Determina si la hora final es después de la hora límite
        $estaEnTarde = $horaFinal->greaterThan($horaFin);

        // Puedes usar dd() para imprimir y detener la ejecución si es necesario
        // dd("Cantidad total de registros: $cantidadTotal", "Suma total del tiempo: $sumaTotalTiempo minutos", "¿Está en hora?: " . ($estaEnTarde ? 'Sí' : 'No'));

        return view('entidad_cola', compact('query', 'cantidadTotal', 'sumaTotalTiempo', 'estaEnTarde', 'horaProgramadaFormato'));
    }

    public function externo()
    {
        return view('externo');
    }

    public function consultas_novo(Request $request)
    {
        $inicio_session_novo = DB::connection('mysql2')->select("CALL sp_InicioSesionAgrupadoEntidad");
        return json_encode($inicio_session_novo);

    }

    /******************************************************** GRAFICOS ***********************************************************************************/

    public function asist_xdia(Request $request)
    {
        if(!isset($request->fecha)){
            $hora1 = Carbon::now()->format('Y-m-d');
        }else{
            $hora1 = $request->fecha;
        }


        $result = DB::table('M_ASISTENCIA as MA')
                            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE, " - (", M_ENTIDAD.ABREV_ENTIDAD, ")" ) AS NOMBREU,
                                                M_ENTIDAD.ABREV_ENTIDAD,
                                                M_CENTRO_MAC.IDCENTRO_MAC,
                                                M_PERSONAL.NUM_DOC,
                                                M_ENTIDAD.IDENTIDAD,
                                                D_PERSONAL_CARGO.NOMBRE_CARGO
                                            FROM M_PERSONAL
                                            LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                            JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                            JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC
                                        ) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->where('PERS.IDCENTRO_MAC',$this->centro_mac_id()->idmac)
                            //->whereBetween(DB::raw('DATE(MA.FECHA)'), [$fecha ])
                            ->where('MA.FECHA', $hora1)
                            ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
                            ->orderBy('MA.FECHA', 'ASC')
                            ->select('PERS.ABREV_ENTIDAD', 'PERS.NOMBRE_CARGO', 'PERS.NOMBREU', 'MA.FECHA', 'MA.NUM_DOC', DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "1" THEN MA.HORA ELSE NULL END) AS hora1'), DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'), 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC')
                            ->get();

        return $result;
    }

}
