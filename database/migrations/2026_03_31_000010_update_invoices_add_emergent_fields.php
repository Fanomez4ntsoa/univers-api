<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Payment tracking
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total');
            $table->decimal('amount_due', 12, 2)->default(0)->after('amount_paid');

            // Payment terms
            $table->string('payment_terms')->default('Paiement à réception')->after('amount_due');

            // Notes
            $table->text('notes')->nullable()->after('payment_terms');

            // Client snapshot (denormalized from client at creation)
            $table->string('client_name')->nullable()->after('client_id');
            $table->string('client_email')->nullable()->after('client_name');
            $table->string('client_address')->nullable()->after('client_email');
            $table->string('client_siret')->nullable()->after('client_address');
            $table->string('client_tva_number')->nullable()->after('client_siret');

            // Tracking dates
            $table->timestamp('sent_at')->nullable()->after('payment_date');
            $table->timestamp('paid_at')->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid',
                'amount_due',
                'payment_terms',
                'notes',
                'client_name',
                'client_email',
                'client_address',
                'client_siret',
                'client_tva_number',
                'sent_at',
                'paid_at',
            ]);
        });
    }
};
