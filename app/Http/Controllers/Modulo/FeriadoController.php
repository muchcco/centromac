<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Feriado;
use App\Models\Itinerante;
use App\Models\Modulo;
use Illuminate\Support\Facades\Validator;


class FeriadoController extends Controller

{
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

    // Método para mostrar la lista inicial de feriados
    public function index()
    {
        return view('feriados.index');
    }

    // Método para cargar los datos de los feriados en la tabla
    public function tb_index()
    {
        // Obtener todos los feriados
        $feriados = Feriado::all();

        // Obtener el nombre del centro MAC para cada feriado
        foreach ($feriados as $feriado) {
            // Obtener el nombre del centro MAC correspondiente al id_centromac
            $centroMac = DB::table('M_CENTRO_MAC')->where('IDCENTRO_MAC', $feriado->id_centromac)->first();

            // Asignar el nombre del centro MAC o un valor predeterminado si no existe
            $feriado->nombre_centromac = $centroMac ? $centroMac->NOMBRE_MAC : 'TODOS';
        }

        // Pasar los feriados (con nombre de centro MAC) a la vista
        return view('feriados.tablas.tb_index', compact('feriados'));
    }


    // Método para mostrar el formulario de creación de feriados
    public function create()
    {
        try {
            $view = view('feriados.modals.md_add_feriado')->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            // Log the error or handle it
            return response()->json(['error' => 'Error processing request'], 500);
        }
    }
    // Método para almacenar un nuevo feriado
    public function store(Request $request)
    {
        // Validar los datos recibidos en la solicitud
        $validator = Validator::make($request->all(), [
            'nombre_feriado' => 'required|string|max:255',
            'fecha_feriado' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Obtener el centro MAC utilizando la función centro_mac()
            $centroMac = $this->centro_mac();
            $idcentroMac = $centroMac->idmac; // Obtener el ID del Centro MAC
            // Crear y guardar el nuevo feriado
            $feriado = new Feriado();
            $feriado->name = $request->nombre_feriado;
            $feriado->fecha = $request->fecha_feriado;
            $feriado->id_centromac = $idcentroMac; // Usar el idcentroMac obtenido de centro_mac()
            $feriado->save();

            return response()->json([
                'data' => $feriado,
                'message' => 'Feriado guardado exitosamente',
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

    public function edit(Request $request)
    {
        try {
            // Encontrar el feriado asociado con el itinerante
            $feriado = Feriado::where('id', $request->id_feriado)->first();

            if (!$feriado) {
                return response()->json(['error' => 'Feriado no encontrado'], 404);
            }

            // Obtener el itinerante asociado al feriado
            $itinerante = Itinerante::where('id', $feriado->id_itinerante)->first();

            // Renderizar la vista con los datos del feriado y el itinerante
            $view = view('feriados.modals.md_edit_feriado', compact('feriado', 'itinerante'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Método para actualizar un itinerante
    public function update(Request $request)
    {
        // Validar los datos recibidos en la solicitud
        $validator = Validator::make($request->all(), [
            'idcentro_mac' => 'required|integer',
            'num_doc' => 'required|string|max:255',
            'idmodulo' => 'required|integer',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date',
            'id_itinerante' => 'required|integer|exists:m_itinerante,ID', // Validación para id_itinerante
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Encontrar el itinerante a actualizar
            $itinerante = Itinerante::find($request->id_itinerante);
            if (!$itinerante) {
                return response()->json(['message' => 'Itinerante no encontrado'], 404);
            }

            // Actualizar los datos del itinerante
            $itinerante->idcentro_mac = $request->idcentro_mac;
            $itinerante->num_doc = $request->num_doc;
            $itinerante->idmodulo = $request->idmodulo;
            $itinerante->fechainicio = $request->fechainicio;
            $itinerante->fechafin = $request->fechafin;
            $itinerante->save();

            return response()->json([
                'data' => $itinerante,
                'message' => 'Itinerante actualizado exitosamente',
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

    // Método para eliminar un itinerante
    public function destroy(Request $request)
    {
        // Iniciar una transacción para asegurar la integridad de la operación
        DB::beginTransaction();

        try {
            // Encontrar el itinerante utilizando Eloquent para mayor seguridad
            $itinerante = Itinerante::findOrFail($request->id);

            // Eliminar el itinerante
            $itinerante->delete();

            // Confirmar la transacción si todo ha ido bien
            DB::commit();

            return response()->json(['message' => 'Itinerante eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
