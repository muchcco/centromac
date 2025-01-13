<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonalModulo; // Modelo para m_personal_modulo
use App\Models\Personal; // Modelo para m_personal
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonalModuloController extends Controller
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
        return view('personalmodulo.index'); // Cambia el nombre de la vista según la ruta de tu proyecto
    }
    // Cargar datos de la tabla
    public function tb_index()
    {
        $personalModulos = DB::table('m_personal_modulo')
            ->join('m_personal', 'm_personal_modulo.num_doc', '=', 'm_personal.num_doc')
            ->join('m_modulo', 'm_personal_modulo.idmodulo', '=', 'm_modulo.IDMODULO')
            ->leftJoin('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
            ->join('m_centro_mac', 'm_personal_modulo.idcentro_mac', '=', 'm_centro_mac.IDCENTRO_MAC')
            ->where('m_personal_modulo.status', '=', 'fijo')  // Agregar condición para filtrar por el status 'itinerante'
            // ->where('m_personal_modulo.idcentro_mac', '=', auth()->user()->idcentro_mac)  // Filtra solo por el idcentro_mac del usuario autenticado
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('m_personal_modulo.idcentro_mac', '=', $this->centro_mac()->idmac);
                }
            })
            ->select(
                'm_personal_modulo.*',
                'm_personal.NOMBRE',
                'm_personal.APE_PAT',
                'm_personal.APE_MAT',
                'm_modulo.N_MODULO',
                'm_entidad.NOMBRE_ENTIDAD',
                'm_centro_mac.NOMBRE_MAC'
            )
            ->get();

        return view('personalmodulo.tablas.tb_index', compact('personalModulos'));
    }


    // Mostrar el formulario de creación
    public function create()
    {

        $allowedCentrosMac = [10, 12, 13, 14, 19];

        $userCentroMac = auth()->user()->idcentro_mac;

        if (!in_array($userCentroMac, $allowedCentrosMac)) {
            // Obtener el personal junto con sus nombres completos
            $personal = DB::table('m_personal')
            ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
            ->where('IDMAC', auth()->user()->idcentro_mac)
            // Filtrar por el centro MAC del usuario autenticado
            ->get();
        }else{
            // Obtener el personal junto con sus nombres completos
            $personal = DB::table('m_personal')
            ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
            ->whereIn('IDMAC', [10, 12, 13, 14, 19])
            // Filtrar por el centro MAC del usuario autenticado
            ->get();
        }

        // Obtener los módulos disponibles junto con la entidad asociada
        $modulos = DB::table('m_modulo')
            ->join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
            ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD')
            ->where('m_modulo.IDCENTRO_MAC', auth()->user()->idcentro_mac) // Filtrar por el centro MAC del usuario autenticado
            ->get();

       
        try {
            // Pasa las variables 'modulos' y 'personal' a la vista
            $view = view('personalmodulo.modals.md_add_personalModulo', compact('modulos', 'personal'))->render();
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
            // Crear un nuevo registro de PersonalModulo
            $personalModulo = new PersonalModulo();
            $personalModulo->num_doc = $request->num_doc;
            $personalModulo->idmodulo = $request->idmodulo;
            $personalModulo->fechainicio = $request->fechainicio;
            $personalModulo->fechafin = $request->fechafin;
            $personalModulo->status = 'fijo'; // Asignar el status 'itinerante'
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

            $allowedCentrosMac = [10, 12, 13, 14, 19];

            $userCentroMac = auth()->user()->idcentro_mac;

            if (!in_array($userCentroMac, $allowedCentrosMac)) {
                // Obtener el personal junto con sus nombres completos
                $personal = DB::table('m_personal')
                ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
                ->where('IDMAC', auth()->user()->idcentro_mac)
                // Filtrar por el centro MAC del usuario autenticado
                ->get();
            }else{
                // Obtener el personal junto con sus nombres completos
                $personal = DB::table('m_personal')
                ->select('num_doc', DB::raw("CONCAT(NOMBRE, ' ', APE_PAT, ' ', APE_MAT) AS nombre_completo"))
                ->whereIn('IDMAC', [10, 12, 13, 14, 19])
                // Filtrar por el centro MAC del usuario autenticado
                ->get();
            }


            // Buscar el registro de PersonalModulo utilizando el ID proporcionado
            $personalModulo = PersonalModulo::findOrFail($request->id);

            // Obtener los módulos disponibles del centro MAC del usuario autenticado
            $modulos = DB::table('m_modulo')
                ->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_modulo.IDENTIDAD')
                ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD')
                ->where('m_modulo.IDCENTRO_MAC', auth()->user()->idcentro_mac)
                ->get();

            // Renderizar la vista con los datos obtenidos
            $view = view('personalmodulo.modals.md_edit_personalModulo', compact('personalModulo', 'modulos', 'personal'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Actualizar un registro existente
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

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Buscar el registro por su ID
            $personalModulo = PersonalModulo::findOrFail($request->id);

            // Actualizar los campos
            $personalModulo->num_doc = $request->num_doc;
            $personalModulo->idmodulo = $request->idmodulo;
            $personalModulo->fechainicio = $request->fechainicio;
            $personalModulo->fechafin = $request->fechafin;
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
    public function getFechasModulo($id)
    {
        $modulo = DB::table('m_modulo')
            ->select('fechainicio', 'fechafin')
            ->where('idmodulo', '=', $id)
            ->first();

        // Aborta directamente si no se encuentra el módulo, sin necesidad de comprobarlo después
        if (!$modulo) {
            return abort(404, 'Módulo no encontrado');
        }

        // Utiliza una función de transformación para manejar las fechas de forma más elegante
        $transformDate = function ($date) {
            return $date ? Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d') : null;
        };

        // Aplica la transformación a las fechas de inicio y fin
        return response()->json([
            'fechainicio' => $transformDate($modulo->fechainicio),
            'fechafin' => $transformDate($modulo->fechafin)
        ]);
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
