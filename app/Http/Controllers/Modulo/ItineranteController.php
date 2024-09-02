<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Itinerante;
use App\Models\MAC;
use App\Models\Personal;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItineranteController extends Controller
{
    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
            ->where('M_CENTRO_MAC.IDCENTRO_MAC', $us_id)
            ->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;

        return (object) ['idmac' => $idmac, 'name_mac' => $name_mac];
    }

    // Método para mostrar la lista de itinerantes
    public function index()
    {
        return view('itinerante.index');
    }

    public function tb_index()
    {
        $itinerantes = DB::table('M_ITINERANTE')
            ->join('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'M_ITINERANTE.IDCENTRO_MAC')
            ->join('M_PERSONAL', 'M_PERSONAL.NUM_DOC', '=', 'M_ITINERANTE.NUM_DOC')
            ->join('M_MODULO', 'M_MODULO.IDMODULO', '=', 'M_ITINERANTE.IDMODULO')
            ->select(
                'M_ITINERANTE.ID',
                'M_CENTRO_MAC.IDCENTRO_MAC',
                'M_CENTRO_MAC.NOMBRE_MAC',
                'M_ITINERANTE.NUM_DOC',
                DB::raw("CONCAT(M_PERSONAL.NOMBRE, ' ', M_PERSONAL.APE_PAT, ' ', M_PERSONAL.APE_MAT) AS NOMBRE_COMPLETO"),
                'M_MODULO.N_MODULO',
                'M_ITINERANTE.FECHAINICIO',
                'M_ITINERANTE.FECHAFIN'
            )
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('M_CENTRO_MAC.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->get();

        return view('itinerante.tablas.tb_index', compact('itinerantes'));
    }



    // Método para mostrar el formulario de creación de itinerantes
    public function create()
    {
        if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
            $centros = MAC::where('IDCENTRO_MAC', $this->centro_mac()->idmac)->get();
            $personales = Personal::where('IDMAC', $this->centro_mac()->idmac)->get();
            $modulos = Modulo::where('IDCENTRO_MAC', $this->centro_mac()->idmac)->get();
        } else {
            $centros = MAC::all();
            $personales = Personal::all();
            $modulos = Modulo::all();
        }

        try {
            $view = view('itinerante.modals.md_add_itinerante', compact('centros', 'personales', 'modulos'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Método para almacenar un nuevo itinerante
    public function store(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'IDCENTRO_MAC' => 'required|integer|exists:m_centro_mac,IDCENTRO_MAC', // Valida que IDCENTRO_MAC exista en la tabla m_centro_mac
            'NUM_DOC' => 'required|string|max:20|exists:m_personal,NUM_DOC', // Valida que NUM_DOC exista en la tabla m_personal
            'IDMODULO' => 'required|integer|exists:m_modulo,IDMODULO', // Valida que IDMODULO exista en la tabla m_modulo
            'FECHAINICIO' => 'required|date',
            'FECHAFIN' => 'required|date',
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
            // Crear y guardar el nuevo itinerante
            $itinerante = new Itinerante();
            $itinerante->IDCENTRO_MAC = $request->IDCENTRO_MAC; // Asignar el ID del centro MAC recibido en la solicitud
            $itinerante->NUM_DOC = $request->NUM_DOC; // Asignar el número de documento recibido en la solicitud
            $itinerante->IDMODULO = $request->IDMODULO; // Asignar el ID del módulo recibido en la solicitud
            $itinerante->FECHAINICIO = $request->FECHAINICIO;
            $itinerante->FECHAFIN = $request->FECHAFIN;
            $itinerante->save();

            // Responder con un mensaje de éxito y los datos del itinerante creado
            return response()->json([
                'data' => $itinerante,
                'message' => 'Itinerante guardado exitosamente',
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

    // Método para editar un itinerante
    // Método para editar un itinerante
    public function edit(Request $request)
    {
        try {
            // Busca el itinerante por su ID
            $itinerante = Itinerante::where('ID', $request->id_itinerante)->first();

            if (!$itinerante) {
                return response()->json(['error' => 'Itinerante no encontrado'], 404);
            }

            // Obtener los centros MAC para el select en el formulario
            $centros = DB::table('m_centro_mac')->get();

            // Obtener el personal para el select en el formulario
            $personales = DB::table('m_personal')->get();

            // Obtener los módulos para el select en el formulario
            $modulos = DB::table('m_modulo')->get();

            // Renderiza la vista con los datos del itinerante
            $view = view('itinerante.modals.md_edit_itinerante', compact('itinerante', 'centros', 'personales', 'modulos'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Método para actualizar un itinerante
    public function update(Request $request)
    {
        $request->validate([
            'IDCENTRO_MAC' => 'required|integer|exists:m_centro_mac,IDCENTRO_MAC',
            'NUM_DOC' => 'required|string|max:20|exists:m_personal,NUM_DOC',
            'IDMODULO' => 'required|integer|exists:m_modulo,IDMODULO',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date',
            'ID' => 'required|integer|exists:m_itinerante,ID',
        ]);

        try {
            $itinerante = Itinerante::find($request->ID);
            if (!$itinerante) {
                return response()->json(['message' => 'Itinerante no encontrado'], 404);
            }

            $idCentroMac = auth()->user()->idcentro_mac;
            $itinerante->IDCENTRO_MAC = $idCentroMac;  // Asignar id_centromac obtenido del usuario autenticado
            $itinerante->NUM_DOC = $request->NUM_DOC;
            $itinerante->IDMODULO = $request->IDMODULO;
            $itinerante->fechainicio = $request->fechainicio;
            $itinerante->fechafin = $request->fechafin;
            $itinerante->save();

            return response()->json(['message' => 'Itinerante actualizado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    // Método para eliminar un itinerante
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:M_itinerante,ID',
        ]);

        try {
            $itinerante = Itinerante::find($request->id);
            if (!$itinerante) {
                return response()->json(['message' => 'Itinerante no encontrado'], 404);
            }

            $itinerante->delete();

            return response()->json(['message' => 'Itinerante eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
