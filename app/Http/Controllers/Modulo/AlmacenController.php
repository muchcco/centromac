<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Almacen;
use App\Models\User;
use App\Imports\AlmacenImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class AlmacenController extends Controller
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

    public function index()
    {
        return view('almacen.index');
    }

    public function tb_index(Request $requet)
    {
        $query = Almacen::from('M_ALMACEN as MA')
                            ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
                            ->leftJoin('ALM_CATEGORIA as AC', 'AC.IDCATEGORIA', '=', 'MA.IDCATEGORIA')
                            ->leftJoin('ALM_MODELO as AM', 'AM.IDMODELO', '=', 'MA.IDMODELO')
                            ->leftJoin('ALM_MARCA as AMM', 'AMM.IDMARCA', '=', 'AM.IDMARCA')
                            ->where('MA.FLAG', 1)
                            ->where('MCM.IDCENTRO_MAC', $this->centro_mac()->idmac)
                            ->get();

        return view('almacen.tablas.tb_index', compact('query'));
    }

    public function tb_modelos(Request $request)
    {
        $modelo = DB::table('ALM_MODELO')->join('ALM_MARCA', 'ALM_MARCA.IDMARCA', '=', 'ALM_MODELO.IDMARCA')->get();

        return view('almacen.tablas.tb_modelos', compact('modelo'));
    }

    public function md_add_item(Request $request)
    {
        $categorias = DB::table('ALM_CATEGORIA')->get();

        $modelo = DB::table('ALM_MODELO')->join('ALM_MARCA', 'ALM_MARCA.IDMARCA', '=', 'ALM_MODELO.IDMARCA')->get();

        $marca = DB::table('ALM_MARCA')->get();

        $view = view('almacen.modals.md_add_item', compact('categorias', 'marca'))->render();

        return response()->json(['html' => $view]);
    }

    public function md_edit_item(Request $request, $id)
    {
        $categorias = DB::table('ALM_CATEGORIA')->get();

        $marca = DB::table('ALM_MARCA')->get();

        // $almacen = DB::table('M_ALMACEN')->where('IDALMACEN', $id)->first();

        $almacen = Almacen::from('M_ALMACEN as MA')
                        ->join('M_CENTRO_MAC as MCM', 'MCM.IDCENTRO_MAC', '=', 'MA.IDCENTRO_MAC')
                        ->leftJoin('ALM_CATEGORIA as AC', 'AC.IDCATEGORIA', '=', 'MA.IDCATEGORIA')
                        ->leftJoin('ALM_MODELO as AM', 'AM.IDMODELO', '=', 'MA.IDMODELO')
                        ->leftJoin('ALM_MARCA as AMM', 'AMM.IDMARCA', '=', 'AM.IDMARCA')
                        ->where('IDALMACEN', $id)
                        ->first();
        // dd($almacen);

        $view = view('almacen.modals.md_edit_item', compact('categorias', 'marca', 'almacen'))->render();

        return response()->json(['html' => $view]);
    }
    
    public function md_add_datos(Request $requet)
    {
        $view = view('almacen.modals.md_add_datos')->render();

        return response()->json(['html' => $view]);
    }

    public function md_categorias(Request $requet)
    {
        $categorias = DB::table('ALM_CATEGORIA')->get();

        $view = view('almacen.modals.md_categorias', compact('categorias'))->render();

        return response()->json(['html' => $view]);
    }

    public function md_modelo(Request $request)
    {
        $modelo = DB::table('ALM_MODELO')->join('ALM_MARCA', 'ALM_MARCA.IDMARCA', '=', 'ALM_MODELO.IDMARCA')->get();
        $marca = DB::table('ALM_MARCA')->get();

        $view = view('almacen.modals.md_modelo', compact('modelo', 'marca'))->render();

        return response()->json(['html' => $view]);
    }

    public function searchMarca(Request $request)
    {
        $term = $request->get('term'); // Término ingresado
        $marcas = DB::table('ALM_MARCA')
                    ->where('NOMBRE_MARCA', 'LIKE', '%' . $term . '%')
                    ->pluck('NOMBRE_MARCA'); // Devuelve solo los nombres de las marcas

        return response()->json($marcas); // Respuesta en formato JSON
    }


    public function store_datos(Request $request)
    {
        try {

            $id = $this->centro_mac()->idmac;

            $file = $request->file('excel_file');

            $usu_reg = auth()->user()->id;

            $upload = Excel::import(new AlmacenImport($id, $usu_reg), $file);

            return response()->json($upload);

        }catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function storeModelo(Request $request)
    {
        try {
            // Validar los datos ingresados
            $request->validate([
                'idmarca' => 'required|string|max:255',
                'idmodelo' => 'required|string|max:255',
            ]);

            $nombreMarca = $request->input('idmarca');
            $nombreModelo = $request->input('idmodelo');

            // Busca si la marca ya existe
            $marca = DB::table('ALM_MARCA')->where('NOMBRE_MARCA', $nombreMarca)->first();

            if (!$marca) {
                // Inserta la marca si no existe
                $idMarca = DB::table('ALM_MARCA')->insertGetId([
                    'NOMBRE_MARCA' => $nombreMarca,
                    'ABREV_MARCA' => $nombreMarca
                ]);
            } else {
                $idMarca = $marca->IDMARCA;
            }

            // Verifica si el modelo ya existe
            $modelo = DB::table('ALM_MODELO')
                ->where('IDMARCA', $idMarca)
                ->where('NOMBRE_MODELO', $nombreModelo)
                ->first();

            if (!$modelo) {
                // Inserta el modelo si no existe
                $idModelo = DB::table('ALM_MODELO')->insertGetId([
                    'IDMARCA' => $idMarca,
                    'NOMBRE_MODELO' => $nombreModelo,
                    'ABREV_MODELO' => $nombreModelo, // Ajustar si necesitas una abreviación distinta
                    'USU_REG' => auth()->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'data' => ['id' => $idModelo],
                    'message' => 'Modelo creado correctamente',
                ], 201);
            } else {
                return response()->json([
                    'data' => null,
                    'message' => 'El modelo ya existe',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al guardar el modelo',
            ], 500);
        }
    }

    public function store_item(Request $request)
    {
        try {
            // Validar los datos enviados
            $request->validate([
                'idcategoria' => 'required|integer',
                'cod_interno_pcm' => 'nullable|string|max:255',
                'cod_sbn' => 'nullable|string|max:255',
                'cod_pronsace' => 'nullable|string|max:255',
                'descripcion' => 'required|string|max:500',
                'idmodelo' => 'required|integer',
                'serie' => 'nullable|string|max:255',
                'oc' => 'nullable|string|max:255',
                'fecha_oc' => 'nullable|date',
                'proveedor' => 'nullable|string|max:255',
                'ubicacion' => 'nullable|string|max:255',
                'cantidad' => 'required|integer|min:1',
                'estado' => 'required|string|max:255',
                'color' => 'nullable|string|max:255',
            ]);

            // Insertar el nuevo registro en la tabla M_ALMACEN
            DB::table('M_ALMACEN')->insert([
                'IDCENTRO_MAC' => $this->centro_mac()->idmac,
                'IDCATEGORIA' => $request->input('idcategoria'),
                'COD_INTERNO_PCM' => $request->input('cod_interno_pcm'),
                'COD_SBN' => $request->input('cod_sbn'),
                'COD_PRONSACE' => $request->input('cod_pronsace'),
                'DESCRIPCION' => $request->input('descripcion'),
                'IDMODELO' => $request->input('idmodelo'),
                'SERIE_MEDIDA' => $request->input('serie'),
                'OC' => $request->input('oc'),
                'FECHA_OC' => $request->input('fecha_oc'),
                'PROVEEDOR' => $request->input('proveedor'),
                'UBICACION_EQUIPOS' => $request->input('ubicacion'),
                'CANTIDAD' => $request->input('cantidad'),
                'ESTADO' => $request->input('estado'),
                'COLOR' => $request->input('color'),
                'USU_REG' => auth()->user()->id, 
            ]);

            return response()->json([
                'message' => 'Item registrado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al intentar guardar el item.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update_item(Request $request, $id)
    {
        try {
            // Validar los datos enviados
            $request->validate([
                'idcategoria' => 'required|integer',
                'cod_interno_pcm' => 'nullable|string|max:255',
                'cod_sbn' => 'nullable|string|max:255',
                'cod_pronsace' => 'nullable|string|max:255',
                'descripcion' => 'required|string|max:500',
                'idmodelo' => 'required|integer',
                'serie' => 'nullable|string|max:255',
                'oc' => 'nullable|string|max:255',
                'fecha_oc' => 'nullable|date',
                'proveedor' => 'nullable|string|max:255',
                'ubicacion' => 'nullable|string|max:255',
                'cantidad' => 'required|integer|min:1',
                'estado' => 'required|string|max:255',
                'color' => 'nullable|string|max:255',
            ]);
    
            // Verificar si el item existe
            $item = DB::table('M_ALMACEN')->where('IDALMACEN', $id)->first();
    
            if (!$item) {
                return response()->json([
                    'message' => 'Item no encontrado.',
                ], 404);
            }
    
            // Actualizar el registro
            DB::table('M_ALMACEN')->where('IDALMACEN', $id)->update([
                'IDCATEGORIA' => $request->input('idcategoria'),
                'COD_INTERNO_PCM' => $request->input('cod_interno_pcm'),
                'COD_SBN' => $request->input('cod_sbn'),
                'COD_PRONSACE' => $request->input('cod_pronsace'),
                'DESCRIPCION' => $request->input('descripcion'),
                'IDMODELO' => $request->input('idmodelo'),
                'SERIE_MEDIDA' => $request->input('serie'),
                'OC' => $request->input('oc'),
                'FECHA_OC' => $request->input('fecha_oc'),
                'PROVEEDOR' => $request->input('proveedor'),
                'UBICACION_EQUIPOS' => $request->input('ubicacion'),
                'CANTIDAD' => $request->input('cantidad'),
                'ESTADO' => $request->input('estado'),
                'COLOR' => $request->input('color'),
                'updated_at' => now(),
            ]);
    
            return response()->json([
                'message' => 'Item actualizado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al intentar actualizar el item.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete_item(Request $request)
    {
        $delete = DB::table('M_ALMACEN')->where('IDALMACEN', $request->id)->delete();

        return $delete;
    }

    public function delete_masivo(Request $request)
    {
        $delete = DB::table('M_ALMACEN')->where('IDCENTRO_MAC', $this->centro_mac()->idmac)->delete();

        return $delete;
    }


    public function eliminar_modelo(Request $request, $id)
    {
        try {
            // Buscar el modelo por ID
            $modelo = DB::table('ALM_MODELO')->where('IDMODELO', $id)->first();
    
            // Verificar si el modelo existe
            if (!$modelo) {
                return response()->json([
                    'message' => 'Modelo no encontrado.',
                ], 404);
            }
    
            // Eliminar el modelo
            DB::table('ALM_MODELO')->where('IDMODELO', $id)->delete();
    
            return response()->json([
                'message' => 'Modelo eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar el modelo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function modelo_marca($idmarca)
    {
        $modelo = DB::table('ALM_MODELO')->where('IDMARCA', $idmarca)->get();

        $options = '<option value="">Selecciona una opción</option>';
        foreach ($modelo as $prov) {
            $options .= '<option value="' . $prov->IDMODELO . '">' . $prov->NOMBRE_MODELO . '</option>';
        }

        return $options;
    }

}
