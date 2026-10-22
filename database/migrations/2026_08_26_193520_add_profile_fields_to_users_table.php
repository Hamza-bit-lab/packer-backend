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
            $table->string('phone_number')->nullable()->unique()->after('email');
            $table->string('profile_pic_url')->nullable()->after('phone_number');
            $table->decimal('average_rating', 3, 2)->default(0)->after('profile_pic_url');
            $table->unsignedInteger('total_reviews')->default(0)->after('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'profile_pic_url', 'average_rating', 'total_reviews']);
        });
    }
};
