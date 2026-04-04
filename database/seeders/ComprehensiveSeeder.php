<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('===========================================');
        $this->command->info('Starting Comprehensive Database Seeding...');
        $this->command->info('===========================================');

        // 1. Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@almiftah.com'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@almiftah.com',
                'password' => Hash::make('admin123'),
                'phone' => '0912345678',
                'role' => 'admin',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Admin user created: admin@almiftah.com / admin123');

        // 2. Create Property Owners - with Syrian phone numbers
        User::updateOrCreate(
            ['email' => 'owner1@almiftah.com'],
            [
                'name' => 'مكتب المفتاح للعقارات',
                'email' => 'owner1@almiftah.com',
                'password' => Hash::make('password123'),
                'phone' => '0911111111',
                'role' => 'owner',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner2@almiftah.com'],
            [
                'name' => 'مؤسسة الغد للعقارات',
                'email' => 'owner2@almiftah.com',
                'password' => Hash::make('password123'),
                'phone' => '0922222222',
                'role' => 'owner',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Property owners created');

        // 3. Create Regular Verified Users
        User::updateOrCreate(
            ['email' => 'user1@test.com'],
            [
                'name' => 'أحمد محمد',
                'email' => 'user1@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0933333333',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user2@test.com'],
            [
                'name' => 'خالد عمر',
                'email' => 'user2@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0944444444',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user3@test.com'],
            [
                'name' => 'محمد علي',
                'email' => 'user3@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0955555555',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Verified users created');

        // 4. Create Unverified Users
        User::updateOrCreate(
            ['email' => 'unverified1@test.com'],
            [
                'name' => 'مستخدم غير موثق 1',
                'email' => 'unverified1@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0966666666',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'unverified2@test.com'],
            [
                'name' => 'مستخدم غير موثق 2',
                'email' => 'unverified2@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0977777777',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => null,
            ]
        );

        // 5. Create Banned User
        User::updateOrCreate(
            ['email' => 'banned@test.com'],
            [
                'name' => 'مستخدم محظور',
                'email' => 'banned@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0988888888',
                'role' => 'user',
                'is_active' => false,
                'is_banned' => true,
                'banned_at' => now(),
                'ban_reason' => 'انتهاك شروط الاستخدام',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ All users created (8 total)');

        // Summary
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('Database seeding completed!');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('   - Users: ' . User::count());
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('   Admin: admin@almiftah.com / admin123');
        $this->command->info('   Owner: owner1@almiftah.com / password123');
        $this->command->info('   User: user1@test.com / password123');
        $this->command->info('');
    }
}
