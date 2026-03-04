<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CoreDataBackupService
{
    private const TABLES = [
        'admins',
        'users',
        'products',
        'orders',
        'order_items',
        'payments',
        'payment_confirmations',
        'invoices',
        'ratings',
    ];

    public function createBackup(?string $customPath = null): string
    {
        $path = $this->resolvePath($customPath, true);
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $payload = [
            'generated_at' => now()->toDateTimeString(),
            'tables' => [],
        ];

        foreach (self::TABLES as $table) {
            $payload['tables'][$table] = DB::table($table)
                ->orderBy('id')
                ->get()
                ->map(static fn ($row) => (array) $row)
                ->all();
        }

        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    public function restoreBackup(string $filePath): array
    {
        $path = $this->resolvePath($filePath, false);

        if (!File::exists($path)) {
            throw new \RuntimeException("File backup tidak ditemukan: {$path}");
        }

        $decoded = json_decode(File::get($path), true);

        if (!is_array($decoded) || !isset($decoded['tables']) || !is_array($decoded['tables'])) {
            throw new \RuntimeException('Format file backup tidak valid.');
        }

        $tableRows = $decoded['tables'];
        $restoredCounts = [];

        DB::transaction(function () use ($tableRows, &$restoredCounts) {
            Schema::disableForeignKeyConstraints();

            try {
                foreach (array_reverse(self::TABLES) as $table) {
                    DB::table($table)->delete();
                }

                foreach (self::TABLES as $table) {
                    $rows = $tableRows[$table] ?? [];

                    if (!is_array($rows) || empty($rows)) {
                        $restoredCounts[$table] = 0;
                        continue;
                    }

                    DB::table($table)->insert($rows);
                    $restoredCounts[$table] = count($rows);
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        return $restoredCounts;
    }

    private function resolvePath(?string $path, bool $generateDefault): string
    {
        if (!$path && $generateDefault) {
            return storage_path('app/backups/core-data-backup-' . now()->format('Ymd-His') . '.json');
        }

        $path = (string) $path;

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return (bool) preg_match('/^(?:[A-Za-z]:\\\\|\/|\\\\\\\\)/', $path);
    }
}
