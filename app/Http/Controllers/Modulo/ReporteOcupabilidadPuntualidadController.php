<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Cache;

class ReporteOcupabilidadPuntualidadController extends Controller

{

    public function index()

    {

        $usuario = auth()->user();

        $todos = $usuario->hasAnyRole(['Administrador', 'Moderador']);

        $centros_mac = DB::table('m_centro_mac')->select('idcentro_mac', 'nombre_mac')->when(!$todos, fn($q) => $q->where('idcentro_mac', $usuario->idcentro_mac))->orderBy('nombre_mac')->get();

        $entidades = DB::table('m_entidad')->select('identidad', 'nombre_entidad', 'abrev_entidad')->orderBy('nombre_entidad')->get();

        $nombre_mac_usuario = $centros_mac->firstWhere('idcentro_mac', $usuario->idcentro_mac)->nombre_mac ?? null;

        return view('reporte.ocupabilidad_puntualidad.index', compact('centros_mac', 'entidades', 'nombre_mac_usuario'));

    }

    public function consultar(Request $request)

    {

        return $this->generarDatos($request, 500);

    }

    public function tb_index(Request $request)
    {
        $respuesta = $this->generarDatos($request, 500);
        if ($respuesta->getStatusCode() !== 200) return $respuesta;
        $datos = $respuesta->getData(true);
        $datos['html'] = view('reporte.ocupabilidad_puntualidad.tablas.tb_index', ['detalle' => $datos['detalle'] ?? []])->render();
        unset($datos['detalle']);
        return response()->json($datos);
    }

    public function exportar(Request $request)

    {

        $resultado = $this->generarDatos($request, null);

        if ($resultado->getStatusCode() !== 200) return $resultado;

        $datos = $resultado->getData(true);

        $grupos = [];

        foreach ($datos['detalle'] as $fila) {

            $clave = mb_strtoupper(trim((string)$fila['entidad']));

            if ($clave !== '') $grupos[$clave][] = $fila;

        }

        if (!$grupos) return response()->json(['message' => 'No se encontraron registros para exportar.'], 422);

        $directorio = storage_path('app/tmp');

        \Illuminate\Support\Facades\File::ensureDirectoryExists($directorio);

        $temporal = $directorio . DIRECTORY_SEPARATOR . 'op_' . uniqid('', true);

        \Illuminate\Support\Facades\File::ensureDirectoryExists($temporal);

        $nombreZip = 'Ocupabilidad_Puntualidad_Por_Entidad_' . $request->fecha_inicio . '_' . $request->fecha_fin . '.zip';

        $rutaZip = $directorio . DIRECTORY_SEPARATOR . 'op_entidades_' . uniqid('', true) . '.zip';

        $zip = new \ZipArchive();

        if ($zip->open($rutaZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {

            \Illuminate\Support\Facades\File::deleteDirectory($temporal);

            return response()->json(['message' => 'No se pudo crear el archivo ZIP.'], 500);

        }

        try {

            foreach ($grupos as $entidad => $detalle) {

                $indicadores = $datos['indicadores_por_entidad'][$entidad] ?? null;

                if (!$indicadores) continue;

                $seguro = preg_replace('/[^A-Z0-9_-]/u', '_', $entidad);

                $nombre = 'ANS_' . $seguro . '_' . $request->fecha_inicio . '_' . $request->fecha_fin . '.xlsx';

                $rutaExcel = $temporal . DIRECTORY_SEPARATOR . $nombre;

                (new \App\Exports\ReporteOcupabilidadPuntualidadExport($detalle, $indicadores, $request->fecha_inicio, $request->fecha_fin))->saveToPath($rutaExcel);

                $zip->addFile($rutaExcel, $nombre);

            }

        } catch (\Throwable $e) {

            $zip->close();

            \Illuminate\Support\Facades\File::deleteDirectory($temporal);

            \Illuminate\Support\Facades\File::delete($rutaZip);

            throw $e;

        }

        $zip->close();

        \Illuminate\Support\Facades\File::deleteDirectory($temporal);

        return response()->download($rutaZip, $nombreZip, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);

    }


    private function opDirectorio(string $token): string
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) abort(404);
        return storage_path('app/tmp/op_masivo_' . $token);
    }
    private function opSesion(string $token): array
    {
        $archivo = $this->opDirectorio($token) . '/manifest.json';
        if (!is_file($archivo)) abort(404, 'Proceso de exportación no encontrado.');
        $sesion = json_decode(file_get_contents($archivo), true);
        if (!is_array($sesion) || (int)($sesion['usuario'] ?? 0) !== (int)auth()->id()) abort(403);
        if (($sesion['creado'] ?? 0) + 7200 < time()) abort(410, 'El proceso ha expirado.');
        return $sesion;
    }
    private function opGuardar(string $token, array $sesion): void
    {
        file_put_contents($this->opDirectorio($token) . '/manifest.json', json_encode($sesion, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    public function iniciarExportacion(Request $request)
    {
        $filtros = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'idmac' => 'nullable|integer',
            'identidad' => 'nullable|integer',
            'resultado' => 'nullable|in:PUNTUAL,TARDANZA,FALTA,FERIADO,DOMINGO,SIN DATOS'
        ]);
        if (Carbon::parse($filtros['fecha_inicio'])->diffInDays(Carbon::parse($filtros['fecha_fin'])) > 92) return response()->json(['message' => 'Seleccione como máximo 93 días.'], 422);
        $usuario = auth()->user();
        $administrador = $usuario->hasAnyRole(['Administrador', 'Moderador']);
        $idmac = $administrador ? ($filtros['idmac'] ?? null) : $usuario->idcentro_mac;
        $filtros['idmac'] = $idmac;
        $seleccionada = null;
        if (!empty($filtros['identidad'])) {
            $seleccionada = DB::table('m_entidad')->where('identidad', $filtros['identidad'])->value('abrev_entidad');
            if (!$seleccionada) return response()->json(['message' => 'Entidad no encontrada.'], 422);
        }
        $consulta = DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('fecha_asistencia', '<=', $filtros['fecha_fin'])
            ->whereNotNull('idmodulo');
        if ($idmac) $consulta->where('idmac', (int)$idmac);
        if ($seleccionada) $consulta->where('abrev_entidad', trim($seleccionada));
        $entidades = $consulta->select('abrev_entidad')->distinct()->pluck('abrev_entidad')
            ->map(fn($e) => mb_strtoupper(trim((string)$e)))->filter()->unique()->sort()->values()->all();
        $entidades = array_values(array_filter($entidades, fn($e) => $e !== 'PCM'));
        if (!$entidades) return response()->json(['message' => 'No se encontraron entidades válidas para exportar.'], 422);
        $token = bin2hex(random_bytes(20));
        $directorio = $this->opDirectorio($token);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($directorio);
        $sesion = ['usuario' => auth()->id(), 'creado' => time(), 'filtros' => $filtros, 'entidades' => $entidades, 'procesadas' => [], 'archivos' => []];
        $this->opGuardar($token, $sesion);
        return response()->json(['token' => $token, 'total' => count($entidades), 'entidades' => $entidades]);
    }
    public function procesarEntidad(Request $request)
    {
        $datos = $request->validate(['token' => 'required|string', 'indice' => 'required|integer|min:0']);
        $token = $datos['token'];
        $sesion = $this->opSesion($token);
        $indice = (int)$datos['indice'];
        if (!isset($sesion['entidades'][$indice])) return response()->json(['message' => 'Índice de entidad inválido.'], 422);
        if (!in_array($indice, $sesion['procesadas'], true)) {
            $entidad = $sesion['entidades'][$indice];
            $identidad = DB::table('m_entidad')->where('abrev_entidad', $entidad)->value('identidad');
            if (!$identidad) {
                $sesion['procesadas'][] = $indice;
                $this->opGuardar($token, $sesion);
                return response()->json(['entidad' => $entidad, 'omitida' => true, 'procesadas' => count($sesion['procesadas']), 'total' => count($sesion['entidades'])]);
            }
            $filtros = $sesion['filtros'];
            $filtros['identidad'] = $identidad;
            $resultado = $this->generarDatos(new Request($filtros), null);
            if ($resultado->getStatusCode() !== 200) return $resultado;
            $reporte = $resultado->getData(true);
            $detalle = $reporte['detalle'] ?? [];
            $indicadores = $reporte['indicadores_por_entidad'][$entidad] ?? $reporte['indicadores'];
            $nombreSeguro = trim(preg_replace('/[^A-Z0-9_-]+/u', '_', $entidad), '_');
            $nombre = 'ANS_' . ($nombreSeguro ?: 'ENTIDAD') . '_' . $filtros['fecha_inicio'] . '_' . $filtros['fecha_fin'] . '.xlsx';
            (new \App\Exports\ReporteOcupabilidadPuntualidadExport($detalle, $indicadores, $filtros['fecha_inicio'], $filtros['fecha_fin']))->saveToPath($this->opDirectorio($token) . '/' . $nombre);
            $sesion['archivos'][(string)$indice] = $nombre;
            $sesion['procesadas'][] = $indice;
            $this->opGuardar($token, $sesion);
        }
        return response()->json(['entidad' => $sesion['entidades'][$indice], 'procesadas' => count($sesion['procesadas']), 'total' => count($sesion['entidades'])]);
    }
    public function finalizarExportacion(Request $request)
    {
        $token = $request->validate(['token' => 'required|string'])['token'];
        $sesion = $this->opSesion($token);
        if (count($sesion['procesadas']) !== count($sesion['entidades'])) return response()->json(['message' => 'Faltan entidades por procesar.'], 422);
        $directorio = $this->opDirectorio($token);
        $rutaZip = $directorio . '/reporte_final.zip';
        $zip = new \ZipArchive();
        if ($zip->open($rutaZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) abort(500, 'No se pudo crear el ZIP.');
        foreach ($sesion['archivos'] as $nombre) {
            if (!is_file($directorio . '/' . $nombre)) { $zip->close(); abort(500, 'Falta el archivo ' . $nombre); }
            $zip->addFile($directorio . '/' . $nombre, $nombre);
        }
        $zip->close();
        return response()->json(['descarga' => route('reporte.ocupabilidad_puntualidad.descargar_exportacion', ['token' => $token])]);
    }
    public function descargarExportacion(string $token)
    {
        $this->opSesion($token);
        $archivo = $this->opDirectorio($token) . '/reporte_final.zip';
        if (!is_file($archivo)) abort(404, 'El ZIP todavía no está listo.');
        return response()->download($archivo, 'ANS_Ocupabilidad_Puntualidad_Por_Entidad.zip', ['Content-Type' => 'application/zip']);
    }

    private function generarDatos(Request $request, ?int $limiteDetalle)

    {

        $datos = $request->validate([

            'fecha_inicio' => 'required|date',

            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',

            'idmac' => 'nullable|integer',

            'identidad' => 'nullable|integer',

            'resultado' => 'nullable|in:PUNTUAL,TARDANZA,FALTA,FERIADO,DOMINGO,SIN DATOS'

        ]);

        $inicio = Carbon::parse($datos['fecha_inicio'])->startOfDay();

        $fin = Carbon::parse($datos['fecha_fin'])->startOfDay();

        if ($inicio->diffInDays($fin) > 92) return response()->json(['message' => 'Seleccione como máximo 93 días por consulta.'], 422);

        $usuario = auth()->user();

        $todos = $usuario->hasAnyRole(['Administrador', 'Moderador']);

        $idmac = $todos ? ($datos['idmac'] ?? null) : $usuario->idcentro_mac;

        $macs = DB::table('m_centro_mac')->select('idcentro_mac', 'nombre_mac')->get();

        $mapaMac = $macs->keyBy(fn($m) => (int)$m->idcentro_mac);

        if ($idmac && !$mapaMac->has((int)$idmac)) return response()->json(['message' => 'Centro MAC no válido.'], 422);

        $entidad = !empty($datos['identidad']) ? DB::table('m_entidad')->where('identidad', $datos['identidad'])->first(['abrev_entidad', 'nombre_entidad']) : null;

        if (!empty($datos['identidad']) && !$entidad) return response()->json(['message' => 'Entidad no válida.'], 422);

        $filtrar = function ($query) use ($idmac, $entidad) {

            if ($idmac) $query->where('ar.idmac', (int)$idmac);

            if ($entidad) $query->where('ar.abrev_entidad', trim((string)$entidad->abrev_entidad));

            return $query;

        };

        $claveHistorica = 'op_hist_' . md5(json_encode([$idmac, $entidad->abrev_entidad ?? null, $fin->toDateString()]));

        $baseHistorica = Cache::remember($claveHistorica, now()->addMinutes(10), function () use ($filtrar, $fin) {

            return $filtrar(DB::table('db_centro_mac_reporte.asistencia_resumen as ar')

                ->where('ar.fecha_asistencia', '<=', $fin->toDateString())

                ->whereNotNull('ar.idmodulo')

                ->select('ar.idmac', 'ar.abrev_entidad', 'ar.idmodulo')

                ->distinct())->get();

        });

        $consulta = $filtrar(DB::table('db_centro_mac_reporte.asistencia_resumen as ar')

            ->whereBetween('ar.fecha_asistencia', [$inicio->toDateString(), $fin->toDateString()])

            ->whereNotNull('ar.idmodulo')

            ->select('ar.fecha_asistencia', 'ar.idmac', 'ar.idmodulo', 'ar.abrev_entidad', 'ar.nombreu', 'ar.n_dni', 'ar.nombre_modulo', 'ar.fecha_biometrico'));

        $registros = $consulta->orderBy('ar.fecha_asistencia')->orderBy('ar.idmodulo')->get();

        $idsModulo = $baseHistorica->pluck('idmodulo')->unique()->values()->all();

        $modulos = DB::table('m_modulo')->whereIn('IDMODULO', $idsModulo ?: [-1])->whereRaw("COALESCE(UPPER(TRIM(ES_ADMINISTRATIVO)), 'NO') <> 'SI'")->get()->keyBy('IDMODULO');

        $ultimaCarga = Cache::remember('op_ultima_carga', now()->addMinutes(5), fn() => DB::table('db_centro_mac_reporte.asistencia_resumen')->max('fecha_asistencia'));

        $feriados = DB::table('feriados')->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])->get();

        $feriadosPorFecha = [];

        foreach ($feriados as $f) $feriadosPorFecha[Carbon::parse($f->fecha)->toDateString()][] = $f;

        $grupos = [];

        foreach ($baseHistorica as $r) {

            $mac = $mapaMac->get((int)$r->idmac);

            $modulo = $modulos->get($r->idmodulo);

            if (!$mac || !$modulo) continue;

            $abrev = mb_strtoupper(trim((string)$r->abrev_entidad));

            $clave = $mac->idcentro_mac . '|' . $abrev . '|' . $r->idmodulo;

            if (!isset($grupos[$clave])) $grupos[$clave] = ['mac' => $mac, 'entidad' => $abrev, 'modulo' => $modulo, 'personas' => []];

        }

        foreach ($registros as $r) {

            $abrev = mb_strtoupper(trim((string)$r->abrev_entidad));

            $clave = $r->idmac . '|' . $abrev . '|' . $r->idmodulo;

            if (!isset($grupos[$clave])) continue;

            $fecha = Carbon::parse($r->fecha_asistencia)->toDateString();

            $marcas = array_values(array_filter(array_map('trim', explode(',', (string)$r->fecha_biometrico))));

            $grupos[$clave]['personas'][$fecha][] = ['nombre' => (string)$r->nombreu, 'dni' => (string)$r->n_dni, 'cargo' => mb_strtoupper(trim((string)$r->nombre_modulo)) === 'ADMINISTRATIVO' ? 'Administrativo' : 'Asesor(a)', 'ingreso' => $marcas[0] ?? '', 'salida' => count($marcas) > 1 ? end($marcas) : '', 'refrigerio' => count($marcas) >= 4 ? $marcas[1] . ' - ' . $marcas[2] : ''];

        }

        $detalle = [];

        $totalAsistencias = 0;

        $totalHabiles = 0;

        $totalPuntuales = 0;

        $centros = [];

        $indicadoresPorEntidad = [];

        $numero = 0;

        $cantidadDetalle = 0;

        $filtroResultado = $datos['resultado'] ?? null;

        foreach ($grupos as $g) {

            $modulo = $g['modulo'];

            if (!$modulo) continue;

            $mac = $g['mac'];

            $abrevEntidad = $g['entidad'];

            if (!isset($indicadoresPorEntidad[$abrevEntidad])) $indicadoresPorEntidad[$abrevEntidad] = ['dias_habiles' => 0, 'puntuales' => 0, 'asistencias' => 0, 'centros_ids' => []];

            $indicadoresPorEntidad[$abrevEntidad]['centros_ids'][$mac->idcentro_mac] = true;

            $centros[$mac->idcentro_mac] = true;

            for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {

                $fecha = $dia->toDateString();

                if ($modulo->FECHAINICIO && $fecha < substr((string)$modulo->FECHAINICIO, 0, 10)) continue;

                if ($modulo->FECHAFIN && $fecha > substr((string)$modulo->FECHAFIN, 0, 10)) continue;

                $esFeriado = false;

                $tipoFeriado = '';

                $motivosFeriado = [];

                foreach ($feriadosPorFecha[$fecha] ?? [] as $feriado) {

                    if ($feriado->id_centromac === null || (int)$feriado->id_centromac === (int)$mac->idcentro_mac) {

                        $esFeriado = true;

                        if ($feriado->id_centromac === null) $tipoFeriado = 'GENERAL';

                        elseif ($tipoFeriado !== 'GENERAL') $tipoFeriado = 'CENTRO MAC';

                        if (!empty($feriado->name)) $motivosFeriado[] = $feriado->name;

                    }

                }

                $esDomingo = $dia->dayOfWeekIso === 7;

                $personas = $g['personas'][$fecha] ?? [];

                $horas = array_values(array_filter(array_column($personas, 'ingreso')));

                sort($horas, SORT_STRING);

                $horaModulo = $horas[0] ?? '';

                $sinCarga = $ultimaCarga !== null && $fecha > substr((string)$ultimaCarga, 0, 10);

                $estado = $esFeriado ? 'FERIADO' : ($esDomingo ? 'DOMINGO' : ($sinCarga ? 'SIN DATOS' : ($horaModulo === '' ? 'FALTA' : ($horaModulo <= '08:16:00' ? 'PUNTUAL' : 'TARDANZA'))));

                if (!$esFeriado && !$esDomingo && !$sinCarga) {

                    $totalHabiles++;

                    $indicadoresPorEntidad[$abrevEntidad]['dias_habiles']++;

                    if ($horaModulo !== '') {

                        $totalAsistencias++;

                        $indicadoresPorEntidad[$abrevEntidad]['asistencias']++;

                        if ($estado === 'PUNTUAL') {

                            $totalPuntuales++;

                            $indicadoresPorEntidad[$abrevEntidad]['puntuales']++;

                        }

                    }

                }

                if ($filtroResultado && $estado !== $filtroResultado) continue;

                if (!$personas) $personas = [['nombre' => '', 'dni' => '', 'cargo' => '', 'ingreso' => '', 'salida' => '']];

                foreach ($personas as $p) {

                    $numero++;

                    $cantidadDetalle++;

                    if ($limiteDetalle !== null && count($detalle) >= $limiteDetalle) continue;

                    $detalle[] = ['numero' => $numero, 'centro_mac' => $mac->nombre_mac, 'entidad' => $g['entidad'], 'colaborador' => $p['nombre'], 'cargo' => $p['cargo'], 'modulo' => $modulo->N_MODULO, 'dni' => $p['dni'], 'fecha' => $fecha, 'dia' => ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'][$dia->dayOfWeekIso - 1], 'ingreso' => $p['ingreso'], 'salida' => $p['salida'], 'estado' => $estado, 'puntual' => $esFeriado || $esDomingo || $sinCarga ? 'NO APLICA' : ($estado === 'PUNTUAL' ? 'SI' : 'NO'), 'feriado' => $esFeriado ? 'SI' : 'NO', 'tipo_feriado' => $tipoFeriado, 'motivo_feriado' => implode(' / ', array_unique($motivosFeriado))];

                }

            }

        }

        foreach ($indicadoresPorEntidad as &$resumen) {

            $resumen['centros'] = count($resumen['centros_ids']);

            unset($resumen['centros_ids']);

            $resumen['ocupabilidad'] = $resumen['dias_habiles'] ? round($resumen['asistencias'] * 100 / $resumen['dias_habiles'], 2) : 0;

            $resumen['puntualidad'] = $resumen['asistencias'] ? round($resumen['puntuales'] * 100 / $resumen['asistencias'], 2) : 0;

        }

        unset($resumen);

        return response()->json([

            'indicadores_por_entidad' => $indicadoresPorEntidad,

            'indicadores' => ['dias_habiles' => $totalHabiles, 'puntuales' => $totalPuntuales, 'asistencias' => $totalAsistencias, 'ocupabilidad' => $totalHabiles ? round($totalAsistencias * 100 / $totalHabiles, 2) : 0, 'puntualidad' => $totalAsistencias ? round($totalPuntuales * 100 / $totalAsistencias, 2) : 0, 'centros' => count($centros)],

            'detalle' => $detalle,

            'total_detalle' => $cantidadDetalle,

            'mostrando' => count($detalle),

            'ultima_fecha_importada' => $ultimaCarga,

            'advertencia' => $ultimaCarga && $fin->toDateString() > substr((string)$ultimaCarga, 0, 10) ? 'Hay fechas posteriores a la última carga de asistencia. Se muestran como SIN DATOS y no se calculan como faltas.' : null

        ]);

    }

}
