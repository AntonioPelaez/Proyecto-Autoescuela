<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResultConvocatoriaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $examCall;
    public $examStudent;
    public $student;
    public $startDate;
    public $teacher;
    public $vehicle;
    public $status_result;
    public $notes;

    /**
     * Create a new message instance.
     */
    public function __construct($examCall, $examStudent, $student, $startDate, $teacher, $vehicle, $status_result, $notes)
    {
        $this->examCall = $examCall;
        $this->examStudent = $examStudent;
        $this->student = $student;
        $this->startDate = $startDate;
        $this->teacher = $teacher;
        $this->vehicle = $vehicle;
        $this->status_result = $status_result;
        $this->notes = $notes;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resultado de la convocatoria para el estudiante '.$this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.resultado_convocatoria',
            with: [
                'examCall' => $this->examCall,
                'examStudent' => $this->examStudent,
                'student' => $this->student,
                'startDate' => $this->startDate,
                'teacher' => $this->teacher,
                'vehicle' => $this->vehicle,
                'status_result' => $this->status_result,
                'notes' => $this->notes,
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
