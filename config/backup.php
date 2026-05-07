<?php

return [
    'directory' => env('BACKUP_DIRECTORY', 'backups/database'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 7),
];
