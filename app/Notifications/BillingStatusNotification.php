<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingStatusNotification extends Notification
{
    use Queueable;

    /**
     * @param array<int, string> $channels
     * @param array<string, mixed> $payload
     */
    public function __construct(
        protected array $channels,
        protected array $payload,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => (string) ($this->payload['title'] ?? 'Actualizacion de billing'),
            'body' => (string) ($this->payload['body'] ?? ''),
            'level' => (string) ($this->payload['level'] ?? 'info'),
            'action_url' => (string) ($this->payload['action_url'] ?? ''),
            'event_key' => (string) ($this->payload['event_key'] ?? 'billing'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject((string) ($this->payload['title'] ?? 'Actualizacion de billing'))
            ->line((string) ($this->payload['body'] ?? ''));

        $actionUrl = (string) ($this->payload['action_url'] ?? '');
        if ($actionUrl !== '') {
            $mail->action(
                (string) ($this->payload['action_label'] ?? 'Abrir billing'),
                $actionUrl
            );
        }

        return $mail;
    }
}
