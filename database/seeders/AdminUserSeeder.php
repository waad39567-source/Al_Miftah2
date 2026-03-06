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
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@almiftah.com');
        $this->command->info('Password: admin123');
    }
}
