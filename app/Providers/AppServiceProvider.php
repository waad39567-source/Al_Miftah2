<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->createFirebaseCredentialsFile();
    }

    private function createFirebaseCredentialsFile()
    {
        $base64 = env('FIREBASE_CREDENTIALS_BASE64');
        
        if ($base64) {
            $path = base_path('firebase-credentials.json');
            
            if (!file_exists($path)) {
                $decoded = base64_decode($base64, true);
                if ($decoded !== false) {
                    @file_put_contents($path, $decoded);
                }
            }
        }
    }
}
