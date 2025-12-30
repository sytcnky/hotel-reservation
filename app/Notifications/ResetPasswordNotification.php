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
            ->subject('Şifre sıfırlama talebi')
            ->view('emails.auth.reset-password', [
                'subject'    => 'Şifrenizi Sıfırlayın',
                'title'      => 'Şifre sıfırlama',
                'intro'      => 'Şifrenizi sıfırlamak için aşağıdaki linke tıklayın.',
                'actionUrl'  => $url,
                'actionText' => 'Şifremi sıfırla',
                'outro'      => 'Parola sıfırlama isteğinde bulunmadıysanız, bu e-postayı güvenle görmezden gelebilirsiniz. Hesabınızda herhangi bir değişiklik yapılmadı.',
            ]);
    }
}
