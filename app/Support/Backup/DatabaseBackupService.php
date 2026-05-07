<?php

namespace App\Support\Backup;

use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

class DatabaseBackupService
{
    public function createBackup(): array
    {
        $driver = (string) config('database.default');
        $directory = $this->backupDirectory();

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Nao foi possivel criar diretorio de backup.');
        }

        $timestamp = now()->format('Ymd_His');

        if ($driver === 'sqlite') {
            return $this->backupSqlite($directory, $timestamp);
        }

        if ($driver === 'mysql') {
            return $this->backupMysql($directory, $timestamp);
        }

        throw new RuntimeException("Driver {$driver} nao suportado para backup automatico.");
    }

    public function restoreBackup(string $filePath, bool $force = false): array
    {
        if (! $force) {
            throw new RuntimeException('Use --force para confirmar a restauracao do banco.');
        }

        $driver = (string) config('database.default');
        $resolvedPath = $this->resolveBackupPath($filePath);
        if (! file_exists($resolvedPath)) {
            throw new RuntimeException('Arquivo de backup nao encontrado.');
        }

        if ($driver === 'sqlite') {
            return $this->restoreSqlite($resolvedPath);
        }

        if ($driver === 'mysql') {
            return $this->restoreMysql($resolvedPath, null);
        }

        throw new RuntimeException("Driver {$driver} nao suportado para restauracao automatica.");
    }

    public function testRestore(string $filePath): array
    {
        $driver = (string) config('database.default');
        $resolvedPath = $this->resolveBackupPath($filePath);
        if (! file_exists($resolvedPath)) {
            throw new RuntimeException('Arquivo de backup nao encontrado para teste.');
        }

        if ($driver === 'sqlite') {
            return $this->testRestoreSqlite($resolvedPath);
        }

        if ($driver === 'mysql') {
            return $this->testRestoreMysql($resolvedPath);
        }

        throw new RuntimeException("Driver {$driver} nao suportado para teste de restauracao.");
    }

    public function latestBackupFile(): ?string
    {
        $dir = $this->backupDirectory();
        if (! is_dir($dir)) {
            return null;
        }

        $files = glob($dir.DIRECTORY_SEPARATOR.'*');
        if (! is_array($files) || empty($files)) {
            return null;
        }

        $files = array_values(array_filter($files, function ($file) {
            return is_file($file) && (str_ends_with($file, '.sql') || str_ends_with($file, '.sqlite'));
        }));

        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        return $files[0];
    }

    private function backupSqlite(string $directory, string $timestamp): array
    {
        $database = (string) config('database.connections.sqlite.database');
        if ($database === '' || ! file_exists($database)) {
            throw new RuntimeException('Arquivo SQLite nao encontrado para backup.');
        }

        $destino = $directory.DIRECTORY_SEPARATOR."backup_sqlite_{$timestamp}.sqlite";
        if (! copy($database, $destino)) {
            throw new RuntimeException('Falha ao copiar arquivo SQLite para backup.');
        }

        $meta = [
            'driver' => 'sqlite',
            'database' => $database,
            'created_at' => now()->toIso8601String(),
        ];
        file_put_contents($destino.'.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->cleanupOldBackups($directory);

        return [
            'driver' => 'sqlite',
            'file' => $destino,
            'size_bytes' => filesize($destino) ?: 0,
        ];
    }

    private function backupMysql(string $directory, string $timestamp): array
    {
        $file = $directory.DIRECTORY_SEPARATOR."backup_mysql_{$timestamp}.sql";
        $connection = DB::connection('mysql');
        $pdo = $connection->getPdo();
        $database = (string) config('database.connections.mysql.database');

        $tabelas = [];
        foreach ($connection->select('SHOW TABLES') as $row) {
            $tabelas[] = (string) array_values((array) $row)[0];
        }

        $handle = fopen($file, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Nao foi possivel abrir arquivo de backup para escrita.');
        }

        fwrite($handle, "-- Backup automatico {$database} em ".now()->toDateTimeString().PHP_EOL);
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;".PHP_EOL.PHP_EOL);

        foreach ($tabelas as $tabela) {
            $createRow = $connection->selectOne("SHOW CREATE TABLE `{$tabela}`");
            $createTable = (array) $createRow;
            $createSql = '';
            foreach ($createTable as $chave => $valor) {
                if (str_contains((string) $chave, 'Create Table')) {
                    $createSql = (string) $valor;
                    break;
                }
            }

            if ($createSql === '') {
                continue;
            }

            fwrite($handle, "DROP TABLE IF EXISTS `{$tabela}`;".PHP_EOL);
            fwrite($handle, $createSql.';'.PHP_EOL.PHP_EOL);

            $stmt = $pdo->query("SELECT * FROM `{$tabela}`");
            if (! $stmt) {
                continue;
            }

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $colunas = array_map(function ($coluna) {
                    return "`{$coluna}`";
                }, array_keys($row));

                $valores = array_map(function ($valor) use ($pdo) {
                    if ($valor === null) {
                        return 'NULL';
                    }

                    return $pdo->quote((string) $valor);
                }, array_values($row));

                $insert = sprintf(
                    "INSERT INTO `%s` (%s) VALUES (%s);",
                    $tabela,
                    implode(', ', $colunas),
                    implode(', ', $valores)
                );
                fwrite($handle, $insert.PHP_EOL);
            }

            fwrite($handle, PHP_EOL);
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;".PHP_EOL);
        fclose($handle);

        $meta = [
            'driver' => 'mysql',
            'database' => $database,
            'created_at' => now()->toIso8601String(),
            'tables' => $tabelas,
        ];
        file_put_contents($file.'.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->cleanupOldBackups($directory);

        return [
            'driver' => 'mysql',
            'file' => $file,
            'size_bytes' => filesize($file) ?: 0,
            'tables' => count($tabelas),
        ];
    }

    private function restoreSqlite(string $backupFile): array
    {
        $database = (string) config('database.connections.sqlite.database');
        if ($database === '') {
            throw new RuntimeException('Configuracao do arquivo SQLite invalida.');
        }

        if (! copy($backupFile, $database)) {
            throw new RuntimeException('Falha ao restaurar backup SQLite.');
        }

        return [
            'driver' => 'sqlite',
            'restored_from' => $backupFile,
        ];
    }

    private function restoreMysql(string $backupFile, ?string $databaseOverride): array
    {
        $sql = file_get_contents($backupFile);
        if ($sql === false) {
            throw new RuntimeException('Nao foi possivel ler arquivo SQL de backup.');
        }

        $config = (array) config('database.connections.mysql');
        $database = $databaseOverride ?: (string) ($config['database'] ?? '');
        if ($database === '') {
            throw new RuntimeException('Banco MySQL nao configurado para restauracao.');
        }

        $pdo = $this->buildMysqlPdo($config, $database);
        $statements = $this->splitSqlStatements($sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            $pdo->exec($statement);
        }

        return [
            'driver' => 'mysql',
            'restored_from' => $backupFile,
            'database' => $database,
            'statements' => count($statements),
        ];
    }

    private function testRestoreSqlite(string $backupFile): array
    {
        $tempFile = storage_path('app/backups/tmp_restore_test_'.uniqid().'.sqlite');
        if (! copy($backupFile, $tempFile)) {
            throw new RuntimeException('Falha ao preparar backup SQLite para teste de restauracao.');
        }

        $pdo = new PDO('sqlite:'.$tempFile);
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $tables = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
        $totalTables = (int) ($tables['total'] ?? 0);
        @unlink($tempFile);

        return [
            'driver' => 'sqlite',
            'backup_file' => $backupFile,
            'tables_found' => $totalTables,
            'status' => $totalTables > 0 ? 'ok' : 'warning',
        ];
    }

    private function testRestoreMysql(string $backupFile): array
    {
        $config = (array) config('database.connections.mysql');
        $baseDatabase = (string) ($config['database'] ?? '');
        if ($baseDatabase === '') {
            throw new RuntimeException('Banco MySQL nao configurado para teste de restauracao.');
        }

        $tempDatabase = $baseDatabase.'_restore_test_'.now()->format('YmdHis');
        $serverPdo = $this->buildMysqlPdo($config, null);
        $serverPdo->exec("CREATE DATABASE `{$tempDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        try {
            $restoreInfo = $this->restoreMysql($backupFile, $tempDatabase);
            $tempPdo = $this->buildMysqlPdo($config, $tempDatabase);
            $row = $tempPdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_ASSOC);
            $totalTables = count($row);
        } finally {
            $serverPdo->exec("DROP DATABASE IF EXISTS `{$tempDatabase}`");
        }

        return [
            'driver' => 'mysql',
            'backup_file' => $backupFile,
            'test_database' => $tempDatabase,
            'tables_found' => $totalTables,
            'restore_statements' => $restoreInfo['statements'] ?? 0,
            'status' => $totalTables > 0 ? 'ok' : 'warning',
        ];
    }

    private function splitSqlStatements(string $sql): array
    {
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql) ?? $sql;
        $lines = preg_split('/\R/', $sql) ?: [];
        $filtered = [];
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }
            $filtered[] = $line;
        }
        $sql = implode(PHP_EOL, $filtered);

        $statements = [];
        $current = '';
        $inSingle = false;
        $inDouble = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($char === "'" && ! $inDouble && $prev !== '\\') {
                $inSingle = ! $inSingle;
            } elseif ($char === '"' && ! $inSingle && $prev !== '\\') {
                $inDouble = ! $inDouble;
            }

            if ($char === ';' && ! $inSingle && ! $inDouble) {
                $statements[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $current = trim($current);
        if ($current !== '') {
            $statements[] = $current;
        }

        return array_values(array_filter($statements, function ($stmt) {
            return trim($stmt) !== '';
        }));
    }

    private function buildMysqlPdo(array $config, ?string $database): PDO
    {
        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '3306');
        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $unixSocket = (string) ($config['unix_socket'] ?? '');

        if ($unixSocket !== '') {
            $dsn = "mysql:unix_socket={$unixSocket};charset={$charset}";
        } else {
            $dsn = "mysql:host={$host};port={$port};charset={$charset}";
        }

        if ($database) {
            $dsn .= ";dbname={$database}";
        }

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private function backupDirectory(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.trim((string) config('backup.directory', 'backups/database'), '/\\'));
    }

    private function resolveBackupPath(string $filePath): string
    {
        if (str_starts_with($filePath, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $filePath) === 1) {
            return $filePath;
        }

        return $this->backupDirectory().DIRECTORY_SEPARATOR.$filePath;
    }

    private function cleanupOldBackups(string $directory): void
    {
        $retentionDays = max(1, (int) config('backup.retention_days', 7));
        $limite = now()->subDays($retentionDays)->getTimestamp();

        $arquivos = glob($directory.DIRECTORY_SEPARATOR.'*');
        if (! is_array($arquivos)) {
            return;
        }

        foreach ($arquivos as $arquivo) {
            if (! is_file($arquivo)) {
                continue;
            }

            if (filemtime($arquivo) < $limite) {
                @unlink($arquivo);
            }
        }
    }
}
