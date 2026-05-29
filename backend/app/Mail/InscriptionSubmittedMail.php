<?php

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscriptionSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inscription $inscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de inscripción recibida — '.$this->inscription->championship->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inscription-submitted',
            with: [
                'user'         => $this->inscription->user,
                'championship' => $this->inscription->championship,
                'inscription'  => $this->inscription,
            ],
        );
    }
}
