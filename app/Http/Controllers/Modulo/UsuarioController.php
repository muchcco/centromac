<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Personal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\CreaUsuario;

class UsuarioController extends Controller
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
        return view('usuarios.index');
    }

    public function tb_index(Request $request)
    {
        $usuarios = User::leftJoin('M_CENTRO_MAC', 'M_CENTRO_MAC.IDCENTRO_MAC', '=', 'users.idcentro_mac')
                            ->leftJoin('M_PERSONAL', 'M_PERSONAL.IDPERSONAL', '=', 'users.idpersonal')
                            // ->where('users.idcentro_mac', $this->centro_mac()->idmac)
                            ->where(function($query) {
                                if (!auth()->user()->hasRole('Administrador')) { 
                                    if (auth()->user()->hasRole('Especialista TIC|Orientador|Asesor|Supervisor|Coordinador')) {
                                        $query->where('users.idcentro_mac', '=', $this->centro_mac()->idmac);
                                    }
                                }
                            })                      
                            ->get();

        return view('usuarios.tablas.tb_index', compact('usuarios'));
    }

    public function md_add_usuario(Request $request)
    {
        $us_exist = DB::select("SELECT GROUP_CONCAT(idpersonal) AS list_us FROM users WHERE idcentro_mac = ". $this->centro_mac()->idmac ." ;");

        // Convertir el resultado de la consulta a un array
        $us_exist_array = array_map('intval', explode(',', $us_exist[0]->list_us));

        $query = Personal::where('IDMAC', $this->centro_mac()->idmac)
            ->where('flag', 1)
            ->whereNotIn('IDPERSONAL', $us_exist_array)
            ->get();

        $roles = Role::pluck('name', 'id');

        $view = view('usuarios.modals.md_add_usuario', compact('query', 'roles'))->render();

        return response()->json(["html" => $view]);
    }

    public function store_user(Request $request)
    {
        try{
            // dd($request->all()); 
            $personal = Personal::where('IDPERSONAL', $request->id_usuario)->first();

            $save = new User;
            $save->name = $personal->NOMBRE.' '.$personal->APE_PAT.' '.$personal->APE_MAT;
            $save->email = $personal->NUM_DOC; 
            $save->idpersonal = $request->id_usuario;
            $save->idcentro_mac = $this->centro_mac()->idmac;
            $save->password = bcrypt($personal->NUM_DOC);
            $save->save();

            // split the string into an array of role names
            $roles = explode(',', $request->roles);

            // filter out any empty elements
            $roles = array_filter($roles, function ($value) {
                return !empty($value);
            });

            // $actv_correo = Configua

            // create roles if they don't exist
            foreach ($roles as $role) {
                $role = Role::firstOrCreate(['name' => $role]); 
            }

            // sync roles
            $save->syncRoles($roles);

            if($personal->CORREO){
                Mail::to($personal->CORREO)->send(new CreaUsuario($personal));
            }

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

    public function md_edit_usuario(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        $roles = Role::pluck('name', 'id');

        $view = view('usuarios.modals.md_edit_usuario', compact('user', 'roles'))->render();

        return response()->json(["html" => $view]);
    }

    public function update_user(Request $request)
    {
        try {
            $save = User::findOrFail($request->id);
            $save->name = $request->name;
            $save->flag = $request->flag;
            $save->save();

            // Dividir los roles en un array
            $roles = explode(',', $request->roles);

            // Filtrar elementos vacíos
            $roles = array_filter($roles, function ($value) {
                return !empty($value);
            });

            // Crear roles si no existen y sincronizar con el usuario
            $roleIds = [];
            foreach ($roles as $roleName) {
                $role = Role::firstOrCreate(['name' => $roleName]);
                $roleIds[] = $role->id; // Guardar el ID del rol
            }

            $save->syncRoles($roles);

            // Buscar el usuario relacionado en jwt-mac.users
            $user_bd2 = DB::table('jwt-mac.users')
                ->where('name', $save->email)
                ->where('id_personal', $save->idpersonal)
                ->first();

            if ($user_bd2) {
                // Actualizar roles en la tabla model_has_roles con los IDs de los roles
                DB::table('jwt-mac.model_has_roles')
                    ->where('model_id', $user_bd2->id)
                    ->update([
                        'role_id' => implode(',', $roleIds), // Actualiza con los IDs de los roles
                    ]);
            }

            return $save;

        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD'
            ], 400);
        }
    }


    public function md_password_usuario(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        $view = view('usuarios.modals.md_password_usuario', compact('user'))->render();

        return response()->json(["html" => $view]);
    }

    public function updatepass_user(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
        ]);

        try {
            $save = null;
            $hashedPassword = bcrypt($request->password);
            $authDb = env('AUTH_DB_DATABASE', 'auth_db');

            DB::transaction(function () use ($request, $hashedPassword, $authDb, &$save) {
                $save = User::findOrFail($request->id);
                $save->password = $hashedPassword;
                $save->save();

                // Sincroniza en la BD de autenticación por num_doc (email local = DNI).
                $updatedInAuth = DB::table($authDb . '.users')
                    ->where('num_doc', $save->email)
                    ->update(['password' => $hashedPassword]);

                // Compatibilidad con esquema legado.
                if ($updatedInAuth === 0) {
                    $updatedInAuth = DB::table('jwt-mac.users')
                        ->where('name', $save->email)
                        ->update(['password' => $hashedPassword]);
                }

                if ($updatedInAuth === 0) {
                    throw new \RuntimeException('No se encontró el usuario en la BD de autenticación para sincronizar la contraseña.');
                }
            });

            return $save->fresh();
        } catch (\Throwable $e) {
            return response()->json([
                'data' => null,
                'error' => $e->getMessage(),
                'message' => 'BAD',
            ], 400);
        }
    }

    public function delete_user(Request $request)
    {
        $delete_us = DB::table('users')->where('id', $request->id)->delete();

        $delete_perf = DB::table('model_has_roles')->where('model_id', $request->id)->delete();

        return $delete_perf;
    }
}
