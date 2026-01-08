<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $token
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-reset',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->getResetUrl(),
                'expiresAt' => now()->addHour()->format('M j, Y \a\t g:i A'),
            ]
        );
    }

    /**
     * Get the password reset URL.
     */
    private function getResetUrl(): string
    {
        $baseUrl = config('app.frontend_url', config('app.url'));
        return $baseUrl . '/auth/reset-password?token=' . $this->token . '&email=' . urlencode($this->user->email);
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}