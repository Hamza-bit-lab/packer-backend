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
        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('from_city');
            $table->string('to_city');
            $table->string('item_description');
            $table->decimal('weight_kg', 6, 2);
            $table->decimal('offered_reward', 8, 2);
            $table->string('currency_type', 3);
            $table->timestamp('terms_accepted_at');
            $table->timestamps();

            $table->index(['from_city', 'to_city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods');
    }
};
