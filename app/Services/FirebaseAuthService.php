<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseAuthService
{
    /**
     * Verify a Firebase ID token via Firebase REST API.
     * Returns the Firebase user array on success, or null on failure.
     */
    public function verifyIdToken(string $idToken): ?array
    {
        $apiKey = config('firebase.web_api_key');

        if (empty($apiKey)) {
            Log::error('FIREBASE_WEB_API_KEY is not configured');
            return null;
        }

        try {
            $response = Http::post(
                "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}",
                ['idToken' => $idToken]
            );

            if ($response->failed()) {
                Log::warning('Firebase token verification failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (empty($data['users'][0])) {
                return null;
            }

            return $data['users'][0];
        } catch (\Throwable $e) {
            Log::error('Firebase token verification exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Detect the auth provider from Firebase user data.
     * Returns: 'google', 'phone', or 'email'
     */
    public function detectProvider(array $firebaseUser): string
    {
        foreach ($firebaseUser['providerUserInfo'] ?? [] as $provider) {
            if ($provider['providerId'] === 'google.com') return 'google';
            if ($provider['providerId'] === 'phone')      return 'phone';
        }

        return isset($firebaseUser['phoneNumber']) ? 'phone' : 'email';
    }

    /**
     * Normalize a phone number from Firebase E.164 format (+963912345678)
     * by stripping the leading '+'.
     */
    public function normalizePhone(string $phone): string
    {
        return ltrim($phone, '+');
    }
}
