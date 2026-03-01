<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAdminData extends Command
{
    protected $signature = 'admin:check';
    protected $description = 'Check admin data in both tables';

    public function handle()
    {
        $this->info('=== Tabel ADMINS ===');
        $admins = DB::table('admins')->get();
        if ($admins->isEmpty()) {
            $this->warn('Tabel admins kosong!');
        } else {
            foreach ($admins as $a) {
                $this->line("ID: {$a->id} | Email: {$a->email} | Name: {$a->name}");
            }
        }

        $this->info('=== Tabel USERS (admin) ===');
        $users = DB::table('users')->where('email', 'admin@durianlovers.com')->get();
        if ($users->isEmpty()) {
            $this->warn('Tidak ada admin di tabel users!');
        } else {
            foreach ($users as $u) {
                $this->line("ID: {$u->id} | Email: {$u->email} | Name: {$u->name}");
            }
        }

        return 0;
    }
}
