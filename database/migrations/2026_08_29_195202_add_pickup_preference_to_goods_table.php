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
        Schema::table('goods', function (Blueprint $table) {
            // The sender's stance on the origin-side handoff: will they
            // deliver the item to the traveler, or does the traveler need
            // to come collect it from them. Same values/meaning as a
            // Trip's `pickup_method`, from the other party's point of view.
            $table->string('pickup_preference')->default('sender_delivers')->after('photo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->dropColumn('pickup_preference');
        });
    }
};
