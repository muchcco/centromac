<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EntidadCatalogoController extends Controller
{

    /**
     * 🔹 Vista principal
     */
    public function index()
    {
        return view('entidades.index');
    }

    /**
     * 🔥 Tabla agrupada (PADRE → HIJOS)
     */
    public function tb_index()
    {
        try {
            $entidades = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->orderBy('nome')
                ->get();
            $padres = $entidades->whereNull('macro_id');
            $hijos = $entidades->whereNotNull('macro_id');
            $hijosAgrupados = $hijos->groupBy('macro_id');
            $data = $padres->map(function ($padre) use ($hijosAgrupados) {
                return [
                    'id_entidad' => $padre->id_entidad,
                    'nombre' => $padre->nome,
                    'status' => $padre->status,
                    'peso' => $padre->peso,
                    'hijos' => ($hijosAgrupados[$padre->id_entidad] ?? collect())
                        ->map(function ($hijo) {
                            return [
                                'id_entidad' => $hijo->id_entidad,
                                'nombre' => $hijo->nome,
                                'status' => $hijo->status,
                                'peso' => $hijo->peso
                            ];
                        })->values()
                ];
            });
            return view('entidades.tablas.tb_index', compact('data'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Error al cargar entidades'
            ], 400);
        }
    }

    /**
     * 🔹 Modal crear entidad
     */
    public function create()
    {
        try {
            $padres = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->whereNull('macro_id')
                ->where('status', 1)
                ->orderBy('nome')
                ->get();
            $view = view('entidades.modals.md_add', compact('padres'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar modal'
            ], 500);
        }
    }

    /**
     * 🔹 Guardar entidad
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'macro_id' => 'nullable|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }
        DB::beginTransaction();
        try {
            DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->insert([
                    'nome' => $request->nombre,
                    'descricao' => $request->nombre,
                    'macro_id' => $request->macro_id,
                    'status' => 1,
                    'peso' => 1
                ]);
            DB::commit();
            return response()->json([
                'message' => 'Entidad creada correctamente',
                'status' => 201
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 🔹 Modal editar
     */
    public function edit(Request $request)
    {
        try {
            $entidad = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->where('id_entidad', $request->id_entidad)
                ->first();
            if (!$entidad) {
                return response()->json(['error' => 'Entidad no encontrada'], 404);
            }
            $padres = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->whereNull('macro_id')
                ->where('id_entidad', '!=', $request->id_entidad)
                ->get();
            $view = view('entidades.modals.md_edit', compact('entidad', 'padres'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Actualizar entidad
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'status' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }
        DB::beginTransaction();
        try {
            DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->where('id_entidad', $request->id_entidad)
                ->update([
                    'nome' => $request->nombre,
                    'descricao' => $request->nombre,
                    'macro_id' => $request->macro_id,
                    'status' => $request->status
                ]);
            DB::commit();
            return response()->json([
                'message' => 'Entidad actualizada correctamente',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 🔥 Ver detalle de una entidad (con hijos)
     */
    public function show($id)
    {
        try {
            $entidad = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->where('id_entidad', $id)
                ->first();
            if (!$entidad) {
                return response()->json(['error' => 'Entidad no encontrada'], 404);
            }
            $hijos = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->where('macro_id', $id)
                ->get();
            return response()->json([
                'entidad' => $entidad,
                'hijos' => $hijos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Eliminar entidad
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->where('id_entidad', $request->id_entidad)
                ->delete();
            DB::commit();
            return response()->json([
                'message' => 'Entidad eliminada'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}