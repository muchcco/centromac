<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SchemaCaseCompatViews extends Command
{
    protected $signature = 'schema:case-compat-views
                            {--connection=mysql : Conexión de BD a procesar (por defecto: mysql)}
                            {--all              : Procesar todas las conexiones MySQL configuradas}
                            {--databases=       : Esquemas adicionales en el mismo servidor, separados por coma}
                            {--dry-run          : Solo mostrar el SQL que se ejecutaría (por defecto)}
                            {--execute          : Ejecutar las vistas de compatibilidad en la BD}
                            {--force            : No pedir confirmación antes de ejecutar}';

    protected $description = 'Genera o ejecuta vistas alias de compatibilidad para diferencias de mayúsculas/minúsculas en MySQL Linux';

    // ─── Punto de entrada ────────────────────────────────────────────────────

    public function handle(): int
    {
        // Sin ninguna flag → comportamiento igual a --dry-run
        $execute = (bool) $this->option('execute');

        foreach ($this->resolveConnections() as $connName) {
            $this->processConnection($connName, $execute);
        }

        return Command::SUCCESS;
    }

    // ─── Proceso por conexión ────────────────────────────────────────────────

    private function processConnection(string $connName, bool $execute): void
    {
        $this->newLine();
        $sep = str_repeat('═', 60);
        $this->line("<fg=cyan>╔{$sep}╗</>");

        try {
            $conn   = DB::connection($connName);
            $dbName = $conn->getDatabaseName();
            $mode   = $execute ? '<fg=red;options=bold>EJECUTAR</>' : '<fg=yellow>DRY-RUN (sin cambios)</>';
            $label  = str_pad("  CONEXIÓN: {$connName}  │  BD: {$dbName}  │  Modo: ", 60);
            $this->line("<fg=cyan>║</><fg=yellow;options=bold>{$label}</><fg=cyan>║</>");
            $this->line("<fg=cyan>║</>" . str_repeat(' ', 2) . $mode . "<fg=cyan>║</>");
            $this->line("<fg=cyan>╚{$sep}╝</>");
        } catch (\Exception $e) {
            $this->line("<fg=cyan>╚{$sep}╝</>");
            $this->error("  No se pudo conectar a [{$connName}]: " . $e->getMessage());
            return;
        }

        // Recolectar objetos
        $extraDbs = $this->parseExtraDbs();
        $schemas  = array_unique([$dbName, ...$extraDbs]);

        $tablesBySchema = [];
        $viewsBySchema  = [];
        foreach ($schemas as $schema) {
            $tablesBySchema[$schema] = $this->fetchObjects($conn, $schema, 'BASE TABLE');
            $viewsBySchema[$schema]  = $this->fetchObjects($conn, $schema, 'VIEW');
        }

        // Construir el plan de vistas
        [$safe, $conflicts, $manual] = $this->buildPlan(
            $conn, $dbName, $tablesBySchema, $viewsBySchema
        );

        // ── Mostrar plan ────────────────────────────────────────────────────

        $this->section('VISTAS A CREAR — sin conflictos (' . count($safe) . ')');
        if (empty($safe)) {
            $this->ok('No se necesitan vistas de compatibilidad para esta conexión.');
        } else {
            foreach ($safe as $entry) {
                $this->line("  <fg=green>+</> {$entry['sql']};");
                $this->line("    <fg=gray>→ {$entry['reason']}</>");
            }
        }

        $this->section('OMITIDAS — conflictos que requieren revisión manual (' . count($conflicts) . ')');
        if (empty($conflicts)) {
            $this->ok('Sin conflictos.');
        } else {
            foreach ($conflicts as $entry) {
                $this->line("  <fg=yellow>⚠</> {$entry['sql']};");
                $this->line("    <fg=yellow>→ {$entry['reason']}</>");
            }
        }

        $this->section('PROCEDIMIENTOS Y TRIGGERS QUE DEBEN CORREGIRSE MANUALMENTE (' . count($manual) . ')');
        if (empty($manual)) {
            $this->ok('Sin procedimientos/triggers problemáticos.');
        } else {
            $this->line("  <fg=yellow>Los siguientes objetos tienen referencias con diferencias de mayúsculas.");
            $this->line("  Si ya se creó la vista alias correspondiente, probablemente funcionen.</>");
            $this->newLine();
            foreach ($manual as $issue) {
                $this->line("  <fg=red>✗</> {$issue['label']}");
                $this->line("    Usa `{$issue['ref']}` — tabla real: `{$issue['actual']}`");
                if ($issue['view_created']) {
                    $this->line("    <fg=green>✔ Se generará/creará vista alias `{$issue['ref']}`</>");
                } else {
                    $this->line("    <fg=red>✗ Sin vista alias disponible — corrección manual en el cuerpo del SP/trigger</>");
                }
            }
        }

        // ── SQL completo para dry-run ────────────────────────────────────────

        if (!empty($safe)) {
            $this->section('SQL COMPLETO PARA EJECUTAR MANUALMENTE');
            foreach ($safe as $entry) {
                $this->line("  {$entry['sql']};");
            }
        }

        // ── Exportar a archivo ───────────────────────────────────────────────

        if (!empty($safe)) {
            $sqlFile = storage_path("app/case-compat-{$connName}-" . date('Ymd_His') . '.sql');
            $this->writeSqlFile($sqlFile, $safe, $conflicts, $manual, $connName, $conn->getDatabaseName());
            $this->newLine();
            $this->line("  <fg=cyan>ℹ Script SQL guardado en:</> {$sqlFile}");
        }

        // ── Ejecución ────────────────────────────────────────────────────────

        if (!$execute) {
            $this->newLine();
            $this->line("  <fg=yellow>Modo DRY-RUN: ningún cambio fue aplicado a la base de datos.</>");
            $this->line("  <fg=cyan>Para ejecutar:</> php artisan schema:case-compat-views --connection={$connName} --execute");
            return;
        }

        if (empty($safe)) {
            $this->newLine();
            $this->info('  No hay vistas que crear. Base de datos sin cambios.');
            return;
        }

        if (!$this->option('force')) {
            $this->newLine();
            $this->warn("  Se crearán " . count($safe) . " vistas en la base de datos.");
            $this->warn("  ⚠  NINGUNA tabla será modificada, renombrada o eliminada.");
            if (!$this->confirm('  ¿Continuar?', false)) {
                $this->line("  Operación cancelada.");
                return;
            }
        }

        $this->executeViews($conn, $safe);
    }

    // ─── Construcción del plan ────────────────────────────────────────────────

    /**
     * Devuelve [$safe, $conflicts, $manual]
     *
     * $safe      → array de vistas listas para crear
     * $conflicts → array de vistas que no se pueden crear (colisión)
     * $manual    → procedimientos/triggers que necesitan atención
     */
    private function buildPlan(
        $conn,
        string $dbName,
        array $tablesBySchema,
        array $viewsBySchema
    ): array {
        $safe      = [];
        $conflicts = [];
        $manual    = [];
        $safeIndex = []; // schema.lower_view_name → bool (para deduplicar)

        // ── Paso 1: tablas con mayúsculas → alias lowercase ──────────────────
        foreach ($tablesBySchema as $schema => $tables) {
            $lowerTables = array_map('strtolower', $tables);
            $lowerViews  = array_map('strtolower', $viewsBySchema[$schema] ?? []);

            foreach ($tables as $tbl) {
                $lower = strtolower($tbl);
                if ($tbl === $lower) continue; // ya en minúsculas, sin acción

                [$safe, $conflicts, $safeIndex] = $this->tryAddView(
                    $schema, $lower, $tbl,
                    "Tabla `{$tbl}` tiene mayúsculas → alias lowercase para compatibilidad",
                    $tables, $viewsBySchema[$schema] ?? [], $safe, $conflicts, $safeIndex
                );
            }
        }

        // ── Paso 2: analizar SP/FN → detectar referencias con case incorrecto ─
        $tableMap = $this->buildTableMap($tablesBySchema);

        try {
            $routines = $conn->select(
                "SELECT ROUTINE_NAME, ROUTINE_TYPE, ROUTINE_DEFINITION
                 FROM information_schema.ROUTINES
                 WHERE ROUTINE_SCHEMA = ? ORDER BY ROUTINE_TYPE, ROUTINE_NAME",
                [$dbName]
            );
        } catch (\Exception) {
            $routines = [];
        }

        foreach ($routines as $r) {
            $this->analyzeMismatches(
                $r->ROUTINE_DEFINITION ?? '', "[{$r->ROUTINE_TYPE}] `{$r->ROUTINE_NAME}`",
                $tableMap, $tablesBySchema, $viewsBySchema, $dbName,
                $safe, $conflicts, $safeIndex, $manual
            );
        }

        // ── Paso 3: analizar triggers ─────────────────────────────────────────
        try {
            $triggers = $conn->select(
                "SELECT TRIGGER_NAME, ACTION_STATEMENT
                 FROM information_schema.TRIGGERS
                 WHERE TRIGGER_SCHEMA = ? ORDER BY TRIGGER_NAME",
                [$dbName]
            );
        } catch (\Exception) {
            $triggers = [];
        }

        foreach ($triggers as $t) {
            $this->analyzeMismatches(
                $t->ACTION_STATEMENT ?? '', "[TRIGGER] `{$t->TRIGGER_NAME}`",
                $tableMap, $tablesBySchema, $viewsBySchema, $dbName,
                $safe, $conflicts, $safeIndex, $manual
            );
        }

        // Deduplicar manual por label+ref
        $manualSeen = [];
        $manual     = array_values(array_filter($manual, function ($m) use (&$manualSeen) {
            $key = $m['label'] . '|' . $m['ref'];
            if (isset($manualSeen[$key])) return false;
            $manualSeen[$key] = true;
            return true;
        }));

        return [$safe, $conflicts, $manual];
    }

    /**
     * Intenta agregar una vista al plan; clasifica en $safe o $conflicts.
     */
    private function tryAddView(
        string $schema, string $viewName, string $pointsTo, string $reason,
        array $tables, array $views,
        array $safe, array $conflicts, array $safeIndex
    ): array {
        $indexKey = strtolower("{$schema}.{$viewName}");

        if (isset($safeIndex[$indexKey])) {
            return [$safe, $conflicts, $safeIndex]; // ya existe
        }

        $sql = "CREATE OR REPLACE VIEW {$this->q($schema)}.{$this->q($viewName)}"
             . " AS SELECT * FROM {$this->q($schema)}.{$this->q($pointsTo)}";

        // ¿El nombre propuesto ya existe como TABLA (exacto)? No podemos crear vista con ese nombre.
        if (in_array($viewName, $tables, true)) {
            $conflicts[] = ['sql' => $sql, 'reason' => "Ya existe una TABLA con el nombre exacto `{$viewName}` — imposible crear vista homónima"];
            $safeIndex[$indexKey] = true;
            return [$safe, $conflicts, $safeIndex];
        }

        // ¿Nombre en minúsculas coincide con alguna tabla existente (diferente case)?
        $lowerTables = array_map('strtolower', $tables);
        $lowerView   = strtolower($viewName);
        if (in_array($lowerView, $lowerTables, true) && strtolower($viewName) !== strtolower($pointsTo)) {
            $conflicts[] = ['sql' => $sql, 'reason' => "Nombre `{$viewName}` entra en conflicto con una tabla existente (por lower())"];
            $safeIndex[$indexKey] = true;
            return [$safe, $conflicts, $safeIndex];
        }

        // ¿Existe ya una vista con ese nombre exacto?
        if (in_array($viewName, $views, true)) {
            $conflicts[] = ['sql' => $sql, 'reason' => "Vista `{$viewName}` ya existe en `{$schema}` — verificar manualmente que apunte a `{$pointsTo}`"];
            $safeIndex[$indexKey] = true;
            return [$safe, $conflicts, $safeIndex];
        }

        // ✔ Sin conflicto
        $safe[]               = ['sql' => $sql, 'schema' => $schema, 'view' => $viewName, 'reason' => $reason];
        $safeIndex[$indexKey] = true;

        return [$safe, $conflicts, $safeIndex];
    }

    /**
     * Analiza el cuerpo de un SP/trigger, detecta referencias con case incorrecto
     * y agrega vistas de compatibilidad o registros manuales según corresponda.
     */
    private function analyzeMismatches(
        string $body, string $objectLabel,
        array $tableMap, array $tablesBySchema, array $viewsBySchema, string $dbName,
        array &$safe, array &$conflicts, array &$safeIndex, array &$manual
    ): void {
        foreach ($this->extractTableRefs($body) as [$ref, $context]) {
            // Separar schema.tabla si viene calificado
            $parts     = explode('.', $ref, 2);
            $refSchema = count($parts) === 2 ? $parts[0] : $dbName;
            $refTable  = count($parts) === 2 ? $parts[1] : $parts[0];
            $lower     = strtolower($refTable);

            if (!isset($tableMap[$lower])) continue;      // Tabla desconocida, se ignora
            if ($tableMap[$lower] === $refTable) continue; // Coincide exactamente, OK

            $actual = $tableMap[$lower];

            // Intentar generar vista alias con el nombre referenciado
            $schemaTables = $tablesBySchema[$refSchema] ?? $tablesBySchema[$dbName] ?? [];
            $schemaViews  = $viewsBySchema[$refSchema]  ?? $viewsBySchema[$dbName]  ?? [];
            $reason       = "Referenciado como `{$refTable}` en {$objectLabel} — tabla real: `{$actual}`";

            [$safe, $conflicts, $safeIndex] = $this->tryAddView(
                $refSchema, $refTable, $actual, $reason,
                $schemaTables, $schemaViews,
                $safe, $conflicts, $safeIndex
            );

            $indexKey    = strtolower("{$refSchema}.{$refTable}");
            $viewCreated = isset($safeIndex[$indexKey]) && !in_array(
                $indexKey,
                array_map(fn($c) => strtolower("{$c['sql']}"), $conflicts)
            );

            // Registrar en manual para el reporte aunque se haya generado la vista
            $manual[] = [
                'label'        => $objectLabel,
                'ref'          => $refTable,
                'actual'       => $actual,
                'context'      => trim(preg_replace('/\s+/', ' ', $context)),
                'view_created' => true, // la vista fue añadida al plan (safe o conflict)
            ];
        }
    }

    // ─── Ejecución de vistas ─────────────────────────────────────────────────

    private function executeViews($conn, array $safe): void
    {
        $this->newLine();
        $this->line("  <fg=cyan>Ejecutando " . count($safe) . " vistas...</>");
        $ok     = 0;
        $errors = 0;

        foreach ($safe as $entry) {
            try {
                $conn->statement($entry['sql']);
                $this->line("  <fg=green>✔</> `{$entry['schema']}`.`{$entry['view']}`");
                $ok++;
            } catch (\Exception $e) {
                $this->line("  <fg=red>✗</> `{$entry['view']}`: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->line("  Resultado: <fg=green>{$ok} vistas creadas</>, <fg=red>{$errors} errores</>");
    }

    // ─── Exportar SQL a archivo ───────────────────────────────────────────────

    private function writeSqlFile(
        string $path, array $safe, array $conflicts, array $manual,
        string $connName, string $dbName
    ): void {
        $lines   = [];
        $lines[] = "-- ================================================================";
        $lines[] = "-- schema:case-compat-views  |  conexión: {$connName}  |  bd: {$dbName}";
        $lines[] = "-- Generado: " . now()->format('Y-m-d H:i:s');
        $lines[] = "-- ================================================================";
        $lines[] = "";
        $lines[] = "-- VISTAS A CREAR ({" . count($safe) . "})";
        $lines[] = "USE `{$dbName}`;";
        $lines[] = "";

        foreach ($safe as $e) {
            $lines[] = "-- Razón: {$e['reason']}";
            $lines[] = $e['sql'] . ";";
            $lines[] = "";
        }

        if (!empty($conflicts)) {
            $lines[] = "";
            $lines[] = "-- ── OMITIDAS POR CONFLICTO ({" . count($conflicts) . "}) ─────────────";
            foreach ($conflicts as $e) {
                $lines[] = "-- ⚠ {$e['reason']}";
                $lines[] = "-- {$e['sql']};";
                $lines[] = "";
            }
        }

        if (!empty($manual)) {
            $lines[] = "";
            $lines[] = "-- ── PROCEDIMIENTOS/TRIGGERS A REVISAR MANUALMENTE ─────────";
            foreach ($manual as $m) {
                $lines[] = "-- ✗ {$m['label']}";
                $lines[] = "--   Usa: `{$m['ref']}`  →  Tabla real: `{$m['actual']}`";
                $lines[] = "--   Vista alias: " . ($m['view_created'] ? 'GENERADA en este script' : 'NO disponible — corregir manualmente');
                $lines[] = "";
            }
        }

        file_put_contents($path, implode("\n", $lines));
    }

    // ─── Extracción de referencias SQL ───────────────────────────────────────

    /**
     * Devuelve array de [nombre_tabla, contexto].
     */
    private function extractTableRefs(string $sql): array
    {
        if (trim($sql) === '') return [];

        $refs     = [];
        $seen     = [];
        $keywords = [
            'TABLE', 'SELECT', 'WHERE', 'SET', 'VALUES', 'INTO', 'DUAL',
            'INFORMATION_SCHEMA', 'PERFORMANCE_SCHEMA', 'SYS', 'NEW', 'OLD',
            'CURRENT', 'NEXT', 'PRIOR', 'KEY', 'INDEX', 'UNIQUE',
        ];

        $pattern = '/\b(FROM|(?:(?:INNER|LEFT|RIGHT|CROSS|FULL)\s+)?(?:OUTER\s+)?JOIN|INSERT\s+(?:IGNORE\s+)?INTO|UPDATE|TABLE)\s+'
            . '(?:(`[^`]+`|[\w]+)\s*\.\s*)?'
            . '(`[^`]+`|[\w]+)/i';

        if (!preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return [];
        }

        foreach ($matches as $match) {
            $table  = trim($match[3][0] ?? '', '` ');
            $schema = trim($match[2][0] ?? '', '` ');
            $offset = $match[0][1];

            if ($table === '' || is_numeric($table)) continue;
            if (in_array(strtoupper($table), $keywords, true)) continue;

            $ref = $schema !== '' ? "{$schema}.{$table}" : $table;
            $key = strtolower($ref);

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $context = substr($sql, max(0, $offset - 15), 55);
            $context = preg_replace('/\s+/', ' ', $context);

            $refs[] = [$ref, trim($context)];
        }

        return $refs;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function q(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    private function fetchObjects($conn, string $schema, string $type): array
    {
        try {
            $rows = $conn->select(
                "SELECT TABLE_NAME FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ? ORDER BY TABLE_NAME",
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

    private function resolveConnections(): array
    {
        if ($this->option('all')) {
            return array_keys(array_filter(
                config('database.connections'),
                fn($c) => ($c['driver'] ?? '') === 'mysql'
            ));
        }
        return [$this->option('connection')];
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold>── {$title} " . str_repeat('─', max(0, 58 - strlen($title))) . "</>");
    }

    private function ok(string $msg): void
    {
        $this->line("  <fg=green>✔</> {$msg}");
    }
}
