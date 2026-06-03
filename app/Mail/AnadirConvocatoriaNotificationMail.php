<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnadirConvocatoriaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $examCall;


    /**
     * Create a new message instance.
     */
    public function __construct($examCall)
    {
        $this->examCall = $examCall;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva convocatoria para todos los estudiantes',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
    markdown: 'students.crear_nueva_convocatoria',
    with: [
        'examCall' => $this->examCall,
        'students' => $this->examCall->examStudents,
        'teacher' => $this->examCall->examStudents->first()->teacher,
        'vehicle' => $this->examCall->examStudents->first()->vehicle,
        'town' => $this->examCall->town,
        'date' => $this->examCall->exam_date,
        'notes' => $this->examCall->notes,
        'status' => $this->examCall->examCallStatus->name,
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
