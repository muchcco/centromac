<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsistenciaSyncController extends Controller
{
    private const TIPO_API = 3;
    private const BATCH    = 500;

    public function ping(): JsonResponse
    {
        return response()->json(['ok' => true, 'ts' => now()->toIso8601String()]);
    }

    public function sync(Request $request): JsonResponse
    {
        /** @var \App\Models\AsistenciaApiToken $token */
        $token = $request->attributes->get('api_token');

        $validated = $request->validate([
            'id_mac'            => 'required|integer',
            'data'              => 'required|array|min:1',
            'data.*.ibempleado' => 'required|string',
            'data.*.fecha_iso'  => 'required|date_format:Y-m-d H:i:s',
            'data.*.item'       => 'required|integer',
        ]);

        $idMac = (int) $validated['id_mac'];

        if ($idMac !== $token->id_mac) {
            return response()->json([
                'ok'    => false,
                'error' => "El token no está autorizado para id_mac={$idMac}.",
            ], 403);
        }

        $registros = $this->normalizar($validated['data'], $idMac);

        [$insertados, $duplicados] = $this->persistir($registros, $idMac);

        $this->registrarLog(
            $token->id, $idMac,
            count($validated['data']), $insertados, $duplicados,
            'ok', null, $request->ip()
        );

        Log::info('[AsistenciaSync] Sync completado', [
            'id_mac'     => $idMac,
            'recibidos'  => count($validated['data']),
            'insertados' => $insertados,
            'duplicados' => $duplicados,
        ]);

        return response()->json([
            'ok'         => true,
            'insertados' => $insertados,
            'duplicados' => $duplicados,
            'total'      => count($validated['data']),
        ]);
    }

    public function ultimoItem(Request $request): JsonResponse
    {
        $idMac = (int) $request->query('id_mac', 0);

        if ($idMac <= 0) {
            return response()->json(['ok' => false, 'error' => 'id_mac requerido.'], 422);
        }

        $max = DB::table('m_asistencia')
            ->where('IDCENTRO_MAC', $idMac)
            ->where('IDTIPO_ASISTENCIA', self::TIPO_API)
            ->max(DB::raw('CAST(CORRELATIVO AS UNSIGNED)'));

        return response()->json(['ok' => true, 'ultimo_item' => (int) ($max ?? 0)]);
    }

    // ─── Privados ─────────────────────────────────────────────────────────────

    private function normalizar(array $data, int $idMac): array
    {
        $rows = [];
        foreach ($data as $rec) {
            $ts     = strtotime($rec['fecha_iso']);
            $rows[] = [
                'IDTIPO_ASISTENCIA' => self::TIPO_API,
                'NUM_DOC'           => trim((string) $rec['ibempleado']),
                'IDCENTRO_MAC'      => $idMac,
                'MES'               => date('m', $ts),
                'AÑO'               => date('Y', $ts),
                'FECHA'             => date('Y-m-d', $ts),
                'HORA'              => date('H:i:s', $ts),
                'FECHA_BIOMETRICO'  => $rec['fecha_iso'],
                'NUM_BIOMETRICO'    => '',
                'CORRELATIVO'       => (string) $rec['item'],
                'CORRELATIVO_DIA'   => '',
                'estado'            => 0,
            ];
        }
        return $rows;
    }

    private function persistir(array $registros, int $idMac): array
    {
        $insertados = 0;
        $duplicados = 0;

        foreach (array_chunk($registros, self::BATCH) as $lote) {
            [$ins, $dup] = $this->flushBatch($lote, $idMac);
            $insertados += $ins;
            $duplicados += $dup;
        }

        return [$insertados, $duplicados];
    }

    private function flushBatch(array $records, int $idMac): array
    {
        $fechas = array_values(array_unique(array_column($records, 'FECHA_BIOMETRICO')));
        $docs   = array_values(array_unique(array_column($records, 'NUM_DOC')));

        $existingKeys = DB::table('m_asistencia')
            ->where('IDCENTRO_MAC', $idMac)
            ->whereIn('NUM_DOC', $docs)
            ->whereIn('FECHA_BIOMETRICO', $fechas)
            ->selectRaw("CONCAT(NUM_DOC, '|', FECHA_BIOMETRICO) AS k")
            ->pluck('k')
            ->flip()
            ->all();

        $toInsert = [];
        $skipped  = 0;

        foreach ($records as $rec) {
            $key = $rec['NUM_DOC'] . '|' . $rec['FECHA_BIOMETRICO'];
            if (isset($existingKeys[$key])) {
                $skipped++;
                continue;
            }
            $toInsert[] = $rec;
        }

        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, 200) as $chunk) {
                DB::table('m_asistencia')->insert($chunk);
            }
        }

        return [count($toInsert), $skipped];
    }

    private function registrarLog(
        ?int $tokenId, int $idMac, int $recibidos, int $insertados,
        int $duplicados, string $status, ?string $mensaje, string $ip
    ): void {
        DB::table('asistencia_sync_logs')->insert([
            'token_id'         => $tokenId,
            'id_mac'           => $idMac,
            'total_recibidos'  => $recibidos,
            'total_insertados' => $insertados,
            'total_duplicados' => $duplicados,
            'status'           => $status,
            'mensaje'          => $mensaje,
            'ip_origen'        => $ip,
            'created_at'       => now(),
        ]);
    }
}
