<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use PDOException;

class ProcessAsistenciaCallao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $path;
    private int $idCentroMac;
    private string $uploadToken;
    private string $fechaInicio;
    private string $fechaFin;

    public function __construct(string $path, int $idCentroMac, string $uploadToken, string $fechaInicio, string $fechaFin)
    {
        $this->path = $path;
        $this->idCentroMac = $idCentroMac;
        $this->uploadToken = $uploadToken;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function handle(): void
    {
        $progressKey = 'upload_progress:' . $this->uploadToken;
        $cancelKey = 'upload_cancelled:' . $this->uploadToken;
        cache()->put('upload_status:' . $this->uploadToken, 'running');
        cache()->put($progressKey, 0);

        $fullPath = Storage::disk('local')->path($this->path);
        $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$fullPath;";

        try {
            $accessDb = new PDO($dsn);
            $callaoDb = DB::connection('asistencia_callao');

            cache()->put($progressKey, 10);

            $tablesQuery = $accessDb->query("SELECT Name FROM MSysObjects WHERE Type=1 AND Name NOT LIKE 'MSys%'");
            $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

            cache()->put($progressKey, 20);

            foreach ($tables as $table) {
                if (cache()->get($cancelKey, false)) {
                    cache()->put('upload_status:' . $this->uploadToken, 'cancelled');
                    cache()->put($progressKey, 0);
                    Storage::disk('local')->delete($this->path);
                    return;
                }

                if ($table === 'Switchboard Items') {
                    continue;
                }

                try {
                    $dataQuery = $accessDb->query("SELECT * FROM [$table]");
                    $rows = $dataQuery->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    continue;
                }

                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $tableExists = $callaoDb->select("SHOW TABLES LIKE ?", [$table]);

                    if (!empty($tableExists)) {
                        $callaoDb->statement("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        foreach ($columns as $column) {
                            $callaoDb->statement("ALTER TABLE `$table` MODIFY `$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        }
                    } else {
                        $columnsSQL = [];
                        foreach ($columns as $column) {
                            $columnsSQL[] = "`$column` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                        }
                        $createTableSQL = "CREATE TABLE `$table` (" . implode(', ', $columnsSQL) . ")";
                        $callaoDb->statement($createTableSQL);
                    }

                    $callaoDb->table($table)->delete();

                    foreach ($rows as &$row) {
                        array_walk_recursive($row, function (&$value) {
                            if (!mb_check_encoding($value, 'UTF-8')) {
                                $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                            }
                        });

                        $callaoDb->table($table)->insert($row);
                    }
                }

                $currentProgress = cache()->get($progressKey, 0);
                cache()->put($progressKey, min($currentProgress + 10, 90));
            }

            DB::statement("INSERT INTO M_ASISTENCIA (
                    IDTIPO_ASISTENCIA,
                    NUM_DOC,
                    IDCENTRO_MAC,
                    MES,
                    AÑO,
                    FECHA,
                    HORA,
                    FECHA_BIOMETRICO,
                    NUM_BIOMETRICO,
                    CORRELATIVO,
                    CORRELATIVO_DIA
                )
                SELECT
                    2,
                    ui.ssn AS DNI,
                    ? AS nom_mac,
                    LPAD(MONTH(chk.CHECKTIME), 2, '0') AS mes,
                    YEAR(chk.CHECKTIME) AS anio,
                    DATE(chk.CHECKTIME) AS fecha,
                    TIME_FORMAT(chk.CHECKTIME, '%H:%i:%s') AS hora,
                    chk.CHECKTIME AS FECHA_BIOMETRICO,
                    '',
                    '',
                    ''
                FROM asistencia_callao.checkinout chk
                JOIN asistencia_callao.userinfo ui ON ui.userid = chk.userid
                WHERE ui.ssn IS NOT NULL
                AND ui.ssn > 0
                AND DATE(chk.CHECKTIME) BETWEEN ? AND ?
                AND NOT EXISTS (
                        SELECT 2
                        FROM M_ASISTENCIA ma
                        WHERE
                            ma.NUM_DOC = ui.ssn COLLATE utf8mb4_unicode_ci
                            AND ma.IDCENTRO_MAC = ?
                            AND ma.FECHA = DATE(chk.CHECKTIME)
                            AND ma.HORA = TIME_FORMAT(chk.CHECKTIME, '%H:%i:%s')
                )", [$this->idCentroMac, $this->fechaInicio, $this->fechaFin, $this->idCentroMac]);

            cache()->put('upload_status:' . $this->uploadToken, 'completed');
            cache()->put($progressKey, 100);
        } catch (\Exception $e) {
            cache()->put('upload_status:' . $this->uploadToken, 'failed');
            cache()->put('upload_error:' . $this->uploadToken, $e->getMessage());
            throw $e;
        } finally {
            Storage::disk('local')->delete($this->path);
        }
    }
}
