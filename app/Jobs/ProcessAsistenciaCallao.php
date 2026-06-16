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

class ProcessAsistenciaCallao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Sin reintentos: el archivo puede ya no existir en un segundo intento
    public int $tries = 1;

    private string $path;
    private int    $idCentroMac;
    private string $uploadToken;
    private string $fechaInicio;
    private string $fechaFin;

    public function __construct(
        string $path,
        int    $idCentroMac,
        string $uploadToken,
        string $fechaInicio,
        string $fechaFin
    ) {
        $this->path        = $path;
        $this->idCentroMac = $idCentroMac;
        $this->uploadToken = $uploadToken;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin    = $fechaFin;
    }

    // ─── Punto de entrada del Job ────────────────────────────────────────────

    public function handle(): void
    {
        $progressKey = 'upload_progress:'  . $this->uploadToken;
        $cancelKey   = 'upload_cancelled:' . $this->uploadToken;
        $statusKey   = 'upload_status:'    . $this->uploadToken;
        $errorKey    = 'upload_error:'     . $this->uploadToken;

        Cache::put($statusKey,   'running');
        Cache::put($progressKey, 0);

        $fullPath = Storage::disk('local')->path($this->path);
        $ext      = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        try {
            // ── 0. Diagnóstico y validación del archivo ───────────────────────
            // Lanza RuntimeException si el archivo no existe, está vacío o extensión inválida.
            // En ese caso el catch NO elimina el archivo (ya no existe o es irrecuperable).
            $this->validateFile($fullPath, $ext, $errorKey, $statusKey);

            $callaoDb = DB::connection('asistencia_callao');
            Cache::put($progressKey, 10);

            // ── 1. Listar tablas con mdb-tables (proc_open) ───────────────────
            [$tables, $mdbOut, $mdbErr, $mdbExit] = $this->runMdbTables($fullPath);

            if ($tables === null) {
                $msg = $this->buildMdbTablesError($ext, $mdbOut, $mdbErr, $mdbExit);

                Log::error('[ProcessAsistenciaCallao] mdb-tables falló', [
                    'token'    => $this->uploadToken,
                    'path'     => $this->path,
                    'fullPath' => $fullPath,
                    'ext'      => $ext,
                    'exitCode' => $mdbExit,
                    'stdout'   => $mdbOut,
                    'stderr'   => $mdbErr,
                ]);

                Cache::put($errorKey,  $msg);
                Cache::put($statusKey, 'failed');
                return;
            }

            Log::info('[ProcessAsistenciaCallao] Tablas encontradas', [
                'token'  => $this->uploadToken,
                'tables' => $tables,
            ]);

            Cache::put($progressKey, 20);

            $tableCount = count($tables);
            $tableIndex = 0;

            // ── 2. Exportar e importar cada tabla ─────────────────────────────
            foreach ($tables as $table) {
                $tableIndex++;

                if (Cache::get($cancelKey, false)) {
                    Cache::put($statusKey,   'cancelled');
                    Cache::put($progressKey, 0);
                    Storage::disk('local')->delete($this->path); // cancelación voluntaria → limpiar
                    return;
                }

                $mysqlTable = strtolower($table);
                $tempCsv    = sys_get_temp_dir() . '/mdb_' . uniqid() . '.csv';

                try {
                    // 2a. mdb-export → CSV temporal
                    [$exportOk, , $exportErr, $exportExit] = $this->runMdbExport($fullPath, $table, $tempCsv);

                    if (!$exportOk) {
                        Log::warning('[ProcessAsistenciaCallao] mdb-export omitió tabla', [
                            'token'    => $this->uploadToken,
                            'table'    => $table,
                            'exitCode' => $exportExit,
                            'stderr'   => $exportErr,
                        ]);
                        continue;
                    }

                    $handle = fopen($tempCsv, 'r');
                    if (!$handle) continue;

                    try {
                        $headers = fgetcsv($handle);
                        if (!$headers) continue;

                        $wrappedTable = $this->qi($mysqlTable);

                        // 2b. Crear o actualizar esquema en asistencia_callao
                        if ($this->tableExists($callaoDb, $mysqlTable)) {
                            $existing = $this->getExistingColumns($callaoDb, $mysqlTable);
                            $callaoDb->statement(
                                "ALTER TABLE {$wrappedTable} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
                            );
                            foreach ($headers as $col) {
                                $wc = $this->qi($col);
                                if (in_array($col, $existing, true)) {
                                    $callaoDb->statement("ALTER TABLE {$wrappedTable} MODIFY {$wc} LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
                                } else {
                                    $callaoDb->statement("ALTER TABLE {$wrappedTable} ADD {$wc} LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
                                }
                            }
                        } else {
                            $cols = array_map(fn($c) => $this->qi($c) . ' LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL', $headers);
                            $callaoDb->statement('CREATE TABLE ' . $wrappedTable . ' (' . implode(', ', $cols) . ')');
                        }

                        // 2c. Limpiar e insertar
                        $callaoDb->table($mysqlTable)->delete();

                        $chunk = [];
                        while (($row = fgetcsv($handle)) !== false) {
                            if (count($row) !== count($headers)) continue;

                            $record = array_combine($headers, $row);
                            array_walk($record, function (&$v) {
                                if (!is_string($v)) return;
                                if (!mb_check_encoding($v, 'UTF-8')) {
                                    $v = mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
                                }
                                if ($v === '') $v = null;
                            });

                            $chunk[] = $record;

                            if (count($chunk) >= 500) {
                                $callaoDb->table($mysqlTable)->insert($chunk);
                                $chunk = [];
                            }
                        }

                        if (!empty($chunk)) {
                            $callaoDb->table($mysqlTable)->insert($chunk);
                        }

                    } finally {
                        fclose($handle);
                    }

                } finally {
                    if (file_exists($tempCsv)) {
                        @unlink($tempCsv);
                    }
                }

                $pct = 20 + (int)(($tableIndex / max($tableCount, 1)) * 70);
                Cache::put($progressKey, min($pct, 90));
            }

            // ── 3. Diagnóstico: filas con CHECKTIME inválido ─────────────────
            $invalidCount = DB::connection('asistencia_callao')
                ->selectOne("
                    SELECT COUNT(*) AS cnt
                    FROM checkinout
                    WHERE CHECKTIME IS NOT NULL
                      AND TRIM(CHECKTIME) <> ''
                      AND COALESCE(
                            STR_TO_DATE(CHECKTIME, '%m/%d/%y %H:%i:%s'),
                            STR_TO_DATE(CHECKTIME, '%m/%d/%Y %H:%i:%s'),
                            STR_TO_DATE(CHECKTIME, '%d/%m/%y %H:%i:%s'),
                            STR_TO_DATE(CHECKTIME, '%d/%m/%Y %H:%i:%s'),
                            STR_TO_DATE(CHECKTIME, '%Y-%m-%d %H:%i:%s')
                          ) IS NULL
                ")->cnt ?? 0;

            if ($invalidCount > 0) {
                Log::warning('[ProcessAsistenciaCallao] Filas con CHECKTIME inválido', [
                    'token'        => $this->uploadToken,
                    'invalidCount' => $invalidCount,
                ]);
            }

            // ── 4. INSERT final hacia m_asistencia con STR_TO_DATE ────────────
            //
            // CHECKTIME viene como texto desde mdb-export en formato MM/DD/YY HH:MM:SS.
            // Se convierte con COALESCE(STR_TO_DATE(...)) probando varios formatos.
            // Solo se insertan filas donde check_dt no sea NULL (conversión exitosa).
            $inserted = DB::affectingStatement("
                INSERT INTO m_asistencia (
                    IDTIPO_ASISTENCIA, NUM_DOC, IDCENTRO_MAC,
                    MES, `AÑO`, FECHA, HORA, FECHA_BIOMETRICO,
                    NUM_BIOMETRICO, CORRELATIVO, CORRELATIVO_DIA
                )
                SELECT
                    2,
                    ui.ssn,
                    ?,
                    LPAD(MONTH(x.check_dt), 2, '0'),
                    YEAR(x.check_dt),
                    DATE(x.check_dt),
                    TIME_FORMAT(x.check_dt, '%H:%i:%s'),
                    x.check_dt,
                    '', '', ''
                FROM (
                    SELECT
                        chk.*,
                        COALESCE(
                            STR_TO_DATE(chk.CHECKTIME, '%m/%d/%y %H:%i:%s'),
                            STR_TO_DATE(chk.CHECKTIME, '%m/%d/%Y %H:%i:%s'),
                            STR_TO_DATE(chk.CHECKTIME, '%d/%m/%y %H:%i:%s'),
                            STR_TO_DATE(chk.CHECKTIME, '%d/%m/%Y %H:%i:%s'),
                            STR_TO_DATE(chk.CHECKTIME, '%Y-%m-%d %H:%i:%s')
                        ) AS check_dt
                    FROM asistencia_callao.checkinout chk
                    WHERE chk.CHECKTIME IS NOT NULL
                      AND TRIM(chk.CHECKTIME) <> ''
                ) x
                JOIN asistencia_callao.userinfo ui ON ui.userid = x.userid
                WHERE ui.ssn IS NOT NULL
                  AND TRIM(ui.ssn) <> ''
                  AND x.check_dt IS NOT NULL
                  AND DATE(x.check_dt) BETWEEN ? AND ?
                  AND NOT EXISTS (
                      SELECT 1
                      FROM m_asistencia ma
                      WHERE ma.NUM_DOC      = ui.ssn COLLATE utf8mb4_unicode_ci
                        AND ma.IDCENTRO_MAC = ?
                        AND ma.FECHA        = DATE(x.check_dt)
                        AND ma.HORA         = TIME_FORMAT(x.check_dt, '%H:%i:%s')
                  )
            ", [$this->idCentroMac, $this->fechaInicio, $this->fechaFin, $this->idCentroMac]);

            Log::info('[ProcessAsistenciaCallao] INSERT completado', [
                'token'        => $this->uploadToken,
                'inserted'     => $inserted,
                'invalid'      => $invalidCount,
                'idCentroMac'  => $this->idCentroMac,
                'fechaInicio'  => $this->fechaInicio,
                'fechaFin'     => $this->fechaFin,
            ]);

            // ── 5. Marcar resultado ───────────────────────────────────────────
            if ($inserted === 0) {
                // El archivo se leyó bien pero no hubo marcaciones en el rango
                $msg = "El archivo fue leído correctamente ({$invalidCount} filas con formato inválido), "
                     . "pero no hay marcaciones dentro del rango de fechas seleccionado "
                     . "({$this->fechaInicio} → {$this->fechaFin}). "
                     . "Verifique que el archivo corresponda al período indicado.";

                Cache::put($errorKey,  $msg);
                Cache::put($statusKey, 'failed');

                Log::warning('[ProcessAsistenciaCallao] Sin registros en rango de fechas', [
                    'token'       => $this->uploadToken,
                    'fechaInicio' => $this->fechaInicio,
                    'fechaFin'    => $this->fechaFin,
                    'invalid'     => $invalidCount,
                ]);

                // Archivo leído correctamente → se puede eliminar
                Storage::disk('local')->delete($this->path);
                return;
            }

            // Éxito real: eliminar archivo y completar
            Storage::disk('local')->delete($this->path);
            Cache::put($statusKey,   'completed');
            Cache::put($progressKey, 100);

        } catch (\Exception $e) {
            // NO se elimina el archivo: puede ser útil para diagnóstico
            $msg = $e->getMessage();
            Cache::put($statusKey, 'failed');
            Cache::put($errorKey,  $msg);

            Log::error('[ProcessAsistenciaCallao] Excepción no controlada', [
                'token'   => $this->uploadToken,
                'message' => $msg,
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            throw $e;
        }
    }

    // ─── Validación y diagnóstico previo del archivo ─────────────────────────

    private function validateFile(string $fullPath, string $ext, string $errorKey, string $statusKey): void
    {
        $storageExists = Storage::disk('local')->exists($this->path);
        $fsExists      = file_exists($fullPath);
        $size          = $fsExists ? filesize($fullPath) : 0;
        $whoami        = trim((string) shell_exec('whoami 2>/dev/null'));
        $fileCmd       = trim((string) shell_exec('file ' . escapeshellarg($fullPath) . ' 2>/dev/null'))
                     ?: 'comando "file" no disponible en este entorno';

        Log::info('[ProcessAsistenciaCallao] Diagnóstico de archivo', [
            'token'          => $this->uploadToken,
            'storedPath'     => $this->path,
            'fullPath'       => $fullPath,
            'extension'      => $ext,
            'sizeBytes'      => $size,
            'storageExists'  => $storageExists,
            'fsExists'       => $fsExists,
            'linuxUser'      => $whoami,
            'fileCmd'        => $fileCmd,
        ]);

        if (!$fsExists) {
            $msg = "Archivo no encontrado en el sistema de archivos: {$fullPath}. "
                 . "Storage::exists={$storageExists}. Usuario Linux: {$whoami}.";
            Cache::put($errorKey,  $msg);
            Cache::put($statusKey, 'failed');
            throw new \RuntimeException($msg);
        }

        if ($size === 0) {
            $msg = "El archivo llegó vacío (0 bytes): {$fullPath}. "
                 . "Puede que el ensamblado de chunks haya fallado parcialmente.";
            Cache::put($errorKey,  $msg);
            Cache::put($statusKey, 'failed');
            throw new \RuntimeException($msg);
        }

        if (!in_array($ext, ['mdb', 'accdb'], true)) {
            $msg = "Extensión no permitida: .{$ext}. Solo se aceptan .mdb y .accdb.";
            Cache::put($errorKey,  $msg);
            Cache::put($statusKey, 'failed');
            throw new \RuntimeException($msg);
        }
    }

    // ─── Ejecución de mdb-tables ─────────────────────────────────────────────

    /**
     * Devuelve [array $tables|null, string $stdout, string $stderr, int $exitCode].
     * Usa proc_open para capturar stdout, stderr y exit code por separado.
     */
    private function runMdbTables(string $fullPath): array
    {
        $pipes = [];
        $proc  = proc_open(
            ['mdb-tables', '-1', $fullPath],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($proc)) {
            return [null, '', 'proc_open no pudo iniciar mdb-tables', -1];
        }

        fclose($pipes[0]);
        $stdout   = (string) stream_get_contents($pipes[1]);
        $stderr   = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        if ($exitCode !== 0 || trim($stdout) === '') {
            return [null, $stdout, $stderr, $exitCode];
        }

        $tables = array_values(array_filter(
            array_map('trim', explode("\n", trim($stdout))),
            fn($t) => $t !== '' && $t !== 'Switchboard Items'
        ));

        return [
            empty($tables) ? null : $tables,
            $stdout,
            $stderr,
            $exitCode,
        ];
    }

    // ─── Ejecución de mdb-export ─────────────────────────────────────────────

    /**
     * Devuelve [bool $ok, string $stdout, string $stderr, int $exitCode].
     */
    private function runMdbExport(string $fullPath, string $table, string $destCsv): array
    {
        $pipes = [];
        $proc  = proc_open(
            ['mdb-export', $fullPath, $table],
            [
                0 => ['pipe', 'r'],
                1 => ['file', $destCsv, 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($proc)) {
            return [false, '', 'proc_open no pudo iniciar mdb-export', -1];
        }

        fclose($pipes[0]);
        $stderr   = (string) stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        $ok = $exitCode === 0 && file_exists($destCsv) && filesize($destCsv) > 0;

        return [$ok, '', $stderr, $exitCode];
    }

    // ─── Construcción del mensaje de error mdb-tables ────────────────────────

    private function buildMdbTablesError(string $ext, string $stdout, string $stderr, int $exitCode): string
    {
        $detail = trim($stderr ?: $stdout);
        $detail = $detail !== '' ? " Detalle técnico: {$detail}" : " Sin mensaje adicional de mdb-tools.";

        if ($ext === 'accdb') {
            return "El archivo .accdb no pudo ser leído por mdb-tools en Linux. "
                 . "mdb-tools en Linux soporta principalmente el formato Access 97-2003 (.mdb). "
                 . "Solución: abra el archivo en Microsoft Access y guárdelo como 'Base de datos de Access 2002-2003 (*.mdb)'. "
                 . "(Código de salida: {$exitCode}.{$detail})";
        }

        return "El archivo .mdb fue recibido pero mdb-tools no pudo leer sus tablas. "
             . "Posibles causas: archivo corrupto, ensamblado incompleto de chunks, "
             . "o formato MDB no compatible con esta versión de mdb-tools. "
             . "(Código de salida: {$exitCode}.{$detail})";
    }

    // ─── Helpers de esquema MySQL ─────────────────────────────────────────────

    private function qi(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function tableExists($connection, string $table): bool
    {
        return $connection->table('information_schema.tables')
            ->where('table_schema', $connection->getDatabaseName())
            ->where('table_name', $table)
            ->exists();
    }

    private function getExistingColumns($connection, string $table): array
    {
        $rows = $connection->select(
            'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
            [$connection->getDatabaseName(), $table]
        );

        return collect($rows)
            ->map(fn($r) => (array) $r)
            ->map(fn($r) => $r['column_name'] ?? $r['COLUMN_NAME'] ?? $r['Field'] ?? null)
            ->filter(fn($c) => is_string($c) && $c !== '')
            ->values()
            ->all();
    }
}
