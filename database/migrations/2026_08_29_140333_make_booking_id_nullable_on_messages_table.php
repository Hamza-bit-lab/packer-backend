<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Chat is now a direct user-to-user conversation rather than something
     * scoped to a booking, so a message no longer requires one. Recreates
     * the column instead of using ->change(), since doctrine/dbal isn't
     * installed in this project.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['booking_id', 'created_at']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['sender_id', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'receiver_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('booking_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['booking_id', 'created_at']);
        });
    }
};
