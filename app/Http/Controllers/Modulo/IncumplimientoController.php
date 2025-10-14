<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Observacion;
use App\Models\Entidad;
use App\Models\TipoIntObs;
use App\Models\User;
use App\Exports\IncumplimientosExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
    public function index()
    {
        $centros_mac = DB::table('db_centros_mac.m_centro_mac')
            ->select('idcentro_mac', 'nombre_mac')
            ->orderBy('nombre_mac')
            ->get();

        $centro_mac = $this->centro_mac();

        return view('incumplimiento.index', compact('centros_mac', 'centro_mac'));
    }
    public function tb_index(Request $request)
    {
        $user = auth()->user();

        $query = Observacion::with([
            'entidad:IDENTIDAD,NOMBRE_ENTIDAD,ABREV_ENTIDAD',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion,nom_tipo_int_obs',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ])->whereHas('tipoIntObs', function ($q) {
            $q->where('tipo_obs', 'INCUMPLIMIENTO');
        });

        // 🔹 Filtro por centro MAC
        if ($request->filled('idmac')) {
            $query->where('idcentro_mac', $request->idmac);
        } elseif (!$user->hasRole(['Administrador|Moderador'])) {
            $query->where('idcentro_mac', $user->idcentro_mac);
        }

        // 🔹 Filtro por fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_observacion', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $incumplimientos = $query->orderBy('fecha_observacion', 'desc')->get();

        return view('incumplimiento.tablas.tb_index', compact('incumplimientos'));
    }


    public function create()
    {
        $centro_mac = $this->centro_mac();

        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();

        $tipos = TipoIntObs::where('tipo_obs', 'INCUMPLIMIENTO')->get();

        $view = view('incumplimiento.modals.md_add_incumplimiento', compact('entidades', 'tipos'))->render();
        return response()->json(['html' => $view]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identidad'           => 'required|integer|exists:m_entidad,identidad',
            'id_tipo_int_obs'     => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'descripcion'         => 'required|string',
            'descripcion_accion'  => 'nullable|string',
            'fecha_observacion'   => 'required|date',
            'fecha_solucion'      => 'nullable|date',
            'estado'              => 'nullable|string|in:ABIERTO,CERRADO',
            'responsable'         => 'nullable|integer|exists:users,id',
            'idcentro_mac'        => 'required|integer|exists:m_centro_mac,idcentro_mac',
            'archivo'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422]);
        }

        $data = $request->except('archivo');

        // 👇 Forzar siempre ABIERTO si no es Monitor o Administrador
        if (!auth()->user()->hasRole(['Administrador', 'Monitor','Moderador'])) {
            $data['estado'] = 'ABIERTO';
            $data['fecha_solucion'] = null;
        }

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            $nombreArchivo = 'incumplimiento_' . now()->format('Ymd_His') . '.' . $extension;
            $ruta = 'archivo_inc/';
            $file->move(public_path($ruta), $nombreArchivo);
            $data['archivo'] = $ruta . $nombreArchivo;
        }

        Observacion::create($data);

        return response()->json(['message' => 'Incumplimiento registrado exitosamente', 'status' => 201]);
    }

    public function edit(Request $request)
    {
        $incumplimiento = Observacion::findOrFail($request->id_observacion);

        $centro_mac = $this->centro_mac();

        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();

        $tipos = TipoIntObs::where('tipo_obs', 'INCUMPLIMIENTO')->get();

        $view = view('incumplimiento.modals.md_edit_incumplimiento', compact('incumplimiento', 'entidades', 'tipos'))->render();
        return response()->json(['html' => $view]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_observacion'      => 'required|integer|exists:m_observacion,id_observacion',
            'identidad'           => 'required|integer|exists:m_entidad,identidad',
            'id_tipo_int_obs'     => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'descripcion'         => 'required|string',
            'descripcion_accion'  => 'nullable|string',
            'fecha_observacion'   => 'required|date',
            'fecha_solucion'      => 'nullable|date',
            'estado'              => 'nullable|string|in:ABIERTO,CERRADO',
            'archivo'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422]);
        }

        $user = auth()->user();
        $incumplimiento = Observacion::findOrFail($request->id_observacion);

        // 🚫 Bloqueo si está cerrado y el usuario NO es Admin o Monitor
        if ($incumplimiento->estado === 'CERRADO' && !$user->hasRole(['Administrador', 'Monitor','Moderador','Especialista TIC'])) {
            return response()->json([
                'status' => 403,
                'message' => 'No puede modificar un incumplimiento cerrado.'
            ]);
        }

        $data = $request->except('archivo');

        // 📅 Ajustar fechas según estado
        if ($data['estado'] === 'CERRADO' && empty($data['fecha_solucion'])) {
            $data['fecha_solucion'] = now()->toDateString();
        }

        if ($data['estado'] === 'ABIERTO') {
            $data['fecha_solucion'] = null;
        }

        // 📂 Manejo del archivo adjunto
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            $nombreArchivo = 'incumplimiento_' . now()->format('Ymd_His') . '.' . $extension;
            $ruta = 'archivo_inc/';
            $file->move(public_path($ruta), $nombreArchivo);
            $data['archivo'] = $ruta . $nombreArchivo;

            if ($incumplimiento->archivo && file_exists(public_path($incumplimiento->archivo))) {
                unlink(public_path($incumplimiento->archivo));
            }
        }

        $incumplimiento->update($data);

        return response()->json(['message' => 'Incidente Operativo actualizado exitosamente', 'status' => 200]);
    }


    public function destroy(Request $request)
    {
        $user = auth()->user();
        $incumplimiento = Observacion::findOrFail($request->id_observacion);

        // 🚫 No permitir eliminar si está cerrado y el usuario no es Admin/Monitor
        if ($incumplimiento->estado === 'CERRADO' && !$user->hasRole(['Administrador', 'Monitor','Moderador'])) {
            return response()->json([
                'status' => 403,
                'message' => 'No puede eliminar un incumplimiento cerrado.'
            ]);
        }

        // 🗑️ Eliminar archivo asociado si existe
        if ($incumplimiento->archivo && file_exists(public_path($incumplimiento->archivo))) {
            unlink(public_path($incumplimiento->archivo));
        }

        $incumplimiento->delete();

        return response()->json([
            'message' => 'Incidente Operativo eliminado exitosamente',
            'status' => 200
        ]);
    }

    public function ver(Request $request)
    {
        $incumplimiento = Observacion::with('entidad', 'tipoIntObs', 'centroMac', 'responsableUsuario')
            ->findOrFail($request->id_observacion);

        $view = view('incumplimiento.modals.md_ver_incumplimiento', compact('incumplimiento'))->render();
        return response()->json(['html' => $view]);
    }

    public function export_excel()
    {
        $incumplimientos = Observacion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac'])
            ->where('idcentro_mac', auth()->user()->idcentro_mac)
            ->whereHas('tipoIntObs', function ($q) {
                $q->where('tipo_obs', 'INCUMPLIMIENTO');
            })
            ->orderBy('fecha_observacion', 'desc')
            ->get();

        $nombreMac = auth()->user()->centroMac->nombre_mac ?? 'Centro MAC';
        $nombreMes = ucfirst(\Carbon\Carbon::now()->monthName);

        return Excel::download(
            new IncumplimientosExport($incumplimientos, $nombreMac, $nombreMes),
            'Incumplimientos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function cerrarGuardar(Request $request)
    {
        // 🔐 Solo MONITOR puede cerrar
        if (!auth()->user()->hasRole(['Monitor', 'Administrador','Moderador'])) {
            return response()->json([
                'status' => 403,
                'message' => 'No tiene permisos para cerrar incumplimientos.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_observacion' => 'required|integer|exists:m_observacion,id_observacion',
            'estado' => 'required|string|in:CERRADO,ABIERTO',
            'fecha_solucion' => 'nullable|date',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422]);
        }

        $incumplimiento = Observacion::findOrFail($request->id_observacion);

        $data = $request->only(['estado', 'fecha_solucion']);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $nombreArchivo = 'cierre_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $ruta = 'archivo_incumplimientos/';
            $file->move(public_path($ruta), $nombreArchivo);
            $data['archivo'] = $ruta . $nombreArchivo;
        }

        $incumplimiento->update($data);

        return response()->json([
            'status' => 200,
            'message' => 'Incumplimiento cerrado correctamente.'
        ]);
    }
}
