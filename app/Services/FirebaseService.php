<?php

namespace App\Services;

use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private $projectId;
    private $senderId;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->senderId = config('firebase.sender_id');
    }

    private function getAccessToken()
    {
        $credentialsPath = config('firebase.credentials_path');
        
        if (!file_exists($credentialsPath)) {
            Log::warning('Firebase credentials file not found');
            return null;
        }
        
        $credentials = json_decode(file_get_contents($credentialsPath), true);
        
        if (!$credentials) {
            Log::warning('Firebase credentials file is invalid');
            return null;
        }
        
        $jwt = $this->createJwt($credentials);
        
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

    private function createJwt($credentials)
    {
        $now = time();
        $exp = $now + 3600;
        
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        
        $payload = base64_encode(json_encode([
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging https://www.googleapis.com/auth/cloud-platform',
        ]));
        
        $privateKey = $credentials['private_key'];
        
        $signature = '';
        openssl_sign("$header.$payload", $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
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
