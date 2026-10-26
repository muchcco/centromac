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
        $entidades = DB::table('m_entidad')->select('identidad', 'nombre_entidad', 'abrev_entidad')->whereNull('id_entidad_padre')->where('flag', 1)->orderBy('nombre_entidad')->get();
        $nombre_mac_usuario = $centros_mac->firstWhere('idcentro_mac', $usuario->idcentro_mac)->nombre_mac ?? null;
        return view('reporte.ocupabilidad_puntualidad.index', compact('centros_mac', 'entidades', 'nombre_mac_usuario'));
    }
    /**
     * Catálogo de entidades activas: las hijas se asignan a su entidad principal.
     * Se mantienen las abreviaturas para consultar asistencia_resumen sin alterar datos.
     */
    private function catalogoConsolidado(): array
    {
        $registros = DB::table('m_entidad')
            ->select('identidad', 'id_entidad_padre', 'abrev_entidad', 'nombre_entidad')
            ->where('flag', 1)->orderBy('identidad')->get();
        $porId = $registros->keyBy('identidad');
        $principales = [];
        $mapa = [];
        foreach ($registros as $e) {
            $id = (int) $e->identidad;
            $padre = $e->id_entidad_padre === null ? $id : (int) $e->id_entidad_padre;
            // No asignar entidades a padres inexistentes o inactivos.
            if (!isset($porId[$padre])) continue;
            if ($porId[$padre]->id_entidad_padre !== null) continue;
            if (!isset($principales[$padre])) {
                $principal = $porId[$padre];
                $principales[$padre] = [
                    'id' => $padre,
                    'abrev' => mb_strtoupper(trim((string)$principal->abrev_entidad)),
                    'nombre' => trim((string)$principal->nombre_entidad),
                    'abreviaturas' => [],
                ];
            }
            $alias = mb_strtoupper(trim((string)$e->abrev_entidad));
            if ($alias !== '') $principales[$padre]['abreviaturas'][$alias] = $alias;
        }
        // Priorizar abreviaturas de entidades principales ante un catálogo ambiguo.
        foreach ($principales as $id => $principal) {
            if ($principal['abrev'] !== '') $mapa[$principal['abrev']] = $id;
        }
        foreach ($principales as $id => $principal) {
            foreach ($principal['abreviaturas'] as $alias) {
                if (!isset($mapa[$alias])) $mapa[$alias] = $id;
            }
        }
        foreach ($principales as $id => &$principal) {
            $principal['abreviaturas'] = array_values(array_filter(
                $principal['abreviaturas'], fn($alias) => ($mapa[$alias] ?? null) === $id
            ));
        }
        unset($principal);
        return [$principales, $mapa];
    }

    private function entidadPrincipal(?int $id, array $principales)
    {
        if (!$id) return null;
        return $principales[$id] ?? null;
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
        return response()->json(['message' => 'Para exportaciones grandes utilice la ruta ans-masivo/iniciar y procese las entidades una por una.'], 422);
    }
    private function opDirectorio(string $token): string
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) abort(404);
        return storage_path('app/tmp/op_masivo_'.$token);
    }
    private function opSesion(string $token): array
    {
        $archivo = $this->opDirectorio($token).'/manifest.json';
        if (!is_file($archivo)) abort(404, 'Exportación no encontrada');
        $sesion = json_decode(file_get_contents($archivo), true);
        if (!is_array($sesion) || (int)$sesion['usuario'] !== (int)auth()->id()) abort(403);
        if ($sesion['creado']+7200<time()) abort(410, 'La exportación ha caducado');
        return $sesion;
    }
    private function opGuardar(string $token, array $sesion): void
    {
        $archivo=$this->opDirectorio($token).'/manifest.json';
        if (file_put_contents($archivo.'.new',json_encode($sesion,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),LOCK_EX)===false || !rename($archivo.'.new',$archivo)) throw new \RuntimeException('No se pudo actualizar temporal de exportación');
    }
    public function iniciarExportacion(Request $request)
    {
        $f=$request->validate(['fecha_inicio'=>'required|date','fecha_fin'=>'required|date|after_or_equal:fecha_inicio','idmac'=>'nullable|integer','identidad'=>'nullable|integer','resultado'=>'nullable|in:PUNTUAL,TARDANZA,FALTA,FERIADO,DOMINGO,SIN DATOS']);
        if (Carbon::parse($f['fecha_inicio'])->diffInDays(Carbon::parse($f['fecha_fin']))>92) return response()->json(['message'=>'Máximo 93 días'],422);
        $user=auth()->user();
        $f['idmac']=$user->hasAnyRole(['Administrador','Moderador']) ? ($f['idmac']??null) : $user->idcentro_mac;
        [$principales, $mapa] = $this->catalogoConsolidado();
        $seleccionada = $this->entidadPrincipal(!empty($f['identidad']) ? (int)$f['identidad'] : null, $principales);
        if (!empty($f['identidad']) && !$seleccionada) return response()->json(['message'=>'Entidad principal no válida'],422);
        $q=DB::table('db_centro_mac_reporte.asistencia_resumen')
            ->where('fecha_asistencia','<=',$f['fecha_fin'])->whereNotNull('idmodulo');
        if ($f['idmac']) $q->where('idmac',(int)$f['idmac']);
        if ($seleccionada) $q->whereIn(DB::raw('UPPER(TRIM(abrev_entidad))'), $seleccionada['abreviaturas'] ?: ['__NO_EXISTE__']);
        $existentes=$q->distinct()->pluck('abrev_entidad')
            ->map(fn($x)=>mb_strtoupper(trim((string)$x)))->filter(fn($x)=>$x!==''&&$x!=='PCM')->unique();
        $ids=[];
        foreach ($existentes as $abrev) {
            if (isset($mapa[$abrev])) $ids[$mapa[$abrev]] = true;
        }
        $entidades=[];
        foreach (array_keys($ids) as $id) {
            $p=$principales[$id];
            $entidades[]=['abrev'=>$p['abrev'], 'id'=>$id, 'abreviaturas'=>$p['abreviaturas']];
        }
        usort($entidades, fn($a,$b)=>strcmp($a['abrev'],$b['abrev']));
        if (!$entidades) return response()->json(['message'=>'Sin entidades válidas para exportar'],422);
        $token=bin2hex(random_bytes(20));
        \Illuminate\Support\Facades\File::ensureDirectoryExists($this->opDirectorio($token));
        $sesion=['usuario'=>auth()->id(),'creado'=>time(),'filtros'=>$f,'entidades'=>$entidades,'procesadas'=>[],'archivos'=>[],'avance'=>[]];
        $this->opGuardar($token,$sesion);
        return response()->json(['token'=>$token,'total'=>count($entidades),'entidades'=>array_column($entidades,'abrev')]);
    }
    public function procesarEntidad(Request $request)
    {
        $data=$request->validate(['token'=>'required|string','indice'=>'required|integer|min:0']);
        $token=$data['token'];$indice=(int)$data['indice'];$sesion=$this->opSesion($token);
        if (!isset($sesion['entidades'][$indice])) return response()->json(['message'=>'Entidad inválida'],422);
        $e=$sesion['entidades'][$indice];$f=$sesion['filtros'];
        $inicio=Carbon::parse($f['fecha_inicio']);$fin=Carbon::parse($f['fecha_fin']);
        $nDias=$inicio->diffInDays($fin)+1;
        $tamanoLote=7;
        $totalLotes=(int)ceil($nDias/$tamanoLote);
        $dir=$this->opDirectorio($token);$subdir=$dir.'/entidad_'.$indice;
        \Illuminate\Support\Facades\File::ensureDirectoryExists($subdir);
        if (in_array($indice,$sesion['procesadas'],true)) return response()->json(['entidad'=>$e['abrev'],'completa'=>true,'procesadas'=>count($sesion['procesadas']),'total'=>count($sesion['entidades'])]);
        $avance=$sesion['avance'][(string)$indice]??['dia'=>0,'dias_habiles'=>0,'asistencias'=>0,'puntuales'=>0,'centros'=>[]];
        try {
            if ($avance['dia']<$nDias) {
                $desde=$inicio->copy()->addDays($avance['dia'])->toDateString();
                $hasta=$inicio->copy()->addDays(min($avance['dia']+$tamanoLote-1,$nDias-1))->toDateString();
                $indiceLote=intdiv($avance['dia'],$tamanoLote);
                $parte=$subdir.'/detalle_'.$indiceLote.'.jsonl';
                $temporal=$parte.'.tmp';
                $resumen=$this->procesarLoteExportacion($f,$e['abrev'],$e['abreviaturas'],$desde,$hasta,$temporal);
                if (!rename($temporal,$parte)) throw new \RuntimeException('No se pudo cerrar lote de datos');
                $avance['dia']=min($avance['dia']+$tamanoLote,$nDias);
                foreach (['dias_habiles','asistencias','puntuales'] as $k) $avance[$k]+=$resumen[$k];
                foreach ($resumen['centros'] as $id=>$nombre) $avance['centros'][(string)$id]=$nombre;
                $sesion['avance'][(string)$indice]=$avance;
                $this->opGuardar($token,$sesion);
                return response()->json(['entidad'=>$e['abrev'],'completa'=>false,'dia'=>$avance['dia'],'dias'=>$nDias,'procesadas'=>count($sesion['procesadas']),'total'=>count($sesion['entidades'])]);
            }
            $nombres=array_values(array_unique(array_values($avance['centros'])));
            $centro=count($nombres)===1?$nombres[0]:(count($nombres)>1?'VARIOS CENTROS MAC':'SIN REGISTROS');
            $nombre='ANS_'.preg_replace('/[^A-Z0-9_-]/','_', $e['abrev']).'_'.$f['fecha_inicio'].'_'.$f['fecha_fin'].'.xlsx';
            $ruta=$dir.'/'.$nombre;$tmp=$ruta.'.tmp';
            \App\Exports\ReporteOcupabilidadPuntualidadExport::save($subdir,$tmp,$avance,$f['fecha_inicio'],$f['fecha_fin'],$e['abrev'],$centro,$totalLotes);
            if (!rename($tmp,$ruta)) throw new \RuntimeException('No se pudo finalizar el Excel');
            \Illuminate\Support\Facades\File::deleteDirectory($subdir);
            $sesion['archivos'][(string)$indice]=$nombre;$sesion['procesadas'][]=$indice;
            unset($sesion['avance'][(string)$indice]);$this->opGuardar($token,$sesion);
            return response()->json(['entidad'=>$e['abrev'],'completa'=>true,'procesadas'=>count($sesion['procesadas']),'total'=>count($sesion['entidades'])]);
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::error('ANS OP exportación',['entidad'=>$e['abrev'],'dia'=>$avance['dia'],'error'=>$ex->getMessage()]);
            return response()->json(['message'=>'No se pudo exportar '.$e['abrev'].': '.$ex->getMessage()],500);
        }
    }
    private function procesarLoteExportacion(array $f, string $abrev, array $abreviaturas, string $desde, string $hasta, string $destino): array
    {
        $idmac = !empty($f['idmac']) ? (int)$f['idmac'] : null;
        $macs = DB::table('m_centro_mac')->select('idcentro_mac', 'nombre_mac')->get()->keyBy('idcentro_mac');
        // Una consulta historica por semana, no una por cada dia.
        $historico = DB::table('db_centro_mac_reporte.asistencia_resumen as ar')
            ->where('ar.fecha_asistencia', '<=', $f['fecha_fin'])
            ->whereIn(DB::raw('UPPER(TRIM(ar.abrev_entidad))'), $abreviaturas)->whereNotNull('ar.idmodulo')
            ->select('ar.idmac', 'ar.idmodulo')->distinct();
        if ($idmac) $historico->where('ar.idmac', $idmac);
        $pares = $historico->get();
        $ids = $pares->pluck('idmodulo')->unique()->values()->all();
        $modulos = DB::table('m_modulo')->whereIn('IDMODULO', $ids ?: [-1])
            ->whereRaw("COALESCE(UPPER(TRIM(ES_ADMINISTRATIVO)), 'NO') <> 'SI'")->get()->keyBy('IDMODULO');
        $lista = [];
        foreach ($pares as $par) {
            $modulo = $modulos->get($par->idmodulo);
            $mac = $macs->get($par->idmac);
            if (!$modulo || !$mac) continue;
            $lista[$par->idmac.'|'.$par->idmodulo] = ['modulo'=>$modulo, 'mac'=>$mac, 'fechas'=>[]];
        }
        unset($pares, $modulos, $historico);
        // Trae las marcaciones de hasta 7 dias en una consulta paginada.
        $query = DB::table('db_centro_mac_reporte.asistencia_resumen as ar')
            ->whereBetween('ar.fecha_asistencia', [$desde, $hasta])
            ->whereIn(DB::raw('UPPER(TRIM(ar.abrev_entidad))'), $abreviaturas)->whereNotNull('ar.idmodulo')
            ->select('ar.fecha_asistencia','ar.idmac','ar.idmodulo','ar.nombreu','ar.n_dni','ar.nombre_modulo','ar.fecha_biometrico');
        if ($idmac) $query->where('ar.idmac', $idmac);
        $query->orderBy('ar.fecha_asistencia')->orderBy('ar.idmac')->orderBy('ar.idmodulo')->orderBy('ar.n_dni')
            ->chunk(2000, function ($registros) use (&$lista) {
                foreach ($registros as $r) {
                    $clave=$r->idmac.'|'.$r->idmodulo;
                    if (!isset($lista[$clave])) continue;
                    $fecha=substr((string)$r->fecha_asistencia,0,10);
                    $marcas=array_values(array_filter(array_map('trim',explode(',',(string)$r->fecha_biometrico))));
                    $hora=$marcas[0]??'';
                    if (!isset($lista[$clave]['fechas'][$fecha])) $lista[$clave]['fechas'][$fecha]=['personas'=>[], 'hora'=>''];
                    $lista[$clave]['fechas'][$fecha]['personas'][]=[
                        'nombre'=>(string)$r->nombreu,'dni'=>(string)$r->n_dni,
                        'cargo'=>mb_strtoupper(trim((string)$r->nombre_modulo))==='ADMINISTRATIVO'?'Administrativo':'Asesor(a)',
                        'ingreso'=>$hora,'salida'=>count($marcas)>1?end($marcas):''
                    ];
                    if ($hora!=='' && ($lista[$clave]['fechas'][$fecha]['hora']==='' || $hora<$lista[$clave]['fechas'][$fecha]['hora']))
                        $lista[$clave]['fechas'][$fecha]['hora']=$hora;
                }
            });
        $feriados=DB::table('feriados')->whereBetween('fecha',[$desde,$hasta])->get()->groupBy(fn($x)=>substr((string)$x->fecha,0,10));
        $ultima=Cache::remember('op_ultima_carga',now()->addMinutes(5),fn()=>DB::table('db_centro_mac_reporte.asistencia_resumen')->max('fecha_asistencia'));
        $fh=fopen($destino,'wb');
        if (!$fh) throw new \RuntimeException('No se pudo crear archivo temporal');
        $res=['dias_habiles'=>0,'asistencias'=>0,'puntuales'=>0,'centros'=>[]];
        try {
            for ($dia=Carbon::parse($desde); $dia->lte(Carbon::parse($hasta)); $dia->addDay()) {
                $fecha=$dia->toDateString();
                $domingo=$dia->dayOfWeekIso===7;
                $sinCarga=$ultima!==null && $fecha>substr((string)$ultima,0,10);
                $nombreDia=['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO'][$dia->dayOfWeekIso-1];
                foreach ($lista as $item) {
                    $mac=$item['mac'];$m=$item['modulo'];
                    if ($m->FECHAINICIO && $fecha<substr((string)$m->FECHAINICIO,0,10)) continue;
                    if ($m->FECHAFIN && $fecha>substr((string)$m->FECHAFIN,0,10)) continue;
                    $fer=false;$tipo='';$motivos=[];
                    foreach ($feriados->get($fecha,collect()) as $feriado) {
                        if ($feriado->id_centromac===null || (int)$feriado->id_centromac===(int)$mac->idcentro_mac) {
                            $fer=true;
                            if ($feriado->id_centromac===null) $tipo='GENERAL';
                            elseif ($tipo!=='GENERAL') $tipo='CENTRO MAC';
                            if (!empty($feriado->name)) $motivos[]=$feriado->name;
                        }
                    }
                    $informacion=$item['fechas'][$fecha]??['personas'=>[],'hora'=>''];
                    $hora=$informacion['hora'];
                    $estado=$fer?'FERIADO':($domingo?'DOMINGO':($sinCarga?'SIN DATOS':($hora===''?'FALTA':($hora<'08:16:00'?'PUNTUAL':'TARDANZA'))));
                    if (!$fer && !$domingo && !$sinCarga) {
                        $res['dias_habiles']++;
                        if ($hora!=='') { $res['asistencias']++; if ($estado==='PUNTUAL') $res['puntuales']++; }
                    }
                    $res['centros'][$mac->idcentro_mac]=$mac->nombre_mac;
                    if (!empty($f['resultado']) && $f['resultado']!==$estado) continue;
                    $personas=$informacion['personas'] ?: [['nombre'=>'','dni'=>'','cargo'=>'','ingreso'=>'','salida'=>'']];
                    foreach ($personas as $p) {
                        $fila=['centro_mac'=>$mac->nombre_mac,'entidad'=>$abrev,'colaborador'=>$p['nombre'],
                            'cargo'=>$p['cargo'],'modulo'=>$m->N_MODULO,'dni'=>$p['dni'],'fecha'=>$fecha,
                            'dia'=>$nombreDia,'ingreso'=>$p['ingreso'],'salida'=>$p['salida'],'estado'=>$estado,
                            'puntual'=>$fer||$domingo||$sinCarga?'NO APLICA':($estado==='PUNTUAL'?'SI':'NO'),
                            'feriado'=>$fer?'SI':'NO','tipo_feriado'=>$tipo,'motivo_feriado'=>implode(' / ',array_unique($motivos))];
                        if (fwrite($fh,json_encode($fila,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR)."\n")===false)
                            throw new \RuntimeException('No se pudo escribir el lote');
                    }
                }
            }
        } finally { fclose($fh); }
        return $res;
    }
    public function finalizarExportacion(Request $request)
    {
        $token=$request->validate(['token'=>'required|string'])['token'];$s=$this->opSesion($token);
        if(count($s['procesadas'])!==count($s['entidades']))return response()->json(['message'=>'Faltan entidades por procesar'],422);
        $dir=$this->opDirectorio($token);$zip=new \ZipArchive();
        if($zip->open($dir.'/reporte_final.zip',\ZipArchive::CREATE|\ZipArchive::OVERWRITE)!==true)abort(500,'No se pudo crear ZIP');
        foreach($s['archivos'] as $name){if(!is_file($dir.'/'.$name)){ $zip->close();abort(500,'Falta '.$name); }$zip->addFile($dir.'/'.$name,$name);}
        if(!$zip->close())abort(500,'Error cerrando ZIP');
        return response()->json(['descarga'=>route('reporte.ocupabilidad_puntualidad.descargar_exportacion',['token'=>$token])]);
    }
    public function descargarExportacion(string $token)
    {
        $this->opSesion($token);$archivo=$this->opDirectorio($token).'/reporte_final.zip';
        if(!is_file($archivo))abort(404,'ZIP no preparado');
        return response()->download($archivo,'ANS_Ocupabilidad_Puntualidad_Por_Entidad.zip',['Content-Type'=>'application/zip']);
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
        [$principales, $mapa] = $this->catalogoConsolidado();
        $entidad = $this->entidadPrincipal(!empty($datos['identidad']) ? (int)$datos['identidad'] : null, $principales);
        if (!empty($datos['identidad']) && !$entidad) return response()->json(['message' => 'Entidad principal no válida.'], 422);
        $filtrar = function ($query) use ($idmac, $entidad) {
            if ($idmac) $query->where('ar.idmac', (int)$idmac);
            if ($entidad) $query->whereIn(DB::raw('UPPER(TRIM(ar.abrev_entidad))'), $entidad['abreviaturas'] ?: ['__NO_EXISTE__']);
            return $query;
        };
        $claveHistorica = 'op_hist_' . md5(json_encode([$idmac, $entidad['id'] ?? null, $entidad['abreviaturas'] ?? null, $fin->toDateString()]));
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
        // La consulta se ejecuta por lotes; nunca se trae completa con get().
        $consultaRegistros = $consulta;
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
            $alias = mb_strtoupper(trim((string)$r->abrev_entidad));
            if (!isset($mapa[$alias])) continue;
            $abrev = $principales[$mapa[$alias]]['abrev'];
            $clave = $mac->idcentro_mac . '|' . $abrev . '|' . $r->idmodulo;
            if (!isset($grupos[$clave])) $grupos[$clave] = ['mac' => $mac, 'entidad' => $abrev, 'modulo' => $modulo, 'personas' => []];
        }
        $consultaRegistros->orderBy('ar.fecha_asistencia')->orderBy('ar.idmac')->orderBy('ar.idmodulo')->orderBy('ar.n_dni')->chunk(2000, function ($lote) use (&$grupos, $mapa, $principales) {
            foreach ($lote as $r) {
                $alias = mb_strtoupper(trim((string)$r->abrev_entidad));
                if (!isset($mapa[$alias])) continue;
                $abrev = $principales[$mapa[$alias]]['abrev'];
                $clave = $r->idmac . '|' . $abrev . '|' . $r->idmodulo;
                if (!isset($grupos[$clave])) continue;
                $fecha = Carbon::parse($r->fecha_asistencia)->toDateString();
                $marcas = array_values(array_filter(array_map('trim', explode(',', (string)$r->fecha_biometrico))));
                $grupos[$clave]['personas'][$fecha][] = ['nombre' => (string)$r->nombreu, 'dni' => (string)$r->n_dni, 'cargo' => mb_strtoupper(trim((string)$r->nombre_modulo)) === 'ADMINISTRATIVO' ? 'Administrativo' : 'Asesor(a)', 'ingreso' => $marcas[0] ?? '', 'salida' => count($marcas) > 1 ? end($marcas) : '', 'refrigerio' => count($marcas) >= 4 ? $marcas[1] . ' - ' . $marcas[2] : ''];
            }
        });
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
                $estado = $esFeriado ? 'FERIADO' : ($esDomingo ? 'DOMINGO' : ($sinCarga ? 'SIN DATOS' : ($horaModulo === '' ? 'FALTA' : ($horaModulo < '08:16:00' ? 'PUNTUAL' : 'TARDANZA'))));
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
