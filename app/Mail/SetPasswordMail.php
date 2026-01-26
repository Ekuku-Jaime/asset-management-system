<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

     public $user;
    public $activationUrl;
    /**
     * Create a new message instance.
     */
    public function __construct($user, $token)
    {
    
        $this->user = $user;
        $this->activationUrl = route('activation.show', ['token' => $token]);
    }
 public function build()
    {
        return $this->subject('Ativar a sua conta - Asset Management System')
                    ->markdown('emails.activation')
                    ->with([
                        'user' => $this->user,
                        'activationUrl' => $this->activationUrl,
                    ]);
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Password Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
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
