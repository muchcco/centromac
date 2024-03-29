<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\Entidad;
use Illuminate\Support\Facades\DB;
use App\Imports\AsistenciaImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Personal;
use Carbon\Carbon;
use App\Exports\AsistenciaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AsistenciaGroupExport;
use App\Models\Configuracion;
use Carbon\CarbonPeriod;

class AsistenciaController extends Controller
{
    public function asistencia()
    {
        // $da = User::first()->locales;
        // dd($da);
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $entidad = DB::table('M_MAC_ENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                        ->where('M_MAC_ENTIDAD.IDCENTRO_MAC', $idmac)
                        ->get();

        return view('asistencia.asistencia', compact('entidad', 'idmac'));
    }

    public function tb_asistencia(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        // VERIFICAMOS LA HORA DE INGRESO PARA INDICAR SI ESTAN EN HORA O TARDANZA TOMAMOS REF DE LUN A VIER YA QUE ES EL MISMO HORARIO DEL SABADO
        $conf = Configuracion::where('IDCONFIGURACION', 2)->first();

        $entidad = Entidad::select( 'NOMBRE_ENTIDAD', 'ABREV_ENTIDAD', 'IDENTIDAD');

        $datos = Asistencia::from('M_ASISTENCIA as MA')
                            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
                            ->leftJoinSub($entidad, 'I', function($join) {
                                $join->on('MP.IDENTIDAD', '=', 'I.IDENTIDAD');
                            })
                            ->select(
                                'MA.FECHA as fecha_asistencia',
                                DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
                                DB::raw('MIN(MA.FECHA_BIOMETRICO) as fecha_biometrico'),
                                'MA.NUM_DOC as n_dni',
                                DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS nombreu'),
                                'ABREV_ENTIDAD',
                                'MC.NOMBRE_MAC'
                            )
                            ->where(function($que) use ($request) {
                                $fecha_I = date("Y-m-d");
                                if($request->fecha != '' ){
                                    $que->where('MA.FECHA', $request->fecha);
                                }else{
                                    $que->where('MA.FECHA', $fecha_I);
                                }
                            })
                            ->where(function($que) use ($request) {
                                if($request->entidad != '' ){
                                    $que->where('MP.IDENTIDAD', $request->entidad);
                                }
                            })
                            ->where('MA.IDCENTRO_MAC', $idmac)
                            ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ABREV_ENTIDAD', 'MC.NOMBRE_MAC')
                            ->get();
        // // dd($datos);
        return view('asistencia.tablas.tb_asistencia', compact('datos', 'conf'));
    }

    public function md_add_asistencia(Request $request)
    {
        $view = view('asistencia.modals.md_add_asistencia')->render();

        return response()->json(['html' => $view]); 
    }

    public function store_asistencia(Request $request)
    {
        $data = Asistencia::where('fecha', $request->fecha_reg)->count();

        if( $data > '0'){
            $eliminar = Asistencia::where('fecha', $request->fecha_reg)->delete();
        }

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $file = $request->file('txt_file');

        $fecha = $request->fecha_reg;  

        $upload = Excel::import(new AsistenciaImport, $file);

        $query = DB::select("UPDATE
                                        M_ASISTENCIA AS A
                                JOIN
                                ( SELECT ROW_NUMBER() OVER(PARTITION BY NUM_DOC ORDER BY FECHA, HORA) AS COR, NUM_DOC, IDASISTENCIA, HORA, FECHA
                                FROM M_ASISTENCIA
                                WHERE FECHA = '".$fecha."'
                                AND IDCENTRO_MAC = '".$idmac."'
                                ORDER BY HORA ASC
                                ) AS SUB
                                ON  A.IDASISTENCIA = SUB.IDASISTENCIA
                                SET
                                A.CORRELATIVO = SUB.COR + 0");


        return response()->json($query);
    }

    public function md_detalle(Request $request)
    {
        $fecha_ = $request->fecha_;
        $query = DB::select("SELECT FECHA,
                                NUM_DOC,
                                MAX(CASE WHEN CORRELATIVO = '1' THEN HORA ELSE NULL END) AS hora1,
                                MAX(CASE WHEN CORRELATIVO = '2' THEN HORA ELSE NULL END) AS hora2,
                                MAX(CASE WHEN CORRELATIVO = '3' THEN HORA ELSE NULL END) AS hora3,
                                MAX(CASE WHEN CORRELATIVO = '4' THEN HORA ELSE NULL END) AS hora4,
                                MAX(CASE WHEN CORRELATIVO = '5' THEN HORA ELSE NULL END) AS hora5
                            FROM
                            M_ASISTENCIA
                            WHERE FECHA = '$fecha_'
                            AND NUM_DOC = '$request->dni_'
                            GROUP BY NUM_DOC;");

        // dd($query);
        $view = view('asistencia.modals.md_detalle', compact('query', 'fecha_'))->render();

        return response()->json(['html' => $view]); 
    }

    public function det_us(Request $request, $id)
    {
        $idPersonal = $id;

        $personal = Personal::where('NUM_DOC', $idPersonal)->first();

        // dd($personal);

        return view ('asistencia.det_us', compact('idPersonal', 'personal'));
    }

    public function tb_det_us(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                    ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL' )
                                    ->where('NUM_DOC', $request->num_doc)
                                    ->first();
                         
        $query = DB::table('M_ASISTENCIA')
                        ->select('FECHA', 'NUM_DOC')
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora1', ['1'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora2', ['2'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora3', ['3'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora4', ['4'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora5', ['5'])
                        ->selectRaw('COUNT(NUM_DOC) AS N_NUM_DOC')
                        ->where('NUM_DOC', $request->num_doc)
                        ->where(function($que) use ($request) {
                            $fecha_mes_actual = Carbon::now()->format('m');
                            if($request->mes != '' ){
                                $que->where('MES', $request->mes);
                            }else{
                                $que->where('MES', $fecha_mes_actual);
                            }
                        })
                        ->where(function($que) use ($request) {
                            $fecha_año_actual = Carbon::now()->format('Y');
                            if($request->año != '' ){
                                $que->where('AÑO', $request->año);
                            }else{
                                $que->where('AÑO', $fecha_año_actual);
                            }
                        })
                        ->groupBy('NUM_DOC', 'FECHA')
                        ->orderBy('FECHA', 'ASC')
                        ->get();
        // dd($query);
        return view('asistencia.tablas.tb_det_us', compact('query', 'datos_persona'));
    }

    public function asistencia_excel(Request $request)
    {
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');

        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();

        // dd($nombreMES);

        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                    ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL' )
                                    ->where('NUM_DOC', $request->num_doc)
                                    ->first();

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $query = DB::table('M_ASISTENCIA')
                        ->select('FECHA', 'NUM_DOC')
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora1', ['1'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora2', ['2'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora3', ['3'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora4', ['4'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora5', ['5'])
                        ->selectRaw('COUNT(NUM_DOC) AS N_NUM_DOC')
                        ->where('NUM_DOC', $request->num_doc)
                        ->where(function($que) use ($request) {
                            $fecha_mes_actual = Carbon::now()->format('m');
                            if($request->mes != '' ){
                                $que->where('MES', $request->mes);
                            }else{
                                $que->where('MES', $fecha_mes_actual);
                            }
                        })
                        ->where(function($que) use ($request) {
                            $fecha_año_actual = Carbon::now()->format('Y');
                            if($request->año != '' ){
                                $que->where('AÑO', $request->año);
                            }else{
                                $que->where('AÑO', $fecha_año_actual);
                            }
                        })
                        ->groupBy('NUM_DOC', 'FECHA')
                        ->orderBy('FECHA', 'ASC')
                        ->get();

        foreach (auth()->user()->locales as $local){
            $MAC = $local->IDCENTRO_MAC;
        }

        // dd($datos_persona);

        $export = Excel::download(new AsistenciaExport($query, $datos_persona, $nombreMES, $hora_1, $hora_2, $hora_3, $hora_4), 'REPORTE DE ASISTENCIA CENTRO MAC - '.$MAC.' '.$datos_persona->ABREV_ENTIDAD.'_'.$nombreMES.'.xlsx');

        return $export;
    }

    public function asistencia_pdf(Request $request)
    {
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');

        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        // dd($hora_1->VALOR);

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $datos_persona = Personal::join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                                    ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL' )
                                    ->where('NUM_DOC', $request->num_doc)
                                    ->first();

        $query = DB::table('M_ASISTENCIA')
                        ->select('FECHA', 'NUM_DOC')
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora1', ['1'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora2', ['2'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora3', ['3'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora4', ['4'])
                        ->selectRaw('MAX(CASE WHEN CORRELATIVO = ? THEN HORA ELSE NULL END) AS hora5', ['5'])
                        ->selectRaw('COUNT(NUM_DOC) AS N_NUM_DOC')
                        ->where('NUM_DOC', $request->num_doc)
                        ->where(function($que) use ($request) {
                            $fecha_mes_actual = Carbon::now()->format('m');
                            if($request->mes != '' ){
                                $que->where('MES', $request->mes);
                            }else{
                                $que->where('MES', $fecha_mes_actual);
                            }
                        })
                        ->where(function($que) use ($request) {
                            $fecha_año_actual = Carbon::now()->format('Y');
                            if($request->año != '' ){
                                $que->where('AÑO', $request->año);
                            }else{
                                $que->where('AÑO', $fecha_año_actual);
                            }
                        })
                        ->groupBy('NUM_DOC', 'FECHA')
                        ->orderBy('FECHA', 'ASC')
                        ->get();


        $pdf = Pdf::loadView('asistencia.asistencia_pdf', compact('nombreMES', 'query', 'datos_persona', 'hora_1', 'hora_2', 'hora_3', 'hora_4'))->setPaper('a4', 'landscape');
        return $pdf->stream();

        // return view('asistencia.asistencia_pdf', compact('nombreMES', 'query', 'datos_persona'));
    }

    // >>>>>>>>>>>>>>>>>>>>>>>>    ASISTENCIA POR ENTIDAD
    /** ***************************************************************************************************************************************************** **/

    public function det_entidad(Request $request)
    {   
        return view('asistencia.det_entidad');
    }

    public function tb_det_entidad(Request $request)
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $data = DB::table('M_PERSONAL')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                        ->select('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC', DB::raw('COUNT(M_ENTIDAD.IDENTIDAD) AS COUNT_PER'))
                        ->groupBy('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC')
                        ->where('M_CENTRO_MAC.IDCENTRO_MAC', $idmac)
                        ->where('M_PERSONAL.FLAG', 1)
                        ->get();

        $data_spcm = DB::table('M_PERSONAL')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                        ->select('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC', DB::raw('COUNT(M_ENTIDAD.IDENTIDAD) AS COUNT_PER'))
                        ->groupBy('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC')
                        ->where('M_CENTRO_MAC.IDCENTRO_MAC', $idmac)
                        ->where('M_PERSONAL.FLAG', 1)
                        ->whereNot('M_ENTIDAD.IDENTIDAD', 17) //QUITAMOS DEL REGISTRO A PERSONAL DE PCM
                        ->get();

        return view('asistencia.tablas.tb_det_entidad', compact('data', 'data_spcm'));
    }

    public function exportgroup_excel(Request $request)
    {
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        // dd($request->all());
        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        // DEFINIMOS EL TIPO DE DESCA
        $tipo_desc = '1';
        $fecha_inicial = '';
        $fecha_fin = '';
        $identidad = $request->identidad;
        

        if($identidad == '17'){         

            // Obtener datos del encabezado
            $nom_ = Personal::from('M_PERSONAL as MP')
                    ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                    ->select('DPC.NOMBRE_CARGO', DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'), 'MP.NUM_DOC', 'MP.IDENTIDAD')
                    ->where('MP.IDENTIDAD', 17)
                    ->get();

            $array_numdoc = $nom_->pluck('NUM_DOC')->unique()->toArray();

            // Obtener datos del detalle

            // Obtén el primer y último día del mes
                $primerDia = Carbon::createFromDate($request->año, $request->mes, 1)->startOfDay();
                $ultimoDia = $primerDia->copy()->endOfMonth();

                // Obtén el rango de fechas entre el primer y último día del mes
                $fechas = CarbonPeriod::create($primerDia, $ultimoDia);

                // Convierte las fechas a un array
                $fechasArray = [];
                foreach ($fechas as $fecha) {
                    $fechasArray[] = $fecha->toDateString();
                }

                // Realiza la consulta para obtener datos agrupados por fecha y número de documento
                $querys = DB::table('M_ASISTENCIA as MA')
                    ->rightJoin('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                    ->rightJoin(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
                                    FROM M_PERSONAL
                                    LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                    JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                    JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                    ->select([
                        'PERS.ABREV_ENTIDAD',
                        'PERS.NOMBRE_CARGO',
                        'PERS.NOMBREU',
                        DB::raw('DATE(MA.FECHA) AS FECHA'), // Utilizamos DATE para obtener solo la fecha sin la hora
                        'MA.NUM_DOC',
                        DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "1" THEN MA.HORA ELSE NULL END) AS hora1'),
                        DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "2" THEN MA.HORA ELSE NULL END) AS hora2'),
                        DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "3" THEN MA.HORA ELSE NULL END) AS hora3'),
                        DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "4" THEN MA.HORA ELSE NULL END) AS hora4'),
                        DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "5" THEN MA.HORA ELSE NULL END) AS hora5'),
                        DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
                        'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
                        'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
                    ])
                    ->whereIn(DB::raw('DATE(MA.FECHA)'), $fechasArray) // Filtra por el rango de fechas
                    ->where('PERS.IDENTIDAD', $request->identidad)
                    ->where('PERS.IDCENTRO_MAC', $idmac)
                    ->whereMonth('MA.FECHA', $request->mes)
                    ->whereYear('MA.FECHA', $request->año)
                    ->groupBy('MA.NUM_DOC', DB::raw('DATE(MA.FECHA)'), 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.NOMBRE_CARGO')
                    ->orderBy('FECHA', 'ASC')
                    ->get();

                // Creamos un array asociativo donde la clave es la fecha y el valor es un array con los datos correspondientes
                $query = [];
                foreach ($fechasArray as $fecha) {
                    $query[$fecha] = $querys->filter(function ($row) use ($fecha) {
                        return $row->FECHA == $fecha;
                    })->toArray();
                }

                // Ahora, $query contiene todos los días del mes con datos o sin datos

                // dd($fechasArray);

            // Agrupar por NUM_DOC
            $datosAgrupados = [];

            foreach ($nom_ as $encabezado) {
                // dd($encabezado);
                $detalle = Asistencia::select([
                                        'M_ASISTENCIA.FECHA',
                                        'M_ASISTENCIA.NUM_DOC',
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "1" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora1'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "2" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora2'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "3" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora3'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "4" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora4'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "5" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora5'),
                                        DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                                    ])
                                    ->where('NUM_DOC', $encabezado->NUM_DOC)
                                    ->whereMonth('M_ASISTENCIA.FECHA', $request->mes) // Mes específico
                                    ->whereYear('M_ASISTENCIA.FECHA', $request->año)   // Año específico
                                    ->groupBy('M_ASISTENCIA.NUM_DOC', 'M_ASISTENCIA.FECHA')
                                    ->orderBy('M_ASISTENCIA.FECHA', 'asc')
                                    ->get();

                // dd($detalle);

                $datosAgrupados[] = ['encabezado' => $encabezado, 'detalle' => $detalle];
            }

            // dd($datosAgrupados);


            // Ahora, $datosAgrupados es un array asociativo donde cada elemento tiene la información del encabezado junto con su detalle correspondiente


        }else{
            $query = DB::table('M_ASISTENCIA as MA')
                            ->select('PERS.ABREV_ENTIDAD', 'PERS.NOMBREU', 'PERS.NOMBRE_CARGO', 'MA.FECHA', 'MA.NUM_DOC')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "1" THEN MA.HORA ELSE NULL END) AS hora1')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "2" THEN MA.HORA ELSE NULL END) AS hora2')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "3" THEN MA.HORA ELSE NULL END) AS hora3')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "4" THEN MA.HORA ELSE NULL END) AS hora4')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "5" THEN MA.HORA ELSE NULL END) AS hora5')
                            ->selectRaw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
                            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
                                FROM M_PERSONAL
                                LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.NOMBRE_CARGO')
                            ->where('PERS.IDENTIDAD', $request->identidad)
                            ->where('PERS.IDCENTRO_MAC', $idmac)
                            ->whereMonth('MA.FECHA', $request->mes)
                            ->whereYear('MA.FECHA', $request->año)
                            ->orderBy('FECHA', 'ASC')
                            ->get();
            
            $datosAgrupados = '';
            $fechasArray = '';        
        }

        // dd($query);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial,$fecha_fin , $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray,), 'REPORTE DE ASISTENCIA CENTRO MAC - '.$name_mac.' _'.$nombreMES.'.xlsx');

        return $export;
    
    }

    public function md_det_entidad_perso(Request $request)
    {
        $identidad = $request->identidad;

        $view = view('asistencia.modals.md_det_entidad_perso', compact('identidad'))->render();

        return response()->json(['html' => $view]); 
    }

    public function exportgroup_excel_pr(Request $request)
    {
        
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        // dd($request->all());
        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        // DEFINIMOS EL TIPO DE DESCA

        $fecha_ini_desc = strftime('%d de %B del %Y',strtotime($request->fecha_inicio));
        $fecha_fin_desc = strftime('%d de %B del %Y',strtotime($request->fecha_fin));

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();


        $tipo_desc = '2';
        $fecha_inicial = $fecha_ini_desc;
        $fecha_fin = $fecha_fin_desc;
        $identidad = $request->identidad;
        // dd($identidad);

        if($identidad == '17'){

            // Obtener datos del encabezado
            $nom_ = Personal::from('M_PERSONAL as MP')
                            ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                            ->select('DPC.NOMBRE_CARGO', DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'), 'MP.NUM_DOC', 'MP.IDENTIDAD')
                            ->where('MP.IDENTIDAD', 17)
                            ->get();

            $primerDia = $request->fecha_inicio;
            $ultimoDia = $request->fecha_fin;

            // Obtén el rango de fechas entre el primer y último día del mes
            $fechas = CarbonPeriod::create($primerDia, $ultimoDia);

            // Convierte las fechas a un array
            $fechasArray = [];
            foreach ($fechas as $fecha) {
                $fechasArray[] = $fecha->toDateString();
            }

            // Obtener datos del detalle
            $querys = DB::table('M_ASISTENCIA as MA')
                        ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
                                        FROM M_PERSONAL
                                        LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                        JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                        JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->select([
                            'PERS.ABREV_ENTIDAD',
                            'PERS.NOMBRE_CARGO',
                            'PERS.NOMBREU',
                            'MA.FECHA',
                            'MA.NUM_DOC',
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "1" THEN MA.HORA ELSE NULL END) AS hora1'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "2" THEN MA.HORA ELSE NULL END) AS hora2'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "3" THEN MA.HORA ELSE NULL END) AS hora3'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "4" THEN MA.HORA ELSE NULL END) AS hora4'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "5" THEN MA.HORA ELSE NULL END) AS hora5'),
                            DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
                            'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
                            'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
                        ])
                        ->where('PERS.IDENTIDAD', $request->identidad)
                        ->where('PERS.IDCENTRO_MAC', $idmac)
                        ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                        ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
                        ->orderBy('MA.FECHA', 'asc')
                        ->get();
                        

            $query = [];
            foreach ($fechasArray as $fecha) {
                $query[$fecha] = $querys->filter(function ($row) use ($fecha) {
                    return $row->FECHA == $fecha;
                })->toArray();
            }
            // Agrupar por NUM_DOC
            $datosAgrupados = [];

            foreach ($nom_ as $encabezado) {
                // Utilizando la relación definida en los modelos
                $detalle = $encabezado->asistencias()
                                    ->select([
                                        'M_ASISTENCIA.FECHA',
                                        'M_ASISTENCIA.NUM_DOC',
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "1" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora1'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "2" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora2'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "3" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora3'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "4" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora4'),
                                        DB::raw('MAX(CASE WHEN M_ASISTENCIA.CORRELATIVO = "5" THEN M_ASISTENCIA.HORA ELSE NULL END) AS hora5'),
                                        DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                                    ])
                                    ->whereBetween(DB::raw('DATE(M_ASISTENCIA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                                    ->groupBy('M_ASISTENCIA.NUM_DOC', 'M_ASISTENCIA.FECHA')
                                    ->orderBy('M_ASISTENCIA.FECHA', 'asc')
                                    ->get();

                // dd($detalle);

                $datosAgrupados[] = ['encabezado' => $encabezado, 'detalle' => $detalle];
            }

            // dd($datosAgrupados);
            // Ahora, $datosAgrupados es un array asociativo donde cada elemento tiene la información del encabezado junto con su detalle correspondiente


        }else{
            $query =  DB::table('M_ASISTENCIA as MA')
                        ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
                                        FROM M_PERSONAL
                                        LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                        JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                        JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->select([
                            'PERS.ABREV_ENTIDAD',
                            'PERS.NOMBRE_CARGO',
                            'PERS.NOMBREU',
                            'MA.FECHA',
                            'MA.NUM_DOC',
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "1" THEN MA.HORA ELSE NULL END) AS hora1'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "2" THEN MA.HORA ELSE NULL END) AS hora2'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "3" THEN MA.HORA ELSE NULL END) AS hora3'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "4" THEN MA.HORA ELSE NULL END) AS hora4'),
                            DB::raw('MAX(CASE WHEN MA.CORRELATIVO = "5" THEN MA.HORA ELSE NULL END) AS hora5'),
                            DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
                            'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
                            'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
                        ])
                        ->where('PERS.IDENTIDAD', $request->identidad)
                        ->where('PERS.IDCENTRO_MAC', $idmac)
                        ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                        ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
                        ->orderBy('MA.FECHA', 'asc')
                        ->get();

                $datosAgrupados = '';
                $fechasArray = '';

        }

        

        // dd($fecha_inicial);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial,$fecha_fin , $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray), 'REPORTE DE ASISTENCIA CENTRO MAC - '.$name_mac.' _'.$nombreMES.'.xlsx');

        return $export;
    }

    public function exportgroup_excel_general(Request $request)
    {
        
        // Establece la configuración regional a español
        setlocale(LC_TIME, 'es_ES', 'es_PE', 'es');
        // dd($request->all());
        // Crea una instancia de Carbon con el mes específico
        $fecha = Carbon::create(null, $request->mes, 1);

        // Obtiene el nombre completo del mes en español
        $nombreMES = $fecha->formatLocalized('%B');

        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        // DEFINIMOS EL TIPO DE DESCA

        $fecha_ini_desc = strftime('%d de %B del %Y',strtotime($request->fecha_inicio));
        $fecha_fin_desc = strftime('%d de %B del %Y',strtotime($request->fecha_fin));

        $hora_1 = Configuracion::where('PARAMETRO', 'HORA_1')->first();
        $hora_2 = Configuracion::where('PARAMETRO', 'HORA_2')->first();
        $hora_3 = Configuracion::where('PARAMETRO', 'HORA_3')->first();
        $hora_4 = Configuracion::where('PARAMETRO', 'HORA_4')->first();
        $hora_5 = Configuracion::where('PARAMETRO', 'HORA_5')->first();


        $tipo_desc = '2';
        $fecha_inicial = $fecha_ini_desc;
        $fecha_fin = $fecha_fin_desc;
        // $identidad = $request->identidad;
        // dd($identidad);
        $identidadArray = DB::table('M_MAC_ENTIDAD')->select('IDENTIDAD')->where('IDCENTRO_MAC', $idmac)->whereNot('IDENTIDAD', 17)->pluck('IDENTIDAD')->toArray();

        $identidadString = '(' . implode(', ', $identidadArray) . ')';

        // dd($identidadString);
        $identidad = $identidadString;

        $query =  DB::table('M_ASISTENCIA as MA')
                            ->select('PERS.ABREV_ENTIDAD', 'PERS.NOMBREU', 'PERS.NOMBRE_CARGO', 'MA.FECHA', 'MA.NUM_DOC')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "1" THEN MA.HORA ELSE NULL END) AS hora1')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "2" THEN MA.HORA ELSE NULL END) AS hora2')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "3" THEN MA.HORA ELSE NULL END) AS hora3')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "4" THEN MA.HORA ELSE NULL END) AS hora4')
                            ->selectRaw('MAX(CASE WHEN MA.CORRELATIVO = "5" THEN MA.HORA ELSE NULL END) AS hora5')
                            ->selectRaw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
                            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
                                FROM M_PERSONAL
                                LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
                                JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                            ->whereIn('PERS.IDENTIDAD', $identidadArray) // Filtra por las entidades del centro mac
                            ->where('PERS.IDCENTRO_MAC', $idmac)
                            ->whereMonth('MA.FECHA', $request->mes)
                            ->whereYear('MA.FECHA', $request->año)
                            ->groupBy('PERS.ABREV_ENTIDAD', 'PERS.NOMBREU', 'PERS.NOMBRE_CARGO', 'MA.FECHA', 'MA.NUM_DOC') // Incluye todas las columnas no agregadas en GROUP BY
                            ->orderBy('PERS.ABREV_ENTIDAD', 'ASC') // Ordenar por ABREV_ENTIDAD en orden ascendente
                            ->orderBy('MA.FECHA', 'ASC') // Luego, ordenar por FECHA en orden ascendente
                            ->get();
    

        $datosAgrupados = '';
        $fechasArray = '';        


        // dd($query);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial,$fecha_fin , $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray,), 'REPORTE DE ASISTENCIA CENTRO MAC - '.$name_mac.' _'.$nombreMES.'.xlsx');

        return $export;
    }

}
