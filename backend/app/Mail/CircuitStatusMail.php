<?php

namespace App\Mail;

use App\Models\Circuit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CircuitStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Circuit $circuit) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->circuit->status) {
            'approved' => 'Tu circuito ha sido aprobado',
            'rejected' => 'Tu circuito no ha sido aprobado',
            default    => 'Actualización de tu circuito',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $title = match ($this->circuit->status) {
            'approved' => 'Circuito aprobado',
            'rejected' => 'Circuito rechazado',
            default    => 'Estado del circuito actualizado',
        };

        return new Content(
            view: 'emails.circuit-status',
            with: [
                'circuit' => $this->circuit,
                'user'    => $this->circuit->user,
                'status'  => $this->circuit->status,
                'title'   => $title,
            ],
        );
    }
}
