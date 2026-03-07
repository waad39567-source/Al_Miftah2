<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@almiftah.com'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@almiftah.com',
                'password' => Hash::make('admin123'),
                'phone' => '01234567890',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'verified@test.com'],
            [
                'name' => 'مستخدم موثق',
                'email' => 'verified@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966501111111',
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'inactive@test.com'],
            [
                'name' => 'مستخدم غير نشط',
                'email' => 'inactive@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966502222222',
                'role' => 'user',
                'is_active' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@almiftah.com');
        $this->command->info('Password: admin123');
    }
}
