<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminAlert;
use Illuminate\Support\Facades\Notification;

class AdminAlerts
{
    /**
     * Notifies every admin, unless the admin has switched this alert type
     * off under Settings. Never throws into the caller — an alert failing
     * must not break the user action that triggered it.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function send(string $settingKey, string $title, string $body, array $extra = []): void
    {
        try {
            if (! Setting::enabled($settingKey)) {
                return;
            }

            $admins = User::query()->where('is_admin', true)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AdminAlert($title, $body, $extra));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
