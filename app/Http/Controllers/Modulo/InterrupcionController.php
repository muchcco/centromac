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
        return view('interrupcion.index');
    }

    public function tb_index()
    {
        $user = auth()->user();

        $query = Interrupcion::with([
            'entidad:identidad,nombre_entidad',
            'tipoIntObs:id_tipo_int_obs,tipo,numeracion',
            'centroMac:idcentro_mac,nombre_mac',
            'responsableUsuario:id,name'
        ]);

        if (!$user->hasRole(['Administrador', 'Moderador'])) {
            $query->where('idcentro_mac', $user->idcentro_mac);
        }

        $interrupciones = $query->get();

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

            $tipos = TipoIntObs::where('tipo_obs', 'INTERRUPCIÓN')->get();
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

            if ($request->estado === 'NO SUBSANADO') {
                $data['fecha_fin'] = null;
                $data['hora_fin'] = null;
                $data['accion_correctiva'] = null;
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

            $tipos = TipoIntObs::where('tipo_obs', 'INTERRUPCIÓN')->get();
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
            'accion_correctiva'  => 'nullable|string',
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
            $data = $request->all();

            if ($request->estado === 'NO SUBSANADO') {
                $data['fecha_fin'] = null;
                $data['hora_fin'] = null;
                $data['accion_correctiva'] = null;
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

        $tipos = TipoIntObs::where('tipo_obs', 'INTERRUPCIÓN')->get();

        $view = view('interrupcion.modals.md_subsanar_interrupcion', compact('interrupcion', 'tipos', 'entidades'))->render();

        return response()->json(['html' => $view]);
    }

    public function subsanarGuardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_interrupcion'    => 'required|integer|exists:m_interrupcion,id_interrupcion',
            'estado'             => 'required|string',
            'accion_correctiva'  => 'nullable|string',
            'fecha_fin'          => 'nullable|date',
            'hora_fin'           => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors(), 'status' => 422], 422);
        }

        try {
            $interrupcion = Interrupcion::find($request->id_interrupcion);

            $interrupcion->update([
                'estado'            => $request->estado,
                'accion_correctiva' => $request->accion_correctiva,
                'fecha_fin'         => $request->fecha_fin,
                'hora_fin'          => $request->hora_fin,
            ]);

            return response()->json(['message' => 'Interrupción subsanada correctamente', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 400]);
        }
    }

    public function export_excel()
    {
        $interrupcion = Interrupcion::with(['entidad', 'tipoIntObs', 'responsableUsuario', 'centroMac'])
            ->where('idcentro_mac', auth()->user()->idcentro_mac)
            ->get();

        $nombreMac = auth()->user()->centroMac->nombre_mac ?? 'Centro MAC';
        $nombreMes = ucfirst(\Carbon\Carbon::now()->monthName);

        return Excel::download(
            new InterrupcionExport($interrupcion, $nombreMac, $nombreMes),
            'Interrupciones_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
