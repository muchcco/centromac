<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Observacion;
use App\Models\Entidad;
use App\Models\TipoIntObs;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncumplimientosExport;

class IncumplimientoController extends Controller
{
    private function centro_mac()
    {
        $us_id = auth()->user()->idcentro_mac;

        $user = User::join('db_centros_mac.m_centro_mac', 'db_centros_mac.m_centro_mac.idcentro_mac', '=', 'users.idcentro_mac')
            ->where('db_centros_mac.m_centro_mac.idcentro_mac', $us_id)
            ->select('db_centros_mac.m_centro_mac.idcentro_mac', 'db_centros_mac.m_centro_mac.nombre_mac')
            ->first();

        return (object)[
            'idmac' => $user->idcentro_mac ?? null,
            'name_mac' => $user->nombre_mac ?? 'No asignado'
        ];
    }

    // 🧭 INDEX PRINCIPAL
    public function index()
    {
        $user = auth()->user();

        // 🔹 Listado general de centros MAC
        $centros_mac = DB::table('db_centros_mac.m_centro_mac')
            ->select('idcentro_mac', 'nombre_mac')
            ->orderBy('nombre_mac')
            ->get();

        // 🔹 Entidades dinámicas según rol
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

        // 🔹 Tipificaciones solo de tipo INCUMPLIMIENTO
        $tipos = TipoIntObs::where('tipo_obs', 'INCUMPLIMIENTO')
            ->select('id_tipo_int_obs', 'tipo', 'numeracion', 'nom_tipo_int_obs')
            ->orderBy('tipo', 'asc')
            ->orderBy('numeracion', 'asc')
            ->get();

        // 🔹 Centro MAC del usuario (solo si no es Administrador o Moderador)
        $centro_mac = null;
        if (!$user->hasAnyRole(['Administrador', 'Moderador'])) {
            $centro_mac = DB::table('db_centros_mac.m_centro_mac')
                ->select('idcentro_mac as idmac', 'nombre_mac as name_mac')
                ->where('idcentro_mac', $user->idcentro_mac)
                ->first();
        }

        // 🔹 Retornar vista con todas las variables necesarias
        return view('incumplimiento.index', compact('centros_mac', 'entidades', 'tipos', 'centro_mac'));
    }


    // 📋 TABLA PRINCIPAL CON FILTROS
    public function tb_index(Request $request)
    {
        $user = auth()->user();

        $query = Observacion::with([
            'entidad:IDENTIDAD,NOMBRE_ENTIDAD,ABREV_ENTIDAD',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion,nom_tipo_int_obs',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ])->whereHas('tipoIntObs', fn($q) => $q->where('tipo_obs', 'INCUMPLIMIENTO'));

        // 🔹 Filtros dinámicos
        if ($request->idmac) {
            $query->where('idcentro_mac', $request->idmac);
        } elseif (!$user->hasAnyRole(['Administrador', 'Moderador'])) {
            $query->where('idcentro_mac', $user->idcentro_mac);
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_observacion', [$request->fecha_inicio, $request->fecha_fin]);
        }

        if ($request->entidad) {
            $query->whereHas('entidad', function ($q) use ($request) {
                $q->where('ABREV_ENTIDAD', 'like', "%{$request->entidad}%")
                    ->orWhere('NOMBRE_ENTIDAD', 'like', "%{$request->entidad}%");
            });
        }

        if ($request->tipificacion) {
            $query->whereHas('tipoIntObs', function ($q) use ($request) {
                $q->where('nom_tipo_int_obs', 'like', "%{$request->tipificacion}%");
            });
        }

        if ($request->estado) {
            $query->where('estado', strtoupper($request->estado));
        }

        if ($request->revision === 'observado') {
            $query->where('observado', 1);
        } elseif ($request->revision === 'no_observado') {
            $query->where('observado', 0);
        }

        $incumplimientos = $query->orderBy('fecha_observacion', 'desc')->get();

        return view('incumplimiento.tablas.tb_index', compact('incumplimientos'));
    }

    // 🧱 CREAR NUEVO
    public function create()
    {
        try {
            $centro_mac = $this->centro_mac();

            $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
                ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
                ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
                ->orderBy('m_entidad.abrev_entidad')
                ->get();

            $tipos = TipoIntObs::where('tipo_obs', 'INCUMPLIMIENTO')->get();

            $responsables = User::where('idcentro_mac', $centro_mac->idmac)
                ->select('id', 'name')
                ->get();

            $view = view('incumplimiento.modals.md_add_incumplimiento', compact('entidades', 'tipos', 'responsables'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 💾 GUARDAR NUEVO
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcentro_mac' => 'required|integer|exists:m_centro_mac,idcentro_mac',
            'identidad' => 'required|integer|exists:m_entidad,identidad',
            'id_tipo_int_obs' => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'descripcion' => 'required|string',
            'fecha_observacion' => 'required|date',
            'estado' => 'required|string',
        ]);

        if ($validator->fails())
            return response()->json(['message' => $validator->errors(), 'status' => 422]);

        try {
            $data = $request->except('archivo');

            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $nombre = 'incumplimiento_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $ruta = 'archivo_inc/';
                if (!file_exists(public_path($ruta))) mkdir(public_path($ruta), 0755, true);
                $file->move(public_path($ruta), $nombre);
                $data['archivo'] = $ruta . $nombre;
            }

            Observacion::create($data);

            return response()->json(['message' => 'Incumplimiento registrado correctamente', 'status' => 201]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ✏️ EDITAR
    public function edit(Request $request)
    {
        try {
            $incumplimiento = Observacion::find($request->id_observacion);
            if (!$incumplimiento)
                return response()->json(['error' => 'Incidente Operativo no encontrado'], 404);

            $centro_mac = $this->centro_mac();

            $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
                ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
                ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
                ->orderBy('m_entidad.abrev_entidad')
                ->get();

            $tipos = TipoIntObs::where('tipo_obs', 'INCUMPLIMIENTO')->get();

            $view = view('incumplimiento.modals.md_edit_incumplimiento', compact('incumplimiento', 'entidades', 'tipos'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 👁️ VER DETALLE
    public function ver(Request $request)
    {
        try {
            $incumplimiento = Observacion::with(['entidad', 'tipoIntObs', 'centroMac', 'responsableUsuario'])
                ->findOrFail($request->id_observacion);

            $view = view('incumplimiento.modals.md_ver_incumplimiento', compact('incumplimiento'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 🚨 MODAL DE OBSERVACIÓN (abrir)
    public function observarModal(Request $request)
    {
        try {
            $incumplimiento = Observacion::findOrFail($request->id_observacion);
            $view = view('incumplimiento.modals.md_observar_incumplimiento', compact('incumplimiento'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cargar modal: ' . $e->getMessage()], 500);
        }
    }

    // 🚨 OBSERVAR / CORREGIR (Guardar)
    public function observarGuardar(Request $request)
    {
        try {
            $user = auth()->user();

            // 🔹 Validación estricta
            $validator = Validator::make($request->all(), [
                'id_observacion' => 'required|integer|exists:m_observacion,id_observacion',
                'retroalimentacion' => 'nullable|string|max:1000',
                'observado' => 'required|in:0,1',
                'corregido' => 'nullable|in:0,1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first()
                ]);
            }

            $incumplimiento = Observacion::find($request->id_observacion);
            if (!$incumplimiento) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Incidente Operativo no encontrado'
                ]);
            }

            // 🟨 ADMIN / MODERADOR → pueden observar o quitar observación
            if ($user->hasAnyRole(['Administrador', 'Moderador'])) {
                if ((int) $request->observado === 1) {
                    // ✅ Marcar como observado
                    $incumplimiento->update([
                        'observado' => 1,
                        'retroalimentacion' => $request->retroalimentacion,
                        'observado_por' => $user->id,
                        'fecha_observado' => now(),
                    ]);
                } else {
                    // ❌ Quitar observación → limpiar todo
                    $incumplimiento->update([
                        'observado' => 0,
                        'retroalimentacion' => null,
                        'observado_por' => null,
                        'fecha_observado' => null,
                        'corregido' => 0,
                        'corregido_por' => null,
                        'fecha_corregido' => null,
                    ]);
                }
            }

            // 🟩 Vuelve a refrescar el modelo (para leer los cambios recién guardados)
            $incumplimiento->refresh();

            // 🟩 SUPERVISOR / TIC → pueden corregir solo si está observado
            if ($user->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador']) && $incumplimiento->observado == 1) {
                $incumplimiento->update([
                    'corregido' => (int) $request->corregido,
                    'corregido_por' => $request->corregido ? $user->id : null,
                    'fecha_corregido' => $request->corregido ? now() : null,
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Cambios guardados correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }

    // 📤 EXPORTAR CON FILTROS
    public function export_excel(Request $request)
    {
        try {
            $user = auth()->user();

            $query = Observacion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac'])
                ->whereHas('tipoIntObs', fn($q) => $q->where('tipo_obs', 'INCUMPLIMIENTO'));

            if ($request->idmac) {
                $query->where('idcentro_mac', $request->idmac);
            } elseif (!$user->hasAnyRole(['Administrador', 'Moderador'])) {
                $query->where('idcentro_mac', $user->idcentro_mac);
            }

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_observacion', [$request->fecha_inicio, $request->fecha_fin]);
            }

            if ($request->entidad) {
                $query->whereHas('entidad', function ($q) use ($request) {
                    $q->where('ABREV_ENTIDAD', 'like', "%{$request->entidad}%")
                        ->orWhere('NOMBRE_ENTIDAD', 'like', "%{$request->entidad}%");
                });
            }

            if ($request->tipificacion) {
                $query->whereHas('tipoIntObs', function ($q) use ($request) {
                    $q->where('nom_tipo_int_obs', 'like', "%{$request->tipificacion}%");
                });
            }

            if ($request->estado) {
                $query->where('estado', strtoupper($request->estado));
            }

            if ($request->revision === 'observado') {
                $query->where('observado', 1);
            } elseif ($request->revision === 'no_observado') {
                $query->where('observado', 0);
            }

            $incumplimientos = $query->orderBy('fecha_observacion', 'desc')->get();

            $nombreMac = auth()->user()->centroMac->nombre_mac ?? 'Centro MAC';
            $rango = '';
            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $rango = '_(' . \Carbon\Carbon::parse($request->fecha_inicio)->format('d-m-Y') . '_a_' .
                    \Carbon\Carbon::parse($request->fecha_fin)->format('d-m-Y') . ')';
            }

            $nombreArchivo = 'Incumplimientos_' . str_replace(' ', '_', $nombreMac) . $rango . '.xlsx';

            return Excel::download(
                new IncumplimientosExport($incumplimientos, $nombreMac, $rango),
                $nombreArchivo
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 💾 ACTUALIZAR
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_observacion' => 'required|integer',
                'identidad' => 'required|integer',
                'id_tipo_int_obs' => 'required|integer',
                'descripcion' => 'required|string',
                'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => $validator->errors()]);

            $incumplimiento = Observacion::findOrFail($request->id_observacion);
            $data = $request->except('archivo');

            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $nombre = 'incumplimiento_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $ruta = 'archivo_inc/';
                $file->move(public_path($ruta), $nombre);
                $data['archivo'] = $ruta . $nombre;
                if ($incumplimiento->archivo && file_exists(public_path($incumplimiento->archivo))) unlink(public_path($incumplimiento->archivo));
            }

            $incumplimiento->update($data);

            return response()->json(['status' => 200, 'message' => 'Incidente Operativo actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $incumplimiento = Observacion::findOrFail($request->id_observacion);

            if ($incumplimiento->archivo && file_exists(public_path($incumplimiento->archivo))) {
                unlink(public_path($incumplimiento->archivo));
            }

            $incumplimiento->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Incidente Operativo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}
