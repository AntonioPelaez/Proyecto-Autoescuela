<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancelarConvocatoriaEstudianteMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $student;
    public $examStudent;
    public $examCall;
    public $motive;

    /**
     * Create a new message instance.
     */
    public function __construct($student, $examStudent, $examCall, $motive)
    {
        $this->student = $student;
        $this->examStudent = $examStudent;
        $this->examCall = $examCall;
        $this->motive = $motive;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cancelación de la convocatoria del estudiante'. $this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'students.cancelar_convocatoria_estudiante',
            with: [
                'student' => $this->student,
                'examStudent' => $this->examStudent,
                'examCall' => $this->examCall,
                'motive' => $this->motive,
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
