<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Feriado;
use App\Models\Modulo;
use Illuminate\Support\Facades\Validator;


class FeriadoController extends Controller

{
    // Método para mostrar la lista inicial de feriados
    public function index()
    {
        return view('feriados.index');
    }

    // Método para cargar los datos de los feriados en la tabla
    public function tb_index()
    {
        $feriados = Feriado::all(); // Obtener todos los feriados
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
        $validator = Validator::make($request->all(), [
            'nombre_modulo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'entidad_id' => 'required|integer|exists:entidades,id',
            'id_centromac' => 'required|integer|exists:centros_mac,id', // Agrega la validación para id_centromac
        ]);
    
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
            $modulo->fechainicio = $request->fecha_inicio;
            $modulo->fechafin = $request->fecha_fin;
            $modulo->IDENTIDAD = $request->entidad_id;
            $modulo->IDCENTRO_MAC = $request->id_centromac;  // Asignar el id_centromac recibido en la solicitud
            $modulo->save();
    
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
    
    public function edit(Request $request)
    {
        try {
            // Asumiendo que cada feriado puede o no estar asociado a un centro MAC específico
            $feriado = Feriado::where('id', $request->id_feriado)->first();

            if (!$feriado) {
                return response()->json(['error' => 'Feriado no encontrado'], 404);
            }

            // Renderiza la vista con los datos del feriado
            $view = view('feriados.modals.md_edit_feriado', compact('feriado'))->render();
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
           'id_itinerante' => 'required|integer|exists:itinerantes,id'
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
    // Método para eliminar un feriado
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
