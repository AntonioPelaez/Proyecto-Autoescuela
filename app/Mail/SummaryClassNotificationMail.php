<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SummaryClassNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $session;
    public $teacher;
    public $vehicle;
    public $student;
    public $sessionDate;
    public $startTime;
    public $endTime;
    public $studentSkills;
    public $drivingSkills;
    public $score;
    public $readyForExam;
    public $notes;

    /**
     * Create a new message instance.
     */
    public function __construct($session, $teacher, $vehicle, $student, $sessionDate, $startTime, $endTime, $studentSkills, $drivingSkills, $score, $readyForExam, $notes)
    {
        $this->session = $session;
        $this->teacher = $teacher;
        $this->vehicle = $vehicle;
        $this->student = $student;
        $this->sessionDate = $sessionDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->studentSkills = $studentSkills;
        $this->drivingSkills = $drivingSkills;
        $this->score = $score;
        $this->readyForExam = $readyForExam;
        $this->notes = $notes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resumen de la clase para el estudiante '.$this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.resumen_clase',
            with: [
                'session' => $this->session,
                'teacher' => $this->teacher,
                'vehicle' => $this->vehicle,
                'student' => $this->student,
                'sessionDate' => $this->sessionDate,
                'startTime' => $this->startTime,
                'endTime' => $this->endTime,
                'studentSkills' => $this->studentSkills,
                'drivingSkills' => $this->drivingSkills,
                'score' => $this->score,
                'readyForExam' => $this->readyForExam,
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
