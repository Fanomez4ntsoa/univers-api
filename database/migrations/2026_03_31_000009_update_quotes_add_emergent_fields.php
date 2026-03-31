<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Emergent fields
            $table->string('title')->nullable()->after('quote_number');
            $table->decimal('global_discount_percent', 5, 2)->default(0)->after('tax_amount');
            $table->decimal('global_discount_amount', 12, 2)->default(0)->after('global_discount_percent');
            $table->integer('validity_days')->default(30)->after('valid_until');
            $table->string('payment_terms')->default('Paiement à réception')->after('validity_days');
            $table->integer('payment_delay_days')->default(30)->after('payment_terms');
            $table->text('notes')->nullable()->after('payment_delay_days');
            $table->text('internal_notes')->nullable()->after('notes');
            $table->text('terms_and_conditions')->nullable()->after('internal_notes');

            // Signature details
            $table->string('signed_by')->nullable()->after('signature_url');
            $table->timestamp('signed_at')->nullable()->after('signed_by');
            $table->string('signed_ip')->nullable()->after('signed_at');

            // Tracking
            $table->timestamp('sent_at')->nullable()->after('signed_ip');
            $table->timestamp('viewed_at')->nullable()->after('sent_at');

            // Link to invoice when converted
            $table->foreignId('invoice_id')->nullable()->after('viewed_at')
                ->constrained('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn([
                'title',
                'global_discount_percent',
                'global_discount_amount',
                'validity_days',
                'payment_terms',
                'payment_delay_days',
                'notes',
                'internal_notes',
                'terms_and_conditions',
                'signed_by',
                'signed_at',
                'signed_ip',
                'sent_at',
                'viewed_at',
                'invoice_id',
            ]);
        });
    }
};
