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
            $table->dropConstrainedForeignId('referred_by_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_referral_code_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_code', 'referral_credits']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 8)->nullable()->unique()->after('country');
            $table->foreignId('referred_by_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('referral_credits')->default(0)->after('referred_by_id');
        });
    }
};
