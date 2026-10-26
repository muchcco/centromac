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

        $user = User::join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('m_centro_mac.IDCENTRO_MAC', $us_id)
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
    /**
     * Entidades visibles: padres e independientes.
     * Los hijos se consultan mediante supermacro_id, sin alterar los IDs NOVO.
     */
    private function entidadesPrincipales()
    {
        return DB::table('db_centro_mac_tiempos.catalogo_entidades')
            ->whereNull('supermacro_id')
            ->where('status', 1)
            ->orderBy('nome');
    }

    /**
     * Devuelve IDs de la entidad seleccionada y todas sus dependencias.
     * Incluye hijas inactivas para conservar la visibilidad histórica del catálogo;
     * el estado de cada servicio no se modifica.
     */
    private function idsEntidadGrupo($idEntidad): array
    {
        $entidades = DB::table('db_centro_mac_tiempos.catalogo_entidades')
            ->select('id_entidad', 'supermacro_id')
            ->get();

        $porId = $entidades->keyBy('id_entidad');
        $seleccionada = $porId->get((int) $idEntidad);
        if (!$seleccionada) {
            return [];
        }
        // También admite que llegue un ID hijo desde enlaces antiguos.
        $raiz = (int) $seleccionada->id_entidad;
        $visitados = [];
        while ($porId->has($raiz) && $porId->get($raiz)->supermacro_id !== null) {
            if (isset($visitados[$raiz])) break;
            $visitados[$raiz] = true;
            $raiz = (int) $porId->get($raiz)->supermacro_id;
        }

        $ids = [$raiz];
        for ($i = 0; $i < count($ids); $i++) {
            foreach ($entidades as $item) {
                if ($item->supermacro_id !== null && (int) $item->supermacro_id === $ids[$i]
                    && !in_array((int) $item->id_entidad, $ids, true)) {
                    $ids[] = (int) $item->id_entidad;
                }
            }
        }
        return $ids;
    }

    // Vista principal
    public function index()
    {
        $entidades = $this->entidadesPrincipales()->get();

        return view('ans.index', compact('entidades'));
    }

    // Tabla
    public function tb_index()
    {
        try {

            $data = DB::table('db_centro_mac_tiempos.catalogo_entidades as e')
                ->leftJoin('db_centro_mac_tiempos.catalogo_entidades as padre',
                    'padre.id_entidad', '=', 'e.supermacro_id')
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
                    DB::raw('COALESCE(padre.id_entidad, e.id_entidad) as id_entidad'),
                    DB::raw('COALESCE(padre.nome, e.nome) as entidad'),
                    'e.nome as entidad_origen',
                    'e.id_entidad as id_entidad_origen',

                    's.id_servicio',
                    's.nome as servicio',
                    's.status',

                    't.fecha_inicio',
                    't.fecha_fin',

                    't.limite_espera',
                    't.limite_atencion',
                    't.se_calcula'
                )

                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('e.supermacro_id')->where('e.status', 1);
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('e.supermacro_id')
                            ->where('padre.status', 1)
                            ->whereNotNull('s.id_servicio');
                    });
                })
                ->orderBy('entidad')
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

            $entidades = $this->entidadesPrincipales()->get();

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

        $principal = DB::table('db_centro_mac_tiempos.catalogo_entidades')
            ->where('id_entidad', $request->macro_id)
            ->whereNull('supermacro_id')
            ->where('status', 1)
            ->exists();
        if (!$principal) {
            return response()->json(['message' => 'Seleccione una entidad principal activa'], 422);
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
                ->leftJoin('db_centro_mac_tiempos.catalogo_entidades as padre',
                    'padre.id_entidad', '=', 'e.supermacro_id')

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

                ->whereIn('s.macro_id', $this->idsEntidadGrupo($request->id_entidad))

                ->select(
                    's.id_servicio',
                    's.nome',
                    's.status',
                    DB::raw('COALESCE(padre.nome, e.nome) as entidad'),
                    'e.nome as entidad_origen',
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
            ->leftJoin('db_centro_mac_tiempos.catalogo_entidades as padre',
                'padre.id_entidad', '=', 'e.supermacro_id')

            ->leftJoin(
                'db_centro_mac_tiempos.catalogo_servicios_ans_tiempos as t',
                function ($join) {

                    $join->on('t.id_servicio', '=', 's.id_servicio')

                        ->whereNull('t.fecha_fin');
                }
            )

            ->whereIn('s.macro_id', $this->idsEntidadGrupo($request->id_entidad))

            ->select(
                's.id_servicio',
                's.nome',
                DB::raw('COALESCE(padre.nome, e.nome) as entidad'),
                'e.nome as entidad_origen',
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
