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
        return view('modulo.index');
    }

    // Método para cargar los datos de los módulos en la tabla
    public function tb_index()
    {
        // Realizar leftJoin para asegurar que los módulos sin entidad o centro MAC también se muestren
        $modulos = DB::table('M_MODULO')
            ->leftJoin('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MODULO.IDENTIDAD') // Usar leftJoin para incluir todos los módulos
            ->leftJoin('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MODULO.IDCENTRO_MAC')
            ->where(function ($query) {
                // Condición para roles específicos
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->select(
                'M_MODULO.*',
                'M_ENTIDAD.NOMBRE_ENTIDAD', // Campo de la entidad
                'M_CENTRO_MAC.NOMBRE_MAC'   // Campo del centro MAC
            )
            ->get();

        // Pasar los módulos a la vista
        return view('modulo.tablas.tb_index', compact('modulos'));
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
}
