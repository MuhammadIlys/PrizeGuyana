<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PrizeGuyanaNotification;
use Illuminate\Support\Collection;

class NotificationService
{
    public function send(
        User $user,
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'general'
    ): void {
        $user->notify(
            new PrizeGuyanaNotification($title, $message, $url, $type)
        );
    }

    public function notifyAdmins(
        string $title,
        string $message,
        ?string $url = null,
        string $type = 'general'
    ): void {
        User::where('role', 'admin')->each(function ($admin) use (
            $title,
            $message,
            $url,
            $type
        ) {
            $this->send($admin, $title, $message, $url, $type);
        });
    }
}
