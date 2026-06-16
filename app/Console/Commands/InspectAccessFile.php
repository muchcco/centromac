<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InspectAccessFile extends Command
{
    protected $signature = 'asistencia:inspect-access
                            {path : Ruta relativa a storage/app/ o ruta absoluta del archivo .mdb/.accdb}
                            {--rows=5 : Número de filas a mostrar por tabla de muestra}';

    protected $description = 'Diagnóstico de un archivo Access (.mdb/.accdb): valida existencia, corre mdb-tables y muestra primeras filas de tablas clave';

    // Tablas de Access que se intentarán previsualizar
    private const PREVIEW_TABLES = ['USERINFO', 'userinfo', 'CHECKINOUT', 'checkinout', 'Machines', 'machines'];

    public function handle(): int
    {
        $input    = $this->argument('path');
        $fullPath = $this->resolvePath($input);

        $this->newLine();
        $this->line('<fg=cyan;options=bold>╔══════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan;options=bold>║      DIAGNÓSTICO ARCHIVO ACCESS — mdb-tools          ║</>');
        $this->line('<fg=cyan;options=bold>╚══════════════════════════════════════════════════════╝</>');

        // ── §1: Validación del archivo ────────────────────────────────────────
        $this->section('1', 'INFORMACIÓN DEL ARCHIVO');

        if (!file_exists($fullPath)) {
            $this->error("  Archivo NO encontrado: {$fullPath}");
            $this->newLine();
            $this->line("  Rutas intentadas desde el argumento '{$input}':");
            $this->line("    • Absoluta:           {$fullPath}");
            $this->line("    • storage/app/:       " . storage_path("app/{$input}"));
            return Command::FAILURE;
        }

        $size   = filesize($fullPath);
        $ext    = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $whoami = trim((string) shell_exec('whoami 2>/dev/null'));

        $this->table(['Campo', 'Valor'], [
            ['Ruta completa',  $fullPath],
            ['Extensión',      ".{$ext}"],
            ['Tamaño',         number_format($size) . ' bytes (' . round($size / 1024 / 1024, 2) . ' MB)'],
            ['Permisos Unix',  substr(sprintf('%o', fileperms($fullPath)), -4)],
            ['Usuario PHP',    $whoami],
            ['Propietario',    $this->getFileOwner($fullPath)],
        ]);

        // ── §2: Salida de `file` ──────────────────────────────────────────────
        $this->section('2', 'COMANDO: file');
        $fileOut = $this->runSimple(['file', $fullPath]);
        $this->line('  ' . ($fileOut ?: '(sin salida)'));

        // Advertencia si parece ZIP (formato .accdb / .xlsx)
        if (stripos($fileOut, 'zip') !== false || stripos($fileOut, 'microsoft excel') !== false) {
            $this->newLine();
            $this->warn("  ⚠ El archivo parece ser formato ZIP/Office Open XML (.accdb >= Access 2007).");
            $this->warn("    mdb-tools solo soporta el formato Jet/MDB (Access 97-2003).");
            $this->warn("    Solución: guarde el archivo como 'Access 2002-2003 (*.mdb)' desde Microsoft Access.");
        }

        // ── §3: mdb-tables ───────────────────────────────────────────────────
        $this->section('3', 'COMANDO: mdb-tables -1');
        [$stdout, $stderr, $exitCode] = $this->runCapture(['mdb-tables', '-1', $fullPath]);

        $this->line("  Exit code: <fg=" . ($exitCode === 0 ? 'green' : 'red') . ">{$exitCode}</>");

        if ($stderr !== '') {
            $this->line("  <fg=red>STDERR:</> {$stderr}");
        }

        if ($stdout === '') {
            $this->line("  <fg=yellow>STDOUT: (vacío)</>");
        } else {
            $this->line("  <fg=green>STDOUT:</>");
            foreach (explode("\n", trim($stdout)) as $line) {
                $this->line("    • {$line}");
            }
        }

        if ($exitCode !== 0 || trim($stdout) === '') {
            $this->newLine();
            $this->error("  mdb-tables no pudo leer el archivo. Ver §2 y §3 para diagnóstico.");
            $this->printTroubleshooting($ext, $stderr);
            return Command::FAILURE;
        }

        // ── §4: Previsualización de tablas clave ──────────────────────────────
        $tables = array_values(array_filter(
            array_map('trim', explode("\n", trim($stdout))),
            fn($t) => $t !== '' && $t !== 'Switchboard Items'
        ));

        $this->section('4', 'TABLAS ENCONTRADAS (' . count($tables) . ')');
        foreach ($tables as $t) {
            $this->line("  • {$t}");
        }

        $this->section('5', 'PREVISUALIZACIÓN DE TABLAS CLAVE (primeras ' . $this->option('rows') . ' filas)');

        $tablesToPreview = array_intersect($tables, self::PREVIEW_TABLES);

        if (empty($tablesToPreview)) {
            $this->line("  <fg=yellow>Ninguna de las tablas clave (" . implode(', ', self::PREVIEW_TABLES) . ") fue encontrada.");
            $this->line("  Las tablas disponibles son: " . implode(', ', $tables));
        }

        foreach ($tablesToPreview as $table) {
            $this->previewTable($fullPath, $table, (int) $this->option('rows'));
        }

        // ── §6: Resumen final ─────────────────────────────────────────────────
        $this->section('6', 'RESUMEN');

        $hasUserinfo    = !empty(array_intersect($tables, ['USERINFO', 'userinfo']));
        $hasCheckinout  = !empty(array_intersect($tables, ['CHECKINOUT', 'checkinout']));

        $this->table(['Verificación', 'Estado'], [
            ['Archivo existe',               '✔'],
            ['Tamaño > 0',                   $size > 0 ? '✔' : '✗'],
            ['mdb-tables exitCode=0',         $exitCode === 0 ? '✔' : '✗'],
            ['Tablas encontradas',            count($tables) > 0 ? '✔ (' . count($tables) . ')' : '✗'],
            ['Tabla USERINFO presente',      $hasUserinfo   ? '✔' : '✗ (requerida para importar)'],
            ['Tabla CHECKINOUT presente',    $hasCheckinout ? '✔' : '✗ (requerida para importar)'],
        ]);

        if ($hasUserinfo && $hasCheckinout) {
            $this->newLine();
            $this->info("  ✔ El archivo parece compatible para importar con el Job ProcessAsistenciaCallao.");
        } else {
            $this->newLine();
            $this->warn("  ⚠ Faltan tablas requeridas (USERINFO y/o CHECKINOUT). El Job fallará en el INSERT final.");
        }

        return Command::SUCCESS;
    }

    // ─── Previsualización de una tabla ───────────────────────────────────────

    private function previewTable(string $fullPath, string $table, int $maxRows): void
    {
        $this->newLine();
        $this->line("  <fg=yellow;options=bold>Tabla: {$table}</>");

        $tempCsv = sys_get_temp_dir() . '/inspect_' . uniqid() . '.csv';

        try {
            [$stdout, $stderr, $exitCode] = $this->runCapture(['mdb-export', $fullPath, $table], $tempCsv);

            if ($exitCode !== 0 || !file_exists($tempCsv) || filesize($tempCsv) === 0) {
                $this->line("    <fg=red>mdb-export falló (exitCode={$exitCode}). STDERR: {$stderr}</>");
                return;
            }

            $handle = fopen($tempCsv, 'r');
            if (!$handle) {
                $this->line("    <fg=red>No se pudo abrir el CSV temporal.</>");
                return;
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                $this->line("    <fg=yellow>Tabla vacía o sin cabeceras.</>");
                fclose($handle);
                return;
            }

            $rows = [];
            $count = 0;
            while (($row = fgetcsv($handle)) !== false && $count < $maxRows) {
                $rows[] = $row;
                $count++;
            }
            fclose($handle);

            if (empty($rows)) {
                $this->line("    <fg=yellow>Tabla vacía (sin filas de datos).</>");
                return;
            }

            $this->table($headers, $rows);

        } finally {
            if (file_exists($tempCsv)) {
                @unlink($tempCsv);
            }
        }
    }

    // ─── Sugerencias de troubleshooting ──────────────────────────────────────

    private function printTroubleshooting(string $ext, string $stderr): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold>── SUGERENCIAS ──────────────────────────────────────────</>");

        if ($ext === 'accdb') {
            $this->line("  El formato .accdb (Access 2007+) usa el motor ACE, que mdb-tools no soporta completamente.");
            $this->line("  Para resolver:");
            $this->line("    1. Abra el archivo en Microsoft Access.");
            $this->line("    2. Vaya a Archivo → Guardar como → Formato: Access 2002-2003 (*.mdb).");
            $this->line("    3. Suba el archivo .mdb resultante.");
        } else {
            $this->line("  El archivo es .mdb pero mdb-tools no pudo abrirlo.");
            $this->line("  Posibles causas:");
            $this->line("    1. Archivo incompleto o corrupto (chunk upload fallido).");
            $this->line("    2. Archivo protegido con contraseña.");
            $this->line("    3. Formato MDB muy antiguo (Access 1.x/2.x) o muy nuevo.");

            if (stripos($stderr, 'password') !== false || stripos($stderr, 'encrypted') !== false) {
                $this->newLine();
                $this->warn("  ⚠ El error menciona contraseña/cifrado. El archivo puede estar protegido.");
            }
        }

        $this->newLine();
        $this->line("  Verificar que mdb-tools esté instalado:");
        $this->line("    " . $this->runSimple(['which', 'mdb-tables']));
        $this->line("    " . $this->runSimple(['mdb-tables', '--version']));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolvePath(string $input): string
    {
        if (str_starts_with($input, '/')) {
            return $input;
        }
        // Intentar primero como relativo a storage/app/
        $fromStorage = storage_path('app/' . $input);
        if (file_exists($fromStorage)) {
            return $fromStorage;
        }
        // Fallback: relativo al directorio de trabajo
        return realpath($input) ?: $input;
    }

    /**
     * Ejecuta un comando y captura stdout, stderr y exit code por separado.
     * Si $stdoutFile es dado, redirige stdout a ese archivo en lugar de capturarlo.
     */
    private function runCapture(array $cmd, ?string $stdoutFile = null): array
    {
        $stdoutDescriptor = $stdoutFile
            ? ['file', $stdoutFile, 'w']
            : ['pipe', 'w'];

        $pipes = [];
        $proc  = proc_open($cmd, [
            0 => ['pipe', 'r'],
            1 => $stdoutDescriptor,
            2 => ['pipe', 'w'],
        ], $pipes);

        if (!is_resource($proc)) {
            return ['', 'proc_open falló', -1];
        }

        fclose($pipes[0]);
        $stdout   = isset($pipes[1]) ? (string) stream_get_contents($pipes[1]) : '';
        $stderr   = (string) stream_get_contents($pipes[2]);
        if (isset($pipes[1])) fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        return [$stdout, $stderr, $exitCode];
    }

    /** Ejecuta un comando y devuelve stdout+stderr combinados como string. */
    private function runSimple(array $cmd): string
    {
        [$out, $err] = $this->runCapture($cmd);
        return trim($out . ($err ? "\n{$err}" : ''));
    }

    private function getFileOwner(string $path): string
    {
        $stat = @stat($path);
        if ($stat === false) return 'desconocido';
        $uid = $stat['uid'];
        $info = @posix_getpwuid($uid);
        return $info ? "{$info['name']} (uid={$uid})" : "uid={$uid}";
    }

    private function section(string $num, string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold>[{$num}] {$title}</>");
        $this->line('<fg=cyan>' . str_repeat('─', min(strlen($title) + 6, 58)) . '</>');
    }
}
