<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferSqliteToMysql extends Command
{
    protected $signature = 'db:transfer-sqlite-to-mysql {--dry-run} {--skip-truncate}';
    protected $description = 'Transfer data from SQLite to MySQL safely with dry-run support';

    private $dryRun = false;
    private $skipTruncate = false;
    private $stats = [
        'tables_processed' => 0,
        'total_rows_transferred' => 0,
        'errors' => [],
    ];

    public function handle()
    {
        $this->dryRun = $this->option('dry-run');
        $this->skipTruncate = $this->option('skip-truncate');

        $this->info($this->dryRun ? '🔍 DRY-RUN MODE (no data will be modified)' : '⚙️  LIVE MODE (data will be transferred)');
        $this->line('');

        if (!$this->dryRun && !$this->skipTruncate && !$this->confirm('⚠️  This will TRUNCATE MySQL tables. Proceed?', false)) {
            $this->error('Transfer cancelled.');
            return 1;
        }

        try {
            // Get list of tables from SQLite
            $tables = $this->getTablesList();

            if (empty($tables)) {
                $this->error('No tables found in SQLite database');
                return 1;
            }

            $this->info("Found " . count($tables) . " tables to transfer:\n");
            foreach ($tables as $table) {
                $this->line("  • $table");
            }
            $this->line('');

            if (!$this->dryRun && !$this->confirm('Continue with transfer?', false)) {
                $this->error('Transfer cancelled.');
                return 1;
            }

            // Disable foreign key checks in MySQL
            if (!$this->dryRun) {
                DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // Process each table
            foreach ($tables as $table) {
                $this->transferTable($table);
            }

            // Re-enable foreign key checks
            if (!$this->dryRun) {
                DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            $this->line('');
            $this->info('═══════════════════════════════════════');
            $this->info('📊 TRANSFER SUMMARY');
            $this->info('═══════════════════════════════════════');
            $this->line('Tables processed: ' . $this->stats['tables_processed']);
            $this->line('Total rows transferred: ' . $this->stats['total_rows_transferred']);

            if (!empty($this->stats['errors'])) {
                $this->error('Errors encountered:');
                foreach ($this->stats['errors'] as $error) {
                    $this->error("  ❌ $error");
                }
                return 1;
            }

            if ($this->dryRun) {
                $this->warn('(DRY-RUN: no changes made)');
            } else {
                $this->info('✅ Transfer completed successfully!');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Transfer failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function getTablesList()
    {
        $sqlite = DB::connection('sqlite_backup');
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name;";
        $result = $sqlite->select($query);

        return array_map(function ($item) {
            return $item->name ?? $item['name'];
        }, $result);
    }

    private function transferTable($tableName)
    {
        $bar = $this->output->createProgressBar(1);
        $bar->setFormat('%current%/%max% [%bar%] %message%');

        try {
            $sqlite = DB::connection('sqlite_backup');
            $mysql = DB::connection('mysql');

            // Count rows in SQLite
            $rowCount = $sqlite->table($tableName)->count();

            if ($rowCount === 0) {
                $this->line("⏭️  <fg=gray>$tableName</> (0 rows, skipped)");
                return;
            }

            // Truncate MySQL table if not dry-run
            if (!$this->dryRun && !$this->skipTruncate) {
                $bar->setMessage("Preparing $tableName...");
                $bar->advance();

                $mysql->statement("TRUNCATE TABLE `$tableName`;");
            }

            // Chunk transfer data
            $transferred = 0;
            $chunkSize = 500;

            $bar->setMessage("Transferring $tableName ($rowCount rows)...");
            $bar->setMaxSteps($rowCount);
            $bar->start();

            // Get column names for this table from SQLite
            $columnNames = collect($sqlite->select("PRAGMA table_info($tableName)"))
                ->pluck('name')
                ->all();

            $sqlite->table($tableName)
                ->orderByRaw('ROWID')
                ->chunk($chunkSize, function ($rows) use ($tableName, $mysql, $columnNames, &$transferred, $bar) {
                    if (!$this->dryRun && count($rows) > 0) {
                        // Convert stdClass objects to keyed arrays
                        $insertData = $rows->map(function ($row) use ($columnNames) {
                            $data = [];
                            foreach ($columnNames as $column) {
                                $data[$column] = $row->$column ?? null;
                            }
                            return $data;
                        })->toArray();

                        if (count($insertData) > 0) {
                            $mysql->table($tableName)->insert($insertData);
                        }
                    }

                    $transferred += count($rows);
                    $bar->setProgress($transferred);
                });

            $bar->finish();

            $this->line(" ✅ <fg=green>$tableName</> transferred: <fg=cyan>$rowCount</> rows");
            $this->stats['tables_processed']++;
            $this->stats['total_rows_transferred'] += $rowCount;

        } catch (\Exception $e) {
            $this->line(" ❌ Error on $tableName: " . $e->getMessage());
            $this->stats['errors'][] = "$tableName: " . $e->getMessage();
        }
    }
}
