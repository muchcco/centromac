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
            // $itinerante = Itinerante::where('id', $feriado->id_itinerante)->first();

            // Renderizar la vista con los datos del feriado y el itinerante
            $view = view('feriados.modals.md_edit_feriado', compact('feriado'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Método para actualizar un Feriado
    public function update(Request $request)
    {
        // Validar los datos recibidos en la solicitud
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',  // Nombre del feriado
            'fecha' => 'required|date',           // Fecha del feriado
            'id_feriado' => 'required|integer|exists:feriados,id',  // Asegúrate de que el ID del feriado exista
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'message' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            // Encontrar el feriado a actualizar
            $feriado = Feriado::find($request->id_feriado);
            if (!$feriado) {
                return response()->json(['message' => 'Feriado no encontrado'], 404);
            }

            // No actualizar idcentro_mac, solo los campos 'name' y 'fecha'
            $feriado->name = $request->name;
            $feriado->fecha = $request->fecha;

            // Guardar el feriado con los nuevos valores de nombre y fecha, manteniendo idcentro_mac sin cambios
            $feriado->save();

            return response()->json([
                'data' => $feriado,
                'message' => 'Feriado actualizado exitosamente',
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

    // Método para eliminar un feriado
    public function destroy(Request $request)
    {
        // Iniciar una transacción para asegurar la integridad de la operación
        DB::beginTransaction();

        try {
            // Obtener el centro MAC del usuario autenticado
            $centroMac = $this->centro_mac();  // Llamar al método centro_mac para obtener el idcentro_mac

            // Encontrar el feriado utilizando Eloquent para mayor seguridad
            $feriado = Feriado::findOrFail($request->id);
            // Verificar si el feriado pertenece al mismo centro MAC que el usuario
            if ($feriado->id_centromac !== $centroMac->idmac) {
                return response()->json(['error' => 'No tienes permisos para eliminar este feriado'], 403);
            }

            // Eliminar el feriado
            $feriado->delete();

            // Confirmar la transacción si todo ha ido bien
            DB::commit();

            return response()->json(['message' => 'Feriado eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
