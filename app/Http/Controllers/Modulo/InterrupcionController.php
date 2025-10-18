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


class InterrupcionController extends Controller
{
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

        // Listado completo de MAC (solo para admins y moderadores)
        $centros_mac = DB::table('m_centro_mac')
            ->select('idcentro_mac as IDCENTRO_MAC', 'nombre_mac as NOMBRE_MAC')
            ->orderBy('nombre_mac')
            ->get();

        // Nombre del MAC del usuario actual
        $nombre_mac_usuario = DB::table('m_centro_mac')
            ->where('idcentro_mac', $user->idcentro_mac)
            ->value('nombre_mac');

        return view('interrupcion.index', compact('centros_mac', 'nombre_mac_usuario'));
    }


    public function tb_index(Request $request)
    {
        $user = auth()->user();
        $idmac = $request->idmac;
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $query = Interrupcion::with([
            'entidad:IDENTIDAD,NOMBRE_ENTIDAD,ABREV_ENTIDAD',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion,nom_tipo_int_obs',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ]);

        // 🔹 Filtro por MAC
        if ($idmac) {
            $query->where('idcentro_mac', $idmac);
        } else {
            if (!$user->hasAnyRole(['Administrador', 'Monitor', 'Moderador'])) {
                $query->where('idcentro_mac', $user->idcentro_mac);
            }
        }

        // 🔹 Filtro por fechas
        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin]);
        }

        $interrupciones = $query->orderBy('fecha_inicio', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

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

        try {
            $centro_mac = $this->centro_mac();
            $data = $request->except('accion_correctiva'); // ✅ se ignora completamente

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
            $data = $request->except('accion_correctiva'); // ✅ ignorado

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
            $interrupcion->delete();

            DB::commit();
            return response()->json(['message' => 'Interrupción eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
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

            $interrupcion->update([
                'estado'    => $request->estado,
                'fecha_fin' => $request->fecha_fin,
                'hora_fin'  => $request->hora_fin,
            ]);

            return response()->json(['message' => 'Interrupción cerrada correctamente', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400]);
        }
    }

    public function export_excel(Request $request)
    {
        try {
            $user = auth()->user();
            $idmac = $request->idmac;
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;

            $query = Interrupcion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac']);

            if ($idmac) {
                $query->where('idcentro_mac', $idmac);
            } elseif (!$user->hasAnyRole(['Administrador', 'Monitor', 'Moderador'])) {
                $query->where('idcentro_mac', $user->idcentro_mac);
            }

            if ($fecha_inicio && $fecha_fin) {
                $query->whereBetween('fecha_inicio', [$fecha_inicio, $fecha_fin]);
            }

            $interrupciones = $query->orderBy('fecha_inicio', 'desc')->get();

            $nombreMac = $idmac
                ? DB::table('m_centro_mac')->where('idcentro_mac', $idmac)->value('nombre_mac')
                : ($user->centroMac->nombre_mac ?? 'Centro MAC');

            $rango = '';
            if ($fecha_inicio && $fecha_fin) {
                $rango = '_(' . \Carbon\Carbon::parse($fecha_inicio)->format('d-m-Y') . '_a_' . \Carbon\Carbon::parse($fecha_fin)->format('d-m-Y') . ')';
            }

            $nombreArchivo = 'Interrupciones_' . str_replace(' ', '_', $nombreMac) . $rango . '.xlsx';

            return Excel::download(
                new InterrupcionExport($interrupciones, $nombreMac, $rango),
                $nombreArchivo
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400]);
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

            // 🔹 Detecta tipo de acceso
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

            // 🧠 1️⃣ Si el usuario es ADMIN o MODERADOR → puede observar o quitar observación
            if ($user->hasAnyRole(['Administrador', 'Moderador'])) {
                $datos = [
                    'observado' => $request->observado,
                    'retroalimentacion' => $request->retroalimentacion,
                    'observado_por' => $request->observado ? $user->id : null,
                    'fecha_observado' => $request->observado ? now() : null,
                ];

                // 👇 Si se desmarca "observado", se limpian también los campos de corrección
                if (!$request->observado) {
                    $datos['corregido'] = 0;
                    $datos['corregido_por'] = null;
                    $datos['fecha_corregido'] = null;
                }

                $interrupcion->update($datos);
            }

            // 🧠 2️⃣ Si el usuario es SUPERVISOR o ESPECIALISTA TIC → puede marcar corrección
            if ($user->hasAnyRole(['Supervisor', 'Especialista TIC', 'Moderador', 'Administrador'])) {
                // Solo puede corregir si ya fue observado
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
}
