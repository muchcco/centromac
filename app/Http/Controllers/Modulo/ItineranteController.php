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


    // Método para mostrar la lista inicial de itinerantes
    public function index()
    {
        return view('itinerante.index');
    }

    // Método para cargar los datos de los itinerantes en la tabla
    public function tb_index()
    {
        $itinerantes = Itinerante::with('centroMac', 'personal', 'modulo')
                        ->where(function($query) {
                            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                                $query->where('IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                            }
                        })
                        ->get();
        return view('itinerante.tablas.tb_index', compact('itinerantes'));
    }

    // Método para mostrar el formulario de creación de itinerantes
    public function create()
    {
        try {
            $centros = Mac::all();
            $personales = Personal::all();
            $modulos = Modulo::all();

            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                $centros = Mac::where('IDCENTRO_MAC', $this->centro_mac()->idmac)->get();

                $personales = Personal::where('IDMAC', $this->centro_mac()->idmac)->get();

                $modulos = Modulo::where('IDCENTRO_MAC', $this->centro_mac()->idmac)->get();
            } else{
                $centros = Mac::all();
                $personales = Personal::all();
                $modulos = Modulo::all();
            }

            $view = view('itinerante.modals.md_add_itinerante', compact('centros', 'personales', 'modulos'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Método para almacenar un nuevo itinerante
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'IDCENTRO_MAC' => 'required|integer|exists:m_centro_mac,IDCENTRO_MAC',
            'NUM_DOC' => 'required|string|exists:m_personal,NUM_DOC',
            'IDMODULO' => 'required|integer|exists:m_modulo,IDMODULO',
            'fechainicio' => 'required|date',
            'fechafin' => 'required|date|after_or_equal:fechainicio',
        ]);

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
            $itinerante->IDCENTRO_MAC = $request->IDCENTRO_MAC;
            $itinerante->NUM_DOC = $request->NUM_DOC;
            $itinerante->IDMODULO = $request->IDMODULO;
            $itinerante->fechainicio = $request->fechainicio;
            $itinerante->fechafin = $request->fechafin;
            $itinerante->save();

            return response()->json([
                'data' => $itinerante,
                'message' => 'Itinerante guardado exitosamente',
                'status' => 201  // Estado HTTP para creación exitosa
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

    // Método para mostrar el formulario de edición de itinerantes
    public function edit(Request $request)
    {
        try {

            $itinerante = Itinerante::where('IDCENTRO_MAC', $request->IDCENTRO_MAC)
                ->where('NUM_DOC', $request->NUM_DOC)
                ->where('IDMODULO', $request->IDMODULO)
                ->first();

            // dd($request->all());

            if (!$itinerante) {
                return response()->json(['error' => 'Itinerante no encontrado'], 404);
            }

            if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                $centros = Mac::where('IDCENTRO_MAC', $this->centro_mac()->idmac)->get();

                $personales = Personal::where('IDMAC', $this->centro_mac()->idmac)->get();

                $modulos = Modulo::where('IDCENTRO_MAC', $this->centro_mac()->idmac)->get();
            } else{
                $centros = Mac::all();
                $personales = Personal::all();
                $modulos = Modulo::all();
            }

            $view = view('itinerante.modals.md_edit_itinerante', compact('itinerante', 'centros', 'personales', 'modulos'))->render();
            return response()->json(['html' => $view]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   // Método para actualizar un itinerante
   public function update(Request $request)
   {
       // Validación de los datos de entrada
       $request->validate([
           'IDCENTRO_MAC' => 'required|exists:centros,IDCENTRO_MAC',
           'NUM_DOC' => 'required|exists:personal,NUM_DOC',
           'IDMODULO' => 'required|exists:modulos,IDMODULO',
           'fechainicio' => 'required|date',
           'fechafin' => 'required|date|after_or_equal:fechainicio',
       ]);

       try {
           // Buscar el itinerante por su identificador
           $itinerante = Itinerante::find($request->id_itinerante);
           if (!$itinerante) {
               return response()->json(['message' => 'Itinerante no encontrado'], 404);
           }

           // Actualizar los campos del itinerante
           $itinerante->IDCENTRO_MAC = $request->IDCENTRO_MAC;
           $itinerante->NUM_DOC = $request->NUM_DOC;
           $itinerante->IDMODULO = $request->IDMODULO;
           $itinerante->fechainicio = $request->fechainicio;
           $itinerante->fechafin = $request->fechafin;
           $itinerante->save();

           return response()->json(['message' => 'Itinerante actualizado exitosamente'], 200);
       } catch (\Exception $e) {
           return response()->json(['error' => 'Error al actualizar el itinerante: ' . $e->getMessage()], 400);
       }
   }
    // Método para eliminar un itinerante
    public function destroy(Request $request)
    {
        DB::beginTransaction();

        try {
            $itinerante = Itinerante::where('IDCENTRO_MAC', $request->IDCENTRO_MAC)
                ->where('NUM_DOC', $request->NUM_DOC)
                ->where('IDMODULO', $request->IDMODULO)
                ->firstOrFail();

            $itinerante->delete();

            DB::commit();

            return response()->json(['message' => 'Itinerante eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
