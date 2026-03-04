<?php

namespace App\Console\Commands;

use App\Services\CoreDataBackupService;
use Illuminate\Console\Command;

class BackupCoreData extends Command
{
    protected $signature = 'backup:data-utama {--path= : Lokasi file backup (opsional)}';

    protected $description = 'Backup data admin, pelanggan, produk, dan transaksi inti ke file JSON';

    public function handle(CoreDataBackupService $backupService): int
    {
        $path = $backupService->createBackup($this->option('path'));

        $this->info('Backup berhasil dibuat.');
        $this->line('File: ' . $path);

        return self::SUCCESS;
    }
}
