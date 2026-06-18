<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevToolsController extends Controller
{
    private string $logFile;

    public function __construct()
    {
        $this->logFile = storage_path('logs/laravel.log');
    }

    public function index()
    {
        return view('devtools.logs');
    }

    /**
     * Devuelve nuevas líneas del log a partir de un offset.
     * El cliente guarda el offset y lo envía en la siguiente petición.
     */
    public function tail(Request $request)
    {
        clearstatcache(true, $this->logFile);

        if (!file_exists($this->logFile)) {
            return response()->json(['lines' => [], 'offset' => 0, 'size' => 0]);
        }

        $fileSize = filesize($this->logFile);

        // Primera llamada: devolver los últimos 80 KB del log
        $offset = $request->integer('offset', -1);
        if ($offset < 0 || $offset > $fileSize) {
            $offset = max(0, $fileSize - 81920);
        }

        if ($offset >= $fileSize) {
            return response()->json(['lines' => [], 'offset' => $fileSize, 'size' => $fileSize]);
        }

        $fp = fopen($this->logFile, 'rb');
        fseek($fp, $offset);
        $raw = fread($fp, min(131072, $fileSize - $offset)); // máximo 128 KB por tick
        fclose($fp);

        $newOffset = $offset + strlen($raw);

        // Dividir en entradas de log (cada una empieza con "[YYYY-")
        $lines = $this->parseLogEntries($raw);

        return response()->json([
            'lines'  => $lines,
            'offset' => $newOffset,
            'size'   => $fileSize,
        ]);
    }

    /**
     * Estado de las colas y jobs recientes.
     */
    public function queueStatus()
    {
        $pending = DB::table('jobs')
            ->selectRaw('queue, COUNT(*) as total')
            ->groupBy('queue')
            ->get();

        $failed = DB::table('failed_jobs')
            ->selectRaw('queue, COUNT(*) as total')
            ->groupBy('queue')
            ->get();

        $recentJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get(['id', 'queue', 'failed_at', 'exception'])
            ->map(function ($j) {
                $firstLine = explode("\n", $j->exception)[0] ?? '';
                return [
                    'id'        => $j->id,
                    'queue'     => $j->queue,
                    'failed_at' => $j->failed_at,
                    'error'     => mb_substr($firstLine, 0, 120),
                ];
            });

        $recentSuccess = DB::table('m_asistencia')
            ->selectRaw('IDTIPO_ASISTENCIA, MAX(FECHA_BIOMETRICO) as ultima, COUNT(*) as total')
            ->whereRaw('FECHA_BIOMETRICO >= NOW() - INTERVAL 24 HOUR')
            ->groupBy('IDTIPO_ASISTENCIA')
            ->get();

        return response()->json([
            'pending'        => $pending,
            'failed'         => $failed,
            'recent_failed'  => $recentJobs,
            'recent_inserts' => $recentSuccess,
            'timestamp'      => now()->format('H:i:s'),
        ]);
    }

    // ─── Privado ─────────────────────────────────────────────────────────────

    private function parseLogEntries(string $raw): array
    {
        $entries = [];
        $lines   = explode("\n", $raw);
        $current = '';

        foreach ($lines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2}/', $line) && $current !== '') {
                $entries[] = $this->classifyEntry(rtrim($current));
                $current   = $line;
            } else {
                $current .= ($current === '' ? '' : "\n") . $line;
            }
        }

        if ($current !== '') {
            $entries[] = $this->classifyEntry(rtrim($current));
        }

        return array_values(array_filter($entries, fn($e) => $e['text'] !== ''));
    }

    private function classifyEntry(string $text): array
    {
        $level = 'info';
        if (preg_match('/\.(ERROR|EMERGENCY|CRITICAL|ALERT):/i', $text)) {
            $level = 'error';
        } elseif (preg_match('/\.WARNING:/i', $text)) {
            $level = 'warning';
        } elseif (preg_match('/\.(DEBUG):/i', $text)) {
            $level = 'debug';
        }

        // Extraer timestamp
        $ts = '';
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $text, $m)) {
            $ts = $m[1];
        }

        return [
            'text'  => $text,
            'level' => $level,
            'ts'    => $ts,
        ];
    }
}
