<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PendingInscriptionsDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, array{championship_name: string, championship_id: int, pending_count: int}>  $summary
     */
    public function __construct(
        public User $organizer,
        public Collection $summary,
        public int $totalPending,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tienes '.$this->totalPending.' inscripción(es) pendientes de revisar',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pending-inscriptions-digest',
            with: [
                'organizer'     => $this->organizer,
                'summary'       => $this->summary,
                'totalPending'  => $this->totalPending,
            ],
        );
    }
}
