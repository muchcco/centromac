<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessAsistenciaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    private string $path;
    private int $idCentroMac;
    private string $uploadToken;

    private const BATCH_SIZE = 500;

    public function __construct(string $path, int $idCentroMac, string $uploadToken)
    {
        $this->path = $path;
        $this->idCentroMac = $idCentroMac;
        $this->uploadToken = $uploadToken;
    }

    public function handle(): void
    {
        $progressKey = 'upload_progress:' . $this->uploadToken;
        $cancelKey = 'upload_cancelled:' . $this->uploadToken;
        $statusKey = 'upload_status:' . $this->uploadToken;
        $errorKey = 'upload_error:' . $this->uploadToken;

        Cache::put($statusKey, 'processing');
        Cache::put($progressKey, 5);

        $fullPath = Storage::disk('local')->path($this->path);

        try {
            $this->waitForFile($fullPath);

            $fileSize = max(filesize($fullPath), 1);
            $file = fopen($fullPath, 'r');
            if (!$file) {
                throw new \RuntimeException('No se pudo abrir el archivo CSV.');
            }

            $batch = [];
            $rowIndex = 0;
            $bytesRead = 0;
            $correlativo = 0;
            $inserted = 0;
            $skipped = 0;
            $lastPercent = 0;

            while (($line = fgets($file)) !== false) {
                if (Cache::get($cancelKey, false)) {
                    Cache::put($statusKey, 'cancelled');
                    Cache::put($progressKey, 0);
                    fclose($file);
                    Storage::disk('local')->delete($this->path);
                    return;
                }

                $bytesRead += strlen($line);
                $rowIndex++;

                $pct = min((int) (($bytesRead / $fileSize) * 99), 99);
                if ($pct > $lastPercent) {
                    $lastPercent = $pct;
                    Cache::put($progressKey, $pct);
                }

                $row = $this->parseCsvLine($line);
                if (!$row || $row === [null] || count($row) < 4) {
                    continue;
                }

                $record = $this->parseRow($row, $rowIndex, $correlativo + 1);
                if ($record === null) {
                    continue;
                }

                $correlativo++;
                $batch[] = $record;

                if (count($batch) >= self::BATCH_SIZE) {
                    [$ins, $skip] = $this->flushBatch($batch);
                    $inserted += $ins;
                    $skipped += $skip;
                    $batch = [];
                }
            }

            fclose($file);

            if (!empty($batch)) {
                [$ins, $skip] = $this->flushBatch($batch);
                $inserted += $ins;
                $skipped += $skip;
            }

            Log::info('[ProcessAsistenciaExcel] Completado', [
                'token' => $this->uploadToken,
                'idCentroMac' => $this->idCentroMac,
                'inserted' => $inserted,
                'skipped' => $skipped,
            ]);

            Storage::disk('local')->delete($this->path);
            Cache::put($statusKey, 'completed');
            Cache::put($progressKey, 100);
        } catch (\Throwable $e) {
            Cache::put($statusKey, 'failed');
            Cache::put($errorKey, $e->getMessage());

            Log::error('[ProcessAsistenciaExcel] Error', [
                'token' => $this->uploadToken,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            Storage::disk('local')->delete($this->path);
            throw $e;
        }
    }

    private function waitForFile(string $fullPath): void
    {
        $delays = [200000, 500000, 1000000, 2000000, 3000000];
        clearstatcache(true, $fullPath);

        if (!file_exists($fullPath)) {
            foreach ($delays as $delay) {
                usleep($delay);
                clearstatcache(true, $fullPath);
                if (file_exists($fullPath)) {
                    break;
                }
            }
        }

        if (!file_exists($fullPath) || filesize($fullPath) === 0) {
            throw new \RuntimeException('El archivo CSV no existe o está vacío.');
        }
    }

    private function parseRow(array $row, int $rowIndex, int $correlativo): ?array
    {
        $numDoc = preg_replace('/\D+/', '', trim((string) ($row[2] ?? '')));
        $fechaRaw = trim((string) ($row[3] ?? ''));

        if ($numDoc === '' || $fechaRaw === '') {
            return null;
        }

        $fecha = $this->parseDate($fechaRaw);
        if (!$fecha) {
            if ($rowIndex > 1) {
                Log::warning('[ProcessAsistenciaExcel] Fecha inválida omitida', [
                    'token' => $this->uploadToken,
                    'row' => $rowIndex,
                    'fecha' => $fechaRaw,
                ]);
            }
            return null;
        }

        return [
            'IDTIPO_ASISTENCIA' => 3,
            'NUM_DOC' => $numDoc,
            'IDCENTRO_MAC' => $this->idCentroMac,
            'MES' => $fecha->format('m'),
            'AÑO' => $fecha->format('Y'),
            'FECHA' => $fecha->format('Y-m-d'),
            'HORA' => $fecha->format('H:i:s'),
            'FECHA_BIOMETRICO' => $fecha->format('Y-m-d H:i:s'),
            'NUM_BIOMETRICO' => '',
            'CORRELATIVO' => $correlativo,
            'CORRELATIVO_DIA' => '',
        ];
    }

    private function parseCsvLine(string $line): array
    {
        $best = [];
        foreach ([',', ';', "\t"] as $delimiter) {
            $columns = str_getcsv($line, $delimiter);
            if (count($columns) > count($best)) {
                $best = $columns;
            }
        }

        return $best;
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = preg_replace('/\s+/', ' ', trim($value));
        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/y H:i:s',
            'd/m/y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
                // Probar siguiente formato.
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function flushBatch(array $records): array
    {
        $fechas = array_values(array_unique(array_column($records, 'FECHA_BIOMETRICO')));
        $docs = array_values(array_unique(array_column($records, 'NUM_DOC')));

        $existingKeys = DB::table('m_asistencia')
            ->where('IDCENTRO_MAC', $this->idCentroMac)
            ->whereIn('NUM_DOC', $docs)
            ->whereIn('FECHA_BIOMETRICO', $fechas)
            ->selectRaw("CONCAT(NUM_DOC, '|', FECHA_BIOMETRICO) AS k")
            ->pluck('k')
            ->flip()
            ->all();

        $toInsert = [];
        $skipped = 0;

        foreach ($records as $record) {
            $key = $record['NUM_DOC'] . '|' . $record['FECHA_BIOMETRICO'];
            if (isset($existingKeys[$key])) {
                $skipped++;
                continue;
            }
            $toInsert[] = $record;
        }

        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, 200) as $chunk) {
                DB::table('m_asistencia')->insert($chunk);
            }
        }

        return [count($toInsert), $skipped];
    }
}
