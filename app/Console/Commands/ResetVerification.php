<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetVerification extends Command
{
    protected $signature = 'test:reset-verification {email}';

    protected $description = 'Reset email verification';

    public function handle(): int
    {
        $email = $this->argument('email');
        User::where('email', $email)->update(['email_verified_at' => null]);
        $this->info("Email verification reset for $email");
        return 0;
    }
}
