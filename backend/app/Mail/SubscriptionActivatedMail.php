<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu suscripción está activa');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-activated',
            with: [
                'user'         => $this->subscription->user,
                'plan'         => $this->subscription->plan,
                'subscription' => $this->subscription,
            ],
        );
    }
}
