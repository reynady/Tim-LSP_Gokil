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
            [
                'monster_code' => 'KJ-002',
                'name' => 'Leviathan Prime',
                'description' => 'Monster laut berukuran kolosal bertipe anomali akuatik.',
                'condition' => 'Secured',
                'threat_level' => 'Extreme',
                'origin_type' => 'Kaiju',
                'status' => 'available',
                'categories' => ['Kaiju', 'Colossal'],
            ],
            [
                'monster_code' => 'SC-001',
                'name' => 'Shadow Crawler',
                'description' => 'Anomali bayangan yang aktif di ruang minim cahaya.',
                'condition' => 'Contained',
                'threat_level' => 'Moderate',
                'origin_type' => 'SCP',
                'status' => 'available',
                'categories' => ['SCP', 'Anomalous Entity'],
            ],
            [
                'monster_code' => 'HM-001',
                'name' => 'Iron Warden',
                'description' => 'Penjaga baja dengan kecerdasan mandiri dan pertahanan tinggi.',
                'condition' => 'Ready',
                'threat_level' => 'High',
                'origin_type' => 'Humanoid',
                'status' => 'available',
                'categories' => ['Humanoid'],
            ],
            [
                'monster_code' => 'AN-002',
                'name' => 'Aether Wraith',
                'description' => 'Entitas gas berenergi murni yang memerlukan medan penahan.',
                'condition' => 'Contained',
                'threat_level' => 'Critical',
                'origin_type' => 'Anomaly',
                'status' => 'available',
                'categories' => ['Anomalous Entity'],
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