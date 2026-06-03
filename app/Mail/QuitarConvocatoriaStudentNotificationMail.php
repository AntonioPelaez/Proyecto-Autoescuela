<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuitarConvocatoriaStudentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $examCall;
    public $examStudent;
    public $student;
    public $startDate;
    public $motive;
    

    /**
     * Create a new message instance.
     */
    public function __construct($examCall, $examStudent, $student, $startDate, $motive)
    {
        $this->examCall = $examCall;
        $this->examStudent = $examStudent;
        $this->student = $student;
        $this->startDate = $startDate;
        $this->motive = $motive;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convocatoria eliminada para el estudiante '.$this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.retirar_convocatoria_student',
            with: [
                'examCall' => $this->examCall,
                'student' => $this->student,
                'startDate' => $this->startDate,
                'motive' => $this->motive,
                'examStudent' => $this->examStudent,
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
