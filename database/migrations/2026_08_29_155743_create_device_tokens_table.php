<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Unique on the token alone (not per-user) — an FCM token belongs
            // to one device installation at a time. Registering it again for
            // a different user (e.g. logging out and into another account on
            // the same phone) must move it, not duplicate it, or the
            // previous owner would keep receiving that device's pushes.
            $table->string('token')->unique();
            $table->string('platform');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
