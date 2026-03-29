<?php

namespace App\Services;

use App\Models\User;
use App\Models\PropertyFavorite;
use App\Models\Property;
use App\Models\ContactRequest;
use App\Models\Notification;
use App\Models\UserFcmToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function deleteAccount(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        $userId = $user->id;

        // حذف رموز المصادقة
        $user->tokens()->delete();

        // حذف صور العقارات من التخزين وحذف العقارات
        foreach ($user->properties as $property) {
            try {
                Storage::disk('public')->deleteDirectory('properties/' . $property->id);
            } catch (\Exception $e) {
                // تجاهل خطأ حذف المجلد
            }
            $property->images()->delete();
            $property->delete();
        }

        // حذف جميع السجلات المرتبطة يدوياً
        PropertyFavorite::where('user_id', $userId)->delete();
        ContactRequest::where('user_id', $userId)->delete();
        Notification::where('user_id', $userId)->delete();
        UserFcmToken::where('user_id', $userId)->delete();

        // حذف المستخدم
        $user->delete();

        return true;
    }

    public function updateEmail(User $user, string $email, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return false;
        }

        $user->update(['email' => $email]);

        return true;
    }
}
