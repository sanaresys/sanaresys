<?php

namespace App\Mail;

use App\Models\ClinicRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicRegistrationVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ClinicRegistrationRequest $registrationRequest,
        public string $verificationUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifica tu correo para crear tu clinica',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.clinic-registration-verification',
            with: [
                'clinicName' => $this->registrationRequest->nombre_centro,
                'ownerName' => $this->registrationRequest->owner_name,
                'verificationUrl' => $this->verificationUrl,
                'expiresAt' => $this->registrationRequest->verification_expires_at,
            ],
        );
    }
}

