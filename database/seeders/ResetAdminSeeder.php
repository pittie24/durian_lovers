<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\User;

class ResetAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Reset di tabel admins
        Admin::updateOrCreate(
            ['email' => 'admin@durianlovers.com'],
            [
                'name' => 'Admin Durian Lovers',
                'password' => Hash::make('Admin@2026'),
            ]
        );

        // Reset di tabel users (karena AdminAuthController pakai guard 'web')
        User::updateOrCreate(
            ['email' => 'admin@durianlovers.com'],
            [
                'name' => 'Admin Durian Lovers',
                'password' => Hash::make('Admin@2026'),
            ]
        );

        $this->command->info('✓ Admin berhasil direset!');
        $this->command->info('Email: admin@durianlovers.com');
        $this->command->info('Password: Admin@2026');
    }
}
