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

    private function centro_mac(){
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac'=>$idmac, 'name_mac'=>$name_mac ];

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
        $modulos = DB::table('M_MODULO')
                        ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MODULO.IDENTIDAD')
                        ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MODULO.IDCENTRO_MAC')
                        ->where(function($query) {
                            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                                $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                            }
                        })
                        ->get();
        
        // dd($modulos);
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
        } else{
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
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'nombre_modulo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'entidad_id' => 'required|integer|exists:m_entidad,IDENTIDAD', // Valida que entidad_id exista en la tabla m_entidad
            'id_centromac' => 'required|integer|exists:m_centro_mac,IDCENTRO_MAC', // Valida que id_centromac exista en la tabla m_centro_mac
        ]);

        // Si la validación falla, devolver un error 422 con los mensajes de error
        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Crear y guardar el nuevo módulo
            $modulo = new Modulo();
            $modulo->N_MODULO = $request->nombre_modulo;
            $modulo->FECHAINICIO = $request->fecha_inicio;
            $modulo->FECHAFIN = $request->fecha_fin;
            $modulo->IDENTIDAD = $request->entidad_id; // Asigna el IDENTIDAD del módulo
            $modulo->IDCENTRO_MAC = $request->id_centromac;  // Asignar el id_centromac recibido en la solicitud
            $modulo->save();

            // Responder con un mensaje de éxito y los datos del módulo creado
            return response()->json([
                'data' => $modulo,
                'message' => 'Módulo guardado exitosamente',
                'status' => 201  // Estado HTTP para creación exitosa
            ], 201);
        } catch (\Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la transacción
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
            // Busca el módulo por su ID
            $modulo = Modulo::where('IDMODULO', $request->id_modulo)->first();

            if (!$modulo) {
                return response()->json(['error' => 'Módulo no encontrado'], 404);
            }

            // Obtener las entidades para el select en el formulario
            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                $entidades = DB::table('M_MAC_ENTIDAD')
                            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_MAC_ENTIDAD.IDCENTRO_MAC')
                            ->join('M_ENTIDAD', 'M_ENTIDAD.IDENTIDAD', '=', 'M_MAC_ENTIDAD.IDENTIDAD')
                            ->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac)
                            ->get();
            } else{
                $entidades = Entidad::all();
            }

            // Renderiza la vista con los datos del módulo
            $view = view('modulo.modals.md_edit_modulo', compact('modulo', 'entidades'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Método para actualizar un módulo
    public function update(Request $request)
    {
        $request->validate([
            'nombre_modulo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'id_modulo' => 'required|integer|exists:m_modulo,IDMODULO',
        ]);

        try {
            $modulo = Modulo::find($request->id_modulo);
            if (!$modulo) {
                return response()->json(['message' => 'Módulo no encontrado'], 404);
            }
            $idCentroMac = auth()->user()->idcentro_mac;

            $modulo->N_MODULO = $request->nombre_modulo;
            $modulo->FECHAINICIO = $request->fecha_inicio;
            $modulo->FECHAFIN = $request->fecha_fin;
            $modulo->IDENTIDAD = $request->entidad_id;
            $modulo->IDCENTRO_MAC = $idCentroMac;  // Asignar id_centromac obtenido del usuario autenticado
            $modulo->save();

            return response()->json(['message' => 'Módulo actualizado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
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