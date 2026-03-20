<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'توثيق البريد الإلكتروني - شركة المفتاح',
        );
    }

    public function content(): Content
    {
        $baseUrl = config('app.url');
        $token = base64_encode($this->user->email . '|' . $this->user->id . '|' . $this->user->email_verified_at);
        
        return new Content(
            view: 'emails.email-verification',
            with: [
                'userName' => $this->user->name,
                'verificationUrl' => $baseUrl . '/api/auth/verify-email?token=' . $token,
            ],
        );
    }
}
