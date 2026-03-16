<?php

namespace App\Http\Controllers;

use App\Models\UserFcmToken;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function __construct(
        private FirebaseService $firebaseService
    ) {}

    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
        ]);

        UserFcmToken::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'token' => $request->token,
            ],
            [
                'device_type' => $request->device_type,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token saved successfully'
        ]);
    }

    public function removeToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        UserFcmToken::where('user_id', Auth::id())
            ->where('token', $request->token)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token removed successfully'
        ]);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $tokens = UserFcmToken::where('user_id', $request->user_id)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return response()->json([
                'success' => false,
                'message' => 'No FCM tokens found for this user'
            ]);
        }

        $data = [
            'type' => 'notification',
            'extra' => $request->extra ?? [],
        ];

        foreach ($tokens as $token) {
            $this->firebaseService->sendNotification($token, $request->title, $request->body, $data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully'
        ]);
    }
}
