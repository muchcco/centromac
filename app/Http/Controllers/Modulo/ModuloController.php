<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Modulo;
use App\Models\Entidad;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    //PRUEBA NOOB
    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac' => $idmac, 'name_mac' => $name_mac];

        return (object) $resp;
    }

    // Método para mostrar la lista inicial de módulos
    public function index()
    {
        $usuario = auth()->user();

        // 🔹 Si el usuario NO es administrador o moderador, obtener su MAC
        $centro_mac = null;
        if (!$usuario->hasRole(['Administrador', 'Moderador'])) {
            $centro_mac = DB::table('m_centro_mac')
                ->where('IDCENTRO_MAC', $usuario->idcentro_mac)
                ->select('IDCENTRO_MAC as idmac', 'NOMBRE_MAC as name_mac')
                ->first();
        }

        // 🔹 Listado de MACs disponibles (solo admins pueden ver todos)
        $macs = $usuario->hasRole(['Administrador', 'Moderador'])
            ? DB::table('m_centro_mac')
            ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
            ->orderBy('NOMBRE_MAC')
            ->get()
            : collect(); // vacío para los demás

        // 🔹 Listado de ENTIDADES según el rol
        if ($usuario->hasRole(['Administrador', 'Moderador'])) {
            // Todos los MAC → todas las entidades
            $entidades = DB::table('m_entidad')
                ->select('IDENTIDAD', 'NOMBRE_ENTIDAD')
                ->orderBy('NOMBRE_ENTIDAD')
                ->get();
        } else {
            // Solo entidades del MAC del usuario
            $entidades = DB::table('m_mac_entidad')
                ->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_mac_entidad.IDENTIDAD')
                ->where('m_mac_entidad.IDCENTRO_MAC', $usuario->idcentro_mac)
                ->select('m_entidad.IDENTIDAD', 'm_entidad.NOMBRE_ENTIDAD')
                ->orderBy('m_entidad.NOMBRE_ENTIDAD')
                ->get();
        }

        return view('modulo.index', compact('macs', 'entidades', 'centro_mac'));
    }

    // Método para cargar los datos de los módulos en la tabla
    public function tb_index(Request $request)
    {
        $usuario = auth()->user();

        $query = DB::table('m_modulo as m')
            ->leftJoin('m_entidad as e', 'm.IDENTIDAD', '=', 'e.IDENTIDAD')
            ->leftJoin('m_centro_mac as c', 'm.IDCENTRO_MAC', '=', 'c.IDCENTRO_MAC')
            ->select(
                'm.IDMODULO',
                'm.N_MODULO',
                'm.FECHAINICIO',
                'm.FECHAFIN',
                'm.ES_ADMINISTRATIVO',
                'e.NOMBRE_ENTIDAD',
                'c.NOMBRE_MAC'
            )
            ->orderBy('m.N_MODULO', 'asc');

        // 🔹 FILTROS OPCIONALES
        if ($request->filled('id_mac')) {
            $query->where('m.IDCENTRO_MAC', $request->id_mac);
        }

        if ($request->filled('id_entidad')) {
            $query->where('m.IDENTIDAD', $request->id_entidad);
        }

        if ($request->filled('es_admin')) {
            $query->where('m.ES_ADMINISTRATIVO', $request->es_admin);
        }

        // 🔒 RESTRICCIÓN POR ROL
        if (!$usuario->hasRole(['Administrador', 'Moderador'])) {
            // Forzar que solo vea los módulos de su MAC
            $query->where('m.IDCENTRO_MAC', $usuario->idcentro_mac);
        }

        $modulos = $query->get();

        $view = view('modulo.tablas.tb_index', compact('modulos'))->render();
        return response()->json($view);
    }

    // Método para mostrar el formulario de creación de módulos
    public function create()
    {
        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $entidades = DB::table('M_MAC_ENTIDAD')
                ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                ->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac)
                ->get();
        } else {
            $entidades = Entidad::all();
        }

        try {
            // Pasa la variable 'entidades' a la vista para que pueda ser utilizada
            $view = view('modulo.modals.md_add_modulo', compact('entidades'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            // Manejo de errores en caso de que algo salga mal
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Método para almacenar un nuevo módulo
    public function store(Request $request)
    {
        // 🔹 Validación inicial de los datos
        $validator = Validator::make($request->all(), [
            'nombre_modulo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'entidad_id' => 'required|integer|exists:m_entidad,IDENTIDAD',
            'id_centromac' => 'required|integer|exists:m_centro_mac,IDCENTRO_MAC',
            'es_administrativo' => 'required|in:SI,NO',
        ]);

        // 🔹 Validación de campos obligatorios
        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        // 🔹 Validar rango de fechas
        if (strtotime($request->fecha_fin) < strtotime($request->fecha_inicio)) {
            return response()->json([
                'data' => null,
                'message' => 'La fecha de fin no puede ser menor que la fecha de inicio.',
                'status' => 422
            ], 422);
        }

        try {
            // Crear y guardar el nuevo módulo
            $modulo = new Modulo();
            $modulo->N_MODULO = $request->nombre_modulo;
            $modulo->FECHAINICIO = $request->fecha_inicio;
            $modulo->FECHAFIN = $request->fecha_fin;
            $modulo->IDENTIDAD = $request->entidad_id;
            $modulo->IDCENTRO_MAC = $request->id_centromac;
            $modulo->ES_ADMINISTRATIVO = $request->es_administrativo;
            $modulo->save();

            return response()->json([
                'data' => $modulo,
                'message' => 'Módulo guardado exitosamente',
                'status' => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'Error al procesar la solicitud',
                'status' => 400
            ], 400);
        }
    }

    // Método para editar un módulo
    public function edit(Request $request)
    {
        try {
            $modulo = Modulo::where('IDMODULO', $request->id_modulo)->first();

            if (!$modulo) {
                return response()->json(['error' => 'Módulo no encontrado'], 404);
            }

            // Entidades
            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                $entidades = DB::table('M_MAC_ENTIDAD')
                    ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                    ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                    ->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac)
                    ->get();
            } else {
                $entidades = Entidad::all();
            }

            // 🔒 Determinar si solo puede editar FECHA FIN
            $solo_fecha_fin = !auth()->user()->hasRole('Administrador');

            $view = view('modulo.modals.md_edit_modulo', compact('modulo', 'entidades', 'solo_fecha_fin'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Método para actualizar un módulo
    public function update(Request $request)
    {
        // 🔹 Validar campos obligatorios mínimos
        $request->validate([
            'id_modulo' => 'required|integer|exists:m_modulo,IDMODULO',
            'fecha_fin' => 'required|date',
        ]);

        try {
            $modulo = Modulo::find($request->id_modulo);
            if (!$modulo) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }

            // 🔹 Si se envía fecha_inicio, validar que fin >= inicio
            if ($request->filled('fecha_inicio') && strtotime($request->fecha_fin) < strtotime($request->fecha_inicio)) {
                return response()->json([
                    'message' => 'La fecha de fin no puede ser menor que la fecha de inicio.'
                ], 422);
            }

            // 🔒 Solo Administrador puede editar todos los campos
            if (auth()->user()->hasRole('Administrador')) {
                if (empty($request->nombre_modulo) || empty($request->fecha_inicio) || empty($request->entidad_id) || empty($request->es_administrativo)) {
                    return response()->json([
                        'message' => 'Faltan campos requeridos para la actualización completa.'
                    ], 422);
                }

                $modulo->N_MODULO = $request->nombre_modulo;
                $modulo->FECHAINICIO = $request->fecha_inicio;
                $modulo->IDENTIDAD = $request->entidad_id;
                $modulo->ES_ADMINISTRATIVO = $request->es_administrativo;
            }

            // 👥 Todos pueden actualizar la fecha de fin
            $modulo->FECHAFIN = $request->fecha_fin;
            $modulo->save();

            return response()->json([
                'message' => 'Módulo actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(Request $request)
    {
        // Inicia una transacción para asegurar la integridad de la operación
        DB::beginTransaction();

        try {
            // Encuentra el módulo utilizando Eloquent para mayor seguridad
            $modulo = Modulo::findOrFail($request->id);

            // Elimina el módulo
            $modulo->delete();

            // Si todo ha ido bien, confirma la transacción
            DB::commit();

            return response()->json([
                'message' => 'Módulo eliminado exitosamente'
            ], 200); // Respuesta exitosa con mensaje

        } catch (\Exception $e) {
            // En caso de error, revierte la transacción
            DB::rollback();

            // Regresa una respuesta de error con el mensaje de excepción
            return response()->json([
                'error' => $e->getMessage()
            ], 400); // Código de estado HTTP para errores de cliente
        }
    }
    public function modalCambiarEntidad(Request $request)
    {
        try {
            $modulo = Modulo::with('entidad')->where('IDMODULO', $request->id_modulo)->first();

            if (!$modulo) {
                return response()->json(['error' => 'Módulo no encontrado'], 404);
            }

            // ✅ Si es Administrador, puede ver todas las entidades
            if (auth()->user()->hasRole('Administrador')) {
                $entidades = DB::table('M_ENTIDAD')
                    ->select('IDENTIDAD', 'NOMBRE_ENTIDAD')
                    ->orderBy('NOMBRE_ENTIDAD')
                    ->get();
            } else {
                // 🔹 Caso contrario: solo entidades del mismo MAC del usuario autenticado
                $idMacUsuario = $this->centro_mac()->idmac;

                $entidades = DB::table('M_MAC_ENTIDAD')
                    ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                    ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                    ->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $idMacUsuario)
                    ->select('M_ENTIDAD.IDENTIDAD', 'M_ENTIDAD.NOMBRE_ENTIDAD')
                    ->orderBy('M_ENTIDAD.NOMBRE_ENTIDAD')
                    ->get();
            }

            $view = view('modulo.modals.md_cambiar_entidad', compact('modulo', 'entidades'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cambiarEntidad(Request $request)
    {
        $request->validate([
            'id_modulo' => 'required|integer|exists:m_modulo,IDMODULO',
            'nueva_entidad_id' => 'required|integer|exists:m_entidad,IDENTIDAD',
            'fecha_fin' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $moduloActual = Modulo::findOrFail($request->id_modulo);

            // 🔹 Validar fecha
            if (strtotime($request->fecha_fin) < strtotime($moduloActual->FECHAINICIO)) {
                return response()->json([
                    'message' => 'La fecha de fin no puede ser menor que la fecha de inicio actual.'
                ], 422);
            }

            // 🔹 Validar que la nueva entidad pertenezca al MAC del usuario
            $idMacUsuario = $this->centro_mac()->idmac;

            $entidadPertenece = DB::table('M_MAC_ENTIDAD')
                ->where('IDCENTRO_MAC', $idMacUsuario)
                ->where('IDENTIDAD', $request->nueva_entidad_id)
                ->exists();

            if (!$entidadPertenece && !auth()->user()->hasRole('Administrador')) {
                return response()->json([
                    'message' => 'No puede asignar una entidad que no pertenece a su Centro MAC.'
                ], 403);
            }

            // 🔹 1. Cerrar módulo actual
            $moduloActual->FECHAFIN = $request->fecha_fin;
            $moduloActual->save();

            // 🔹 2. Crear nuevo módulo con nueva entidad
            $nuevoModulo = new Modulo();
            $nuevoModulo->N_MODULO = $moduloActual->N_MODULO;
            $nuevoModulo->FECHAINICIO = date('Y-m-d', strtotime($request->fecha_fin . ' +1 day'));
            $nuevoModulo->FECHAFIN = '2050-12-31'; // cierre largo fijo
            $nuevoModulo->IDENTIDAD = $request->nueva_entidad_id;
            $nuevoModulo->IDCENTRO_MAC = $moduloActual->IDCENTRO_MAC;
            $nuevoModulo->ES_ADMINISTRATIVO = $moduloActual->ES_ADMINISTRATIVO;
            $nuevoModulo->save();

            DB::commit();

            return response()->json([
                'message' => 'Cambio de entidad realizado correctamente.',
                'nuevo_modulo' => [
                    'id' => $nuevoModulo->IDMODULO,
                    'nueva_entidad' => DB::table('M_ENTIDAD')
                        ->where('IDENTIDAD', $request->nueva_entidad_id)
                        ->value('NOMBRE_ENTIDAD'),
                    'fecha_inicio' => $nuevoModulo->FECHAINICIO,
                    'fecha_fin' => $nuevoModulo->FECHAFIN
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
