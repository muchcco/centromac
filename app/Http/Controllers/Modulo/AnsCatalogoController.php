<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AnsCatalogoController extends Controller
{
    private function centro_mac()
    {
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;

        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
            ->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = [
            'idmac' => $idmac,
            'name_mac' => $name_mac
        ];

        return (object) $resp;
    }
    // Vista principal
    public function index()
    {
        $entidades = DB::table('db_centro_mac_tiempos.catalogo_entidades')
            ->orderBy('nome')
            ->get();

        return view('ans.index', compact('entidades'));
    }

    // Tabla
    public function tb_index()
    {
        try {

            $data = DB::table('db_centro_mac_tiempos.catalogo_entidades as e')

                ->leftJoin(
                    'db_centro_mac_tiempos.catalogo_servicios_ans as s',
                    's.macro_id',
                    '=',
                    'e.id_entidad'
                )

                ->leftJoin(
                    'db_centro_mac_tiempos.catalogo_servicios_ans_tiempos as t',
                    function ($join) {

                        $join->on('t.id_servicio', '=', 's.id_servicio')

                            ->where('t.fecha_inicio', '<=', now())

                            ->where(function ($q) {

                                $q->whereNull('t.fecha_fin')
                                    ->orWhere('t.fecha_fin', '>=', now());
                            });
                    }
                )

                ->select(
                    'e.id_entidad',
                    'e.nome as entidad',

                    's.id_servicio',
                    's.nome as servicio',
                    's.status',

                    't.fecha_inicio',
                    't.fecha_fin',

                    't.limite_espera',
                    't.limite_atencion',
                    't.se_calcula'
                )

                ->orderBy('e.nome')
                ->orderBy('s.nome')

                ->get();

            return view('ans.tablas.tb_index', compact('data'));
        } catch (\Exception $e) {

            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'Error al cargar la tabla'
            ], 400);
        }
    }

    // Modal crear
    public function create()
    {
        try {

            $entidades = DB::table('db_centro_mac_tiempos.catalogo_entidades')
                ->where('status', 1)
                ->orderBy('nome')
                ->get();

            $view = view('ans.modals.md_add_servicio', compact('entidades'))->render();

            return response()->json(['html' => $view]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error procesando la solicitud'
            ], 500);
        }
    }

    // Guardar
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'macro_id' => 'required|integer',
            'nombre_servicio' => 'required|string|max:255',
            'limite_espera' => 'required|integer',
            'limite_atencion' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        DB::beginTransaction();

        try {

            $idServicio = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans')
                ->insertGetId([
                    'macro_id' => $request->macro_id,
                    'descricao' => $request->nombre_servicio,
                    'nome' => $request->nombre_servicio,
                    'status' => 1,
                    'peso' => 1
                ]);

            DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                ->insert([
                    'id_servicio' => $idServicio,
                    'fecha_inicio' => now()->toDateString(),
                    'limite_espera' => $request->limite_espera,
                    'limite_atencion' => $request->limite_atencion,
                    'se_calcula' => 1
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Servicio ANS creado correctamente',
                'status' => 201
            ]);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'Error al guardar'
            ], 400);
        }
    }
    // Modal editar
    public function edit(Request $request)
    {
        try {

            $servicio = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans as s')

                ->leftJoin(
                    'db_centro_mac_tiempos.catalogo_servicios_ans_tiempos as t',
                    function ($join) {

                        $join->on('t.id_servicio', '=', 's.id_servicio')

                            ->where('t.fecha_inicio', '<=', now())

                            ->where(function ($q) {

                                $q->whereNull('t.fecha_fin')
                                    ->orWhere('t.fecha_fin', '>=', now());
                            });
                    }
                )

                ->select(
                    's.id_servicio',
                    's.nome',
                    's.status',

                    't.fecha_inicio',
                    't.fecha_fin',

                    't.limite_espera',
                    't.limite_atencion',
                    't.se_calcula'
                )

                ->where('s.id_servicio', $request->id_servicio)

                ->first();

            if (!$servicio) {

                return response()->json([
                    'error' => 'Servicio no encontrado'
                ], 404);
            }

            $view = view('ans.modals.md_edit_servicio', compact('servicio'))->render();

            return response()->json(['html' => $view]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Actualizar
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_servicio' => 'required|string|max:255',
            'limite_espera' => 'required|integer',
            'limite_atencion' => 'required|integer',
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }
        DB::beginTransaction();

        try {

            DB::table('db_centro_mac_tiempos.catalogo_servicios_ans')
                ->where('id_servicio', $request->id_servicio)
                ->update([
                    'descricao' => $request->nombre_servicio,
                    'nome' => $request->nombre_servicio,
                    'status' => $request->status
                ]);

            $tiempoActual = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                ->where('id_servicio', $request->id_servicio)
                ->whereNull('fecha_fin')
                ->first();

            if ($tiempoActual) {

                DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                    ->where('id_tiempo', $tiempoActual->id_tiempo)
                    ->update([
                        'fecha_fin' => now()->toDateString()
                    ]);
            }

            DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                ->insert([
                    'id_servicio' => $request->id_servicio,
                    'fecha_inicio' => now()->toDateString(),
                    'limite_espera' => $request->limite_espera,
                    'limite_atencion' => $request->limite_atencion,
                    'se_calcula' => 1
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Servicio actualizado correctamente',
                'status' => 200
            ]);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
    // Eliminar
    public function destroy(Request $request)
    {

        DB::beginTransaction();

        try {

            DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                ->where('id_servicio', $request->id_servicio)
                ->delete();

            DB::table('db_centro_mac_tiempos.catalogo_servicios_ans')
                ->where('id_servicio', $request->id_servicio)
                ->delete();

            DB::commit();

            return response()->json([
                'message' => 'Servicio eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function md_servicios_entidad(Request $request)
    {
        try {

            $servicios = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans as s')

                ->join(
                    'db_centro_mac_tiempos.catalogo_entidades as e',
                    'e.id_entidad',
                    '=',
                    's.macro_id'
                )

                ->leftJoin(DB::raw("
                (
                    SELECT t1.*
                    FROM db_centro_mac_tiempos.catalogo_servicios_ans_tiempos t1
                    INNER JOIN (
                        SELECT id_servicio, MAX(id_tiempo) AS max_id
                        FROM db_centro_mac_tiempos.catalogo_servicios_ans_tiempos
                        GROUP BY id_servicio
                    ) t2 ON t1.id_tiempo = t2.max_id
                ) t
            "), 't.id_servicio', '=', 's.id_servicio')

                ->where('s.macro_id', $request->id_entidad)

                ->select(
                    's.id_servicio',
                    's.nome',
                    's.status',
                    'e.nome as entidad',
                    't.fecha_inicio',
                    't.fecha_fin',
                    't.limite_espera',
                    't.limite_atencion',
                    't.se_calcula'
                )

                ->orderBy('s.nome')
                ->get();


            if ($servicios->isEmpty()) {
                return response()->json([
                    'html' => '<div class="p-4 text-center">No hay servicios registrados para esta entidad</div>'
                ]);
            }

            $view = view('ans.modals.md_servicios_entidad', compact('servicios'))->render();

            return response()->json([
                'html' => $view
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'Error al cargar los servicios'
            ], 500);
        }
    }
public function update_tiempos_entidad(Request $request)
{
    DB::beginTransaction();

    try {

        foreach ($request->data as $row) {

            $ultimo = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                ->where('id_servicio', $row['id_servicio'])
                ->orderByDesc('id_tiempo')
                ->first();

            if (!$ultimo) {
                continue;
            }

            DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                ->where('id_tiempo', $ultimo->id_tiempo)
                ->update([
                    'fecha_inicio' => $row['fecha_inicio'],
                    'fecha_fin' => $row['fecha_fin'],
                    'limite_espera' => $row['limite_espera'],
                    'limite_atencion' => $row['limite_atencion'],
                    'se_calcula' => $row['se_calcula']
                ]);

        }

        DB::commit();

        return response()->json([
            'status' => 200
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return response()->json([
            'error' => $e->getMessage()
        ], 400);

    }
}
    public function md_cambiar_tiempos(Request $request)
    {

        $servicios = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans as s')

            ->join(
                'db_centro_mac_tiempos.catalogo_entidades as e',
                'e.id_entidad',
                '=',
                's.macro_id'
            )

            ->leftJoin(
                'db_centro_mac_tiempos.catalogo_servicios_ans_tiempos as t',
                function ($join) {

                    $join->on('t.id_servicio', '=', 's.id_servicio')

                        ->whereNull('t.fecha_fin');
                }
            )

            ->where('s.macro_id', $request->id_entidad)

            ->select(
                's.id_servicio',
                's.nome',
                'e.nome as entidad',
                't.fecha_inicio',
                't.fecha_fin',
                't.limite_espera',
                't.limite_atencion'
            )

            ->orderBy('s.nome')

            ->get();


        $view = view('ans.modals.md_cambiar_tiempos', compact('servicios'))->render();

        return response()->json([
            'html' => $view
        ]);
    }
    public function cambiar_tiempos_servicio(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->data as $row) {
                $actual = DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                    ->where('id_servicio', $row['id_servicio'])
                    ->whereNull('fecha_fin')
                    ->first();
                if (!$actual) continue;
                DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                    ->where('id_tiempo', $actual->id_tiempo)
                    ->update([
                        'fecha_fin' => $row['fecha_fin']
                    ]);
                $inicioNuevo = \Carbon\Carbon::parse($row['fecha_fin'])->addDay();
                $finNuevo = \Carbon\Carbon::now()->endOfYear();

                DB::table('db_centro_mac_tiempos.catalogo_servicios_ans_tiempos')
                    ->insert([
                        'id_servicio' => $row['id_servicio'],
                        'fecha_inicio' => $inicioNuevo,
                        'fecha_fin' => $finNuevo,
                        'limite_espera' => $row['limite_espera'],
                        'limite_atencion' => $row['limite_atencion'],
                        'se_calcula' => 1,
                        'created_at' => now()
                    ]);
            }
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
