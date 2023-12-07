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

class PagesController extends Controller
{
    public function index()
    {
        return view('inicio');
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

                return $per_mac;
                
            }            

        }else{
            
            $personal = new Personal;
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
        // dd($request->all());

        $data = $request->except(['tvl_otro', 'gi_otro', 'dni', 'cv']);
        $missingValues = [];

        foreach ($data as $key => $value) {
            if ($value === NULL || $value === "undefined") {
                $missingValues[] = $key;
            }
        }
        // dd($missingValues);
        if (!empty($missingValues)) {
            // Enviar un mensaje con los valores faltantes.
            // return response()->json(['message' => 'Falta llenar los siguientes valos :  ' ."\n". implode(', ', $missingValues)], 400);

            $errorMessage = 'Falta llenar los siguientes campos : ' . "\n";

            foreach ($missingValues as $value) {
                $errorMessage .= "* " . str_replace(
                    [
                        'nombre', 'ape_pat', 'ape_mat', 'idtipo_doc', 'num_doc', 'identidad', 'direccion', 'sexo', 'fech_nacimiento', 'distrito',  'distrito2', 'telefono', 'celular', 'correo', 'grupo_sanguineo', 'e_nomape', 'e_telefono', 'e_celular', 'ecivil', 'df_n_hijos', 'dp_fecha_ingreso', 'dp_puesto_trabajo', 'dp_tiempo_ptrabajo', 'dp_centro_atencion', 'dp_codigo_identificacion', 'dlp_fecha_ingreso', 'dlp_puesto_trabajo', 'dlp_tiempo_puesto', 'dlp_area_trabajo', 'dlp_jefe_inmediato', 'dlp_cargo', 'dlp_telefono', 'tlv_id', 'gi_id',  'gi_carrera', 'gi_desde', 'gi_hasta', 
                    ],
                    [
                        'Ingrese su nombre', 'Ingrese su Apellido Paterno', 'Ingrese su Apellido Materno', 'Ingrese su Tipo de Documento', 'Ingrese su Número de Documento', 'Ingrese su Entidad', 'Ingrese su Dirección', 'Ingrese su sexo', 'Ingrese su Fecha de Nacimiento', 'Ingrese su Distrito Actual', 'Ingrese el lugar de nacimiento', 'Ingrese su númerp de teléfono', 'Ingrese su número de celular', 'Ingrese su Correo', 'Ingrese su grupo sanguineo', 'Ingrese Nombre y apellido de su contacto de emergencia', 'Ingrese el número de teléfono de su contacto de emergencia', 'Ingrese el número celular de su contacto de emergencia', 'Ingrese su estado civil', 'Ingrese el número de hijos si da el caso que no tiene colocar "0"', 'Ingrese fecha de Ingreso al centro MAC', 'Ingrese su puesto de trabajo', 'Ingrese el tiempo en el puesto de trabajo', 'Seleccione tipo de atención en el centro MAC', 'Ingrese su código de identificación que le remiten de su entidad si no lo tiene colocar (-)', 'Ingrese la fecha de ingreso a su entidad','Ingrese el puesto que tiene a su entidad', 'Ingrese el tiempo que tiene en su entidad ejem: x años y meses d dias ', 'Ingrese al área que pertenece en su entidad', 'Ingrese el nombre y apellido de su jefe inmediato', 'Ingrese el cargo al que corresponde su jefe inmediato', 'ingrese el número de telefono de su jefe inmediato', 'Seleccione el tipo de vinculación laboral que tiene con su entidad', 'Seleccione su grado de instruccón', 'Ingrese su carrera o profesión', 'Ingrese la fecha cuando inicio su carrera o profesión', 'Ingrese la fecha cuando culmino su carrera o profesión'
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

    public function entidad_cola(Request $request, $identidad)
    {
        // dd($identidad);
        $query = DB::connection('mysql2')->select('SELECT 
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

        return view('entidad_cola', compact('query'));
    }

    public function externo()
    {
        return view('externo');
    }
}
