<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ProcessAsistenciaTxt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    private string $path;
    private int    $idCentroMac;
    private string $uploadToken;

    private const BATCH_SIZE = 500;

    public function __construct(string $path, int $idCentroMac, string $uploadToken)
    {
        $this->path        = $path;
        $this->idCentroMac = $idCentroMac;
        $this->uploadToken = $uploadToken;
    }

    public function handle(): void
    {
        $progressKey = 'upload_progress:' . $this->uploadToken;
        $cancelKey   = 'upload_cancelled:' . $this->uploadToken;
        $statusKey   = 'upload_status:'    . $this->uploadToken;
        $errorKey    = 'upload_error:'     . $this->uploadToken;

        Cache::put($statusKey,   'processing');
        Cache::put($progressKey, 5);

        $fullPath = Storage::disk('local')->path($this->path);

        try {
            // ── Diagnóstico + retry por race condition del filesystem ─────────
            // El worker puede arrancar tan rápido que el archivo aún no es visible
            // en el stat cache del proceso. clearstatcache + reintento lo resuelve.
            clearstatcache(true, $fullPath);
            $fsExists = file_exists($fullPath);
            if (!$fsExists) {
                usleep(500000); // 500ms
                clearstatcache(true, $fullPath);
                $fsExists = file_exists($fullPath);
            }

            $storageExists = Storage::disk('local')->exists($this->path);
            $sizeBytes     = $fsExists ? filesize($fullPath) : 0;
            $dirPath       = Storage::disk('local')->path('asistencia-txt');
            $dirExists     = is_dir($dirPath);
            $dirPerms      = $dirExists ? substr(sprintf('%o', fileperms($dirPath)), -4) : 'no-existe';
            $whoami        = trim((string) shell_exec('whoami 2>/dev/null'));

            Log::info('[ProcessAsistenciaTxt] Diagnóstico de archivo', [
                'token'         => $this->uploadToken,
                'storedPath'    => $this->path,
                'fullPath'      => $fullPath,
                'storageExists' => $storageExists,
                'fsExists'      => $fsExists,
                'sizeBytes'     => $sizeBytes,
                'dirExists'     => $dirExists,
                'dirPerms'      => $dirPerms,
                'linuxUser'     => $whoami,
            ]);

            if (!$fsExists || $sizeBytes === 0) {
                $msg = !$storageExists
                    ? "El archivo nunca se guardó en storage (Storage::exists=false). Path: {$fullPath}"
                    : ($sizeBytes === 0
                        ? "El archivo existe pero está vacío (0 bytes). Path: {$fullPath}"
                        : "Archivo en storage pero no encontrado en sistema de archivos. Path: {$fullPath}");

                throw new \RuntimeException($msg);
            }

            $fileSize = max(filesize($fullPath), 1);
            $file     = new SplFileObject($fullPath, 'r');

            $batch       = [];
            $lineIndex   = 0;
            $bytesRead   = 0;
            $lastPercent = 0;
            $inserted    = 0;
            $skipped     = 0;

            while (!$file->eof()) {
                if (Cache::get($cancelKey, false)) {
                    Cache::put($statusKey, 'cancelled');
                    Cache::put($progressKey, 0);
                    Storage::disk('local')->delete($this->path);
                    return;
                }

                $line = $file->fgets();
                if ($line === false) {
                    continue;
                }

                $bytesRead += strlen($line);
                $pct = min((int)(($bytesRead / $fileSize) * 99), 99);
                if ($pct > $lastPercent) {
                    $lastPercent = $pct;
                    Cache::put($progressKey, $pct);
                }

                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $record = $this->parseLine($line, $lineIndex);
                if ($record === null) {
                    continue;
                }

                $lineIndex++;
                $batch[] = $record;

                if (count($batch) >= self::BATCH_SIZE) {
                    [$ins, $skip] = $this->flushBatch($batch);
                    $inserted += $ins;
                    $skipped  += $skip;
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                [$ins, $skip] = $this->flushBatch($batch);
                $inserted += $ins;
                $skipped  += $skip;
            }

            Log::info('[ProcessAsistenciaTxt] Completado', [
                'token'       => $this->uploadToken,
                'idCentroMac' => $this->idCentroMac,
                'inserted'    => $inserted,
                'skipped'     => $skipped,
            ]);

            Storage::disk('local')->delete($this->path);
            Cache::put($statusKey,   'completed');
            Cache::put($progressKey, 100);

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Cache::put($statusKey, 'failed');
            Cache::put($errorKey,  $msg);

            Log::error('[ProcessAsistenciaTxt] Error', [
                'token'   => $this->uploadToken,
                'message' => $msg,
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // Limpiar archivo para no acumular en storage
            Storage::disk('local')->delete($this->path);

            throw $e;
        }
    }

    // ─── Parseo de línea ─────────────────────────────────────────────────────

    private function parseLine(string $line, int $lineIndex): ?array
    {
        $data = explode("\t", $line);
        if (count($data) < 7) {
            return null;
        }

        $numDoc          = trim($data[2]);
        $fechaBiometrico = trim($data[6]);

        if ($numDoc === '' || $fechaBiometrico === '') {
            return null;
        }

        $fechaHora = explode(' ', $fechaBiometrico);
        if (count($fechaHora) !== 2) {
            return null;
        }

        $hora       = $fechaHora[1];
        $fechaParts = explode('/', $fechaHora[0]);
        if (count($fechaParts) !== 3) {
            return null;
        }

        return [
            'IDTIPO_ASISTENCIA' => 1,
            'NUM_DOC'           => $numDoc,
            'IDCENTRO_MAC'      => $this->idCentroMac,
            'MES'               => $fechaParts[1],
            'AÑO'               => $fechaParts[0],
            'FECHA'             => $fechaBiometrico,
            'HORA'              => $hora,
            'FECHA_BIOMETRICO'  => $fechaBiometrico,
            'NUM_BIOMETRICO'    => '',
            'CORRELATIVO'       => $lineIndex + 1,
            'CORRELATIVO_DIA'   => '',
        ];
    }

    // ─── Flush de lote ───────────────────────────────────────────────────────

    private function flushBatch(array $records): array
    {
        $fechas = array_values(array_unique(array_column($records, 'FECHA_BIOMETRICO')));
        $docs   = array_values(array_unique(array_column($records, 'NUM_DOC')));

        // Una sola query para saber qué combos (doc, fecha_bio) ya existen
        $existingKeys = DB::table('m_asistencia')
            ->where('IDCENTRO_MAC', $this->idCentroMac)
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
}
