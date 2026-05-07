<?php

namespace App\Console\Commands;

use App\Support\Backup\DatabaseBackupService;
use Illuminate\Console\Command;

class TestRestoreDatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:test-restore
                            {file? : Caminho completo ou nome do backup (usa o mais recente quando omitido)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa teste de restauracao em ambiente temporario para validar o backup.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $file = $this->argument('file');
        if (! $file) {
            $file = $backupService->latestBackupFile();
            if (! $file) {
                $this->error('Nao ha backup disponivel para validar.');

                return self::FAILURE;
            }
        }

        try {
            $result = $backupService->testRestore((string) $file);
        } catch (\Throwable $exception) {
            $this->error('Falha no teste de restauracao: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Teste de restauracao executado.');
        $this->line('Driver: '.$result['driver']);
        $this->line('Arquivo: '.$result['backup_file']);
        $this->line('Tabelas encontradas: '.($result['tables_found'] ?? 0));
        $this->line('Status: '.($result['status'] ?? 'desconhecido'));

        return self::SUCCESS;
    }
}
