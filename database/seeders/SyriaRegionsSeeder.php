<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class SyriaRegionsSeeder extends Seeder
{
    public function run(): void
    {
        $syria = Region::create([
            'name' => 'سوريا',
            'type' => 'country',
            'parent_id' => null
        ]);

        $governorates = [
            'دمشق' => 'Damascus',
            'ريف دمشق' => 'Rural Damascus',
            'حلب' => 'Aleppo',
            'حمص' => 'Homs',
            'اللاذقية' => 'Latakia',
            'حماه' => 'Hama',
            'طرطوس' => 'Tartus',
            'إدلب' => 'Idlib',
            'درعا' => 'Daraa',
            'السويداء' => 'Al-Sweida',
            'القنيطرة' => 'Quneitra',
            'دير الزور' => 'Deir Ezzor',
            'الرقة' => 'Raqqa',
            'الحسكة' => 'Hasaka',
        ];

        $damascusNeighborhoods = [
            'المزة', 'باب توما', 'الرويحة', 'الشاغور', 'القيمرية', 'الدميرية',
            'الميدان', 'الحريقة', 'البرامكة', 'المرج', 'ركن الدين', 'الصالحية',
            'الشهداء', 'اليرموك', 'كفرسوسة', 'المستودع', 'العدوي', 'جسرови',
            'العباسيين', 'الحاجز', 'باب شرقي', 'باب صل无能', 'الكلاسة', 'النوفرة',
            'القدم', 'يلدا', 'ببيلا', 'بيت سحم', 'يلدا', 'رأس النبع',
            'خلف الرازي', 'الس具و', 'خاني شيخ', 'المالكية', 'الجرمانا',
            'مخيم اليرموك', 'شارع الرشيد', 'الخضر', 'وادي الشاطئ', 'النزهة'
        ];

        $governorateIds = [];

        foreach ($governorates as $name => $nameEn) {
            $governorate = Region::create([
                'name' => $name,
                'type' => 'governorate',
                'parent_id' => $syria->id
            ]);
            $governorateIds[$name] = $governorate->id;

            if ($name === 'دمشق') {
                $damascusCity = Region::create([
                    'name' => 'مدينة دمشق',
                    'type' => 'city',
                    'parent_id' => $governorate->id
                ]);

                foreach ($damascusNeighborhoods as $neighborhood) {
                    Region::create([
                        'name' => $neighborhood,
                        'type' => 'neighborhood',
                        'parent_id' => $damascusCity->id
                    ]);
                }
            }
        }

        $this->command->info('تم تعبئة المناطق بنجاح!');
        $this->command->info('عدد المحافظات: ' . count($governorates));
        $this->command->info('عدد أحياء دمشق: ' . count($damascusNeighborhoods));
    }
}
