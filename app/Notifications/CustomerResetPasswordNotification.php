<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);
        $expiresInMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset Password Akun Durian Lovers')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun pelanggan Anda.')
            ->action('Reset Password', $resetUrl)
            ->line('Link ini berlaku selama '.$expiresInMinutes.' menit.')
            ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini. Password Anda tidak berubah.')
            ->salutation('Salam, Tim Durian Lovers');
    }

    protected function resetUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
