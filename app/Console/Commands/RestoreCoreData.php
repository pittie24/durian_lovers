<?php

namespace App\Console\Commands;

use App\Services\CoreDataBackupService;
use Illuminate\Console\Command;

class RestoreCoreData extends Command
{
    protected $signature = 'restore:data-utama {file : Path file backup JSON}';

    protected $description = 'Restore data admin, pelanggan, produk, dan transaksi inti dari file JSON';

    public function handle(CoreDataBackupService $backupService): int
    {
        try {
            $restoredCounts = $backupService->restoreBackup($this->argument('file'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Restore berhasil dijalankan.');

        foreach ($restoredCounts as $table => $count) {
            $this->line("{$table}: {$count} data");
        }

        return self::SUCCESS;
    }
}
