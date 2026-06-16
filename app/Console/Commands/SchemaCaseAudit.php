<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SchemaCaseAudit extends Command
{
    protected $signature = 'schema:case-audit
                            {--connection=mysql : Conexión de BD a auditar (por defecto: mysql)}
                            {--all              : Auditar todas las conexiones MySQL configuradas}
                            {--databases=       : Esquemas adicionales en el mismo servidor, separados por coma (ej: db_centro_mac_reporte,asistencia_callao)}';

    protected $description = 'Audita el esquema MySQL para detectar problemas de mayúsculas/minúsculas en Linux (lower_case_table_names=0)';

    // ─── Punto de entrada ────────────────────────────────────────────────────

    public function handle(): int
    {
        foreach ($this->resolveConnections() as $connName) {
            $this->runAudit($connName);
        }
        return Command::SUCCESS;
    }

    // ─── Audit por conexión ──────────────────────────────────────────────────

    private function runAudit(string $connName): void
    {
        $this->newLine();
        $sep = str_repeat('═', 60);
        $this->line("<fg=cyan>╔{$sep}╗</>");

        try {
            $conn   = DB::connection($connName);
            $dbName = $conn->getDatabaseName();
            $label  = str_pad("  CONEXIÓN: {$connName}  │  BD: {$dbName}", 60);
            $this->line("<fg=cyan>║</><fg=yellow;options=bold>{$label}</><fg=cyan>║</>");
            $this->line("<fg=cyan>╚{$sep}╝</>");
        } catch (\Exception $e) {
            $this->line("<fg=cyan>╚{$sep}╝</>");
            $this->error("  No se pudo conectar a [{$connName}]: " . $e->getMessage());
            return;
        }

        // ── §1 Configuración del servidor ────────────────────────────────────
        $this->section('1', 'CONFIGURACIÓN DEL SERVIDOR MYSQL');
        $lctn = $this->checkLowerCaseSetting($conn);

        // ── §2–3 Inventario de objetos ───────────────────────────────────────
        $extraDbs = $this->parseExtraDbs();
        $schemas  = array_unique([$dbName, ...$extraDbs]);

        $tablesBySchema = [];
        $viewsBySchema  = [];
        foreach ($schemas as $schema) {
            $tablesBySchema[$schema] = $this->fetchObjects($conn, $schema, 'BASE TABLE');
            $viewsBySchema[$schema]  = $this->fetchObjects($conn, $schema, 'VIEW');
        }

        // ── §2 Tablas con mayúsculas ─────────────────────────────────────────
        $this->section('2', 'TABLAS CON CARACTERES EN MAYÚSCULA');
        $upperTables = 0;
        foreach ($tablesBySchema as $schema => $tables) {
            $upper = array_values(array_filter($tables, fn($t) => $t !== strtolower($t)));
            if (empty($upper)) continue;
            $upperTables += count($upper);
            $this->line("  <fg=yellow>Esquema: {$schema}</> (" . count($upper) . ' tablas)');
            foreach ($upper as $tbl) {
                $lower = strtolower($tbl);
                $this->line("    <fg=red>▶</> {$tbl}  <fg=gray>→ alias lower:</> <fg=green>{$lower}</>");
            }
        }
        if ($upperTables === 0) {
            $this->ok('Todas las tablas están en minúsculas.');
        }

        // ── §3 Vistas con mayúsculas ─────────────────────────────────────────
        $this->section('3', 'VISTAS CON CARACTERES EN MAYÚSCULA');
        $upperViews = 0;
        foreach ($viewsBySchema as $schema => $views) {
            $upper = array_values(array_filter($views, fn($v) => $v !== strtolower($v)));
            if (empty($upper)) continue;
            $upperViews += count($upper);
            $this->line("  <fg=yellow>Esquema: {$schema}</> (" . count($upper) . ' vistas)');
            foreach ($upper as $view) {
                $this->line("    <fg=red>▶</> {$view}  <fg=gray>→ alias lower:</> <fg=green>" . strtolower($view) . '</>');
            }
        }
        if ($upperViews === 0) {
            $this->ok('Todas las vistas están en minúsculas.');
        }

        // ── §4 Conflictos de nombres ─────────────────────────────────────────
        $this->section('4', 'CONFLICTOS (objetos que colisionan al aplicar lower())');
        $conflicts = $this->detectConflicts($tablesBySchema, $viewsBySchema);
        if (empty($conflicts)) {
            $this->ok('Sin conflictos detectados.');
        } else {
            foreach ($conflicts as $c) {
                $this->line("  <fg=red;options=bold>⚠ CONFLICTO:</> {$c}");
            }
        }

        // ── §5 Procedimientos y funciones ────────────────────────────────────
        $this->section('5', 'REFERENCIAS EN PROCEDIMIENTOS Y FUNCIONES ALMACENADOS');
        $tableMap      = $this->buildTableMap($tablesBySchema);
        $routineIssues = $this->auditRoutines($conn, $dbName, $tableMap);
        if (empty($routineIssues)) {
            $this->ok('Sin referencias problemáticas en procedimientos/funciones.');
        } else {
            foreach ($routineIssues as [$label, $ref, $actual, $context]) {
                $this->line("  <fg=red>✗</> {$label}");
                $this->line("    <fg=gray>  Referencia: `{$ref}`  →  Tabla real: `{$actual}`</>");
                $this->line("    <fg=gray>  Contexto: ...{$context}...</>");
            }
        }

        // ── §6 Triggers ──────────────────────────────────────────────────────
        $this->section('6', 'REFERENCIAS EN TRIGGERS');
        $triggerIssues = $this->auditTriggers($conn, $dbName, $tableMap);
        if (empty($triggerIssues)) {
            $this->ok('Sin referencias problemáticas en triggers.');
        } else {
            foreach ($triggerIssues as [$label, $ref, $actual, $context]) {
                $this->line("  <fg=red>✗</> {$label}");
                $this->line("    <fg=gray>  Referencia: `{$ref}`  →  Tabla real: `{$actual}`</>");
                $this->line("    <fg=gray>  Contexto: ...{$context}...</>");
            }
        }

        // ── §7 Resumen ───────────────────────────────────────────────────────
        $this->section('7', 'RESUMEN EJECUTIVO');
        $this->table(
            ['Ítem', 'Cantidad', 'Estado'],
            [
                ['lower_case_table_names',              $lctn,                    $lctn === 0 ? '⚠ Sensible (Linux)' : '✔ Insensible'],
                ['Tablas con mayúsculas',                $upperTables,             $upperTables > 0 ? '⚠ Revisar' : '✔ OK'],
                ['Vistas con mayúsculas',                $upperViews,              $upperViews  > 0 ? '⚠ Revisar' : '✔ OK'],
                ['Conflictos de nombres',                count($conflicts),        count($conflicts) > 0 ? '🔴 Acción manual' : '✔ OK'],
                ['Refs. problemáticas (SP/FN)',          count($routineIssues),    count($routineIssues) > 0 ? '⚠ Revisar' : '✔ OK'],
                ['Refs. problemáticas (triggers)',       count($triggerIssues),    count($triggerIssues) > 0 ? '⚠ Revisar' : '✔ OK'],
            ]
        );

        if ($upperTables > 0 || count($routineIssues) > 0 || count($triggerIssues) > 0) {
            $dbs = $extraDbs ? ' --databases=' . implode(',', $extraDbs) : '';
            $this->newLine();
            $this->line("  <fg=cyan>Próximo paso — generar vistas de compatibilidad:</>");
            $this->line("  <fg=white>  php artisan schema:case-compat-views --connection={$connName}{$dbs} --dry-run</>");
        }
    }

    // ─── Revisión del servidor ───────────────────────────────────────────────

    private function checkLowerCaseSetting($conn): int
    {
        try {
            $row   = $conn->selectOne("SHOW VARIABLES LIKE 'lower_case_table_names'");
            $value = $row ? (int) $row->Value : -1;
        } catch (\Exception) {
            $value = -1;
        }

        $info = match ($value) {
            0  => ['red',    '0  →  SENSIBLE a mayúsculas/minúsculas (Linux default)  ⚠'],
            1  => ['green',  '1  →  Insensible, almacena en minúsculas (Windows/MariaDB)'],
            2  => ['yellow', '2  →  Insensible, preserva mayúsculas en disco'],
            default => ['yellow', "{$value}  →  Valor desconocido"],
        };

        $this->line("  lower_case_table_names = <fg={$info[0]}>{$info[1]}</>");

        if ($value === 0) {
            $this->line("  <fg=yellow>ℹ En Linux con valor 0, los nombres de tabla son CASE-SENSITIVE.</>");
            $this->line("  <fg=yellow>  Referencia a `M_ASISTENCIA` falla si la tabla se llama `m_asistencia`.</>");
        }

        return $value;
    }

    // ─── Detección de conflictos ─────────────────────────────────────────────

    private function detectConflicts(array $tablesBySchema, array $viewsBySchema): array
    {
        $issues = [];

        foreach ($tablesBySchema as $schema => $tables) {
            // Tablas vs tablas con mismo lower()
            $lowerMap = [];
            foreach ($tables as $tbl) {
                $l = strtolower($tbl);
                if (isset($lowerMap[$l])) {
                    $issues[] = "[{$schema}] Tablas '{$lowerMap[$l]}' y '{$tbl}' comparten el mismo nombre en minúsculas → conflicto de alias imposible";
                }
                $lowerMap[$l] = $tbl;
            }

            // Tablas vs vistas con mismo lower()
            $views = $viewsBySchema[$schema] ?? [];
            foreach ($views as $view) {
                $l = strtolower($view);
                if (isset($lowerMap[$l]) && $lowerMap[$l] !== $view) {
                    $issues[] = "[{$schema}] Tabla '{$lowerMap[$l]}' y vista '{$view}' comparten lower() '{$l}' → alias no puede crearse sin conflicto";
                }
            }
        }

        return $issues;
    }

    // ─── Análisis de procedimientos ──────────────────────────────────────────

    private function auditRoutines($conn, string $dbName, array $tableMap): array
    {
        $issues = [];

        try {
            $rows = $conn->select(
                "SELECT ROUTINE_NAME, ROUTINE_TYPE, ROUTINE_DEFINITION
                 FROM information_schema.ROUTINES
                 WHERE ROUTINE_SCHEMA = ?
                 ORDER BY ROUTINE_TYPE, ROUTINE_NAME",
                [$dbName]
            );
        } catch (\Exception $e) {
            $this->line("  <fg=yellow>⚠ No se pudo leer ROUTINES: " . $e->getMessage() . '</>');
            return [];
        }

        foreach ($rows as $row) {
            $body = $row->ROUTINE_DEFINITION ?? '';
            $name = $row->ROUTINE_NAME;
            $type = $row->ROUTINE_TYPE;

            foreach ($this->extractTableRefs($body) as [$ref, $context]) {
                $lower = strtolower($ref);
                if (isset($tableMap[$lower]) && $tableMap[$lower] !== $ref) {
                    $actual   = $tableMap[$lower];
                    $issues[] = ["[{$type}] `{$dbName}`.`{$name}`", $ref, $actual, $context];
                }
            }
        }

        return $issues;
    }

    // ─── Análisis de triggers ─────────────────────────────────────────────────

    private function auditTriggers($conn, string $dbName, array $tableMap): array
    {
        $issues = [];

        try {
            $rows = $conn->select(
                "SELECT TRIGGER_NAME, ACTION_STATEMENT
                 FROM information_schema.TRIGGERS
                 WHERE TRIGGER_SCHEMA = ?
                 ORDER BY TRIGGER_NAME",
                [$dbName]
            );
        } catch (\Exception $e) {
            $this->line("  <fg=yellow>⚠ No se pudo leer TRIGGERS: " . $e->getMessage() . '</>');
            return [];
        }

        foreach ($rows as $row) {
            $body = $row->ACTION_STATEMENT ?? '';
            $name = $row->TRIGGER_NAME;

            foreach ($this->extractTableRefs($body) as [$ref, $context]) {
                $lower = strtolower($ref);
                if (isset($tableMap[$lower]) && $tableMap[$lower] !== $ref) {
                    $actual   = $tableMap[$lower];
                    $issues[] = ["[TRIGGER] `{$dbName}`.`{$name}`", $ref, $actual, $context];
                }
            }
        }

        return $issues;
    }

    // ─── Extracción de referencias a tablas en SQL ────────────────────────────

    /**
     * Devuelve array de [nombre_tabla_referenciado, contexto_breve].
     * Detecta: FROM x, JOIN x, INTO x, UPDATE x, TABLE x
     * Maneja: `schema`.`tabla`, `tabla`, schema.tabla, tabla
     */
    private function extractTableRefs(string $sql): array
    {
        $refs = [];

        $keywords = [
            'TABLE', 'SELECT', 'WHERE', 'SET', 'VALUES', 'INTO',
            'DUAL', 'INFORMATION_SCHEMA', 'PERFORMANCE_SCHEMA', 'SYS',
            'NEW', 'OLD', 'CURRENT', 'NEXT', 'PRIOR',
        ];

        // Patrón: FROM/JOIN/INTO/UPDATE/TABLE seguido de [schema.]tabla (con o sin backticks)
        $pattern = '/\b(FROM|(?:(?:INNER|LEFT|RIGHT|CROSS|FULL)\s+)?(?:OUTER\s+)?JOIN|INSERT\s+(?:IGNORE\s+)?INTO|UPDATE|TABLE)\s+'
            . '(?:(`[^`]+`|[\w]+)\s*\.\s*)?'   // schema opcional
            . '(`[^`]+`|[\w]+)/i';              // tabla

        if (!preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return [];
        }

        foreach ($matches as $match) {
            $table   = trim($match[3][0] ?? '', '`');
            $schema  = trim($match[2][0] ?? '', '` ');
            $offset  = $match[0][1];
            $context = substr($sql, max(0, $offset - 20), 60);
            $context = preg_replace('/\s+/', ' ', $context);

            if ($table === '') continue;
            if (in_array(strtoupper($table), $keywords, true)) continue;
            if (is_numeric($table)) continue;

            $ref    = $schema !== '' ? "{$schema}.{$table}" : $table;
            $key    = strtolower($ref);

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $refs[]     = [$ref, trim($context)];
            }
        }

        return $refs;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function fetchObjects($conn, string $schema, string $type): array
    {
        try {
            $rows = $conn->select(
                "SELECT TABLE_NAME
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?
                 ORDER BY TABLE_NAME",
                [$schema, $type]
            );
            return array_column($rows, 'TABLE_NAME');
        } catch (\Exception) {
            return [];
        }
    }

    private function buildTableMap(array $tablesBySchema): array
    {
        $map = [];
        foreach ($tablesBySchema as $tables) {
            foreach ($tables as $tbl) {
                $map[strtolower($tbl)] = $tbl;
            }
        }
        return $map;
    }

    private function parseExtraDbs(): array
    {
        return array_filter(array_map('trim', explode(',', $this->option('databases') ?? '')));
    }

    private function section(string $num, string $title): void
    {
        $this->newLine();
        $line = "[{$num}] {$title}";
        $this->line("<fg=cyan;options=bold>{$line}</>");
        $this->line('<fg=cyan>' . str_repeat('─', min(strlen($line) + 4, 64)) . '</>');
    }

    private function ok(string $msg): void
    {
        $this->line("  <fg=green>✔</> {$msg}");
    }
}
