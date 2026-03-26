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
                'phone' => '0912345678',
                'role' => 'admin',
                'is_active' => true,
                'is_banned' => false,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Admin user created: admin@almiftah.com / admin123');

        // 2. Create Property Owners - with Syrian phone numbers
        $owner1 = User::updateOrCreate(
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

        $owner2 = User::updateOrCreate(
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
        $user1 = User::updateOrCreate(
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

        $user2 = User::updateOrCreate(
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

        $user3 = User::updateOrCreate(
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
        $unverified1 = User::updateOrCreate(
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

        $unverified2 = User::updateOrCreate(
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
        $bannedUser = User::updateOrCreate(
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

        // 6. Get Neighborhoods for Properties
        $neighborhoods = Region::whereType('neighborhood')
            ->with('parent.parent')
            ->get()
            ->keyBy('id');

        // 7. Create Properties (15 properties across different governorates)
        $properties = [];

        // Property 1 - Damascus (شقة للبيع)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة فاخرة في المزة',
            'description' => 'شقة حديثة 3 غرف نوم، صالة كبيرة، موقع متميز قرب وسائل النقل',
            'price' => 85000000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 150,
            'region_id' => 29, // المزة
            'location' => 'دمشق - المزة',
            'latitude' => 33.8321,
            'longitude' => 36.1067,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 2 - Damascus (فيلا للبيع)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'فيلا فاخرة في الصالحية',
            'description' => 'فيلا 4 غرف نوم مع حديقة كبيرة، موقف سيارات،near schools',
            'price' => 250000000,
            'type' => 'sale',
            'property_type' => 'house',
            'area' => 350,
            'region_id' => 34, // الصالحية
            'location' => 'دمشق - الصالحية',
            'latitude' => 33.8350,
            'longitude' => 36.1150,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 3 - Aleppo (شقة للبيع)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'شقة في العزيزية حلب',
            'description' => 'شقة جديدة 2 غرف، مجهزة بالكامل، قرب الخدمات',
            'price' => 45000000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 110,
            'region_id' => 234, // أبو قلقل - منبج
            'location' => 'حلب - العزيزية',
            'latitude' => 36.2012,
            'longitude' => 37.1345,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 4 - Homs (أرض للبيع)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'أرض سكنية في حمص',
            'description' => 'أرض مساحتها 500 متر مربع، مخصصة للبناء السكني',
            'price' => 25000000,
            'type' => 'sale',
            'property_type' => 'land',
            'area' => 500,
            'region_id' => 52, // مركز حمص
            'location' => 'حمص - مركز حمص',
            'latitude' => 34.7328,
            'longitude' => 36.7143,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 5 - Latakia (شقة للإيجار)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة مفروشة للإيجار في جبلة',
            'description' => 'شقة عصرية 3 غرف، مفروشة بالكامل،near the sea',
            'price' => 500000,
            'type' => 'rent',
            'property_type' => 'apartment',
            'area' => 120,
            'region_id' => 302, // الدالية - جبلة
            'location' => 'اللاذقية - جبلة',
            'latitude' => 35.5317,
            'longitude' => 35.3701,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 6 - Tartus (محل تجاري)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'محل تجاري في طرطوس',
            'description' => 'محل في أفضل موقع تجاري، مناسب لأي نشاط تجاري',
            'price' => 35000000,
            'type' => 'sale',
            'property_type' => 'shop',
            'area' => 80,
            'region_id' => 375, // كريمة - طرطوس
            'location' => 'طرطوس - كريمة',
            'latitude' => 34.8915,
            'longitude' => 35.8867,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 7 - Daraa (شقة للبيع)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة في الصنمين درعا',
            'description' => 'شقة جديدة 3 غرف، تشطيب ممتاز',
            'price' => 30000000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 130,
            'region_id' => 339, // تسيل - إزرع
            'location' => 'درعا - الصنمين',
            'latitude' => 32.7542,
            'longitude' => 36.1687,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 8 - Sweida (مزرعة)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'مزرعة في صلخد',
            'description' => 'مزرعة بمساحة كبيرة، مناسبة للزراعة والسياحة',
            'price' => 150000000,
            'type' => 'sale',
            'property_type' => 'farm',
            'area' => 5000,
            'region_id' => 14, // مركز صلخد
            'location' => 'السويداء - صلخد',
            'latitude' => 32.7540,
            'longitude' => 36.4000,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 9 - Hama (شقة للإيجار)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة للإيجار في محردة',
            'description' => 'شقة 2 غرف، newly renovated، قرب المركز',
            'price' => 200000,
            'type' => 'rent',
            'property_type' => 'apartment',
            'area' => 90,
            'region_id' => 281, // كرناز - محردة
            'location' => 'حماة - محردة',
            'latitude' => 35.2678,
            'longitude' => 36.5034,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 10 - Rural Damascus (بيت)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'بيت قديم في عربين',
            'description' => 'بيت شعبي بمساحة كبيرة، مناسب للترميم',
            'price' => 40000000,
            'type' => 'sale',
            'property_type' => 'house',
            'area' => 200,
            'region_id' => 82, // عربين - مركز ريف دمشق
            'location' => 'ريف دمشق - عربين',
            'latitude' => 33.5678,
            'longitude' => 36.4567,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 11 - Deir Ezzor (أرض)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'أرض في البوكمال',
            'description' => 'أرض زراعية بمساحة كبيرة، near the river',
            'price' => 80000000,
            'type' => 'sale',
            'property_type' => 'land',
            'area' => 10000,
            'region_id' => 146, // هجين - البوكمال
            'location' => 'دير الزور - البوكمال',
            'latitude' => 34.4525,
            'longitude' => 40.1745,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 12 - Hasaka (محل)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'محل في رأس العين',
            'description' => 'محل في الشارع الرئيسي، مناسب للتجارة',
            'price' => 20000000,
            'type' => 'sale',
            'property_type' => 'store',
            'area' => 60,
            'region_id' => 153, // مركز رأس العين
            'location' => 'الحسكة - رأس العين',
            'latitude' => 36.5634,
            'longitude' => 40.5745,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 13 - Idleb (شقة)
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة في بنش',
            'description' => 'شقة جديدة 2 غرف، تشطيب سوبر لوكس',
            'price' => 25000000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 100,
            'region_id' => 200, // بنش
            'location' => 'إدلب - بنش',
            'latitude' => 35.8634,
            'longitude' => 36.6234,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 14 - Quneitra (بيت)
        $properties[] = Property::create([
            'owner_id' => $owner2->id,
            'title' => 'بيت في خان أرنبة',
            'description' => 'بيت بمساحة كبيرة، مناسب للعائلة',
            'price' => 35000000,
            'type' => 'sale',
            'property_type' => 'house',
            'area' => 180,
            'region_id' => 175, // خان أرنبة
            'location' => 'القنيطرة - خان أرنبة',
            'latitude' => 33.1234,
            'longitude' => 35.8234,
            'status' => 'active',
            'is_active' => true,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Property 15 - Pending
        $properties[] = Property::create([
            'owner_id' => $owner1->id,
            'title' => 'شقة جديدة في جوبر',
            'description' => 'شقة قيد الإنشاء، تسليم خلال 6 أشهر',
            'price' => 55000000,
            'type' => 'sale',
            'property_type' => 'apartment',
            'area' => 140,
            'region_id' => 24, // جوبر
            'location' => 'دمشق - جوبر',
            'latitude' => 33.8123,
            'longitude' => 36.1456,
            'status' => 'pending',
            'is_active' => true,
        ]);

        $this->command->info('✅ Properties created (15 total)');

        // 8. Create Contact Requests
        // Pending Requests (3)
        ContactRequest::create([
            'property_id' => $properties[0]->id,
            'user_id' => $user1->id,
            'owner_id' => $owner1->id,
            'name' => 'أحمد محمد',
            'phone' => '0912345678',
            'message' => 'أريد معرفة المزيد عن هذه الشقة',
            'status' => 'pending',
        ]);

        ContactRequest::create([
            'property_id' => $properties[2]->id,
            'user_id' => $user2->id,
            'owner_id' => $owner2->id,
            'name' => 'خالد عمر',
            'phone' => '0923456789',
            'message' => 'هل يمكنني زيارة العقار؟',
            'status' => 'pending',
        ]);

        ContactRequest::create([
            'property_id' => $properties[14]->id,
            'user_id' => $user3->id,
            'owner_id' => $owner1->id,
            'name' => 'محمد علي',
            'phone' => '0934567890',
            'message' => 'مهتم بهذا العقار قيد الإنشاء',
            'status' => 'pending',
        ]);

        // Approved Requests (4)
        ContactRequest::create([
            'property_id' => $properties[4]->id,
            'user_id' => $user1->id,
            'owner_id' => $owner1->id,
            'name' => 'سارة أحمد',
            'phone' => '0945678901',
            'message' => 'مهتم بالإيجار، متاح من متى؟',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[7]->id,
            'user_id' => $user2->id,
            'owner_id' => $owner2->id,
            'name' => 'عمر خالد',
            'phone' => '0956789012',
            'message' => 'أريد زيارة المزرعة',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[1]->id,
            'user_id' => $user3->id,
            'owner_id' => $owner1->id,
            'name' => 'ياسر محمد',
            'phone' => '0967890123',
            'message' => 'ما هو السعر النهائي للفيلا؟',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[5]->id,
            'user_id' => $user1->id,
            'owner_id' => $owner2->id,
            'name' => 'رامي سامر',
            'phone' => '0978901234',
            'message' => 'أريد فتح محل في هذا الموقع',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[7]->id,
            'user_id' => $user2->id,
            'owner_id' => $owner2->id,
            'name' => 'عمر خالد',
            'phone' => '0956789012',
            'message' => 'أريد زيارة المزرعة',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[1]->id,
            'user_id' => $user3->id,
            'owner_id' => $owner1->id,
            'name' => 'ياسر محمد',
            'phone' => '0967890123',
            'message' => 'ما هو السعر النهائي للفيلا؟',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[5]->id,
            'user_id' => $user1->id,
            'owner_id' => $owner2->id,
            'name' => 'رامي سامر',
            'phone' => '0978901234',
            'message' => 'أريد فتح محل في هذا الموقع',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Rejected Requests (2)
        ContactRequest::create([
            'property_id' => $properties[10]->id,
            'user_id' => $user2->id,
            'owner_id' => $owner1->id,
            'name' => 'تامر عمر',
            'phone' => '0989012345',
            'message' => 'هل يمكنني زيارة الأرض؟',
            'status' => 'rejected',
            'rejection_reason' => 'العقار غير متاح للبيع حالياً',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ContactRequest::create([
            'property_id' => $properties[6]->id,
            'user_id' => $user3->id,
            'owner_id' => $owner1->id,
            'name' => 'وسيم علي',
            'phone' => '0990123456',
            'message' => 'أريد معرفة تفاصيل أكثر',
            'status' => 'rejected',
            'rejection_reason' => 'المعلومات غير مكتملة',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->command->info('✅ Contact requests created (9 total)');

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
