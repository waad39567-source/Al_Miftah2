<?php

namespace App\Services;

use App\Models\User;
use App\Models\PropertyFavorite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function deleteAccount(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        // حذف رموز المصادقة
        $user->tokens()->delete();

        // حذف صور العقارات من التخزين
        foreach ($user->properties as $property) {
            try {
                Storage::disk('public')->deleteDirectory('properties/' . $property->id);
            } catch (\Exception $e) {
                // تجاهل خطأ حذف المجلد
            }
        }

        // حذف المفضلات يدوياً (لأن belongsToMany لا تدعم onDelete)
        PropertyFavorite::where('user_id', $user->id)->delete();

        // حذف المستخدم (العلاقات تحذف تلقائياً بـ Cascade)
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
