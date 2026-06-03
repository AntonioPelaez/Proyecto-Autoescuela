<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EditConvocatoriaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $examCall;
    public $students;
    public $startDate;
    public $teacher;
    public $town;
    public $vehicle;
    public $notes;

    /**
     * Create a new message instance.
     */
    public function __construct($examCall, $students, $startDate, $teacher, $town, $vehicle, $notes)
    {
        $this->examCall = $examCall;
        $this->students = $students;
        $this->startDate = $startDate;
        $this->teacher = $teacher;
        $this->town = $town;
        $this->vehicle = $vehicle;
        $this->notes = $notes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convocatoria Editada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.cambios_convocatoria',
            with: [
                'examCall' => $this->examCall,
                'students' => $this->students,
                'startDate' => $this->startDate,
                'teacher' => $this->teacher,
                'town' => $this->town,
                'vehicle' => $this->vehicle,
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
