<?php

namespace App\Http\Controllers\Modulo;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaSyncLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AsistenciaSyncLog::with('token')->latest('created_at');

        if ($request->filled('id_mac')) {
            $query->where('id_mac', (int) $request->id_mac);
        }

        if ($request->filled('desde')) {
            $query->where('created_at', '>=', $request->desde . ' 00:00:00');
        }

        if ($request->filled('hasta')) {
            $query->where('created_at', '<=', $request->hasta . ' 23:59:59');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(30)->withQueryString();

        $macs = DB::table('m_centro_mac')
            ->select('IDCENTRO_MAC', 'NOMBRE_MAC')
            ->where('FLAG', 1)
            ->orderBy('NOMBRE_MAC')
            ->get();

        return view('asistencia_api.logs.index', compact('logs', 'macs'));
    }
}
