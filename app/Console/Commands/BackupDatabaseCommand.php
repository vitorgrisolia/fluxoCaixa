<?php

namespace App\Console\Commands;

use App\Support\Backup\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera backup automatico do banco de dados no storage.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        try {
            $result = $backupService->createBackup();
        } catch (\Throwable $exception) {
            $this->error('Falha ao gerar backup: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Backup gerado com sucesso.');
        $this->line('Driver: '.$result['driver']);
        $this->line('Arquivo: '.$result['file']);
        $this->line('Tamanho (bytes): '.($result['size_bytes'] ?? 0));

        return self::SUCCESS;
    }
}
