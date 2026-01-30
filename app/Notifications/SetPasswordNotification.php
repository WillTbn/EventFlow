<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class SetPasswordNotification extends ResetPassword
{
    public function __construct(string $token, private ?Tenant $tenant = null)
    {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $broker = config('auth.defaults.passwords');
        $expire = config("auth.passwords.{$broker}.expire");

        return (new MailMessage)
            ->subject('Crie sua senha')
            ->markdown('emails.auth.set-password', [
                'url' => $url,
                'user' => $notifiable,
                'expire' => $expire,
                'tenant' => $this->tenant,
            ]);
    }
}
