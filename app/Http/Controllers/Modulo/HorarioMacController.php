<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HorarioMac;
use App\Models\Mac;
use App\Models\Modulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HorarioMacController extends Controller
{
    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;

        $resp = ['idmac' => $idmac, 'name_mac' => $name_mac];

        return (object) $resp;
    }

    // Método para mostrar la lista inicial de horarios
    public function index()
    {
        return view('horariomac.index');
    }

    // Método para cargar los datos de los horarios en la tabla
    public function tb_index()
    {
        // Obtener todos los horarios
        $horarios = HorarioMac::all();

        // Realizar el join entre m_modulo y m_entidad
        $modulos = Modulo::join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
            ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD','m_entidad.ABREV_ENTIDAD')
            ->get();

        // Asignar los datos de los módulos y entidades a cada horario
        foreach ($horarios as $horario) {
            // Buscar el módulo y asignar los datos
            $modulo = $modulos->firstWhere('IDMODULO', $horario->idmodulo);

            // Asignar el nombre del módulo y el nombre de la entidad
            $horario->nombre_modulo = $modulo ? $modulo->N_MODULO : 'Todos';
            $horario->nombre_entidad = $modulo ? $modulo->ABREV_ENTIDAD : 'Todos';

            // Obtener el nombre del centro MAC
            $centroMac = Mac::find($horario->idcentro_mac);
            $horario->nombre_centromac = $centroMac ? $centroMac->NOMBRE_MAC : 'TODOS';
        }

        // Pasar los horarios (con nombre de centro MAC, módulo y entidad) a la vista
        return view('horariomac.tablas.tb_index', compact('horarios'));
    }


    public function create()
    {
        try {
            // Obtener los módulos y centros MAC
            // Realizar el join entre m_modulo y m_entidad
            $modulos = Modulo::join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
                ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD', 'm_entidad.ABREV_ENTIDAD') // Seleccionamos los campos necesarios
                ->get();
            $centrosMac = Mac::all(); // Obtén los centros MAC

            // Renderiza la vista y pasa los módulos y centros MAC a la vista
            $view = view('horariomac.modals.md_add_horariomac', compact('modulos', 'centrosMac'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processing request'], 500);
        }
    }

    public function store(Request $request)
    {
        // dd($request->all()); // Esto te permitirá ver todos los datos recibidos en el request

        $validator = Validator::make($request->all(), [
            'horaingreso' => 'required|date_format:H:i',
            'horasalida' => 'nullable|date_format:H:i',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date',
            'idcentro_mac' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            $horario = new HorarioMac();
            $horario->idcentro_mac = $request->idcentro_mac; // This should work if the value is passed correctly
            $horario->idmodulo = $request->idmodulo;
            $horario->horaingreso = $request->horaingreso;
            $horario->horasalida = $request->horasalida;
            $horario->fechainicio = $request->fechainicio;
            $horario->fechafin = $request->fechafin;
            $horario->save();

            return response()->json([
                'data' => $horario,
                'message' => 'Horario guardado exitosamente',
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

    public function edit(Request $request)
    {
        try {
            // Verifica que el ID del horario se pase correctamente
            if (!$request->has('idhorario')) {
                return response()->json([
                    'error' => 'ID de horario no proporcionado',
                    'status' => 400
                ], 400);
            }

            $horario = HorarioMac::findOrFail($request->idhorario); // Encuentra el horario por ID
            // Formatear horaingreso y horasalida
            // $horario->horaingreso = date('H:i', strtotime($horario->horaingreso));
            // $horario->horasalida = date('H:i', strtotime($horario->horasalida));
            $modulos = Modulo::join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
                ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD', 'm_entidad.ABREV_ENTIDAD') // Seleccionamos los campos necesarios
                ->get();
            $centrosMac = Mac::all(); // Obtén todos los centros MAC

            // Renderiza la vista del modal con los datos del horario
            $view = view('horariomac.modals.md_edit_horariomac', compact('horario', 'modulos', 'centrosMac'))->render();

            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            // Captura y muestra el error para depuración
            return response()->json([
                'error' => 'Error al cargar la información del horario: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function update(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'horaingreso' => 'required',
            'horasalida' => 'nullable',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date',
            'idcentro_mac' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Buscar el horario por ID
            $horario = HorarioMac::findOrFail($request->idhorario);

            // Actualizar los datos del horario
            $horario->idcentro_mac = $request->idcentro_mac;
            $horario->idmodulo = $request->idmodulo;
            $horario->horaingreso = $request->horaingreso;
            $horario->horasalida = $request->horasalida;
            $horario->fechainicio = $request->fechainicio;
            $horario->fechafin = $request->fechafin;
            $horario->save();

            return response()->json([
                'data' => $horario,
                'message' => 'Horario actualizado exitosamente',
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


    // Método para eliminar un horario
    public function destroy(Request $request)
    {
        try {
            // Validar que el id_horario haya sido proporcionado
            if (!$request->has('id')) {
                return response()->json(['error' => 'ID del horario no proporcionado'], 400);
            }

            // Encontrar el horario utilizando el ID
            $horario = HorarioMac::findOrFail($request->id);  // Asegúrate de usar el mismo nombre del campo que el que se pasa en la solicitud

            // Eliminar el horario
            $horario->delete();

            return response()->json(['message' => 'Horario eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function getModulosByCentroMac($idcentromac)
    {
        // Obtener los módulos correspondientes al centro MAC
        $modulos = Modulo::join('m_entidad', 'm_modulo.IDENTIDAD', '=', 'm_entidad.IDENTIDAD')
            ->where('m_modulo.idcentro_mac', $idcentromac)
            ->select('m_modulo.IDMODULO', 'm_modulo.N_MODULO', 'm_entidad.NOMBRE_ENTIDAD', 'm_entidad.ABREV_ENTIDAD') // Seleccionamos los campos necesarios
            ->get();
        // Devolver los módulos como JSON
        return response()->json($modulos);
    }
}
