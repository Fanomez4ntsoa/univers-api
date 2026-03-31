<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('chantier_type')->nullable(); // renovation, construction, etc.
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('source')->nullable(); // bouche_a_oreille, site_web, reseau_social, etc.
            $table->string('status')->default('new'); // new|contacted|qualified|converted|lost
            $table->string('pipeline_stage')->default('prospect'); // prospect|devis|negociation|signe|perdu
            $table->integer('signature_score')->default(0); // 0-100
            $table->text('notes')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
