<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'anggota@arsenaltrack.test'],
            [
                'name' => 'Anggota Dummy',
                'password' => Hash::make('Anggota123!'),
                'role' => 'member',
                'is_active' => true,
            ]
        );
    }
}
