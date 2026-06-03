<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancelConvocatoriaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $examCall;
    public $reason;
    public $town;
    public $startDate;
    public $teacher;
    public $vehicle;

    /**
     * Create a new message instance.
     */
    public function __construct($examCall, $reason, $town, $startDate, $teacher, $vehicle)
    {
        $this->examCall = $examCall;
        $this->reason = $reason;
        $this->town = $town;
        $this->startDate = $startDate;
        $this->teacher = $teacher;
        $this->vehicle = $vehicle;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cancelación de convocatoria',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.cancelacion_convocatoria',
            with: [
                'examCall' => $this->examCall,
                'reason' => $this->reason,
                'town' => $this->town,
                'startDate' => $this->startDate,
                'teacher' => $this->teacher,
                'vehicle' => $this->vehicle,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
