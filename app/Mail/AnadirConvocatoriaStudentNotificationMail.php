<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnadirConvocatoriaStudentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $examCall;
    public $examStudent;
    public $student;
    public $startDate;
    public $status;
    public $status_student;
    public $town;
    public $teacher;
    public $vehicle;

    /**
     * Create a new message instance.
     */
    public function __construct($examCall, $examStudent, $student, $startDate, $status, $status_student, $town, $teacher, $vehicle)
    {
        $this->examCall = $examCall;
        $this->examStudent = $examStudent;
        $this->student = $student;
        $this->startDate = $startDate;
        $this->status = $status;
        $this->status_student = $status_student;
        $this->town = $town;
        $this->teacher = $teacher;
        $this->vehicle = $vehicle;
    }   
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva convocatoria para el estudiante '.$this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.nueva_convocatoria_student',
            with: [
                'examCall' => $this->examCall,
                'examStudent' => $this->examStudent,
                'student' => $this->student,
                'startDate' => $this->startDate,
                'status' => $this->status,
                'town' => $this->town,
                'teacher' => $this->teacher,
                'vehicle' => $this->vehicle,
                'status_student' => $this->status_student,
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
