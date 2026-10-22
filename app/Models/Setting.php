<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Small key/value store for admin-tunable behaviour (edited from the admin
 * dashboard's Settings page) — anything not saved yet falls back to
 * [self::DEFAULTS], so a fresh install behaves sensibly with no rows at all.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /**
     * Every setting the admin Settings page manages, with its default.
     *
     * @var array<string, bool>
     */
    public const DEFAULTS = [
        'admin_alert_new_report' => true,
        'admin_alert_new_id_verification' => true,
        'admin_alert_new_feature_request' => true,
        'admin_alert_email' => true,
    ];

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public static function enabled(string $key): bool
    {
        $value = static::query()->whereKey($key)->value('value');

        return $value === null ? (self::DEFAULTS[$key] ?? false) : $value === '1';
    }

    public static function put(string $key, bool $enabled): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $enabled ? '1' : '0']);
    }
}
