<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Update chantiers table ---
        Schema::table('chantiers', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('client_id');
            $table->string('quote_number')->nullable()->after('quote_id');
            $table->string('city')->nullable()->after('address');
            $table->string('postal_code')->nullable()->after('city');
            $table->text('work_description')->nullable()->after('chantier_type');
            $table->string('assigned_team')->nullable()->after('assigned_workers');
            $table->decimal('estimated_cost', 12, 2)->default(0)->after('quote_amount');
            $table->decimal('total_hours', 8, 2)->default(0)->after('estimated_cost');
            $table->string('rentability_level')->default('medium')->after('rentability'); // high|medium|low
        });

        // Change geofence_radius default to 200 (Emergent default)
        Schema::table('chantiers', function (Blueprint $table) {
            $table->integer('geofence_radius')->default(200)->change();
        });

        // Change pipeline_stage default to match status
        Schema::table('chantiers', function (Blueprint $table) {
            $table->string('pipeline_stage')->default('to_plan')->change();
        });

        // --- Chantier documents ---
        Schema::create('chantier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_url');
            $table->string('file_type')->default('document'); // photo|plan|document|invoice
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // --- Chantier comments ---
        Schema::create('chantier_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        // --- Chantier time entries ---
        Schema::create('chantier_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('worker_name');
            $table->decimal('hours', 6, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // --- Chantier costs ---
        Schema::create('chantier_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('chantiers')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->string('category')->default('materials'); // materials|tools|transport|other
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_costs');
        Schema::dropIfExists('chantier_time_entries');
        Schema::dropIfExists('chantier_comments');
        Schema::dropIfExists('chantier_documents');

        Schema::table('chantiers', function (Blueprint $table) {
            $table->dropColumn([
                'client_name',
                'quote_number',
                'city',
                'postal_code',
                'work_description',
                'assigned_team',
                'estimated_cost',
                'total_hours',
                'rentability_level',
            ]);
        });
    }
};
