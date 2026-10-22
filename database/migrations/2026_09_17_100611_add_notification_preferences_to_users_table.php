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
        Schema::table('users', function (Blueprint $table) {
            // Each gates the in-app + push legs of a category of
            // notification (see App\Notifications\PreferenceGatedNotification)
            // — on by default so existing users keep today's behavior.
            $table->boolean('notify_bookings')->default(true)->after('completed_deliveries_count');
            $table->boolean('notify_messages')->default(true)->after('notify_bookings');
            $table->boolean('notify_route_alerts')->default(true)->after('notify_messages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_bookings', 'notify_messages', 'notify_route_alerts']);
        });
    }
};
