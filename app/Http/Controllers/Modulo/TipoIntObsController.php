<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TipoIntObs;
use Illuminate\Support\Facades\Validator;

class TipoIntObsController extends Controller
{
    // Muestra la vista principal
    public function index()
    {
        return view('tipo_int_obs.index');
    }

    // Carga la tabla con todos los registros
    public function tb_index()
    {
        // Podrías filtrar solo los activos si deseas:
        // $tipos = TipoIntObs::where('status', 1)->get();
        $tipos = TipoIntObs::all();

        return view('tipo_int_obs.tablas.tb_index', compact('tipos'));
    }

    // Muestra el formulario (modal) para crear
    public function create()
    {
        try {
            $view = view('tipo_int_obs.modals.md_add_tipo_obs')->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar la solicitud'], 500);
        }
    }

    // Guarda un nuevo tipo de observación
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo'             => 'required|in:A,B,C,I',
            'tipo_obs'         => 'required|in:INTERRUPCIÓN,OBSERVACIÓN,INCUMPLIMIENTO',
            'numeracion'       => 'nullable|integer',
            'nom_tipo_int_obs' => 'required|string|max:255',
            'descripcion'      => 'nullable|string|max:255',  // Nueva validación
            'status'           => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data'    => null,
                'message' => $validator->errors(),
                'status'  => 422
            ], 422);
        }

        try {
            $tipo = new TipoIntObs();
            $tipo->tipo             = $request->tipo;
            $tipo->tipo_obs         = $request->tipo_obs;
            $tipo->numeracion       = $request->numeracion;
            $tipo->nom_tipo_int_obs = $request->nom_tipo_int_obs;
            $tipo->descripcion      = $request->descripcion;  // Asignar 'descripcion'
            $tipo->status           = $request->status ?? 1;
            $tipo->save();

            return response()->json([
                'data'    => $tipo,
                'message' => 'Tipo de observación guardado exitosamente',
                'status'  => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'data'    => null,
                'error'   => $e->getMessage(),
                'message' => 'Error al procesar la solicitud',
                'status'  => 400
            ], 400);
        }
    }

    // Muestra el formulario (modal) para editar
    public function edit(Request $request)
    {
        try {
            $tipo = TipoIntObs::find($request->id_tipo_int_obs);

            if (!$tipo) {
                return response()->json(['error' => 'Tipo de observación no encontrado'], 404);
            }

            $view = view('tipo_int_obs.modals.md_edit_tipo_int_obs', compact('tipo'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Actualiza un tipo de observación
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tipo_int_obs'  => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            'tipo'             => 'required|in:A,B,C,I',
            'tipo_obs'         => 'required|in:INTERRUPCIÓN,OBSERVACIÓN,INCUMPLIMIENTO',
            'numeracion'       => 'nullable|integer',
            'nom_tipo_int_obs' => 'required|string|max:255',
            'descripcion'      => 'nullable|string|max:255',  // Nueva validación
            'status'           => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data'    => null,
                'message' => $validator->errors(),
                'status'  => 422
            ], 422);
        }

        try {
            $tipo = TipoIntObs::find($request->id_tipo_int_obs);
            if (!$tipo) {
                return response()->json(['message' => 'Tipo de observación no encontrado'], 404);
            }

            $tipo->tipo             = $request->tipo;
            $tipo->tipo_obs         = $request->tipo_obs;
            $tipo->numeracion       = $request->numeracion;
            $tipo->nom_tipo_int_obs = $request->nom_tipo_int_obs;
            $tipo->descripcion      = $request->descripcion;  // Asignar 'descripcion'
            $tipo->status           = $request->status ?? 1;
            $tipo->save();

            return response()->json([
                'data'    => $tipo,
                'message' => 'La tipificación se ha actualizado exitosamente',
                'status'  => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data'    => null,
                'error'   => $e->getMessage(),
                'message' => 'Error al procesar la solicitud',
                'status'  => 400
            ], 400);
        }
    }

    // Elimina un tipo de observación
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $tipo = TipoIntObs::findOrFail($request->id_tipo_int_obs);
            $tipo->delete();

            DB::commit();
            return response()->json(['message' => 'Tipo de observación eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function toggleStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validar que venga el ID y exista
            $request->validate([
                'id_tipo_int_obs' => 'required|integer|exists:m_tipo_int_obs,id_tipo_int_obs',
            ]);

            // Buscar el registro
            $tipo = TipoIntObs::findOrFail($request->id_tipo_int_obs);

            // Alternar el estado
            if ($tipo->status == 1) {
                $tipo->status = 0; // lo marcamos como inactivo
            } else {
                $tipo->status = 1; // lo marcamos como activo
            }

            $tipo->save();

            DB::commit();

            return response()->json(['message' => 'Estado actualizado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
