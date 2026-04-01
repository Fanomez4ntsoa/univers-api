<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('price_type')->default('fixed'); // fixed|negotiable|free
            $table->string('category'); // materiaux|outils|equipements|surplus_chantier|occasion
            $table->string('condition')->default('bon_etat'); // new|used|refurbished
            $table->string('city');
            $table->json('images')->nullable(); // [url, ...]
            $table->string('status')->default('active'); // active|sold|expired|cancelled
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('contact_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expires_at']);
            $table->index(['category']);
            $table->index(['city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
