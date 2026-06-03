<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmConvocatoriaStudentMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $student;
    public $examStudent;
    public $examCall;

    /**
     * Create a new message instance.
     */
    public function __construct($student, $examStudent, $examCall)
    {
        $this->student = $student;
        $this->examStudent = $examStudent;
        $this->examCall = $examCall;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de asistencia a la convocatoria',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.confirmar_convocatoria',
            with: [
                'student' => $this->student,
                'examStudent' => $this->examStudent,
                'examCall' => $this->examCall,
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
