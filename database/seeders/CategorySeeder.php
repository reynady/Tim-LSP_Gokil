<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kaiju', 'description' => 'Monster raksasa fiktif'],
            ['name' => 'SCP', 'description' => 'Makhluk anomali fiktif'],
            ['name' => 'Humanoid', 'description' => 'Monster berbentuk humanoid'],
            ['name' => 'Colossal', 'description' => 'Monster berukuran kolosal'],
            ['name' => 'Anomalous Entity', 'description' => 'Entitas dengan karakteristik anomali'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}