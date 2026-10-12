<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Interrupcion;
use App\Models\Entidad;
use App\Models\TipoIntObs;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InterrupcionExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class InterrupcionController extends Controller
{
    private function interrupcionBloqueada($fechaInicio): bool
    {
        return Carbon::parse($fechaInicio)->startOfDay()->lte(Carbon::create(2026, 9, 30)->startOfDay());
    }
    private function respuestaBloqueo()
    {
        return response()->json([
            'message' => 'Las interrupciones con fecha de inicio hasta el 30/09/2026 están bloqueadas. Solo se permite su consulta.',
            'status' => 403,
        ], 403);
    }
    private function centro_mac()
    {
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('m_centro_mac', 'm_centro_mac.idcentro_mac', '=', 'users.idcentro_mac')
            ->where('m_centro_mac.idcentro_mac', $us_id)
            ->first();
        return (object)[
            'idmac' => $user->idcentro_mac,
            'name_mac' => $user->nombre_mac
        ];
    }
    public function index()
    {
        $user = auth()->user();
        $centros_mac = DB::table('m_centro_mac')
            ->select('idcentro_mac as IDCENTRO_MAC', 'nombre_mac as NOMBRE_MAC')
            ->orderBy('nombre_mac')
            ->get();
        $nombre_mac_usuario = DB::table('m_centro_mac')
            ->where('idcentro_mac', $user->idcentro_mac)
            ->value('nombre_mac');
        if ($user->hasAnyRole(['Administrador', 'Moderador'])) {
            $entidades = DB::table('m_entidad')
                ->select('identidad', 'abrev_entidad', 'nombre_entidad')
                ->orderBy('abrev_entidad')
                ->get();
        } else {
            $entidades = DB::table('m_entidad')
                ->join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
                ->where('m_mac_entidad.idcentro_mac', $user->idcentro_mac)
                ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
                ->orderBy('m_entidad.abrev_entidad')
                ->get();
        }
        $tipificaciones = DB::table('m_tipo_int_obs')
            ->where('tipo_obs', 'INTERRUPCIÓN')
            ->orderBy('tipo', 'asc')
            ->orderBy('numeracion', 'asc')
            ->orderBy('nom_tipo_int_obs', 'asc')
            ->get(['id_tipo_int_obs', 'tipo', 'numeracion', 'nom_tipo_int_obs']);
        return view('interrupcion.index', compact(
            'centros_mac',
            'nombre_mac_usuario',
            'entidades',
            'tipificaciones'
        ));
    }
    public function tb_index(Request $request)
    {
        $user = auth()->user();
        $idmac = $request->idmac;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;
        $entidad = $request->entidad;
        $tipificacion = $request->tipificacion;
        $estado = $request->estado;
        $revision = $request->revision;
        $query = Interrupcion::with([
            'entidad:IDENTIDAD,NOMBRE_ENTIDAD,ABREV_ENTIDAD',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion,nom_tipo_int_obs',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ]);
        if ($idmac) {
            $query->where('idcentro_mac', $idmac);
        } else {
            if (!$user->hasAnyRole(['Administrador', 'Monitor', 'Moderador'])) {
                $query->where('idcentro_mac', $user->idcentro_mac);
            }
        }
        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin]);
        }
        if (!empty($entidad)) {
            $query->whereHas('entidad', function ($q) use ($entidad) {
                $q->where('ABREV_ENTIDAD', 'like', "%{$entidad}%")
                    ->orWhere('NOMBRE_ENTIDAD', 'like', "%{$entidad}%");
            });
        }
        if (!empty($tipificacion)) {
            $query->whereHas('tipoIntObs', function ($q) use ($tipificacion) {
                $q->where('nom_tipo_int_obs', 'like', "%{$tipificacion}%")
                    ->orWhere('tipo', 'like', "%{$tipificacion}%");
            });
        }
        if (!empty($estado)) {
            $query->where('estado', strtoupper($estado));
        }
        if ($revision === 'observado') {
            $query->where('observado', 1);
        } elseif ($revision === 'no_observado') {
            $query->where('observado', 0);
        }
        $horariosMac = DB::table('m_horario_mac')
            ->where('activo', 1)
            ->get()
            ->groupBy('idcentro_mac');
        $feriadosMac = DB::table('feriados')
            ->get()
            ->groupBy(function ($f) {
                return $f->id_centromac ?? 'nacional';
            });
        $interrupciones = $query
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get()
            ->map(function ($item) use ($horariosMac, $feriadosMac) {
                if (!$item->fecha_fin || !$item->hora_fin) {
                    $item->tiempo_horario = '-';
                    return $item;
                }
                $inicio = \Carbon\Carbon::parse("{$item->fecha_inicio} {$item->hora_inicio}");
                $fin    = \Carbon\Carbon::parse("{$item->fecha_fin} {$item->hora_fin}");
                if ($fin <= $inicio) {
                    $item->tiempo_horario = '-';
                    return $item;
                }
                $horarios = $horariosMac[$item->idcentro_mac] ?? collect();
                $feriados = collect();
                if (isset($feriadosMac['nacional'])) {
                    $feriados = $feriados->merge($feriadosMac['nacional']);
                }
                if (isset($feriadosMac[$item->idcentro_mac])) {
                    $feriados = $feriados->merge($feriadosMac[$item->idcentro_mac]);
                }
                $feriados = $feriados->pluck('fecha')->toArray();
                $totalMin = 0;
                $cursor = $inicio->copy()->startOfDay();
                while ($cursor->lte($fin)) {
                    $dia = $cursor->format('Y-m-d');
                    $diaIso = $cursor->dayOfWeekIso;
                    if ($diaIso == 7) {
                        $cursor->addDay();
                        continue;
                    }
                    if (in_array($dia, $feriados)) {
                        $cursor->addDay();
                        continue;
                    }
                    $h = $horarios->firstWhere('dia_semana', $diaIso);
                    if (!$h) {
                        $cursor->addDay();
                        continue;
                    }
                    $inicioDia = \Carbon\Carbon::parse("$dia {$h->hora_inicio}");
                    $finDia    = \Carbon\Carbon::parse("$dia {$h->hora_fin}");
                    $desde = $inicioDia->max($inicio);
                    $hasta = $finDia->min($fin);
                    if ($hasta->gt($desde)) {
                        $totalMin += $desde->diffInMinutes($hasta);
                    }
                    $cursor->addDay();
                }
                if ($totalMin <= 0) {
                    $item->tiempo_horario = '-';
                } else {
                    $h = intdiv($totalMin, 60);
                    $m = $totalMin % 60;
                    $item->tiempo_horario = sprintf('%02d:%02d', $h, $m);
                }
                return $item;
            });
        return view('interrupcion.tablas.tb_index', compact('interrupciones'));
    }
    public function create()
    {
        try {
            $centro_mac = $this->centro_mac();
            $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
                ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
                ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
                ->orderBy('m_entidad.abrev_entidad')
                ->get();
            $tipos = TipoIntObs::where('tipo_obs', 'INTERRUPCIÓN')
                ->orderBy('tipo', 'asc')
                ->orderBy('numeracion', 'asc')
                ->orderBy('nom_tipo_int_obs', 'asc')
                ->get();
            $responsables = User::where('idcentro_mac', $centro_mac->idmac)->select('id', 'name')->get();
            $view = view('interrupcion.modals.md_add_interrupcion', compact('entidades', 'tipos', 'responsables'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'responsable'           => 'required|integer|exists:users,id',
            'idcentro_mac'          => 'required|integer|exists:m_centro_mac,idcentro_mac',
            'identidad'             => 'required|integer|exists:m_entidad,identidad',
            'id_tipo_int_obs'       => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'servicio_involucrado'  => 'required|string|max:255',
            'descripcion'           => 'required|string',
            'descripcion_accion'    => 'nullable|string',
            'fecha_inicio'          => 'required|date',
            'hora_inicio'           => 'required|date_format:H:i',
            'fecha_fin'             => 'nullable|date',
            'hora_fin'              => 'nullable|date_format:H:i',
            'estado'                => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
        }
        if ($this->interrupcionBloqueada($request->fecha_inicio)) {
            return $this->respuestaBloqueo();
        }
        try {
            $centro_mac = $this->centro_mac();
            $data = $request->except('accion_correctiva'); //  se ignora completamente
            if ($request->estado === 'ABIERTO') {
                $data['fecha_fin'] = null;
                $data['hora_fin'] = null;
            }
            $data['idcentro_mac'] = $centro_mac->idmac;
            Interrupcion::create($data);
            return response()->json(['message' => 'Interrupción creada exitosamente', 'status' => 201], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }
    }
    public function edit(Request $request)
    {
        try {
            $interrupcion = Interrupcion::find($request->id_interrupcion);
            if (!$interrupcion) {
                return response()->json(['error' => 'Interrupción no encontrada'], 404);
            }
            if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
                return $this->respuestaBloqueo();
            }
            $centro_mac = $this->centro_mac();
            $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
                ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
                ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
                ->orderBy('m_entidad.abrev_entidad')
                ->get();
            $tipos = TipoIntObs::where('tipo_obs', 'INTERRUPCIÓN')
                ->orderBy('tipo', 'asc')
                ->orderBy('numeracion', 'asc')
                ->orderBy('nom_tipo_int_obs', 'asc')
                ->get();
            $responsables = User::where('idcentro_mac', $centro_mac->idmac)->get();
            $view = view('interrupcion.modals.md_edit_interrupcion', compact('interrupcion', 'entidades', 'tipos', 'responsables'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_interrupcion' => 'required|integer|exists:m_interrupcion,id_interrupcion',
            'identidad'       => 'required|integer|exists:m_entidad,identidad',
            'id_tipo_int_obs' => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'descripcion'     => 'required|string',
            'descripcion_accion' => 'nullable|string',
            'fecha_inicio'       => 'required|date',
            'hora_inicio'        => 'required|date_format:H:i',
            'fecha_fin'          => 'nullable|date',
            'hora_fin'           => 'nullable|date_format:H:i',
            'estado'             => 'required|string',
            'responsable'        => 'required|integer|exists:users,id',
            'idcentro_mac'       => 'required|integer|exists:m_centro_mac,idcentro_mac'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
        }
        try {
            $interrupcion = Interrupcion::find($request->id_interrupcion);
            if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
                return $this->respuestaBloqueo();
            }
            if ($this->interrupcionBloqueada($request->fecha_inicio)) {
                return $this->respuestaBloqueo();
            }
            $data = $request->except('accion_correctiva');
            if ($request->estado === 'ABIERTO') {
                $data['fecha_fin'] = null;
                $data['hora_fin'] = null;
            }
            $interrupcion->update($data);
            return response()->json(['message' => 'Interrupción actualizada exitosamente', 'status' => 200], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $interrupcion = Interrupcion::findOrFail($request->id_interrupcion);
            if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
                DB::rollBack();
                return $this->respuestaBloqueo();
            }
            $interrupcion->delete();
            DB::commit();
            return response()->json([
                'message' => 'Interrupción eliminada exitosamente',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
    public function getPersonales(Request $request)
    {
        try {
            $personales = Personal::join('m_personal_modulo as pm', 'pm.num_doc', '=', 'm_personal.NUM_DOC')
                ->where('pm.idcentro_mac', auth()->user()->idcentro_mac)
                ->where('m_personal.identidad', $request->identidad)
                ->select('m_personal.idpersonal', 'm_personal.nombre', 'm_personal.ape_pat', 'm_personal.ape_mat')
                ->distinct()
                ->orderBy('m_personal.nombre', 'asc')
                ->get();
            return response()->json($personales);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los personales'], 500);
        }
    }
    public function subsanarModal(Request $request)
    {
        $interrupcion = Interrupcion::find($request->id_interrupcion);
        if (!$interrupcion) {
            return response()->json(['error' => 'Interrupción no encontrada'], 404);
        }
        if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
            return $this->respuestaBloqueo();
        }
        $centro_mac = $this->centro_mac();
        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();
        $tipos = TipoIntObs::where('tipo_obs', 'INTERRUPCIÓN')
            ->orderBy('tipo', 'asc')
            ->orderBy('numeracion', 'asc')
            ->orderBy('nom_tipo_int_obs', 'asc')
            ->get();
        $view = view('interrupcion.modals.md_subsanar_interrupcion', compact('interrupcion', 'tipos', 'entidades'))->render();
        return response()->json(['html' => $view]);
    }
    public function verModal(Request $request)
    {
        try {
            $interrupcion = Interrupcion::with(['entidad', 'tipoIntObs', 'centroMac', 'responsableUsuario'])
                ->find($request->id_interrupcion);
            if (!$interrupcion) {
                return response()->json(['error' => 'Interrupción no encontrada'], 404);
            }
            $view = view('interrupcion.modals.md_ver_interrupcion', compact('interrupcion'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function subsanarGuardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_interrupcion'    => 'required|integer|exists:m_interrupcion,id_interrupcion',
            'estado'             => 'required|string',
            'fecha_fin'          => 'nullable|date',
            'hora_fin'           => 'nullable|date_format:H:i',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
        }
        try {
            $interrupcion = Interrupcion::find($request->id_interrupcion);
            if (!$interrupcion) {
                return response()->json([
                    'message' => 'Interrupción no encontrada.',
                    'status' => 404
                ], 404);
            }
            if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
                return $this->respuestaBloqueo();
            }
            $interrupcion->update([
                'estado'    => $request->estado,
                'fecha_fin' => $request->fecha_fin,
                'hora_fin'  => $request->hora_fin,
            ]);
            return response()->json([
                'message' => 'Interrupción cerrada correctamente',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 400
            ], 400);
        }
    }
    public function export_excel(Request $request)
    {
        try {
            $user = auth()->user();
            $idmac = $request->idmac;
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;
            $entidad = $request->entidad;
            $tipificacion = $request->tipificacion;
            $estado = $request->estado;
            $revision = $request->revision;
            $query = Interrupcion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac']);
            if ($idmac) {
                $query->where('idcentro_mac', $idmac);
            } elseif (!$user->hasAnyRole(['Administrador', 'Monitor', 'Moderador'])) {
                $query->where('idcentro_mac', $user->idcentro_mac);
            }
            if ($fecha_inicio && $fecha_fin) {
                $query->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin]);
            }
            if (!empty($entidad)) {
                $query->whereHas('entidad', function ($q) use ($entidad) {
                    $q->where('ABREV_ENTIDAD', 'like', "%{$entidad}%")
                        ->orWhere('NOMBRE_ENTIDAD', 'like', "%{$entidad}%");
                });
            }
            if (!empty($tipificacion)) {
                $query->whereHas('tipoIntObs', function ($q) use ($tipificacion) {
                    $q->where('nom_tipo_int_obs', 'like', "%{$tipificacion}%")
                        ->orWhere('tipo', 'like', "%{$tipificacion}%");
                });
            }
            if (!empty($estado)) {
                $query->where('estado', strtoupper($estado));
            }
            if ($revision === 'observado') {
                $query->where('observado', 1);
            } elseif ($revision === 'no_observado') {
                $query->where('observado', 0);
            }
            $interrupciones = $query
                ->with(['centroMac:idcentro_mac,nombre_mac'])
                ->orderBy('fecha_inicio', 'desc')
                ->orderBy('hora_inicio', 'desc')
                ->get()
                ->map(function ($item) {
                    if (!$item->centroMac && $item->idcentro_mac) {
                        $item->centroMac = (object)[
                            'nombre_mac' => DB::table('m_centro_mac')
                                ->where('idcentro_mac', $item->idcentro_mac)
                                ->value('nombre_mac')
                        ];
                    }
                    return $item;
                });
            $macsUnicos = $interrupciones->pluck('centroMac.nombre_mac')->unique()->filter()->values();
            if ($macsUnicos->count() === 1) {
                $nombreMac = $macsUnicos->first();
            } elseif ($idmac) {
                $nombreMac = DB::table('m_centro_mac')
                    ->where('idcentro_mac', $idmac)
                    ->value('nombre_mac') ?? 'Centro MAC';
            } else {
                $nombreMac = 'TODOS LOS CENTROS MAC';
            }
            $rango = '';
            if ($fecha_inicio && $fecha_fin) {
                $rango = '_('
                    . \Carbon\Carbon::parse($fecha_inicio)->format('d-m-Y')
                    . '_a_'
                    . \Carbon\Carbon::parse($fecha_fin)->format('d-m-Y')
                    . ')';
            }
            $nombreMacLimpio = preg_replace('/[\\\/:*?"<>|]/', '_', $nombreMac);
            $nombreMacLimpio = preg_replace('/\s+/', '_', $nombreMacLimpio);
            $nombreArchivo = 'Interrupciones_' . $nombreMacLimpio . $rango . '.xlsx';
            return Excel::download(
                new InterrupcionExport($interrupciones, $nombreMac, $rango),
                $nombreArchivo
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400]);
        }
    }
    public function export_excel_ans(Request $request)
    {
        try {
            $user = auth()->user();
            $idmac = $request->idmac;
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;
            $entidad = $request->entidad;
            $tipificacion = $request->tipificacion;
            $estado = $request->estado;
            $revision = $request->revision;
            $query = Interrupcion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac']);
            if ($idmac) {
                $query->where('idcentro_mac', $idmac);
            } elseif (!$user->hasAnyRole(['Administrador', 'Monitor', 'Moderador'])) {
                $query->where('idcentro_mac', $user->idcentro_mac);
            }
            if ($fecha_inicio && $fecha_fin) {
                $query->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin]);
            }
            if (!empty($entidad)) {
                $query->whereHas('entidad', function ($q) use ($entidad) {
                    $q->where('ABREV_ENTIDAD', 'like', "%{$entidad}%")
                        ->orWhere('NOMBRE_ENTIDAD', 'like', "%{$entidad}%");
                });
            }
            if (!empty($tipificacion)) {
                $query->whereHas('tipoIntObs', function ($q) use ($tipificacion) {
                    $q->where('nom_tipo_int_obs', 'like', "%{$tipificacion}%")
                        ->orWhere('tipo', 'like', "%{$tipificacion}%");
                });
            }
            if (!empty($estado)) {
                $query->where('estado', strtoupper($estado));
            }
            if ($revision === 'observado') {
                $query->where('observado', 1);
            } elseif ($revision === 'no_observado') {
                $query->where('observado', 0);
            }
            $interrupciones = $query
                ->with(['centroMac:idcentro_mac,nombre_mac'])
                ->orderBy('fecha_inicio', 'desc')
                ->orderBy('hora_inicio', 'desc')
                ->get()
                ->map(function ($item) {
                    if (!$item->centroMac && $item->idcentro_mac) {
                        $item->centroMac = (object)[
                            'nombre_mac' => DB::table('m_centro_mac')
                                ->where('idcentro_mac', $item->idcentro_mac)
                                ->value('nombre_mac')
                        ];
                    }
                    return $item;
                });
            $macsUnicos = $interrupciones->pluck('centroMac.nombre_mac')->unique()->filter()->values();
            if ($macsUnicos->count() === 1) {
                $nombreMac = $macsUnicos->first();
            } elseif ($idmac) {
                $nombreMac = DB::table('m_centro_mac')
                    ->where('idcentro_mac', $idmac)
                    ->value('nombre_mac') ?? 'Centro MAC';
            } else {
                $nombreMac = 'TODOS LOS CENTROS MAC';
            }
            return (new \App\Exports\InterrupcionAnsExport(
                $interrupciones,
                $nombreMac,
                $fecha_inicio,
                $fecha_fin
            ))->download();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }
    }
    public function observarModal(Request $request)
    {
        try {
            $interrupcion = Interrupcion::with(['usuarioObservador'])->find($request->id_interrupcion);
            if (!$interrupcion) {
                return response()->json(['error' => 'Interrupción no encontrada'], 404);
            }
            $user = auth()->user();
            if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
                return $this->respuestaBloqueo();
            }
            $soloLectura = !$user->hasAnyRole(['Administrador', 'Moderador']);
            $puedeCorregir = $user->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador']);
            $view = view('interrupcion.modals.md_observar_interrupcion', compact('interrupcion', 'soloLectura', 'puedeCorregir'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function observarGuardar(Request $request)
    {
        try {
            $user = auth()->user();
            $request->merge([
                'observado' => $request->has('observado') ? 1 : 0,
                'corregido' => $request->has('corregido') ? 1 : 0,
            ]);
            $validator = Validator::make($request->all(), [
                'id_interrupcion' => 'required|integer|exists:m_interrupcion,id_interrupcion',
                'retroalimentacion' => 'nullable|string|max:1000',
                'observado' => 'required|boolean',
                'corregido' => 'boolean'
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
            }
            $interrupcion = Interrupcion::find($request->id_interrupcion);
            if (!$interrupcion) {
                return response()->json(['error' => 'Interrupción no encontrada'], 404);
            }
            if ($this->interrupcionBloqueada($interrupcion->fecha_inicio)) {
                return $this->respuestaBloqueo();
            }
            if ($user->hasAnyRole(['Administrador', 'Moderador'])) {
                $datos = [
                    'observado' => $request->observado,
                    'retroalimentacion' => $request->retroalimentacion,
                    'observado_por' => $request->observado ? $user->id : null,
                    'fecha_observado' => $request->observado ? now() : null,
                ];
                if (!$request->observado) {
                    $datos['corregido'] = 0;
                    $datos['corregido_por'] = null;
                    $datos['fecha_corregido'] = null;
                }
                $interrupcion->update($datos);
            }
            if ($user->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador'])) {
                if ($interrupcion->observado) {
                    $interrupcion->update([
                        'corregido' => $request->corregido,
                        'corregido_por' => $request->corregido ? $user->id : null,
                        'fecha_corregido' => $request->corregido ? now() : null,
                    ]);
                }
            }
            return response()->json(['message' => 'Cambios guardados correctamente', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400]);
        }
    }
    private function consultaBaseInterrupcionesAns(Request $request)
    {
        $query = Interrupcion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac']);
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_fin);
        }
        if ($request->filled('tipificacion')) {
            $tipificacion = $request->tipificacion;
            $query->whereHas('tipoIntObs', function ($q) use ($tipificacion) {
                $q->where(function ($sub) use ($tipificacion) {
                    $sub->where('nom_tipo_int_obs', 'like', "%{$tipificacion}%")
                        ->orWhere('tipo', 'like', "%{$tipificacion}%");
                });
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', strtoupper($request->estado));
        }
        if ($request->revision === 'observado') {
            $query->where('observado', 1);
        } elseif ($request->revision === 'no_observado') {
            $query->where('observado', 0);
        }
        return $query->whereNotNull('identidad');
    }
    private function comprobarPermisoAnsMasivo(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasAnyRole(['Administrador', 'Monitor', 'Moderador']), 403, 'No autorizado para exportar todos los Centros MAC.');
    }
    private function directorioAnsMasivo(string $token): string
    {
        abort_unless(preg_match('/^[a-f0-9]{32}$/', $token), 404);
        return storage_path('app/tmp/ans_masivo/' . $token);
    }
    private function leerEstadoAnsMasivo(string $token): array
    {
        $archivo = $this->directorioAnsMasivo($token) . '/estado.json';
        abort_unless(is_file($archivo), 404, 'Exportación no encontrada.');
        $estado = json_decode(File::get($archivo), true);
        abort_unless(is_array($estado) && ($estado['usuario'] ?? null) === (int) auth()->id(), 403);
        return $estado;
    }
    private function guardarEstadoAnsMasivo(string $token, array $estado): void
    {
        File::put($this->directorioAnsMasivo($token) . '/estado.json', json_encode($estado, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), true);
    }
    public function iniciarExportacionAns(Request $request)
    {
        $this->comprobarPermisoAnsMasivo();
        $datos = $request->validate(['fecha_inicio' => 'nullable|date', 'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio', 'tipificacion' => 'nullable|string|max:255', 'estado' => 'nullable|string|max:100', 'revision' => 'nullable|in:observado,no_observado']);
        $consulta = $this->consultaBaseInterrupcionesAns(new Request($datos));
        $ids = (clone $consulta)->whereNotNull('identidad')->select('identidad')->distinct()->orderBy('identidad')->pluck('identidad')->map(fn($id) => (int) $id)->all();
        if (!$ids) return response()->json(['error' => 'No se encontraron interrupciones.'], 404);
        $token = bin2hex(random_bytes(16));
        File::ensureDirectoryExists($this->directorioAnsMasivo($token));
        $this->guardarEstadoAnsMasivo($token, ['usuario' => (int) auth()->id(), 'filtros' => $datos, 'entidades' => $ids, 'procesadas' => [], 'terminado' => false, 'creado' => now()->toDateTimeString()]);
        return response()->json(['token' => $token, 'total' => count($ids)]);
    }
    public function procesarEntidadAns(Request $request)
    {
        $this->comprobarPermisoAnsMasivo();
        $datos = $request->validate(['token' => 'required|string|size:32', 'indice' => 'required|integer|min:0']);
        $token = $datos['token'];
        $estado = $this->leerEstadoAnsMasivo($token);
        abort_if($estado['terminado'], 409, 'La exportación ya terminó.');
        $indice = (int) $datos['indice'];
        abort_unless(isset($estado['entidades'][$indice]), 422, 'Índice no válido.');
        abort_unless($indice === count($estado['procesadas']), 409, 'Las entidades deben procesarse en orden.');
        $id = $estado['entidades'][$indice];
        try {
            $consulta = $this->consultaBaseInterrupcionesAns(new Request($estado['filtros']));
            $registros = $consulta->where('identidad', $id)->orderBy('fecha_inicio', 'desc')->orderBy('hora_inicio', 'desc')->get();
            if ($registros->isEmpty()) throw new \RuntimeException('No hay interrupciones para la entidad ' . $id);
            $nombre = DB::table('m_entidad')->where('identidad', $id)->value('nombre_entidad') ?: 'ENTIDAD_' . $id;
            $limpio = trim(preg_replace('/[^\pL\pN._-]+/u', '_', $nombre), '_');
            $archivo = ($limpio ?: 'ENTIDAD') . '_' . $id . '.xlsx';
            $ruta = $this->directorioAnsMasivo($token) . '/' . $archivo;
            (new \App\Exports\InterrupcionAnsExport($registros, 'TODOS LOS CENTROS MAC', $estado['filtros']['fecha_inicio'] ?? null, $estado['filtros']['fecha_fin'] ?? null))->saveToPath($ruta);
            unset($registros);
            $estado['procesadas'][] = $archivo;
            $this->guardarEstadoAnsMasivo($token, $estado);
            return response()->json(['procesadas' => count($estado['procesadas']), 'total' => count($estado['entidades']), 'entidad' => $nombre]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Error generando la entidad ' . $id . ': ' . $e->getMessage()], 500);
        }
    }
    public function finalizarExportacionAns(Request $request)
    {
        $this->comprobarPermisoAnsMasivo();
        $datos = $request->validate(['token' => 'required|string|size:32']);
        $token = $datos['token'];
        $estado = $this->leerEstadoAnsMasivo($token);
        if ($estado['terminado']) return response()->json(['descarga' => route('interrupcion.descargar_exportacion_ans', ['token' => $token])]);
        abort_unless(count($estado['procesadas']) === count($estado['entidades']), 409, 'Aún faltan entidades por procesar.');
        $directorio = $this->directorioAnsMasivo($token);
        $zip = new ZipArchive();
        $ruta = $directorio . '/INTERRUPCIONES_ANS.zip';
        if ($zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('No se pudo crear el ZIP.');
        try {
            foreach ($estado['procesadas'] as $nombre) {
                $archivo = $directorio . '/' . $nombre;
                if (!is_file($archivo) || !$zip->addFile($archivo, $nombre)) throw new \RuntimeException('No se pudo incorporar ' . $nombre . ' al ZIP.');
            }
            if (!$zip->close()) throw new \RuntimeException('No se pudo cerrar el ZIP.');
            $estado['terminado'] = true;
            $this->guardarEstadoAnsMasivo($token, $estado);
            foreach ($estado['procesadas'] as $nombre) File::delete($directorio . '/' . $nombre);
            return response()->json(['descarga' => route('interrupcion.descargar_exportacion_ans', ['token' => $token])]);
        } catch (\Throwable $e) {
            $zip->close();
            report($e);
            return response()->json(['error' => 'No se pudo finalizar el ZIP.'], 500);
        }
    }
    public function descargarExportacionAns(string $token)
    {
        $this->comprobarPermisoAnsMasivo();
        $estado = $this->leerEstadoAnsMasivo($token);
        abort_unless($estado['terminado'], 409, 'El ZIP todavía no está listo.');
        $archivo = $this->directorioAnsMasivo($token) . '/INTERRUPCIONES_ANS.zip';
        abort_unless(is_file($archivo), 404);
        return response()->download($archivo, 'INTERRUPCIONES_ANS_' . now()->format('Ymd_His') . '.zip');
    }
}
