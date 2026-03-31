<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('core_uuid')->unique(); // UUID from Core /me
            $table->string('email')->unique();
            $table->string('username')->nullable();
            $table->string('display_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('user_type')->default('particulier'); // particulier|professionnel
            $table->string('role')->default('social_user'); // role from Core
            $table->string('city')->nullable();
            $table->string('metier')->nullable();
            $table->string('company_name')->nullable();
            $table->string('siret')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_pro_subscription')->default(false);
            $table->timestamp('last_synced_at')->nullable(); // last /me sync
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
