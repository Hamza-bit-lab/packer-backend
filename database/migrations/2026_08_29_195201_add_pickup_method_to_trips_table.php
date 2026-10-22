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
        Schema::table('trips', function (Blueprint $table) {
            // The existing `delivery_method` covers the destination leg
            // (does the traveler deliver to the receiver, or does the
            // receiver pick up from the traveler). This covers the origin
            // leg: does the traveler pick up from the sender themselves,
            // or does the sender deliver it to the traveler.
            $table->string('pickup_method')->default('traveler_picks_up')->after('delivery_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('pickup_method');
        });
    }
};
