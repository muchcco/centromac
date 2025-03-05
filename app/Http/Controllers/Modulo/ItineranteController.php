<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Itinerante;
use App\Models\MAC;
use App\Models\Personal;
use App\Models\Modulo;
use App\Models\PersonalModulo; // Modelo para m_personal_modulo
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItineranteController extends Controller
{
    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUÉ CENTRO MAC PERTENECE
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        $resp = ['idmac' => $idmac, 'name_mac' => $name_mac];

        return (object) $resp;
    }

    // Mostrar la vista principal
    public function index()
    {
        return view('personalmoduloitinerante.index'); // Cambia el nombre de la vista según la ruta de tu proyecto
    }
    public function tb_index()
    {
        // Realizar la consulta a la base de datos usando el Query Builder de Laravel
        $personalModulos = DB::table('m_personal_modulo')
            ->join('m_personal', 'm_personal_modulo.num_doc', '=', 'm_personal.num_doc')  // Join con la tabla m_personal
            ->join('m_modulo', 'm_personal_modulo.idmodulo', '=', 'm_modulo.IDMODULO')     // Join con la tabla m_modulo
            ->leftJoin('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')      // Left Join con la tabla m_entidad
            ->join('m_centro_mac', 'm_personal_modulo.idcentro_mac', '=', 'm_centro_mac.IDCENTRO_MAC')  // Join con la tabla m_centro_mac
            ->where('m_personal_modulo.idcentro_mac', '=', auth()->user()->idcentro_mac)  // Filtrar por el centro MAC del usuario autenticado
            ->where('m_personal_modulo.status', '=', 'itinerante')  // Agregar condición para filtrar por el status 'itinerante'
            ->select(
                'm_personal_modulo.*',  // Seleccionar todos los campos de m_personal_modulo
                'm_personal.NOMBRE',    // Nombre del personal
                'm_personal.APE_PAT',   // Apellido paterno del personal
                'm_personal.APE_MAT',   // Apellido materno del personal
                'm_modulo.N_MODULO',    // Nombre del módulo
                'm_entidad.NOMBRE_ENTIDAD',  // Nombre de la entidad
                'm_centro_mac.NOMBRE_MAC'    // Nombre del centro MAC
            )
            ->get();  // Obtener los resultados

        // Ver los datos recuperados (para depuración, comentar o eliminar en producción)
        // dd($personalModulos);

        // Retornar la vista con los datos
        return view('personalmoduloitinerante.tablas.tb_index', compact('personalModulos'));
    }
    // Mostrar el formulario de creación
    public function create()
    {
        // Obtener los módulos disponibles junto con la entidad asociada
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
            ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD')
            ->where('m_modulo.IDCENTRO_MAC', auth()->user()->idcentro_mac) // Filtrar por el centro MAC del usuario autenticado
            ->get();
        $allowedCentrosMac = [10, 12, 13, 14, 19];

        $userCentroMac = auth()->user()->idcentro_mac;

        if (!in_array($userCentroMac, $allowedCentrosMac)) {
            // Obtener el personal junto con sus nombres completos
            $personal = DB::table('m_personal')
                ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
                ->where('IDMAC', auth()->user()->idcentro_mac)
                // Filtrar por el centro MAC del usuario autenticado
                ->get();
        } else {
            // Obtener el personal junto con sus nombres completos
            $personal = DB::table('m_personal')
                ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
                ->whereIn('IDMAC', [10, 12, 13, 14, 19])
                // Filtrar por el centro MAC del usuario autenticado
                ->get();
        }
        try {
            // Pasa las variables 'modulos' y 'personal' a la vista
            $view = view('personalmoduloitinerante.modals.md_add_personalModulo', compact('modulos', 'personal'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'num_doc' => 'required|string|max:20|exists:m_personal,num_doc',
            'idmodulo' => 'required|integer|exists:m_modulo,idmodulo',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date|after_or_equal:fechainicio', // Validar que la fecha de fin sea posterior o igual a la fecha de inicio
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Verificar si ya existe un registro para el mismo num_doc con cruce de fechas
            $existingRecord = PersonalModulo::where('num_doc', $request->num_doc)
                ->where('idcentro_mac', auth()->user()->idcentro_mac)
                ->where('status', 'itinerante') // Agregar la condición para que solo busque los registros con status 'itinerante'
                ->where(function ($query) use ($request) {
                    // Validación de cruce de fechas
                    $query->whereBetween('fechainicio', [$request->fechainicio, $request->fechafin])
                        ->orWhereBetween('fechafin', [$request->fechainicio, $request->fechafin])
                        ->orWhere(function ($query2) use ($request) {
                            $query2->where('fechainicio', '<', $request->fechafin)
                                ->where('fechafin', '>', $request->fechainicio);
                        });
                })
                ->exists();


            if ($existingRecord) {
                // Si existe un cruce de fechas, retornar el mensaje de error para mostrar en el modal
                return response()->json([
                    'data' => null,
                    'message' => 'El número de documento ya tiene un registro en ese rango de fechas.',
                    'status' => 422,
                    'show_modal' => true // Asegúrate de que este valor esté siendo enviado
                ], 422);
            }

            // Crear un nuevo registro de PersonalModulo
            $personalModulo = new PersonalModulo();
            $personalModulo->num_doc = $request->num_doc;
            $personalModulo->idmodulo = $request->idmodulo;
            $personalModulo->fechainicio = $request->fechainicio;
            $personalModulo->fechafin = $request->fechafin;
            $personalModulo->status = 'itinerante'; // Asignar el status 'itinerante'
            $personalModulo->idcentro_mac = auth()->user()->idcentro_mac; // Obtener el ID del Centro MAC del usuario autenticado
            $personalModulo->save();

            return response()->json([
                'data' => $personalModulo,
                'message' => 'Personal Módulo guardado exitosamente',
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
    // Mostrar el formulario de edición
    public function edit(Request $request)
    {
        try {
            // Buscar el registro de PersonalModulo utilizando el ID proporcionado
            $personalModulo = PersonalModulo::findOrFail($request->id);

            // Obtener los módulos disponibles del centro MAC del usuario autenticado
            $modulos = DB::table('m_modulo')
                ->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_modulo.IDENTIDAD')
                ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD')
                ->where('m_modulo.IDCENTRO_MAC', auth()->user()->idcentro_mac)
                ->get();

            // Obtener la lista de personal del centro MAC del usuario autenticado
            $personal = DB::table('m_personal')
                ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
                ->where('IDMAC', auth()->user()->idcentro_mac)
                ->get();

            // Renderizar la vista con los datos obtenidos
            $view = view('personalmoduloitinerante.modals.md_edit_personalModulo', compact('personalModulo', 'modulos', 'personal'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'num_doc' => 'required|string|max:20|exists:m_personal,num_doc',
            'idmodulo' => 'required|integer|exists:m_modulo,idmodulo',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date|after_or_equal:fechainicio', // Validar que la fecha de fin sea posterior o igual a la fecha de inicio
            'id' => 'required|integer|exists:m_personal_modulo,id', // Validar que el ID exista en la tabla m_personal_modulo
        ]);

        // Si la validación falla
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->all(), // Envía todos los errores de validación
                'status' => 422
            ], 422);
        }

        try {
            // Buscar el registro por su ID
            $personalModulo = PersonalModulo::findOrFail($request->id);

            // Verificar si ya existe un registro para el mismo num_doc con cruce de fechas (excepto el registro actual)
            $existingRecord = PersonalModulo::where('num_doc', $request->num_doc)
                ->where('idcentro_mac', auth()->user()->idcentro_mac)
                ->where('status', 'itinerante') // Buscar solo los registros con status 'itinerante'
                ->where('id', '!=', $personalModulo->id) // Asegurarse de que no sea el mismo registro que estamos actualizando
                ->where(function ($query) use ($request) {
                    // Validación de cruce de fechas
                    $query->whereBetween('fechainicio', [$request->fechainicio, $request->fechafin])
                        ->orWhereBetween('fechafin', [$request->fechainicio, $request->fechafin])
                        ->orWhere(function ($query2) use ($request) {
                            $query2->where('fechainicio', '<', $request->fechafin)
                                ->where('fechafin', '>', $request->fechainicio);
                        });
                })
                ->exists();

            if ($existingRecord) {
                return response()->json([
                    'message' => 'El número de documento ya tiene un registro en ese rango de fechas.',
                    'status' => 422,
                    'show_modal' => true // Mostrar modal para error
                ], 422);
            }

            // Actualizar los campos
            $personalModulo->num_doc = $request->num_doc;
            $personalModulo->idmodulo = $request->idmodulo;
            $personalModulo->fechainicio = $request->fechainicio;
            $personalModulo->fechafin = $request->fechafin;
            $personalModulo->status = 'itinerante'; // Asegurarse de que el status sea 'itinerante'
            $personalModulo->idcentro_mac = auth()->user()->idcentro_mac; // Actualizar el idcentro_mac con el del usuario autenticado
            $personalModulo->save();

            return response()->json([
                'data' => $personalModulo,
                'message' => 'Personal Módulo actualizado exitosamente',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'Error al procesar la solicitud',
                'status' => 400
            ], 400);
        }
    }


    // Eliminar un registro
    public function destroy(Request $request)
    {
        DB::beginTransaction();

        try {
            $personalModulo = PersonalModulo::findOrFail($request->id);
            $personalModulo->delete();

            DB::commit();

            return response()->json([
                'message' => 'Personal Módulo eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
