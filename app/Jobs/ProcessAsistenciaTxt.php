<?php

namespace App\Jobs;

use App\Models\Asistencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class ProcessAsistenciaTxt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $path;
    private int $idCentroMac;
    private string $uploadToken;

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
        cache()->put('upload_status:' . $this->uploadToken, 'running');
        $fullPath = Storage::disk('local')->path($this->path);
        $totalLines = 0;
        $counter = new SplFileObject($fullPath, 'r');
        while (!$counter->eof()) {
            $counter->fgets();
            $totalLines++;
        }

        $file = new SplFileObject($fullPath, 'r');
        $lineIndex = -1;
        $processedLines = 0;
        $lastPercent = 0;

        while (!$file->eof()) {
            if (cache()->get($cancelKey, false)) {
                cache()->put('upload_status:' . $this->uploadToken, 'cancelled');
                cache()->put($progressKey, 0);
                Storage::disk('local')->delete($this->path);
                return;
            }

            $line = $file->fgets();
            if ($line === false) {
                continue;
            }
            $processedLines++;

            if ($totalLines > 0) {
                $percent = (int) floor(($processedLines / $totalLines) * 100);
                if ($percent > $lastPercent) {
                    $lastPercent = $percent;
                    cache()->put($progressKey, $percent);
                }
            }

            if ($line === false || trim($line) === '') {
                continue;
            }

            $data = explode("\t", $line);
            if (count($data) < 7) {
                continue;
            }

            $numDoc = trim($data[2]);
            $fechaBiometrico = trim($data[6]);
            $fechaHora = explode(' ', $fechaBiometrico);
            if (count($fechaHora) !== 2) {
                continue;
            }

            $fecha = $fechaHora[0];
            $hora = $fechaHora[1];
            $fechaParts = explode('/', $fecha);
            if (count($fechaParts) !== 3) {
                continue;
            }

            $anio = $fechaParts[0];
            $mes = $fechaParts[1];
            $lineIndex++;

            $exists = Asistencia::where('NUM_DOC', $numDoc)
                ->where('IDCENTRO_MAC', $this->idCentroMac)
                ->where('FECHA_BIOMETRICO', $fechaBiometrico)
                ->exists();

            if ($exists) {
                continue;
            }

            Asistencia::create([
                'IDTIPO_ASISTENCIA' => 1,
                'NUM_DOC' => $numDoc,
                'IDCENTRO_MAC' => $this->idCentroMac,
                'MES' => $mes,
                "AÇ'O" => $anio,
                'FECHA' => $fechaBiometrico,
                'HORA' => $hora,
                'FECHA_BIOMETRICO' => $fechaBiometrico,
                'NUM_BIOMETRICO' => '',
                'CORRELATIVO' => $lineIndex + 1,
                'CORRELATIVO_DIA' => '',
            ]);
        }

        cache()->put('upload_status:' . $this->uploadToken, 'completed');
        cache()->put($progressKey, 100);
        Storage::disk('local')->delete($this->path);
    }
}
