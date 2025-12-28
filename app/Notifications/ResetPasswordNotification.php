<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Şifre sıfırlama')
            ->view('emails.auth.reset-password', [
                'subject'    => 'Şifre sıfırlama',
                'title'      => 'Şifre sıfırlama',
                'intro'      => 'Şifrenizi sıfırlamak için aşağıdaki linke tıklayın.',
                'actionUrl'  => $url,
                'actionText' => 'Şifremi sıfırla',
                'outro'      => 'Eğer bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz.',
            ]);
    }
}
