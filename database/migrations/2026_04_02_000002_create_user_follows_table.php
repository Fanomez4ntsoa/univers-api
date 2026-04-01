<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_id', 'following_id']);
        });

        // Add social stats columns to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('followers_count')->default(0)->after('has_pro_subscription');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
            $table->unsignedInteger('posts_count')->default(0)->after('following_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_follows');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['followers_count', 'following_count', 'posts_count']);
        });
    }
};
