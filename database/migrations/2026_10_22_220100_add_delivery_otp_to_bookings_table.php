<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // The code emailed to the receiver (good.receiver_email) to
            // confirm a handoff really happened — hashed like a password,
            // never stored or returned in plain text. Cleared once used.
            $table->string('delivery_otp_hash')->nullable()->after('delivery_proof_photo_url');
            $table->timestamp('delivery_otp_created_at')->nullable()->after('delivery_otp_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['delivery_otp_hash', 'delivery_otp_created_at']);
        });
    }
};
