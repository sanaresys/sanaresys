<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_module_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_module_subscription_id');
            $table->unsignedBigInteger('centro_id');
            $table->unsignedBigInteger('billing_module_id');
            $table->unsignedTinyInteger('days_before_expiry')->index();
            $table->string('channel', 16)->index();
            $table->string('recipient')->default('');
            $table->date('scheduled_for_date')->index();
            $table->timestamp('sent_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(
                ['billing_module_subscription_id', 'days_before_expiry', 'channel', 'recipient', 'scheduled_for_date'],
                'billing_module_reminder_logs_unique'
            );

            $table->foreign('billing_module_subscription_id', 'bmr_logs_sub_fk')
                ->references('id')
                ->on('billing_module_subscriptions')
                ->cascadeOnDelete();

            $table->foreign('centro_id', 'bmr_logs_centro_fk')
                ->references('id')
                ->on('centros_medicos')
                ->cascadeOnDelete();

            $table->foreign('billing_module_id', 'bmr_logs_module_fk')
                ->references('id')
                ->on('billing_modules')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_module_reminder_logs');
    }
};
