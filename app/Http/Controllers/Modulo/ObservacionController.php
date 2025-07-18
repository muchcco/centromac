<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Observacion;
use App\Models\Entidad;
use App\Models\TipoIntObs;
use App\Models\User;
use App\Exports\ObservacionesExport;
use Maatwebsite\Excel\Facades\Excel;

class ObservacionController extends Controller
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
        return view('observacion.index');
    }

    public function tb_index()
    {
        $user = auth()->user();

        $query = Observacion::with([
            'entidad:IDENTIDAD,NOMBRE_ENTIDAD,ABREV_ENTIDAD',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion,nom_tipo_int_obs',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ]);

        // Si NO tiene el rol de Administrador o Moderador, filtramos por su centro MAC
        if (!$user->hasRole(['Administrador', 'Moderador'])) {
            $query->where('idcentro_mac', $user->idcentro_mac);
        }

        $observaciones = $query->orderBy('fecha_observacion', 'desc')->get();
        //dd($observaciones);
        return view('observacion.tablas.tb_index', compact('observaciones'));
    }


    public function create()
    {
        $centro_mac = $this->centro_mac();

        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();

        $tipos = TipoIntObs::where('tipo_obs', 'OBSERVACIÓN')->get();

        $view = view('observacion.modals.md_add_observacion', compact('entidades', 'tipos'))->render();
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
            'estado'              => 'required|string',
            'responsable'         => 'nullable|integer|exists:users,id',
            'idcentro_mac'        => 'required|integer|exists:m_centro_mac,idcentro_mac',
            'archivo'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422]);
        }

        $data = $request->except('archivo'); // todo menos archivo

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            $nombreArchivo = 'observacion_' . now()->format('Ymd_His') . '.' . $extension;
            $ruta = 'archivo_obs_int/';
            $file->move(public_path($ruta), $nombreArchivo);
            $data['archivo'] = $ruta . $nombreArchivo;
        }

        Observacion::create($data);

        return response()->json(['message' => 'Observación creada exitosamente', 'status' => 201]);
    }

    public function edit(Request $request)
    {
        $observacion = Observacion::findOrFail($request->id_observacion);

        $centro_mac = $this->centro_mac();

        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();

        $tipos = TipoIntObs::where('tipo_obs', 'OBSERVACIÓN')->get();

        $view = view('observacion.modals.md_edit_observacion', compact('observacion', 'entidades', 'tipos'))->render();

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
            'estado'              => 'required|string',
            'archivo'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422]);
        }

        $observacion = Observacion::findOrFail($request->id_observacion);
        $data = $request->except('archivo');

        if ($request->hasFile('archivo')) {
            // Procesar nuevo archivo
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            $nombreArchivo = 'observacion_' . now()->format('Ymd_His') . '.' . $extension;
            $ruta = 'archivo_obs_int/';
            $file->move(public_path($ruta), $nombreArchivo);
            $data['archivo'] = $ruta . $nombreArchivo;

            // (Opcional) Eliminar archivo anterior si existe
            if ($observacion->archivo && file_exists(public_path($observacion->archivo))) {
                unlink(public_path($observacion->archivo));
            }
        }

        $observacion->update($data);

        return response()->json(['message' => 'Observación actualizada exitosamente', 'status' => 200]);
    }

    public function destroy(Request $request)
    {
        $observacion = Observacion::findOrFail($request->id_observacion);

        // Verifica si tiene archivo y lo elimina
        if ($observacion->archivo && file_exists(public_path($observacion->archivo))) {
            unlink(public_path($observacion->archivo));
        }

        $observacion->delete();

        return response()->json(['message' => 'Observación eliminada exitosamente']);
    }


    public function subsanarModal(Request $request)
    {
        $observacion = Observacion::findOrFail($request->id_observacion);

        $centro_mac = $this->centro_mac();

        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();

        $tipos = TipoIntObs::where('tipo_obs', 'OBSERVACIÓN')->get();

        $view = view('observacion.modals.md_subsanar_observacion', compact('observacion', 'entidades', 'tipos'))->render();

        return response()->json(['html' => $view]);
    }

    public function subsanarGuardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_observacion'     => 'required|integer|exists:m_observacion,id_observacion',
            'estado'             => 'required|string',
            'fecha_solucion'     => 'nullable|date',
            'archivo'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422]);
        }

        $observacion = Observacion::findOrFail($request->id_observacion);
        $data = $request->only(['estado', 'fecha_solucion']);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $nombreArchivo = 'subsanacion_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $ruta = 'archivo_obs_int/';
            $file->move(public_path($ruta), $nombreArchivo);
            $data['archivo'] = $ruta . $nombreArchivo;

            // (Opcional) Eliminar archivo anterior
            if ($observacion->archivo && file_exists(public_path($observacion->archivo))) {
                unlink(public_path($observacion->archivo));
            }
        }

        $observacion->update($data);

        return response()->json(['message' => 'Observación subsanada correctamente', 'status' => 200]);
    }

    public function ver(Request $request)
    {
        $observacion = Observacion::with('entidad', 'tipoIntObs', 'centroMac', 'responsableUsuario')
            ->findOrFail($request->id_observacion);
        //dd($observacion->entidad->NOMBRE_ENTIDAD);

        $view = view('observacion.modals.md_ver_observacion', compact('observacion'))->render();
        return response()->json(['html' => $view]);
    }
    public function export_excel()
    {
        $observaciones = Observacion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac'])
            ->where('idcentro_mac', auth()->user()->idcentro_mac)
            ->orderBy('fecha_observacion', 'desc')
            ->get();

        $nombreMac = auth()->user()->centroMac->nombre_mac ?? 'Centro MAC';
        $nombreMes = ucfirst(\Carbon\Carbon::now()->monthName);

        return Excel::download(
            new ObservacionesExport($observaciones, $nombreMac, $nombreMes),
            'Observaciones_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
