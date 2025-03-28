<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ObservacionInterrupcion;
use App\Models\Entidad;
use App\Models\TipoIntObs;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ObservacionInterrupcionController extends Controller
{
    private function centro_mac()
    {
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('m_centro_mac', 'm_centro_mac.idcentro_mac', '=', 'users.idcentro_mac')
            ->where('m_centro_mac.idcentro_mac', $us_id)
            ->first();

        return (object) [
            'idmac' => $user->idcentro_mac,
            'name_mac' => $user->nombre_mac
        ];
    }

    public function index()
    {
        return view('observacion_interrupcion.index_ocupabilidad');
    }

    public function tb_index()
    {
        $observaciones = ObservacionInterrupcion::with([
            'entidad:identidad,nombre_entidad',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion',
            'personales:idpersonal,nombre,ape_pat,ape_mat',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ])->get();

        return view('observacion_interrupcion.tablas.tb_index', compact('observaciones'));
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

            $tipos = TipoIntObs::all();
            $responsables = User::where('idcentro_mac', $centro_mac->idmac)->select('id', 'name')->get();

            $view = view('observacion_interrupcion.modals.md_add_observacion', compact('entidades', 'tipos', 'responsables'))->render();
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
            'accion_correctiva'     => 'nullable|string',
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

            $data = $request->all();

            // Si es NO SUBSANADO, limpiar campos de cierre
            if ($request->estado === 'NO SUBSANADO') {
                $data['fecha_fin'] = null;
                $data['hora_fin'] = null;
                $data['accion_correctiva'] = null;
            }

            $data['idcentro_mac'] = $centro_mac->idmac;

            ObservacionInterrupcion::create($data);

            return response()->json(['message' => 'Observación/Interrupción creada exitosamente', 'status' => 201], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }
    }

    public function edit(Request $request)
    {
        try {
            $observacion = ObservacionInterrupcion::with('personales')->find($request->id_observacion_interrupcion);

            if (!$observacion) {
                return response()->json(['error' => 'Observación/Interrupción no encontrada'], 404);
            }

            $centro_mac = $this->centro_mac();

            // Aquí se ajusta la consulta para que sea igual al create()
            $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
                ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
                ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
                ->orderBy('m_entidad.abrev_entidad')
                ->get();

            $tipos = TipoIntObs::all();
            $personales = Personal::all();
            $responsables = User::where('idcentro_mac', $centro_mac->idmac)->get();

            $view = view('observacion_interrupcion.modals.md_edit_observacion', compact(
                'observacion',
                'entidades',
                'tipos',
                'personales',
                'responsables'
            ))->render();

            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_observacion_interrupcion' => 'required|integer|exists:m_observacion_interrupcion,id_observacion_interrupcion',
            'identidad'                   => 'required|integer|exists:m_entidad,identidad',
            'id_tipo_int_obs'             => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'descripcion'                 => 'required|string',
            'descripcion_accion'          => 'nullable|string',
            'accion_correctiva'           => 'nullable|string',
            'fecha_inicio'                => 'required|date',
            'hora_inicio'                 => 'required|date_format:H:i',
            'fecha_fin'                   => 'nullable|date',
            'hora_fin'                    => 'nullable|date_format:H:i',
            'estado'                      => 'required|string',
            'responsable'                 => 'required|integer|exists:users,id',
            'idcentro_mac'                => 'required|integer|exists:m_centro_mac,idcentro_mac'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
        }

        try {
            $observacion = ObservacionInterrupcion::find($request->id_observacion_interrupcion);

            $data = $request->all();

            // Si es NO SUBSANADO, limpiar los campos de finalización
            if ($request->estado === 'NO SUBSANADO') {
                $data['fecha_fin'] = null;
                $data['hora_fin'] = null;
                $data['accion_correctiva'] = null;
            }

            $observacion->update($data);

            return response()->json(['message' => 'Observación/Interrupción actualizada exitosamente', 'status' => 200], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400], 400);
        }
    }


    public function destroy(Request $request)
    {
        DB::beginTransaction();

        try {
            $observacion = ObservacionInterrupcion::findOrFail($request->id_observacion_interrupcion);
            $observacion->personales()->detach();
            $observacion->delete();

            DB::commit();
            return response()->json(['message' => 'Observación/Interrupción eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function getPersonales(Request $request)
    {
        try {
            $personales = Personal::join('m_personal_modulo as pm', 'pm.num_doc', '=', 'm_personal.NUM_DOC')
                ->where('pm.idcentro_mac', auth()->user()->idcentro_mac) // Filtrar por el centro MAC del usuario
                ->where('m_personal.identidad', $request->identidad) // Filtrar por la entidad seleccionada
                ->select('m_personal.idpersonal', 'm_personal.nombre', 'm_personal.ape_pat', 'm_personal.ape_mat') // Obtener solo los datos necesarios
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
        $observacion = ObservacionInterrupcion::find($request->id_observacion_interrupcion);

        if (!$observacion) {
            return response()->json(['error' => 'Observación no encontrada'], 404);
        }

        $centro_mac = $this->centro_mac();

        $entidades = Entidad::join('m_mac_entidad', 'm_mac_entidad.identidad', '=', 'm_entidad.identidad')
            ->where('m_mac_entidad.idcentro_mac', $centro_mac->idmac)
            ->select('m_entidad.identidad', 'm_entidad.abrev_entidad', 'm_entidad.nombre_entidad')
            ->orderBy('m_entidad.abrev_entidad')
            ->get();

        $tipos = TipoIntObs::all();

        $view = view('observacion_interrupcion.modals.md_subsanar_observaciones', compact('observacion', 'tipos', 'entidades'))->render();

        return response()->json(['html' => $view]);
    }
    public function subsanarGuardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_observacion_interrupcion' => 'required|integer|exists:m_observacion_interrupcion,id_observacion_interrupcion',
            'estado'            => 'required|string',
            'accion_correctiva' => 'nullable|string',
            'fecha_fin'         => 'nullable|date',
            'hora_fin'          => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
        }

        try {
            $observacion = ObservacionInterrupcion::find($request->id_observacion_interrupcion);

            $observacion->update([
                'estado'            => $request->estado,
                'accion_correctiva' => $request->accion_correctiva,
                'fecha_fin'         => $request->fecha_fin,
                'hora_fin'          => $request->hora_fin,
            ]);

            return response()->json(['message' => 'Observación subsanada correctamente', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400]);
        }
    }
}
