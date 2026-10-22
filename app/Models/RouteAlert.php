<?php

namespace App\Models;

use App\Enums\RouteAlertType;
use Database\Factories\RouteAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'watch_type', 'from_city', 'to_city'])]
class RouteAlert extends Model
{
    /** @use HasFactory<RouteAlertFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'watch_type' => RouteAlertType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
