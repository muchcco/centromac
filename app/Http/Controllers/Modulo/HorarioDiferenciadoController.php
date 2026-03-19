<?php

namespace App\Http\Controllers\Modulo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HorarioDiferenciado;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HorarioDiferenciadoController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        $macs = $usuario->hasRole(['Administrador', 'Moderador'])
            ? DB::table('m_centro_mac')->select('IDCENTRO_MAC', 'NOMBRE_MAC')->orderBy('NOMBRE_MAC')->get()
            : DB::table('m_centro_mac')->where('IDCENTRO_MAC', $usuario->idcentro_mac)->select('IDCENTRO_MAC', 'NOMBRE_MAC')->get();

        $centro_mac = !$usuario->hasRole(['Administrador', 'Moderador'])
            ? DB::table('m_centro_mac')->where('IDCENTRO_MAC', $usuario->idcentro_mac)->select('IDCENTRO_MAC as idmac', 'NOMBRE_MAC as name_mac')->first()
            : null;

        return view('horario.index', compact('macs', 'centro_mac'));
    }

    public function tb_index()
    {
        $usuario = auth()->user();

        $q = DB::table('m_horario_diferenciado as h')
            ->leftJoin('m_modulo as m', 'h.idmodulo', '=', 'm.IDMODULO')
            ->leftJoin('m_entidad as e', 'm.IDENTIDAD', '=', 'e.IDENTIDAD')
            ->leftJoin('m_centro_mac as c', 'h.idcentro_mac', '=', 'c.IDCENTRO_MAC')
            ->select(
                'h.idhorario_diferenciado',
                'c.NOMBRE_MAC',
                'e.NOMBRE_ENTIDAD',
                'm.N_MODULO',
                'h.fecha_inicio',
                'h.fecha_fin',
                'h.DiaSemana',
                'h.HoraIngreso',
                'h.HoraSalida',
                'h.Observaciones'
            )
            ->orderBy('h.fecha_inicio', 'desc');

        if (!$usuario->hasRole(['Administrador', 'Moderador'])) {
            $q->where('h.idcentro_mac', $usuario->idcentro_mac);
        }

        return response()->json(view('horario.tablas.tb_index', ['horarios' => $q->get()])->render());
    }

    public function md_add_horario()
    {
        $usuario = auth()->user();

        $macs = $usuario->hasRole(['Administrador', 'Moderador'])
            ? DB::table('m_centro_mac')->select('IDCENTRO_MAC', 'NOMBRE_MAC')->orderBy('NOMBRE_MAC')->get()
            : DB::table('m_centro_mac')->where('IDCENTRO_MAC', $usuario->idcentro_mac)->select('IDCENTRO_MAC', 'NOMBRE_MAC')->get();

        return response()->json(['html' => view('horario.modals.md_add_horario', compact('macs'))->render()]);
    }

    /* 🔥 SOLO ESTE MÉTODO ES NECESARIO */
    public function get_modulos(Request $r)
    {
        return DB::table('m_modulo as m')
            ->join('m_entidad as e', 'e.IDENTIDAD', '=', 'm.IDENTIDAD')
            ->where('m.IDCENTRO_MAC', $r->idmac)
            ->where('m.ESTADO', 1)
            ->select(
                'm.IDMODULO',
                'm.N_MODULO',
                'm.IDENTIDAD',
                'e.NOMBRE_ENTIDAD'
            )
            ->orderBy('m.N_MODULO')
            ->get();
    }

    public function store_horario(Request $r)
    {
        $v = Validator::make($r->all(), [
            'idcentro_mac' => 'required',
            'idmodulo' => 'required',
            'identidad' => 'required',
            'DiaSemana' => 'required',
            'HoraIngreso' => 'required',
            'HoraSalida' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date'
        ]);

        if ($v->fails()) {
            return response()->json(['msg' => 'Campos incompletos'], 422);
        }

        if ($r->HoraIngreso >= $r->HoraSalida) {
            return response()->json(['msg' => 'Hora salida inválida'], 422);
        }

        /* 🔥 SOLO VALIDAR CRUCE REAL */
        $cruce = DB::table('m_horario_diferenciado')
            ->where('idcentro_mac', $r->idcentro_mac)
            ->where('idmodulo', $r->idmodulo)
            ->where('DiaSemana', $r->DiaSemana)
            ->where(function ($q) use ($r) {
                $q->whereBetween('HoraIngreso', [$r->HoraIngreso, $r->HoraSalida])
                    ->orWhereBetween('HoraSalida', [$r->HoraIngreso, $r->HoraSalida])
                    ->orWhere(function ($q2) use ($r) {
                        $q2->where('HoraIngreso', '<=', $r->HoraIngreso)
                            ->where('HoraSalida', '>=', $r->HoraSalida);
                    });
            })
            ->exists();

        if ($cruce) {
            return response()->json(['msg' => 'Horario se cruza con uno existente'], 409);
        }

        DB::table('m_horario_diferenciado')->insert([
            'idcentro_mac' => $r->idcentro_mac,
            'idmodulo' => $r->idmodulo,
            'identidad' => $r->identidad,
            'DiaSemana' => $r->DiaSemana,
            'HoraIngreso' => $r->HoraIngreso,
            'HoraSalida' => $r->HoraSalida,
            'fecha_inicio' => $r->fecha_inicio,
            'fecha_fin' => $r->fecha_fin,
            'Observaciones' => $r->Observaciones,
            'activo' => 1
        ]);

        return response()->json(['msg' => 'OK']);
    }

    public function md_edit_horario(Request $r)
    {
        $horario = HorarioDiferenciado::find($r->id);

        $macs = DB::table('m_centro_mac')->select('IDCENTRO_MAC', 'NOMBRE_MAC')->get();

        return response()->json(['html' => view('horario.modals.md_edit_horario', compact('horario', 'macs'))->render()]);
    }

    public function update_horario(Request $r)
    {
        $h = HorarioDiferenciado::find($r->id);

        if (!$h) {
            return response()->json(['msg' => 'No existe'], 404);
        }

        if ($r->HoraIngreso >= $r->HoraSalida) {
            return response()->json(['msg' => 'Hora inválida'], 422);
        }

        $h->idcentro_mac = $r->idcentro_mac;
        $h->idmodulo = $r->idmodulo;
        $h->identidad = $r->identidad;
        $h->fecha_inicio = $r->fecha_inicio;
        $h->fecha_fin = $r->fecha_fin;
        $h->DiaSemana = $r->DiaSemana;
        $h->HoraIngreso = $r->HoraIngreso;
        $h->HoraSalida = $r->HoraSalida;
        $h->Observaciones = $r->Observaciones;
        $h->save();

        return response()->json(['msg' => 'Actualizado']);
    }

    public function delete_horario(Request $r)
    {
        $h = HorarioDiferenciado::find($r->id);

        if (!$h) {
            return response()->json(['msg' => 'No existe'], 404);
        }

        $h->delete();

        return response()->json(['msg' => 'Eliminado']);
    }
}
