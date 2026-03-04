<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckMailConfig extends Command
{
    protected $signature = 'app:check-mail-config';

    protected $description = 'Memeriksa apakah konfigurasi mail siap dipakai untuk mengirim email reset password.';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $host = (string) config("mail.mailers.$mailer.host");
        $port = (string) config("mail.mailers.$mailer.port");
        $encryption = (string) config("mail.mailers.$mailer.encryption");
        $username = config("mail.mailers.$mailer.username");
        $password = config("mail.mailers.$mailer.password");
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name');

        $this->line('Mailer aktif: '.$mailer);
        $this->line('Host: '.($host !== '' ? $host : '-'));
        $this->line('Port: '.($port !== '' ? $port : '-'));
        $this->line('Encryption: '.($encryption !== '' ? $encryption : '-'));
        $this->line('From: '.$fromName.' <'.$fromAddress.'>');

        if ($mailer !== 'smtp') {
            $this->warn('Mailer saat ini bukan SMTP. Pastikan ini memang sesuai kebutuhan Anda.');
            return self::SUCCESS;
        }

        $issues = [];

        if ($host === '' || $host === 'mailpit') {
            $issues[] = 'MAIL_HOST masih lokal/default (mailpit) atau belum diisi.';
        }

        if (empty($port)) {
            $issues[] = 'MAIL_PORT belum diisi.';
        }

        if (empty($username) || $username === 'null') {
            $issues[] = 'MAIL_USERNAME belum diisi.';
        }

        if (empty($password) || $password === 'null') {
            $issues[] = 'MAIL_PASSWORD belum diisi.';
        }

        if ($encryption === '' || $encryption === 'null') {
            $issues[] = 'MAIL_ENCRYPTION belum diisi (umumnya tls atau ssl).';
        }

        if ($fromAddress === '' || str_contains($fromAddress, 'example.com') || str_contains($fromAddress, '.local')) {
            $issues[] = 'MAIL_FROM_ADDRESS masih placeholder dan belum cocok untuk pengiriman email nyata.';
        }

        if ($issues === []) {
            $this->info('Konfigurasi mail terlihat siap untuk uji kirim email.');
            return self::SUCCESS;
        }

        $this->warn('Konfigurasi mail belum siap untuk pengiriman email nyata:');

        foreach ($issues as $issue) {
            $this->line('- '.$issue);
        }

        $this->line('Isi nilai SMTP asli di file .env, lalu jalankan ulang perintah ini.');

        return self::SUCCESS;
    }
}
