<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('paypal');
            $table->string('event_id')->index();
            $table->string('event_type', 120)->nullable()->index();
            $table->string('resource_type', 120)->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->longText('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_webhook_events');
    }
};
