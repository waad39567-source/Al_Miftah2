<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class SyriaRegionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء/جلب سوريا كـ country
        $syria = Region::updateOrCreate(
            ['name' => 'سوريا', 'type' => 'country'],
            ['parent_id' => null]
        );

        // 2. قراءة ملف CSV
        $csvPath = base_path('syrian_towns.csv');
        $rows = array_map('str_getcsv', file($csvPath));
        array_shift($rows);

        // 3. متغيرات لتتبع السجلات
        $governorates = [];
        $cities = [];

        // 4. معالجة كل سطر
        foreach ($rows as $row) {
            $governorateName = trim($row[0]);
            $cityName = trim($row[1]);
            $neighborhoodName = trim($row[2]);

            // إنشاء/جلب المحافظة
            if (!isset($governorates[$governorateName])) {
                $governorate = Region::updateOrCreate(
                    ['name' => $governorateName, 'type' => 'governorate'],
                    ['parent_id' => $syria->id]
                );
                $governorates[$governorateName] = $governorate->id;
            }

            // إنشاء/جلب المدينة
            $cityKey = $governorateName . '|' . $cityName;
            if (!isset($cities[$cityKey])) {
                $city = Region::updateOrCreate(
                    ['name' => $cityName, 'type' => 'city'],
                    ['parent_id' => $governorates[$governorateName]]
                );
                $cities[$cityKey] = $city->id;
            }

            // إنشاء الحي
            Region::updateOrCreate(
                ['name' => $neighborhoodName, 'type' => 'neighborhood'],
                ['parent_id' => $cities[$cityKey]]
            );
        }

        // 5. معلومات
        $this->command->info('تم تعبئة المناطق بنجاح!');
        $this->command->info('عدد المحافظات: ' . count($governorates));
        $this->command->info('عدد المدن: ' . count($cities));
        $this->command->info('إجمالي السطور من CSV: ' . count($rows));
    }
}
