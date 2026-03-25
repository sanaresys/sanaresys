<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_registration_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_registration_requests', 'billing_invoice_id')) {
                $table->foreignId('billing_invoice_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('billing_invoices')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'consent_at')) {
                $table->timestamp('consent_at')->nullable()->after('payment_approved_at');
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'consent_text_version')) {
                $table->string('consent_text_version', 64)->nullable()->after('consent_at');
            }

            if (! Schema::hasColumn('clinic_registration_requests', 'consent_ip')) {
                $table->string('consent_ip', 45)->nullable()->after('consent_text_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_registration_requests', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_registration_requests', 'consent_ip')) {
                $table->dropColumn('consent_ip');
            }
            if (Schema::hasColumn('clinic_registration_requests', 'consent_text_version')) {
                $table->dropColumn('consent_text_version');
            }
            if (Schema::hasColumn('clinic_registration_requests', 'consent_at')) {
                $table->dropColumn('consent_at');
            }
            if (Schema::hasColumn('clinic_registration_requests', 'billing_invoice_id')) {
                $table->dropConstrainedForeignId('billing_invoice_id');
            }
        });
    }
};
