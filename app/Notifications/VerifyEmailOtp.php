<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtp extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your PrizeGuyana Email Address')
            ->greeting('Hello!')
            ->line('Thank you for registering with PrizeGuyana.')
            ->line('Use the verification code below to verify your email address:')
            ->line($this->otp)
            ->line('This code will expire in 5 minutes.')
            ->line('If you did not request this code, you can safely ignore this email.')
            ->salutation('The PrizeGuyana Team');
    }
}
