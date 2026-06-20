<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaApiTokenController extends Controller
{
    public function index()
    {
        $tokens = AsistenciaApiToken::orderByDesc('id')->paginate(20);

        $macs = DB::table('m_centro_mac')
            ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
            ->where('FLAG', 1)
            ->orderBy('NOMBRE_MAC')
            ->get();

        return view('asistencia_api.tokens.index', compact('tokens', 'macs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'id_mac' => 'required|integer|exists:m_centro_mac,IDCENTRO_MAC',
        ]);

        $plain = bin2hex(random_bytes(32)); // 64 hex chars
        $hash  = hash('sha256', $plain);

        AsistenciaApiToken::create([
            'nombre'     => $request->nombre,
            'token_hash' => $hash,
            'id_mac'     => (int) $request->id_mac,
            'activo'     => 1,
        ]);

        return redirect()->route('asistencia-api.tokens.index')
            ->with('token_plain', $plain)
            ->with('success', 'Token creado. Cópialo ahora, no se volverá a mostrar.');
    }

    public function toggle(int $id)
    {
        $token = AsistenciaApiToken::findOrFail($id);
        $token->update(['activo' => !$token->activo]);

        $estado = $token->fresh()->activo ? 'activado' : 'desactivado';

        return redirect()->route('asistencia-api.tokens.index')
            ->with('success', "Token \"{$token->nombre}\" {$estado}.");
    }

    public function destroy(int $id)
    {
        $token = AsistenciaApiToken::findOrFail($id);
        $nombre = $token->nombre;
        $token->delete();

        return redirect()->route('asistencia-api.tokens.index')
            ->with('success', "Token \"{$nombre}\" eliminado.");
    }
}
