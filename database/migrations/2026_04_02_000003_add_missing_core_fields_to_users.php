<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('display_name');
            $table->string('identity_status')->nullable()->after('is_verified');
            $table->boolean('shop_enabled')->default(false)->after('has_pro_subscription');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'identity_status', 'shop_enabled']);
        });
    }
};
