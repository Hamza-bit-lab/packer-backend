<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\IdVerificationStatus;
use Database\Factories\IdVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'document_type', 'document_photo_url', 'status', 'rejection_reason', 'reviewed_at'])]
class IdVerification extends Model
{
    /** @use HasFactory<IdVerificationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => IdVerificationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
