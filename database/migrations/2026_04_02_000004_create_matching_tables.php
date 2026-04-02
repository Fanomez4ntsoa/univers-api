<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category'); // plomberie, electricite, maconnerie, etc.
            $table->string('city');
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('urgency')->default('normal'); // normal|urgent|tres_urgent
            $table->string('status')->default('open'); // open|in_review|matched|closed|cancelled
            $table->json('images')->nullable();
            $table->date('desired_start_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['category', 'city']);
        });

        Schema::create('project_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_request_id')->constrained('project_requests')->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('delay_days')->nullable();
            $table->string('status')->default('pending'); // pending|accepted|refused
            $table->timestamps();

            $table->unique(['project_request_id', 'artisan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_quotes');
        Schema::dropIfExists('project_requests');
    }
};
