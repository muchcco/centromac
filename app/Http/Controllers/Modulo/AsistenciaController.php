<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Asistencia;
use App\Models\Asistenciatest;
use App\Models\Entidad;
use App\Models\Mac;
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
use PDO;
use mysqli;

class AsistenciaController extends Controller
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

    public function store_agregar_asistencia(Request $request)
{
    // Obtener el IDMAC del usuario autenticado
    $userIdMac = auth()->user()->idcentro_mac;

    // Verificar si la solicitud es para buscar el nombre por DNI
    if ($request->has('DNI') && !$request->has('fecha')) {
        try {
            // Buscar el nombre completo en la tabla m_personal utilizando el NUM_DOC proporcionado y el IDMAC
            $personal = DB::table('m_personal')
                ->where('NUM_DOC', $request->input('DNI'))
                ->where('IDMAC', $userIdMac)
                ->first();

            if ($personal) {
                $nombreCompleto = $personal->NOMBRE . ' ' . $personal->APE_PAT . ' ' . $personal->APE_MAT;
                return response()->json(['success' => true, 'nombreCompleto' => $nombreCompleto]);
            }

            return response()->json(['success' => false, 'message' => 'DNI no encontrado o no pertenece a este centro MAC']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al buscar el nombre: ' . $e->getMessage()]);
        }
    }

    // Validar los datos de entrada para guardar la asistencia
    $request->validate([
        'DNI' => 'required|string|max:15',
        'fecha' => 'required|date',
        'id' => 'required|integer',
        'hora1' => 'nullable|date_format:H:i',
        'hora2' => 'nullable|date_format:H:i',
        'hora3' => 'nullable|date_format:H:i',
        'hora4' => 'nullable|date_format:H:i',
    ]);

    try {
        // Buscar el nombre completo en la tabla m_personal utilizando el NUM_DOC proporcionado y el IDMAC
        $personal = DB::table('m_personal')
            ->where('NUM_DOC', $request->input('DNI'))
            ->where('IDMAC', $userIdMac)
            ->first();

        if (!$personal) {
            return response()->json([
                'success' => false,
                'message' => 'El DNI proporcionado no corresponde a ningún personal registrado en este centro MAC.'
            ]);
        }

        // Concatenar el nombre completo del personal
        $nombreCompleto = $personal->NOMBRE . ' ' . $personal->APE_PAT . ' ' . $personal->APE_MAT;

        // Obtener el último correlativo y sumarle 1 para obtener el siguiente
        $lastCorrelativo = DB::table('asistenciatest')->max('correlativo');
        $nextCorrelativo = $lastCorrelativo ? $lastCorrelativo + 1 : 1;

        // Crear el nuevo registro en la tabla asistenciatest para cada hora proporcionada
        $horas = [$request->input('hora1'), $request->input('hora2'), $request->input('hora3'), $request->input('hora4')];

        foreach ($horas as $hora) {
            if (!empty($hora)) {
                // Combinar la fecha y la hora en el formato datetime
                $punchTime = $request->input('fecha') . ' ' . $hora;

                // Verificar si ya existe un registro con el mismo idMAC, DNI y marcacion
                $existingRecord = Asistenciatest::where('idMAC', $request->input('id'))
                    ->where('DNI', $request->input('DNI'))
                    ->where('marcacion', $punchTime)
                    ->first();

                if ($existingRecord) {
                    continue; // Si el registro ya existe, omitirlo
                }

                // Crear un nuevo registro en la tabla asistenciatest
                $asistenciatest = new Asistenciatest();
                $asistenciatest->correlativo = $nextCorrelativo;
                $asistenciatest->idMAC = $request->input('id');
                $asistenciatest->DNI = $request->input('DNI');
                $asistenciatest->marcacion = $punchTime;
                $asistenciatest->save();

                // Incrementar el correlativo para cada registro
                $nextCorrelativo++;
            }
        }

        // Ejecutar el procedimiento almacenado después de guardar los registros
        DB::statement('CALL SP_CARGA_ASISTENCIA()');

        return response()->json([
            'success' => true,
            'message' => 'Registro(s) guardado(s) exitosamente para ' . $nombreCompleto
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar el registro: ' . $e->getMessage()
        ]);
    }
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

        $entidad = Entidad::select('NOMBRE_ENTIDAD', 'ABREV_ENTIDAD', 'IDENTIDAD');

        $datos = Asistencia::from('M_ASISTENCIA as MA')
            ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
            ->leftJoinSub($entidad, 'I', function ($join) {
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
            ->where(function ($que) use ($request) {
                $fecha_I = date("Y-m-d");
                if ($request->fecha != '') {
                    $que->where('MA.FECHA', $request->fecha);
                } else {
                    $que->where('MA.FECHA', $fecha_I);
                }
            })
            ->where(function ($que) use ($request) {
                if ($request->entidad != '') {
                    $que->where('MP.IDENTIDAD', $request->entidad);
                }
            })
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('MA.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            // ->where('MA.IDCENTRO_MAC', $idmac)
            ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ABREV_ENTIDAD', 'MC.NOMBRE_MAC')
            ->get();
        // // dd($datos);
        //                     ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
        //                     ->join('M_CENTRO_MAC as MC', 'MC.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
        //                     ->leftJoinSub($entidad, 'I', function($join) {
        //                         $join->on('MP.IDENTIDAD', '=', 'I.IDENTIDAD');
        //                     })
        //                     ->select(
        //                         'MA.FECHA as fecha_asistencia',
        //                         DB::raw('MAX(MA.IDASISTENCIA) as idAsistencia'),
        //                         DB::raw('MIN(MA.FECHA_BIOMETRICO) as fecha_biometrico'),
        //                         'MA.NUM_DOC as n_dni',
        //                         DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS nombreu'),
        //                         'ABREV_ENTIDAD',
        //                         'MC.NOMBRE_MAC'
        //                     )
        //                     ->where(function($que) use ($request) {
        //                         $fecha_I = date("Y-m-d");
        //                         if($request->fecha != '' ){
        //                             $que->where('MA.FECHA', $request->fecha);
        //                         }else{
        //                             $que->where('MA.FECHA', $fecha_I);
        //                         }
        //                     })
        //                     ->where(function($que) use ($request) {
        //                         if($request->entidad != '' ){
        //                             $que->where('MP.IDENTIDAD', $request->entidad);
        //                         }
        //                     })
        //                     ->where(function($query) {
        //                         if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
        //                             $query->where('MA.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
        //                         }
        //                     })
        //                     // ->where('MA.IDCENTRO_MAC', $idmac)
        //                     ->groupBy('MA.FECHA', 'MP.IDPERSONAL', 'MA.NUM_DOC', 'ABREV_ENTIDAD', 'MC.NOMBRE_MAC')
        //                     ->toSql();
        // dd($datos);
        return view('asistencia.tablas.tb_asistencia', compact('datos', 'conf'));
    }

    public function md_add_asistencia(Request $request)
    {
        $view = view('asistencia.modals.md_add_asistencia')->render();

        return response()->json(['html' => $view]);
    }
    public function md_add_asistencia_callao(Request $request)
    {
        $view = view('asistencia.modals.md_add_asistencia_callao')->render();

        return response()->json(['html' => $view]);
    }
    public function md_agregar_asistencia(Request $request)
    {
        $view = view('asistencia.modals.md_agregar_asistencia')->render();

        return response()->json(['html' => $view]);
    }

    public function store_asistencia(Request $request)
    {
         // Obtener el archivo de la solicitud
        $file = $request->file('txt_file');
        
        // Convertir el archivo a un array de líneas
        $lines = file($file->getRealPath());

        // Inicializar arrays vacíos
        $num_docs = [];
        $fechas_biometrico = [];
        $horas = [];
        $anios = [];
        $meses = [];

        // Recorrer las líneas y procesar datos
        foreach ($lines as $line) {
            // Usar tabulación como separador
            $data = explode("\t", $line);

            // Verificar que se tengan al menos 7 columnas (ya que la columna 6 contiene la fecha y hora)
            if (count($data) >= 7) {
                // Extraer los valores que necesitamos
                $num_docs[] = trim($data[2]); // DNI o NUM_DOC
                $fechas_biometrico[] = trim($data[6]); // FECHA_BIOMETRICO

                // Separar la fecha y la hora
                $fechaHora = explode(' ', trim($data[6]));
                if (count($fechaHora) == 2) {
                    $fecha = $fechaHora[0]; // Fecha
                    $hora = $fechaHora[1]; // Hora
                    $horas[] = $hora;

                    // Extraer año y mes
                    $fechaParts = explode('/', $fecha);
                    if (count($fechaParts) == 3) {
                        $anios[] = $fechaParts[0]; // Año
                        $meses[] = $fechaParts[1]; // Mes
                    }
                }
            } else {
                echo "<pre>Línea con formato incorrecto: " . print_r($line, true) . "</pre>";
            }
        }

        // Obtener el ID del Centro MAC usando el método
        $idCentroMac = $this->centro_mac()->idmac;

        // Ahora insertamos los datos en la base de datos
        foreach ($num_docs as $index => $num_doc) {
            // Verificar si ya existe un registro con los mismos valores de NUM_DOC, IDCENTRO_MAC y FECHA_BIOMETRICO
            $existingRecord = Asistencia::where('NUM_DOC', $num_doc)
                ->where('IDCENTRO_MAC', $idCentroMac)
                ->where('FECHA_BIOMETRICO', $fechas_biometrico[$index])
                ->first();

            // Si no existe el registro, crear uno nuevo
            if (!$existingRecord) {
                Asistencia::create([
                    'IDTIPO_ASISTENCIA' => 1, // Puedes ajustar este valor según tus necesidades
                    'NUM_DOC' => $num_doc,
                    'IDCENTRO_MAC' => $idCentroMac,
                    'MES' => $meses[$index],
                    'AÑO' => $anios[$index],
                    'FECHA' => $fechas_biometrico[$index], // Fecha completa (biométrico)
                    'HORA' => $horas[$index],
                    'FECHA_BIOMETRICO' => $fechas_biometrico[$index],
                    'NUM_BIOMETRICO' => '', // Si no hay valor, se puede dejar vacío
                    'CORRELATIVO' => $index + 1, // Puedes ajustar el correlativo según tu lógica
                    'CORRELATIVO_DIA' => '' // Puedes agregar el valor según lo que necesites
                ]);
            } else {
                // Si ya existe el registro, lo omitimos y continuamos con el siguiente
                continue;
            }
        }

        return response()->json(['success' => true, 'message' => 'Asistencias cargadas exitosamente.']);

    }

    public function store_asistencia_callao(Request $request)
    {
        if ($request->hasFile('txt_file') && $request->file('txt_file')->isValid()) {
            $fileTmpPath = $request->file('txt_file')->getPathName();
            $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$fileTmpPath;";
    
            try {
                $accessDb = new PDO($dsn);
                $mysqli = new mysqli('localhost', 'root', '', 'asistencia_callao');
                if ($mysqli->connect_error) {
                    return response()->json(['success' => false, 'message' => 'Error de conexión a MySQL: ' . $mysqli->connect_error], 500);
                }
                $mysqli->set_charset('utf8mb4');
    
                $tablesQuery = $accessDb->query("SELECT Name FROM MSysObjects WHERE Type=1 AND Name NOT LIKE 'MSys%'");
                $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
    
                foreach ($tables as $table) {
                    if ($table === 'Switchboard Items') {
                        continue;
                    }
    
                    try {
                        $dataQuery = $accessDb->query("SELECT * FROM [$table]");
                        $rows = $dataQuery->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        continue;
                    }
    
                    if (!empty($rows)) {
                        $columns = array_keys($rows[0]);
                        $tableExistsQuery = $mysqli->query("SHOW TABLES LIKE '$table'");
    
                        if ($tableExistsQuery->num_rows > 0) {
                            $mysqli->query("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            foreach ($columns as $column) {
                                $mysqli->query("ALTER TABLE `$table` MODIFY `$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            }
                        } else {
                            $columnsSQL = [];
                            foreach ($columns as $column) {
                                $columnsSQL[] = "`$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                            }
                            $createTableSQL = "CREATE TABLE `$table` (" . implode(', ', $columnsSQL) . ")";
                            if (!$mysqli->query($createTableSQL)) {
                                continue;
                            }
                        }
    
                        $mysqli->query("DELETE FROM `$table`");
    
                        foreach ($rows as &$row) {
                            // Verificar y convertir cada valor a UTF-8 si es necesario
                            array_walk_recursive($row, function (&$value) {
                                if (!mb_check_encoding($value, 'UTF-8')) {
                                    $value = utf8_encode($value);
                                }
                            });
    
                            $values = array_map(function ($value) use ($mysqli) {
                                if (is_null($value)) {
                                    return 'NULL';
                                }
                                $escapedValue = $mysqli->real_escape_string($value);
                                return "'" . $escapedValue . "'";
                            }, $row);
    
                            $insertSQL = "INSERT INTO `$table` (" . implode(',', array_keys($row)) . ") VALUES (" . implode(',', $values) . ")";
                            $mysqli->query($insertSQL);
                        }
                    }
                }
    
                // Verificar y convertir la respuesta JSON a UTF-8
                $responseData = ['success' => true, 'message' => 'Asistencias cargadas exitosamente.'];
                array_walk_recursive($responseData, function (&$item) {
                    if (!mb_check_encoding($item, 'UTF-8')) {
                        $item = utf8_encode($item);
                    }
                });
    
                return response()->json($responseData);
    
            } catch (PDOException $e) {
                return response()->json(['success' => false, 'message' => 'Error al procesar el archivo Access: ' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No se envió ningún archivo o hubo un error en la carga.'], 400);
        }
    }
    


    public function md_detalle(Request $request)
    {
        $fecha_ = $request->fecha_;
        $query = DB::select("SELECT FECHA,
                                NUM_DOC,
                                GROUP_CONCAT(DATE_FORMAT(HORA, '%H:%i') ORDER BY HORA) AS HORAS
                            FROM
                            M_ASISTENCIA
                            WHERE FECHA = '$fecha_'
                            AND NUM_DOC = '$request->dni_'
                            GROUP BY NUM_DOC;");

        // dd($query);

        foreach ($query as $q) {
            $horas = explode(',', $q->HORAS);
            $num_horas = count($horas);
            if ($num_horas == 1) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = null;
            } elseif ($num_horas == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[1];
            } elseif ($num_horas == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[2];
            } elseif ($num_horas >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
        }

        $view = view('asistencia.modals.md_detalle', compact('query', 'fecha_'))->render();

        return response()->json(['html' => $view]);
    }

    public function det_us(Request $request, $id)
    {
        $idPersonal = $id;

        $personal = Personal::where('NUM_DOC', $idPersonal)->first();

        // dd($personal);

        return view('asistencia.det_us', compact('idPersonal', 'personal'));
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
            ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL')
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
            ->where(function ($que) use ($request) {
                $fecha_mes_actual = Carbon::now()->format('m');
                if ($request->mes != '') {
                    $que->where('MES', $request->mes);
                } else {
                    $que->where('MES', $fecha_mes_actual);
                }
            })
            ->where(function ($que) use ($request) {
                $fecha_año_actual = Carbon::now()->format('Y');
                if ($request->año != '') {
                    $que->where('AÑO', $request->año);
                } else {
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
            ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL')
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
            ->where(function ($que) use ($request) {
                $fecha_mes_actual = Carbon::now()->format('m');
                if ($request->mes != '') {
                    $que->where('MES', $request->mes);
                } else {
                    $que->where('MES', $fecha_mes_actual);
                }
            })
            ->where(function ($que) use ($request) {
                $fecha_año_actual = Carbon::now()->format('Y');
                if ($request->año != '') {
                    $que->where('AÑO', $request->año);
                } else {
                    $que->where('AÑO', $fecha_año_actual);
                }
            })
            ->groupBy('NUM_DOC', 'FECHA')
            ->orderBy('FECHA', 'ASC')
            ->get();

        foreach (auth()->user()->locales as $local) {
            $MAC = $local->IDCENTRO_MAC;
        }

        // dd($datos_persona);

        $export = Excel::download(new AsistenciaExport($query, $datos_persona, $nombreMES, $hora_1, $hora_2, $hora_3, $hora_4), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $MAC . ' ' . $datos_persona->ABREV_ENTIDAD . '_' . $nombreMES . '.xlsx');

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
            ->select('M_PERSONAL.NUM_DOC', DB::raw("CONCAT(M_PERSONAL.APE_PAT,' ',M_PERSONAL.APE_MAT,', ',M_PERSONAL.NOMBRE) as NOMBREU"), 'M_ENTIDAD.ABREV_ENTIDAD as NOMBRE_ENTIDAD', 'M_PERSONAL.SEXO', 'M_PERSONAL.TELEFONO', 'M_PERSONAL.FLAG', 'M_PERSONAL.IDPERSONAL')
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
            ->where(function ($que) use ($request) {
                $fecha_mes_actual = Carbon::now()->format('m');
                if ($request->mes != '') {
                    $que->where('MES', $request->mes);
                } else {
                    $que->where('MES', $fecha_mes_actual);
                }
            })
            ->where(function ($que) use ($request) {
                $fecha_año_actual = Carbon::now()->format('Y');
                if ($request->año != '') {
                    $que->where('AÑO', $request->año);
                } else {
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

        $mac = DB::table('M_CENTRO_MAC')
                    ->where(function($query) {
                        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                            $query->where('IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                        }
                    })
                    ->orderBy('NOMBRE_MAC', 'ASC')
                    ->get();


        return view('asistencia.det_entidad', compact('mac'));
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

        $mes = $request->input('mes', Carbon::now()->month);
        $año = $request->input('año', Carbon::now()->year);

        $data = DB::table('M_PERSONAL')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                        ->select('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC', 'M_ENTIDAD.ABREV_ENTIDAD', DB::raw('COUNT(M_ENTIDAD.IDENTIDAD) AS COUNT_PER'))
                        ->groupBy('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC')
                        ->where(function($query) use ($request) {
                            $mac = $request->mac;

                            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                            }else{
                                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $mac);
                            }
                        })
                        ->where('M_PERSONAL.FLAG', 1)
                        ->orderBy('M_ENTIDAD.ABREV_ENTIDAD', 'ASC')
                        ->get();

        $data_spcm = DB::table('M_PERSONAL')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_PERSONAL.IDENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                        ->select('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC', DB::raw('COUNT(M_ENTIDAD.IDENTIDAD) AS COUNT_PER'))
                        ->groupBy('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD', 'M_CENTRO_MAC.IDCENTRO_MAC')
                        // ->where('M_CENTRO_MAC.IDCENTRO_MAC', $idmac)
                        ->where(function($query) use ($request) {
                            $mac = $request->mac;
                            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                            }else{
                                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $mac);
                            }
                        })
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

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        }else{
            $idmac = $request->mac;
        }

        $dec_mac = Mac::where('IDCENTRO_MAC', $idmac)->first();
        $name_mac = $dec_mac->NOMBRE_MAC;

        // DEFINIMOS EL TIPO DE DESCA
        $tipo_desc = '1';
        $fecha_inicial = '';
        $fecha_fin = '';
        $identidad = $request->identidad;


        if ($identidad == '17') {

            // Obtener datos del encabezado
            $nom_ = Personal::from('M_PERSONAL as MP')
                ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                ->select('DPC.NOMBRE_CARGO', DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'), 'MP.NUM_DOC', 'MP.IDENTIDAD')
                ->where('MP.IDENTIDAD', 17)
                ->where('MP.IDMAC', $this->centro_mac()->idmac)
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
                    DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
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

                foreach ($querys as $q) {
                    $horas = explode(',', $q->HORAS);
                    $num_horas = count($horas);
                    if ($num_horas == 1) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = null;
                    } elseif ($num_horas == 2) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[1];
                    } elseif ($num_horas == 3) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[2];
                    } elseif ($num_horas >= 4) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = $horas[2];
                        $q->HORA_4 = $horas[3];
                    }
                }

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
                    DB::raw('GROUP_CONCAT(DATE_FORMAT(HORA, "%H:%i:%s") ORDER BY HORA) AS HORAS'),
                    DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                ])
                    ->where('IDCENTRO_MAC', $this->centro_mac()->idmac)
                    ->whereMonth('M_ASISTENCIA.FECHA', $request->mes) // Mes específico
                    ->whereYear('M_ASISTENCIA.FECHA', $request->año)   // Año específico
                    ->groupBy('M_ASISTENCIA.NUM_DOC', 'M_ASISTENCIA.FECHA')
                    ->orderBy('M_ASISTENCIA.FECHA', 'asc')
                    ->get();


                    foreach ($detalle as $d) {
                        $horas = explode(',', $d->HORAS);
                        $num_horas = count($horas);
                        if ($num_horas == 1) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = null;
                            $d->HORA_3 = null;
                            $d->HORA_4 = null;
                        } elseif ($num_horas == 2) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = null;
                            $d->HORA_3 = null;
                            $d->HORA_4 = $horas[1];
                        } elseif ($num_horas == 3) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = $horas[1];
                            $d->HORA_3 = null;
                            $d->HORA_4 = $horas[2];
                        } elseif ($num_horas >= 4) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = $horas[1];
                            $d->HORA_3 = $horas[2];
                            $d->HORA_4 = $horas[3];
                        }
                    }

                // dd($detalle);

                $datosAgrupados[] = ['encabezado' => $encabezado, 'detalle' => $detalle];
            }

            // dd($datosAgrupados);


            // Ahora, $datosAgrupados es un array asociativo donde cada elemento tiene la información del encabezado junto con su detalle correspondiente


        } else {

            $query = DB::table('M_ASISTENCIA as MA')
                        ->select(
                            'PERS.ABREV_ENTIDAD',
                            'PERS.NOMBREU',
                            'PERS.NOMBRE_CARGO',
                            'MA.FECHA',
                            'MA.NUM_DOC',
                            DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
                            DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
                        )
                        ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->joinSub(
                            DB::table('M_PERSONAL')
                                ->select(
                                    DB::raw('CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU'),
                                    'M_ENTIDAD.ABREV_ENTIDAD',
                                    'M_CENTRO_MAC.IDCENTRO_MAC',
                                    'M_PERSONAL.NUM_DOC',
                                    'M_ENTIDAD.IDENTIDAD',
                                    'D_PERSONAL_CARGO.NOMBRE_CARGO',
                                    'MPM.FECHAINICIO',
                                    'MPM.FECHAFIN'
                                )
                                ->leftJoin('D_PERSONAL_CARGO', 'D_PERSONAL_CARGO.IDCARGO_PERSONAL', '=', 'M_PERSONAL.IDCARGO_PERSONAL')
                                ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_PERSONAL.IDMAC')
                                ->join('M_PERSONAL_MODULO AS MPM', 'MPM.NUM_DOC', '=', 'M_PERSONAL.NUM_DOC')
                                ->join('M_MODULO', function ($join) {
                                    $join->on('M_MODULO.IDMODULO', '=', 'MPM.IDMODULO')
                                        ->on('M_MODULO.IDCENTRO_MAC', '=', 'MPM.IDCENTRO_MAC');
                                })
                                ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MODULO.IDENTIDAD')
                                ->where('M_MODULO.ESTADO', 1),
                            'PERS',
                            'PERS.NUM_DOC',
                            '=',
                            'MA.NUM_DOC'
                        )
                        ->where('PERS.IDENTIDAD', $request->identidad)
                        ->where('MA.IDCENTRO_MAC', $idmac)
                        ->whereMonth('MA.FECHA', $request->mes)
                        ->whereYear('MA.FECHA', $request->año)
                        ->whereBetween('MA.FECHA', [DB::raw('PERS.FECHAINICIO'), DB::raw('PERS.FECHAFIN')])
                        ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.NOMBRE_CARGO')
                        ->orderBy('MA.FECHA', 'ASC')
                        ->get();

            // $query = DB::table('M_ASISTENCIA as MA')
            //     ->select('PERS.ABREV_ENTIDAD', 'PERS.NOMBREU', 'PERS.NOMBRE_CARGO', 'MA.FECHA', 'MA.NUM_DOC', DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'))
            //     ->selectRaw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
            //     ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
            //                     FROM M_PERSONAL
            //                     LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
            //                     JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
            //                     JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.NOMBRE_CARGO')
            //     ->where('PERS.IDENTIDAD', $request->identidad)
            //     ->where('MA.IDCENTRO_MAC', $idmac)
            //     ->whereMonth('MA.FECHA', $request->mes)
            //     ->whereYear('MA.FECHA', $request->año)
            //     ->orderBy('FECHA', 'ASC')
            //     ->get();
                // dd($query);

                foreach ($query as $q) {
                    $horas = explode(',', $q->HORAS);
                    $num_horas = count($horas);
                    if ($num_horas == 1) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = null;
                    } elseif ($num_horas == 2) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[1];
                    } elseif ($num_horas == 3) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[2];
                    } elseif ($num_horas >= 4) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = $horas[2];
                        $q->HORA_4 = $horas[3];
                    }
                }
        

            $datosAgrupados = '';
            $fechasArray = '';
        }

        // dd($query);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin, $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray,), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $name_mac . ' _' . $nombreMES . '.xlsx');

        return $export;
    }

    public function md_det_entidad_perso(Request $request)
    {
        $identidad = $request->identidad;

        $mac = $request->mac;

        $view = view('asistencia.modals.md_det_entidad_perso', compact('identidad', 'mac'))->render();

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

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        }else{
            $idmac = $request->mac;
        }

        $dec_mac = Mac::where('IDCENTRO_MAC', $idmac)->first();
        $name_mac = $dec_mac->NOMBRE_MAC;

        // DEFINIMOS EL TIPO DE DESCA

        $fecha_ini_desc = strftime('%d de %B del %Y', strtotime($request->fecha_inicio));
        $fecha_fin_desc = strftime('%d de %B del %Y', strtotime($request->fecha_fin));

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

        if ($identidad == '17') {

            // Obtener datos del encabezado
            $nom_ = Personal::from('M_PERSONAL as MP')
                ->leftJoin('D_PERSONAL_CARGO as DPC', 'DPC.IDCARGO_PERSONAL', '=', 'MP.IDCARGO_PERSONAL')
                ->select('DPC.NOMBRE_CARGO', DB::raw('CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU'), 'MP.NUM_DOC', 'MP.IDENTIDAD')
                ->where('MP.IDENTIDAD', 17)
                ->where('MP.IDMAC', $this->centro_mac()->idmac)
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
                    DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
                    DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
                    'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
                    'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
                ])
                ->where('PERS.IDENTIDAD', $request->identidad)
                ->where('MA.IDCENTRO_MAC', $idmac)
                ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
                ->orderBy('MA.FECHA', 'asc')
                ->get();

                foreach ($querys as $q) {
                    $horas = explode(',', $q->HORAS);
                    $num_horas = count($horas);
                    if ($num_horas == 1) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = null;
                    } elseif ($num_horas == 2) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[1];
                    } elseif ($num_horas == 3) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[2];
                    } elseif ($num_horas >= 4) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = $horas[2];
                        $q->HORA_4 = $horas[3];
                    }
                }


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
                        DB::raw('GROUP_CONCAT(DATE_FORMAT(HORA, "%H:%i:%s") ORDER BY HORA) AS HORAS'),
                        DB::raw('COUNT(M_ASISTENCIA.NUM_DOC) AS N_NUM_DOC'),
                    ])
                    ->whereBetween(DB::raw('DATE(M_ASISTENCIA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                    ->groupBy('M_ASISTENCIA.NUM_DOC', 'M_ASISTENCIA.FECHA')
                    ->orderBy('M_ASISTENCIA.FECHA', 'asc')
                    ->get();

                    foreach ($detalle as $d) {
                        $horas = explode(',', $d->HORAS);
                        $num_horas = count($horas);
                        if ($num_horas == 1) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = null;
                            $d->HORA_3 = null;
                            $d->HORA_4 = null;
                        } elseif ($num_horas == 2) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = null;
                            $d->HORA_3 = null;
                            $d->HORA_4 = $horas[1];
                        } elseif ($num_horas == 3) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = $horas[1];
                            $d->HORA_3 = null;
                            $d->HORA_4 = $horas[2];
                        } elseif ($num_horas >= 4) {
                            $d->HORA_1 = $horas[0];
                            $d->HORA_2 = $horas[1];
                            $d->HORA_3 = $horas[2];
                            $d->HORA_4 = $horas[3];
                        }
                    }

                // dd($detalle);

                $datosAgrupados[] = ['encabezado' => $encabezado, 'detalle' => $detalle];
            }

            // dd($datosAgrupados);
            // Ahora, $datosAgrupados es un array asociativo donde cada elemento tiene la información del encabezado junto con su detalle correspondiente


        } else {

            $query =  DB::table('M_ASISTENCIA as MA')
                        ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, 
                                                M_ENTIDAD.ABREV_ENTIDAD, 
                                                M_CENTRO_MAC.IDCENTRO_MAC, 
                                                M_PERSONAL.NUM_DOC, 
                                                M_ENTIDAD.IDENTIDAD, 
                                                D_PERSONAL_CARGO.NOMBRE_CARGO, 
                                                MPM.FECHAINICIO, 
                                                MPM.FECHAFIN
                                        FROM M_PERSONAL
                                        LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
                                        JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC
                                        JOIN M_PERSONAL_MODULO AS MPM ON MPM.NUM_DOC = M_PERSONAL.NUM_DOC
                                        JOIN M_MODULO ON M_MODULO.IDMODULO = MPM.IDMODULO AND M_MODULO.IDCENTRO_MAC = MPM.IDCENTRO_MAC
                                        JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_MODULO.IDENTIDAD
                                        
                                        ) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->select([
                            'PERS.ABREV_ENTIDAD',
                            'PERS.NOMBRE_CARGO',
                            'PERS.NOMBREU',
                            'MA.FECHA',
                            'MA.NUM_DOC',
                            DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
                            DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
                            'PERS.IDENTIDAD',
                            'PERS.IDCENTRO_MAC'
                        ])
                        ->where('PERS.IDENTIDAD', $request->identidad)
                        ->where('MA.IDCENTRO_MAC', $idmac)
                        ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
                        ->whereRaw('MA.FECHA BETWEEN PERS.FECHAINICIO AND PERS.FECHAFIN') // Validación del rango de fechas de asignación
                        ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
                        ->orderBy('MA.FECHA', 'asc')
                        ->get();


            // $query =  DB::table('M_ASISTENCIA as MA')
            //     ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->join(DB::raw('(SELECT CONCAT(M_PERSONAL.APE_PAT, " ", M_PERSONAL.APE_MAT, ", ", M_PERSONAL.NOMBRE) AS NOMBREU, M_ENTIDAD.ABREV_ENTIDAD, M_CENTRO_MAC.IDCENTRO_MAC, M_PERSONAL.NUM_DOC, M_ENTIDAD.IDENTIDAD, D_PERSONAL_CARGO.NOMBRE_CARGO
            //                             FROM M_PERSONAL
            //                             LEFT JOIN D_PERSONAL_CARGO ON D_PERSONAL_CARGO.IDCARGO_PERSONAL = M_PERSONAL.IDCARGO_PERSONAL
            //                             JOIN M_ENTIDAD ON M_ENTIDAD.IDENTIDAD = M_PERSONAL.IDENTIDAD
            //                             JOIN M_CENTRO_MAC ON M_CENTRO_MAC.IDCENTRO_MAC = M_PERSONAL.IDMAC) as PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
            //     ->select([
            //         'PERS.ABREV_ENTIDAD',
            //         'PERS.NOMBRE_CARGO',
            //         'PERS.NOMBREU',
            //         'MA.FECHA',
            //         'MA.NUM_DOC',
            //         DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
            //         DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC'),
            //         'PERS.IDENTIDAD', // Agregado para cumplir con GROUP BY
            //         'PERS.IDCENTRO_MAC' // Agregado para cumplir con GROUP BY
            //     ])
            //     ->where('PERS.IDENTIDAD', $request->identidad)
            //     ->where('MA.IDCENTRO_MAC', $idmac)
            //     ->whereBetween(DB::raw('DATE(MA.FECHA)'), [$request->fecha_inicio, $request->fecha_fin])
            //     ->groupBy('MA.NUM_DOC', 'MA.FECHA', 'PERS.NOMBREU', 'PERS.ABREV_ENTIDAD', 'PERS.IDENTIDAD', 'PERS.IDCENTRO_MAC', 'PERS.NOMBRE_CARGO')
            //     ->orderBy('MA.FECHA', 'asc')
            //     ->get();

                foreach ($query as $q) {
                    $horas = explode(',', $q->HORAS);
                    $num_horas = count($horas);
                    if ($num_horas == 1) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = null;
                    } elseif ($num_horas == 2) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = null;
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[1];
                    } elseif ($num_horas == 3) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = null;
                        $q->HORA_4 = $horas[2];
                    } elseif ($num_horas >= 4) {
                        $q->HORA_1 = $horas[0];
                        $q->HORA_2 = $horas[1];
                        $q->HORA_3 = $horas[2];
                        $q->HORA_4 = $horas[3];
                    }
                }

            $datosAgrupados = '';
            $fechasArray = '';
        }



        // dd($fecha_inicial);
        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin, $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $name_mac . ' _' . $nombreMES . '.xlsx');

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

        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $idmac = $this->centro_mac()->idmac;
        }else{
            $idmac = $request->mac;
        }
        
        $dec_mac = Mac::where('IDCENTRO_MAC', $idmac)->first();
        $name_mac = $dec_mac->NOMBRE_MAC;

        

        // DEFINIMOS EL TIPO DE DESCA

        $fecha_ini_desc = strftime('%d de %B del %Y', strtotime($request->fecha_inicio));
        $fecha_fin_desc = strftime('%d de %B del %Y', strtotime($request->fecha_fin));

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
                        ->select(
                            'PERS.ABREV_ENTIDAD', 
                            'PERS.N_MODULO', 
                            'PERS.NOMBREU', 
                            'PERS.NOMBRE_CARGO', 
                            'MA.FECHA', 
                            'MA.NUM_DOC',
                            DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
                            DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
                        )
                        ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->join(DB::raw('(
                            SELECT 
                                CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU, 
                                ME.ABREV_ENTIDAD, 
                                MCM.IDCENTRO_MAC, 
                                MP.NUM_DOC, 
                                ME.IDENTIDAD, 
                                DPC.NOMBRE_CARGO,
                                MPM.FECHAINICIO,
                                MPM.FECHAFIN,
                                MM.N_MODULO
                            FROM M_PERSONAL AS MP
                            LEFT JOIN D_PERSONAL_CARGO AS DPC ON DPC.IDCARGO_PERSONAL = MP.IDCARGO_PERSONAL
                            JOIN M_PERSONAL_MODULO AS MPM ON MPM.NUM_DOC = MP.NUM_DOC
                            JOIN M_MODULO AS MM ON MM.IDMODULO = MPM.IDMODULO AND MM.IDCENTRO_MAC = MPM.IDCENTRO_MAC
                            JOIN M_ENTIDAD AS ME ON ME.IDENTIDAD = MM.IDENTIDAD
                            JOIN M_CENTRO_MAC AS MCM ON MCM.IDCENTRO_MAC = MM.IDCENTRO_MAC
                            
                        ) AS PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
                        ->whereIn('PERS.IDENTIDAD', $identidadArray) // Lista de identidades
                        ->where('MA.IDCENTRO_MAC', $idmac)
                        ->whereMonth('MA.FECHA', $request->mes)
                        ->whereYear('MA.FECHA', $request->año)
                        ->whereRaw('MA.FECHA BETWEEN PERS.FECHAINICIO AND PERS.FECHAFIN') // Validación del rango de fechas de asignación
                        ->groupBy(
                            'PERS.ABREV_ENTIDAD',
                            'PERS.N_MODULO', 
                            'PERS.NOMBREU', 
                            'PERS.NOMBRE_CARGO', 
                            'MA.FECHA', 
                            'MA.NUM_DOC'
                        )
                        ->orderBy('PERS.ABREV_ENTIDAD', 'ASC')
                        ->orderBy('MA.FECHA', 'ASC')
                        ->get();


        // $query =  DB::table('M_ASISTENCIA as MA')
        // ->select(
        //     'PERS.ABREV_ENTIDAD', 
        //     'PERS.NOMBREU', 
        //     'PERS.NOMBRE_CARGO', 
        //     'MA.FECHA', 
        //     'MA.NUM_DOC',
        //     DB::raw('GROUP_CONCAT(DATE_FORMAT(MA.HORA, "%H:%i:%s") ORDER BY MA.HORA) AS HORAS'),
        //     DB::raw('COUNT(MA.NUM_DOC) AS N_NUM_DOC')
        // )
        // ->join('M_PERSONAL as MP', 'MP.NUM_DOC', '=', 'MA.NUM_DOC')
        // ->join(DB::raw('(
        //     SELECT 
        //         CONCAT(MP.APE_PAT, " ", MP.APE_MAT, ", ", MP.NOMBRE) AS NOMBREU, 
        //         ME.ABREV_ENTIDAD, 
        //         MCM.IDCENTRO_MAC, 
        //         MP.NUM_DOC, 
        //         ME.IDENTIDAD, 
        //         DPC.NOMBRE_CARGO
        //     FROM M_PERSONAL AS MP
        //     LEFT JOIN D_PERSONAL_CARGO AS DPC ON DPC.IDCARGO_PERSONAL = MP.IDCARGO_PERSONAL
        //     JOIN M_ENTIDAD AS ME ON ME.IDENTIDAD = MP.IDENTIDAD
        //     JOIN M_CENTRO_MAC AS MCM ON MCM.IDCENTRO_MAC = MP.IDMAC
        // ) AS PERS'), 'PERS.NUM_DOC', '=', 'MA.NUM_DOC')
        // ->whereIn('PERS.IDENTIDAD', $identidadArray) // Reemplaza con tu array de identidades
        // ->where('MA.IDCENTRO_MAC', $idmac)
        // ->whereMonth('MA.FECHA', $request->mes)
        // ->whereYear('MA.FECHA', $request->año)
        // ->groupBy(
        //     'PERS.ABREV_ENTIDAD', 
        //     'PERS.NOMBREU', 
        //     'PERS.NOMBRE_CARGO', 
        //     'MA.FECHA', 
        //     'MA.NUM_DOC'
        // )
        // ->orderBy('PERS.ABREV_ENTIDAD', 'ASC')
        // ->orderBy('MA.FECHA', 'ASC')
        // ->get();

        foreach ($query as $q) {
            $horas = explode(',', $q->HORAS);
            $num_horas = count($horas);
            if ($num_horas == 1) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = null;
            } elseif ($num_horas == 2) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = null;
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[1];
            } elseif ($num_horas == 3) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = null;
                $q->HORA_4 = $horas[2];
            } elseif ($num_horas >= 4) {
                $q->HORA_1 = $horas[0];
                $q->HORA_2 = $horas[1];
                $q->HORA_3 = $horas[2];
                $q->HORA_4 = $horas[3];
            }
        }

        $datosAgrupados = '';
        $fechasArray = '';

        $export = Excel::download(new AsistenciaGroupExport($query, $name_mac, $nombreMES, $tipo_desc, $fecha_inicial, $fecha_fin, $hora_1, $hora_2, $hora_3, $hora_4, $hora_5, $identidad, $datosAgrupados, $fechasArray,), 'REPORTE DE ASISTENCIA CENTRO MAC - ' . $name_mac . ' _' . $nombreMES . '.xlsx');

        return $export;
    }

    public function dow_asistencia()
    {
        try {

            $insert = DB::select("CALL  SP_CARGA_ASISTENCIA();");


            return response()->json([
                'status' => true,
                'message' => 'Se cargaron las asistencia con éxito.',
                'data' => $insert,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Hubo un error al actualizar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function migrarDatos(Request $request)
    {
        try {
            // Conectarse a la base de datos 'mysql'
            $dbCentroMac = DB::connection('mysql');

            // Obtener datos de 'ankarati_asesores' con status = 0
            $asistencias = DB::connection('ankarati_asesores')
                ->table('asistencias')
                ->where('status', 0)
                ->get();

            // Obtener el último correlativo y calcular el siguiente
            $lastCorrelativo = $dbCentroMac->table('asistenciatest')
                ->orderBy('correlativo', 'desc')
                ->value('correlativo');

            // Convertir el último correlativo a un número entero, si es null, iniciar desde 0
            $nextCorrelativo = $lastCorrelativo ? (int)$lastCorrelativo : 0;

            // Contador para los registros procesados
            $processedCount = 0;

            // Insertar los datos en 'mysql'
            foreach ($asistencias as $asistencia) {
                // Incrementar el correlativo y formatear con ceros a la izquierda
                $nextCorrelativo++;
                $formattedCorrelativo = str_pad($nextCorrelativo, 5, '0', STR_PAD_LEFT);
                $formattedPunchTime = Carbon::createFromFormat('Y-m-d H:i:s', $asistencia->punch_time)->second(0);

                try {
                    // Intentar insertar el registro
                    $dbCentroMac->table('asistenciatest')->insert([
                        'correlativo' => $formattedCorrelativo,
                        'idMAC' => 11,
                        'DNI' => $asistencia->emp_code,
                        'marcacion' => $formattedPunchTime
                    ]);

                    // Marcar el registro como procesado en 'ankarati_asesores'
                    DB::connection('ankarati_asesores')
                        ->table('asistencias')
                        ->where('id', $asistencia->id)
                        ->update(['status' => 1]);

                    $processedCount++;
                } catch (QueryException $e) {
                    // Manejar el caso en que el registro ya existe
                    if ($e->getCode() == '23000') {
                        // Omitir el registro duplicado y continuar
                        continue;
                    } else {
                        // Si es otro tipo de error, lanzar excepción
                        throw $e;
                    }
                }
            }

            // Retornar respuesta JSON con éxito
            return response()->json([
                'success' => true,
                'message' => "$processedCount datos migrados correctamente."
            ]);
        } catch (\Exception $e) {
            // Manejar cualquier otra excepción y retornar error
            return response()->json([
                'success' => false,
                'message' => "Hubo un error al migrar los datos: " . $e->getMessage()
            ], 500);
        }
    }
}
