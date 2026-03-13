<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Region;
use App\Models\PropertyImage;
use App\Models\ContactRequest;
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
        $admin = User::updateOrCreate(
            ['email' => 'admin@almiftah.com'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@almiftah.com',
                'password' => Hash::make('admin123'),
                'phone' => '01234567890',
                'role' => 'admin',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Admin user created: admin@almiftah.com / admin123');

        // 2. Create Property Owners - with unique phone numbers
        $owner1 = User::updateOrCreate(
            ['email' => 'owner1@almiftah.com'],
            [
                'name' => 'مكتب المفتاح للعقارات',
                'email' => 'owner1@almiftah.com',
                'password' => Hash::make('password123'),
                'phone' => '966511111111',
                'role' => 'owner',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );

        $owner2 = User::updateOrCreate(
            ['email' => 'owner2@almiftah.com'],
            [
                'name' => 'مؤسسة الغد للعقارات',
                'email' => 'owner2@almiftah.com',
                'password' => Hash::make('password123'),
                'phone' => '966522222222',
                'role' => 'owner',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Property owners created');

        // 3. Create Regular Verified Users
        $user1 = User::updateOrCreate(
            ['email' => 'user1@test.com'],
            [
                'name' => 'أحمد محمد',
                'email' => 'user1@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966533333333',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'user2@test.com'],
            [
                'name' => 'خالد عمر',
                'email' => 'user2@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966544444444',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );

        $user3 = User::updateOrCreate(
            ['email' => 'user3@test.com'],
            [
                'name' => 'محمد علي',
                'email' => 'user3@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966555555555',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Verified users created');

        // 4. Create Unverified Users
        $unverified1 = User::updateOrCreate(
            ['email' => 'unverified1@test.com'],
            [
                'name' => 'مستخدم غير موثق 1',
                'email' => 'unverified1@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966566666666',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => null,
            ]
        );

        $unverified2 = User::updateOrCreate(
            ['email' => 'unverified2@test.com'],
            [
                'name' => 'مستخدم غير موثق 2',
                'email' => 'unverified2@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966577777777',
                'role' => 'user',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => null,
            ]
        );

        // 5. Create Banned User
        $bannedUser = User::updateOrCreate(
            ['email' => 'banned@test.com'],
            [
                'name' => 'مستخدم محظور',
                'email' => 'banned@test.com',
                'password' => Hash::make('password123'),
                'phone' => '966588888888',
                'role' => 'user',
                'is_active' => false,
                'is_banned' => true,
                'banned_at' => now(),
                'ban_reason' => 'انتهاك شروط الاستخدام',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ All users created (8 total)');

        // 6. Get Regions
        $syria = Region::where('name', 'سوريا')->first();
        $damascus = Region::where('name', 'دمشق')->where('type', 'governorate')->first();
        $aleppo = Region::where('name', 'حلب')->where('type', 'governorate')->first();
        $homs = Region::where('name', 'حمص')->where('type', 'governorate')->first();
        $latakia = Region::where('name', 'اللاذقية')->where('type', 'governorate')->first();

        $maza = Region::where('name', 'المزة')->first();
        $babToma = Region::where('name', 'باب توما')->first();

        // 7. Create Properties
        $properties = [];

        // Active Properties (3)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة فاخرة في المزة',
            'description' => 'شقة حديثة 3 غرف نوم، مساحة 120 متر، موقع متميز',
            'price' => 150000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 120,
            'region_id' => $maza?->id ?? 1,
            'location' => 'دمشق - المزة',
            'latitude' => 33.8321,
            'longitude' => 36.1067,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'فيلا في باب توما',
            'description' => 'فيلا فاخرة 4 غرف، garden، garage',
            'price' => 350000,
            'type' => 'sale',
            'property_type' => 'house',
            'area' => 250,
            'region_id' => $babToma?->id ?? 2,
            'location' => 'دمشق - باب توما',
            'latitude' => 33.8234,
            'longitude' => 36.1234,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'شقة للإيجار في اللاذقية',
            'description' => 'شقة على البحر، 2 غرف، مفروشة',
            'price' => 500,
            'type' => 'rent',
            'property_type' => 'apartment',
            'area' => 80,
            'region_id' => $latakia?->id ?? 5,
            'location' => 'اللاذقية - حي الرمل',
            'latitude' => 35.5317,
            'longitude' => 35.3701,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Pending Properties (2)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'محل تجاري في حلب',
            'description' => 'محل في أفضل موقع تجاري',
            'price' => 200000,
            'type' => 'sale',
            'property_type' => 'shop',
            'area' => 60,
            'region_id' => $aleppo?->id ?? 3,
            'location' => 'حلب - المركز',
            'latitude' => 36.1989,
            'longitude' => 37.1345,
            'status' => 'pending',
            'is_active' => true,
        ]);

        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'أرض في حمص',
            'description' => 'أرض سكنية 500 متر',
            'price' => 80000,
            'type' => 'sale',
            'property_type' => 'land',
            'area' => 500,
            'region_id' => $homs?->id ?? 4,
            'location' => 'حمص - حي البيادر',
            'latitude' => 34.7328,
            'longitude' => 36.7143,
            'status' => 'pending',
            'is_active' => true,
        ]);

        // Rejected Properties (2)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة مرفوضة 1',
            'description' => 'شقة مرفوضة原因的',
            'price' => 100000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 100,
            'region_id' => $damascus?->id ?? 1,
            'location' => 'دمشق',
            'status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => 'المعلومات غير كاملة',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'شقة مرفوضة 2',
            'description' => 'شقة مرفوضة原因的',
            'price' => 120000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 110,
            'region_id' => $damascus?->id ?? 1,
            'location' => 'دمشق',
            'status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => 'الصور غير واضحة',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Rented Property (1)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة مؤجرة',
            'description' => 'شقة مؤجرة لمدة سنة',
            'price' => 800,
            'type' => 'rent',
            'property_type' => 'apartment',
            'area' => 90,
            'region_id' => $maza?->id ?? 1,
            'location' => 'دمشق - المزة',
            'status' => 'rented',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Sold Property (1)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => ' villa sold',
            'description' => 'فيلا مباعة',
            'price' => 500000,
            'type' => 'sale',
            'property_type' => 'house',
            'area' => 300,
            'region_id' => $aleppo?->id ?? 3,
            'location' => 'حلب - العزيزية',
            'status' => 'sold',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Inactive Property (1)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة غير نشطة',
            'description' => 'شقة مغلقة مؤقتاً',
            'price' => 180000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 130,
            'region_id' => $damascus?->id ?? 1,
            'location' => 'دمشق',
            'status' => 'active',
            'is_active' => false,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->command->info('✅ Properties created (10 total)');

        // 8. Create Contact Requests
        // Pending Requests (2)
        ContactRequest::create([
            'property_id' => $properties[0]->id,
            'user_id' => $user1->id,
            'owner_id' => $owner1->id,
            'name' => 'أحمد محمد',
            'phone' => '966501234567',
            'message' => 'أريد معرفة المزيد عن هذه الشقة',
            'status' => 'pending',
        ]);

        ContactRequest::create([
            'property_id' => $properties[1]->id,
            'user_id' => $user2->id,
            'owner_id' => $owner1->id,
            'name' => 'خالد عمر',
            'phone' => '966509999999',
            'message' => 'هل يمكنني زيارة العقار؟',
            'status' => 'pending',
        ]);

        // Approved Requests (2)
        ContactRequest::create([
            'property_id' => $properties[2]->id,
            'user_id' => $user3->id,
            'owner_id' => $owner2->id,
            'name' => 'محمد علي',
            'phone' => '966508888888',
            'message' => 'مهتم بالإيجار',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[8]->id,
            'user_id' => $user1->id,
            'owner_id' => $owner2->id,
            'name' => 'أحمد',
            'phone' => '966507777777',
            'message' => 'أريد شراء الفيلا',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Rejected Requests (2)
        ContactRequest::create([
            'property_id' => $properties[3]->id,
            'user_id' => $user2->id,
            'owner_id' => $owner1->id,
            'name' => 'عميل مرفوض',
            'phone' => '966506666666',
            'message' => 'استفسار',
            'status' => 'rejected',
            'rejection_reason' => 'العقار غير متاح',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[4]->id,
            'user_id' => $user3->id,
            'owner_id' => $owner2->id,
            'name' => 'عميل مرفوض 2',
            'phone' => '966505555555',
            'message' => 'استفسار',
            'status' => 'rejected',
            'rejection_reason' => 'المعلومات غير مكتملة',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->command->info('✅ Contact requests created (6 total)');

        // 9. Add Images to Properties
        foreach ($properties as $index => $property) {
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => "https://via.placeholder.com/800x600?text=Property+" . ($index + 1),
                'sort_order' => 1,
            ]);
        }

        $this->command->info('✅ Property images created');

        // Summary
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('🎉 Database seeding completed!');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Users: ' . User::count());
        $this->command->info('   - Properties: ' . Property::count());
        $this->command->info('   - Contact Requests: ' . ContactRequest::count());
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('   Admin: admin@almiftah.com / admin123');
        $this->command->info('   Owner: owner1@almiftah.com / password123');
        $this->command->info('   User: user1@test.com / password123');
        $this->command->info('');
    }
}
