<?php

namespace App\Mail;

use App\Models\Inscription;
use App\Models\Race;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RaceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Race $race,
        public ?Inscription $inscription = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: carrera mañana — '.$this->race->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.race-reminder',
            with: [
                'user'         => $this->user,
                'race'         => $this->race,
                'championship' => $this->race->championship,
                'circuit'      => $this->race->circuit,
                'inscription'  => $this->inscription,
            ],
        );
    }
}
