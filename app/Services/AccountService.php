<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function deleteAccount(User $user, ?string $password): bool
    {
        $isGoogleUser = $user->auth_provider === 'google' || is_null($user->password);

        if (!$isGoogleUser && !Hash::check($password, $user->password)) {
            return false;
        }

        $userId = $user->id;

        // حذف رموز المصادقة
        DB::table('personal_access_tokens')->where('tokenable_id', $userId)->delete();

        // حذف صور العقارات من التخزين
        $properties = DB::table('properties')->where('owner_id', $userId)->get();
        foreach ($properties as $property) {
            try {
                Storage::disk('public')->deleteDirectory('properties/' . $property->id);
            } catch (\Exception $e) {
                // تجاهل خطأ حذف المجلد
            }
            // حذف صور العقار
            DB::table('property_images')->where('property_id', $property->id)->delete();
            // حذف طلبات التواصل للعقار
            DB::table('contact_requests')->where('property_id', $property->id)->delete();
            // حذف المفضلات للعقار
            DB::table('property_favorites')->where('property_id', $property->id)->delete();
            // حذف العقار
            DB::table('properties')->where('id', $property->id)->delete();
        }

        // حذف جميع السجلات المرتبطة يدوياً
        DB::table('property_favorites')->where('user_id', $userId)->delete();
        DB::table('contact_requests')->where('user_id', $userId)->delete();
        DB::table('contact_requests')->where('owner_id', $userId)->delete();
        DB::table('notifications')->where('user_id', $userId)->delete();
        DB::table('user_fcm_tokens')->where('user_id', $userId)->delete();

        // حذف المستخدم
        DB::table('users')->where('id', $userId)->delete();

        return true;
    }

    public function updateEmail(User $user, string $email, ?string $password): bool
    {
        $isGoogleUser = $user->auth_provider === 'google' || is_null($user->password);

        if (!$isGoogleUser && !Hash::check($password, $user->password)) {
            return false;
        }

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return false;
        }

        $user->update(['email' => $email]);

        return true;
    }
}
