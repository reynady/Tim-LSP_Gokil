<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name'      => 'Anggota Dummy',
                'email'     => 'anggota@arsenaltrack.test',
                'password'  => Hash::make('Anggota123!'),
                'role'      => 'member',
                'is_active' => true,
                'phone'     => '0812-3456-7890',
                'address'   => 'Asrama Kadet Sektor A, Gedung 01',
            ],
            [
                'name'      => 'Raka Pratama',
                'email'     => 'raka.pratama@arsenaltrack.test',
                'password'  => Hash::make('Password123!'),
                'role'      => 'member',
                'is_active' => true,
                'phone'     => '0812-9876-5432',
                'address'   => 'Kompleks Fasilitas Hanggar B-12',
            ],
            [
                'name'      => 'Siti Rahmawati',
                'email'     => 'siti.rahma@arsenaltrack.test',
                'password'  => Hash::make('Password123!'),
                'role'      => 'member',
                'is_active' => true,
                'phone'     => '0856-1234-5678',
                'address'   => 'Pusat Riset Lapangan Arunika',
            ],
            [
                'name'      => 'Budi Santoso',
                'email'     => 'budi.santoso@arsenaltrack.test',
                'password'  => Hash::make('Password123!'),
                'role'      => 'member',
                'is_active' => false,
                'phone'     => '0813-7788-9900',
                'address'   => 'Sektor Karantina C-03',
            ],
            [
                'name'      => 'Nadia Safitri',
                'email'     => 'nadia.safitri@arsenaltrack.test',
                'password'  => Hash::make('Password123!'),
                'role'      => 'member',
                'is_active' => true,
                'phone'     => '0878-1122-3344',
                'address'   => 'Hanggar Pertahanan 02',
            ],
            [
                'name'      => 'Dimas Arya Wijaya',
                'email'     => 'dimas.arya@arsenaltrack.test',
                'password'  => Hash::make('Password123!'),
                'role'      => 'member',
                'is_active' => true,
                'phone'     => '0821-5566-7788',
                'address'   => 'Divisi Pemantauan Wilayah D-05',
            ],
        ];

        foreach ($members as $data) {
            $phone = $data['phone'];
            $address = $data['address'];
            unset($data['phone'], $data['address']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'   => $phone,
                    'address' => $address,
                ]
            );
        }
    }
}
