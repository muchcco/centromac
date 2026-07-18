<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mac;
use Carbon\Carbon;
use App\Models\Entidad;
use App\Models\User;

class ConfiguracionController extends Controller
{
    private function centro_mac()
    {
        // VERIFICAMOS EL USUARIO A QUE CENTRO MAC PERTENECE
        /*================================================================================================================*/
        $us_id = auth()->user()->idcentro_mac;
        $user = User::join('m_centro_mac', 'm_centro_mac.IDCENTRO_MAC', '=', 'users.idcentro_mac')->where('m_centro_mac.IDCENTRO_MAC', $us_id)->first();

        $idmac = $user->IDCENTRO_MAC;
        $name_mac = $user->NOMBRE_MAC;
        /*================================================================================================================*/

        $resp = ['idmac' => $idmac, 'name_mac' => $name_mac];

        return (object) $resp;
    }

    public function nuevo_mac()
    {
        return view('configuracion.nuevo_mac');
    }

    public function tb_nuevo_mac(Request $request)
    {
        // $mac = DB::table('m_centro_mac')->where('FLAG', 1)->get();
        $mac = Mac::leftJoin('distrito as D', 'm_centro_mac.UBICACION', '=', 'D.IDDISTRITO')
            ->leftJoin('provincia as P', 'D.PROVINCIA_ID', '=', 'P.IDPROVINCIA')
            ->leftJoin('departamento as DEP', 'D.DEPARTAMENTO_ID', '=', 'DEP.IDDEPARTAMENTO')
            ->select('m_centro_mac.*', 'D.*', 'P.*', 'DEP.*')
            ->where(function ($query) {
                if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                    $query->where('m_centro_mac.IDCENTRO_MAC', '=', $this->centro_mac()->idmac);
                }
            })
            ->where('FLAG', 1)
            ->get();


        return view('configuracion.tablas.tb_nuevo_mac', compact('mac'));
    }

    public function md_add_mac(Request $request)
    {
        $departamento = DB::table('departamento')->get();

        $view = view('configuracion.modals.md_add_mac', compact('departamento'))->render();

        return response()->json(['html' => $view]);
    }

    public function store_mac(Request $request)
    {
        try {

            $save = new Mac;
            $save->DIRECCION_MAC = $request->ubicacion;
            $save->UBICACION = $request->distrito;
            $save->NOMBRE_MAC = $request->centro_mac;
            $save->FECHA_APERTURA = $request->fecha_apertura;
            $save->FECHA_INAGURACION = $request->fecha_inaguracion;
            $save->FECHA_CREACION = Carbon::now();
            $save->save();

            return $save;
        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function md_edit_mac(Request $request)
    {
        $mac = Mac::leftJoin('distrito as D', 'm_centro_mac.UBICACION', '=', 'D.IDDISTRITO')
            ->leftJoin('provincia as P', 'D.PROVINCIA_ID', '=', 'P.IDPROVINCIA')
            ->leftJoin('departamento as DEP', 'D.DEPARTAMENTO_ID', '=', 'DEP.IDDEPARTAMENTO')
            ->select('m_centro_mac.*', 'D.*', 'P.*', 'DEP.*')
            ->where('FLAG', 1)
            ->where('IDCENTRO_MAC', $request->idcentro_mac)
            ->first();
        // dd($mac->IDDEPARTAMENTO);

        $departamento = DB::table('departamento')->get();
        $provincia = DB::table('provincia')->get();
        $distrito = DB::table('distrito')->get();

        $view = view('configuracion.modals.md_edit_mac', compact('mac', 'departamento', 'provincia', 'distrito'))->render();

        return response()->json(['html' => $view]);
    }

    public function update_mac(Request $request)
    {
        try {

            $save = Mac::findOrFail($request->idcentro_mac);
            $save->DIRECCION_MAC = $request->ubicacion;
            $save->UBICACION = $request->distrito;
            $save->FECHA_APERTURA = $request->fecha_apertura;
            $save->FECHA_INAGURACION = $request->fecha_inaguracion;
            $save->save();

            return $save;
        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function delete_mac(Request $request)
    {
        $delete = Mac::where('IDCENTRO_MAC', $request->idcentro_mac)->delete();

        return $delete;
    }

    /************************************************************************* CONFIGURACION DE TABLAS *******************************************************************/

    public function reg_tablas(Request $request, $idcentro_mac)
    {
        $mac = Mac::where('IDCENTRO_MAC', $idcentro_mac)->first();

        $entidad = DB::table('m_mac_entidad as MME')
            ->join('m_centro_mac as MCM', 'MME.IDCENTRO_MAC', '=', 'MCM.IDCENTRO_MAC')
            ->join('m_entidad as ME', 'MME.IDENTIDAD', '=', 'ME.IDENTIDAD')
            ->where('MCM.IDCENTRO_MAC', $idcentro_mac)
            ->get();

        $us_exist = DB::select("SELECT GROUP_CONCAT(identidad) AS list_us FROM m_mac_entidad WHERE idcentro_mac = " . $idcentro_mac . " ;");

        // Convertir el resultado de la consulta a un array
        $us_exist_array = array_map('intval', explode(',', $us_exist[0]->list_us));

        // dd($us_exist_array);

        // $entidad_completo = Entidad::whereNotIn('IDENTIDAD', $us_exist_array)->get();
        $entidad_completo = Entidad::get();

        $modulos = DB::table('m_modulo')->join('m_entidad', 'm_entidad.IDENTIDAD', '=', 'm_modulo.IDENTIDAD')->where('m_modulo.IDCENTRO_MAC',  $idcentro_mac)->get();
        // dd($modulos);

        $configuracionBiometrico = DB::table('configuracion_biometrico')
            ->where('idcentro_mac', $idcentro_mac)
            ->where('flag', 1)
            ->where('status', 1)
            ->orderByDesc('idconfiguracion_biometrico')
            ->first();

        $configuracionesBiometrico = DB::table('configuracion_biometrico')
            ->where('idcentro_mac', $idcentro_mac)
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('idconfiguracion_biometrico')
            ->get();

        return view('configuracion.reg_tablas', compact('mac', 'entidad', 'entidad_completo', 'modulos', 'configuracionBiometrico', 'configuracionesBiometrico'));
    }

    public function storeConfiguracionBiometrico(Request $request)
    {
        $request->validate([
            'idmac' => ['required', 'integer'],
            'tipo_biometrico' => ['required', 'integer', 'in:1,2,3'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $mac = Mac::where('IDCENTRO_MAC', $request->idmac)->firstOrFail();
        $nombres = [
            1 => 'txt',
            2 => 'access',
            3 => 'excel',
        ];

        $existeActivo = DB::table('configuracion_biometrico')
            ->where('idcentro_mac', $request->idmac)
            ->where('flag', 1)
            ->where('status', 1)
            ->exists();

        if ($existeActivo) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un biométrico activo. Primero debe darlo de baja para registrar uno nuevo.',
            ], 422);
        }

        DB::transaction(function () use ($request, $mac, $nombres) {
            DB::table('configuracion_biometrico')->insert([
                'centro_mac' => $mac->NOMBRE_MAC,
                'idcentro_mac' => $mac->IDCENTRO_MAC,
                'tipo_biometrico' => (int) $request->tipo_biometrico,
                'nombre_biometrico' => $nombres[(int) $request->tipo_biometrico],
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'status' => 1,
                'flag' => 1,
                'fecha_creacion' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Configuración de biométrico guardada correctamente.',
        ]);
    }

    public function bajaConfiguracionBiometrico(Request $request)
    {
        $request->validate([
            'idconfiguracion_biometrico' => ['required', 'integer'],
            'idmac' => ['required', 'integer'],
            'fecha_fin' => ['required', 'date'],
        ]);

        $configuracion = DB::table('configuracion_biometrico')
            ->where('idconfiguracion_biometrico', $request->idconfiguracion_biometrico)
            ->where('idcentro_mac', $request->idmac)
            ->first();

        if (!$configuracion) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la configuración del biométrico.',
            ], 404);
        }

        if ($request->fecha_fin < $configuracion->fecha_inicio) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha fin no puede ser menor a la fecha de inicio.',
            ], 422);
        }

        DB::table('configuracion_biometrico')
            ->where('idconfiguracion_biometrico', $request->idconfiguracion_biometrico)
            ->where('idcentro_mac', $request->idmac)
            ->update([
                'fecha_fin' => $request->fecha_fin,
                'status' => 0,
                'flag' => 0,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Biométrico dado de baja correctamente.',
        ]);
    }

    public function addEntidad(Request $request)
    {
        try {

            $exist = DB::table('m_mac_entidad')
                ->where('IDCENTRO_MAC', $request->idmac)
                ->where('IDENTIDAD', $request->addEntidad)
                ->exists();
       //     dd($exist);

            if ($exist) {
                return response()->json(['message' => 'Entidad ya existe'], 400);
            }

            $save = DB::table('m_mac_entidad')->insert([
                'IDCENTRO_MAC'      =>      $request->idmac,
                'IDENTIDAD'         =>      $request->addEntidad,
                'LOG_US'            =>      auth()->user()->id,
            ]);

            return $save;
        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function deleteEntidad(Request $request)
    {
        $delete = DB::table('m_mac_entidad')->where('IDMAC_ENTIDAD', $request->id)->delete();

        return $delete;
    }

    public function addModulo(Request $request)
    {

        try {

            $save = DB::table('m_modulo')->insert([
                'IDCENTRO_MAC'      =>      $request->idmac,
                'IDENTIDAD'         =>      $request->addModEnt,
                'N_MODULO'            =>      $request->n_modulo,
                'CREATED_AT'            =>      Carbon::now(),
            ]);

            return $save;
        } catch (\Exception $e) {
            //Si existe algún error en la Transacción
            $response_ = response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);

            return $response_;
        }
    }

    public function deleteModulo(Request $request)
    {
        $delete = DB::table('m_modulo')->where('IDMODULO', $request->id)->delete();

        return $delete;
    }
}
