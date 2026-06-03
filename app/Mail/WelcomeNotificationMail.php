<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $student;

    /**
     * Create a new message instance.
     */
    public function __construct($student)
    {
        $this->student = $student;
    }

     public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a Autoescuela AIBE, '.$this->student->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'students.bienvenida',
            with: [
                'student' => $this->student
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
