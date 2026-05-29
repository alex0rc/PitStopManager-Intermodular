<?php

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewInscriptionOrganizerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inscription $inscription) {}

    public function envelope(): Envelope
    {
        $pilot = $this->inscription->user?->name ?? 'Un piloto';

        return new Envelope(
            subject: 'Nueva inscripción pendiente: '.$pilot,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-inscription-organizer',
            with: [
                'inscription'  => $this->inscription,
                'championship' => $this->inscription->championship,
                'pilot'        => $this->inscription->user,
                'organizer'    => $this->inscription->championship->user,
            ],
        );
    }
}
