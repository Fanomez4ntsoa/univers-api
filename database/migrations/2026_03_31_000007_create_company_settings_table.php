<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('siret')->nullable();
            $table->string('tva_number')->nullable();
            $table->text('cgv_text')->nullable();
            $table->string('payment_terms')->nullable(); // "30 jours", "comptant", etc.
            $table->json('bank_details')->nullable(); // {iban, bic, bank_name}
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('quote_counter')->default(1);
            $table->integer('invoice_counter')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
