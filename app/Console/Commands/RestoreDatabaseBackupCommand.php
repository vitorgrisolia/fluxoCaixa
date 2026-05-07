<?php

namespace App\Console\Commands;

use App\Support\Backup\DatabaseBackupService;
use Illuminate\Console\Command;

class RestoreDatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore
                            {file : Caminho completo ou nome do arquivo de backup}
                            {--force : Confirma a restauracao do banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaura o banco de dados a partir de um arquivo de backup.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $file = (string) $this->argument('file');
        $force = (bool) $this->option('force');

        try {
            $result = $backupService->restoreBackup($file, $force);
        } catch (\Throwable $exception) {
            $this->error('Falha na restauracao: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Restauracao concluida com sucesso.');
        $this->line('Driver: '.$result['driver']);
        $this->line('Arquivo: '.$result['restored_from']);
        if (isset($result['database'])) {
            $this->line('Database: '.$result['database']);
        }

        return self::SUCCESS;
    }
}
