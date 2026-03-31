<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('total_quotes')->default(0)->after('siret');
            $table->unsignedInteger('total_invoices')->default(0)->after('total_quotes');
            $table->decimal('total_revenue', 12, 2)->default(0)->after('total_invoices');
            $table->timestamp('portal_token_created_at')->nullable()->after('portal_token');
        });

        Schema::create('client_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_voice')->default(false);
            $table->string('voice_url')->nullable();
            $table->text('transcription')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_notes');
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['total_quotes', 'total_invoices', 'total_revenue', 'portal_token_created_at']);
        });
    }
};
