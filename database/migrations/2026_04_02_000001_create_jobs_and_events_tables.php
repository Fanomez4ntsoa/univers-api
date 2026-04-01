<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Jobs ---
        Schema::create('jobs_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('company_name')->nullable();
            $table->string('city');
            $table->string('contract_type')->default('cdi'); // cdi|cdd|interim|freelance|apprentissage
            $table->string('category')->nullable(); // metier
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('experience_level')->default('junior'); // junior|intermediaire|senior
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('applications_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'expires_at']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs_offers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending|viewed|accepted|refused
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
        });

        // --- Events ---
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('event_type')->default('autre'); // formation|salon|conference|reunion|autre
            $table->string('city');
            $table->string('address')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->unsignedInteger('max_attendees')->nullable();
            $table->unsignedInteger('attendees_count')->default(0);
            $table->boolean('is_free')->default(true);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image_url')->nullable();
            $table->string('status')->default('upcoming'); // upcoming|ongoing|completed|cancelled
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'start_date']);
        });

        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
        Schema::dropIfExists('events');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('jobs_offers');
    }
};
