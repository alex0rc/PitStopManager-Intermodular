<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysRemaining <= 1
            ? 'Tu suscripción caduca mañana'
            : 'Tu suscripción caduca en 7 días';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expiring',
            with: [
                'user' => $this->subscription->user,
                'plan' => $this->subscription->plan,
                'subscription' => $this->subscription,
                'daysRemaining' => $this->daysRemaining,
            ],
        );
    }
}
