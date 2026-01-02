<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm Your Email Address',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.email-confirmation',
            with: [
                'user' => $this->user,
                'confirmationUrl' => $this->getConfirmationUrl(),
            ]
        );
    }

    /**
     * Get the confirmation URL.
     */
    private function getConfirmationUrl(): string
    {
        $baseUrl = config('app.frontend_url', config('app.url'));
        return $baseUrl . '/auth/confirm-email?token=' . $this->user->confirmation_token;
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}