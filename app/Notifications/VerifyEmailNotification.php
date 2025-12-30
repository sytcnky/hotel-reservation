<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('E-posta adresinizi doğrulayın')
            ->view('emails.auth.verify-email', [
                'subject'    => 'E-posta adresinizi doğrulayın',
                'title'      => 'E-posta doğrulama',
                'intro'      => 'Kaydınızı tamamlamak için aşağıdaki düğmeye tıkla',
                'actionUrl'  => $verifyUrl,
                'actionText' => 'E-postamı doğrula',
                'outro'      => 'Eğer bu işlemi siz başlatmadıysanız bu e-postayı yok sayabilirsiniz.',
            ]);
    }

    protected function verificationUrl($notifiable): string
    {
        $expiration = Config::get('auth.verification.expire', 60);

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expiration),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
