<?php

namespace Database\Seeders;

use App\Models\Term;
use Illuminate\Database\Seeder;

class TermsSeeder extends Seeder
{
    public function run(): void
    {
        Term::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'الشروط و الأحكام',
                'content' => 'الشروط و الأحكام',
            ]
        );
    }
}
