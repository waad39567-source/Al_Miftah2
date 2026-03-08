<?php

namespace App\Jobs;

use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Log::info('Sending verification email to: ' . $this->user->email);
        
        try {
            Mail::to($this->user->email)->send(new EmailVerification($this->user));
            Log::info('Email sent successfully to: ' . $this->user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            throw $e;
        }
    }
}
