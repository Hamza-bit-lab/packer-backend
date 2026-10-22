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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('from_city');
            $table->string('to_city');
            $table->dateTime('departure_date_time');
            $table->string('airline_name');
            $table->decimal('max_weight_kg', 6, 2);
            $table->decimal('rate_per_kg', 8, 2);
            $table->string('currency_type', 3);
            $table->timestamp('terms_accepted_at');
            $table->timestamps();

            $table->index(['from_city', 'to_city', 'departure_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
