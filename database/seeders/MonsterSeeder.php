<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Monster;
use Illuminate\Database\Seeder;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        $monsters = [
            [
                'monster_code' => 'KJ-001',
                'name' => 'Titanus Gojira',
                'description' => 'Monster raksasa fiktif bertema Kaiju.',
                'condition' => 'Contained',
                'threat_level' => 'Extreme',
                'origin_type' => 'Kaiju',
                'status' => 'available',
                'categories' => ['Kaiju', 'Colossal'],
            ],
            [
                'monster_code' => 'AN-001',
                'name' => 'The Pale Wanderer',
                'description' => 'Entitas anomali humanoid fiktif.',
                'condition' => 'Contained',
                'threat_level' => 'High',
                'origin_type' => 'Anomaly',
                'status' => 'available',
                'categories' => ['Humanoid', 'Anomalous Entity'],
            ],
        ];

        foreach ($monsters as $data) {
            $categoryNames = $data['categories'];
            unset($data['categories']);

            $monster = Monster::updateOrCreate(
                ['monster_code' => $data['monster_code']],
                $data
            );

            $categoryIds = Category::whereIn('name', $categoryNames)
                ->pluck('id');

            $monster->categories()->sync($categoryIds);
        }
    }
}