<?php

namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscriptionStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inscription $inscription) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->inscription->status) {
            'confirmed' => 'Tu inscripción ha sido confirmada',
            'rejected'  => 'Tu inscripción no ha sido aceptada',
            default     => 'Actualización de tu inscripción',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $status = $this->inscription->status;
        $title = match ($status) {
            'confirmed' => 'Inscripción confirmada',
            'rejected'  => 'Inscripción rechazada',
            default     => 'Actualización de inscripción',
        };

        return new Content(
            view: 'emails.inscription-status',
            with: [
                'user'         => $this->inscription->user,
                'championship' => $this->inscription->championship,
                'inscription'  => $this->inscription,
                'status'       => $status,
                'statusTitle'  => $title,
            ],
        );
    }
}
