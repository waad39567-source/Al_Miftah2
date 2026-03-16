<?php

namespace App\Services;

use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private $projectId;
    private $clientEmail;
    private $privateKey;

    public function __construct()
    {
        $this->loadCredentials();
    }

    private function loadCredentials()
    {
        $credentialsFile = config('firebase.credentials_file', base_path('firebase-credentials.json'));
        
        if (file_exists($credentialsFile)) {
            $credentials = json_decode(file_get_contents($credentialsFile), true);
            if ($credentials) {
                $this->projectId = $credentials['project_id'] ?? 'almoftahapp';
                $this->clientEmail = $credentials['client_email'] ?? '';
                $this->privateKey = $credentials['private_key'] ?? '';
                return;
            }
        }

        $this->projectId = config('firebase.project_id', 'almoftahapp');
        $this->clientEmail = config('firebase.client_email', 'firebase-adminsdk-fbsvc@almoftahapp.iam.gserviceaccount.com');
        $key = config('firebase.private_key', '');
        $this->privateKey = str_replace('\n', "\n", $key);
    }

    private function getAccessToken()
    {
        if (empty($this->privateKey) || empty($this->clientEmail)) {
            Log::warning('Firebase credentials not configured');
            return null;
        }
        
        $jwt = $this->createJwt();
        
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        
        if ($response->successful()) {
            return $response->json('access_token');
        }
        
        Log::error('Failed to get Firebase access token', $response->json());
        return null;
    }

    private function createJwt()
    {
        $now = time();
        $exp = $now + 3600;
        
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        
        $payload = base64_encode(json_encode([
            'iss' => $this->clientEmail,
            'sub' => $this->clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging https://www.googleapis.com/auth/cloud-platform',
        ]));
        
        $signature = '';
        openssl_sign("$header.$payload", $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        
        return "$header.$payload." . base64_encode($signature);
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return false;
        }
        
        $response = Http::withToken($accessToken)
            ->post('https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send', [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ],
            ]);
        
        if ($response->successful()) {
            return true;
        }
        
        Log::error('Failed to send Firebase notification', $response->json());
        return false;
    }

    public function sendToTopic($topic, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return false;
        }
        
        $response = Http::withToken($accessToken)
            ->post('https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send', [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ],
            ]);
        
        if ($response->successful()) {
            return true;
        }
        
        Log::error('Failed to send Firebase notification to topic', $response->json());
        return false;
    }

    public function sendToUser($userId, $title, $body, $data = [])
    {
        $tokens = UserFcmToken::where('user_id', $userId)->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return false;
        }
        
        foreach ($tokens as $token) {
            $this->sendNotification($token, $title, $body, $data);
        }
        
        return true;
    }

    public function sendToAdmins($title, $body, $data = [])
    {
        return $this->sendToTopic('admins', $title, $body, $data);
    }
}
