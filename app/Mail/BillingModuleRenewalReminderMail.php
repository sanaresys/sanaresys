<?php

namespace App\Mail;

use App\Models\BillingModuleSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillingModuleRenewalReminderMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public BillingModuleSubscription $subscription,
        public int $daysBeforeExpiry,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: renovacion de modulo proxima',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.billing-module-renewal-reminder',
            with: [
                'centro' => $this->subscription->centro,
                'module' => $this->subscription->module,
                'daysBeforeExpiry' => $this->daysBeforeExpiry,
                'renewsAt' => $this->subscription->renews_at,
            ],
        );
    }
}

