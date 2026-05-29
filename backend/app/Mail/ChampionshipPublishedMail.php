<?php

namespace App\Mail;

use App\Models\Championship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChampionshipPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Championship $championship) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Campeonato publicado: '.$this->championship->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.championship-published',
            with: [
                'championship' => $this->championship,
                'organizer'    => $this->championship->user,
            ],
        );
    }
}
