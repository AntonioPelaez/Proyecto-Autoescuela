<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReserveNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $session;
    public $teacher;
    public $vehicle;
    public $sessionDate;
    public $startTime;
    public $endTime;
    public $paymentType;
    public $paymentStatus;
    public $price;

    /**
     * Create a new message instance.
     */
    public function __construct($student, $session, $teacher, $vehicle, $sessionDate, $startTime, $endTime, $paymentType, $paymentStatus, $price)
    {
        $this->student = $student;
        $this->session = $session;
        $this->teacher = $teacher;
        $this->vehicle = $vehicle;
        $this->sessionDate = $sessionDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->paymentType = $paymentType;
        $this->paymentStatus = $paymentStatus;
        $this->price = $price;
    }
    
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reserva confirmada para la sesión de conducción del estudiante '.$this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'students.reserva',
            with: [
                'student' => $this->student,
                'session' => $this->session,
                'teacher' => $this->teacher,
                'vehicle' => $this->vehicle,
                'sessionDate' => $this->sessionDate,
                'startTime' => $this->startTime,
                'endTime' => $this->endTime,
                'paymentType' => $this->paymentType,
                'paymentStatus' => $this->paymentStatus,
                'price' => $this->price,
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
