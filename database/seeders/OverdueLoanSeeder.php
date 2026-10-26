<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Monster;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OverdueLoanSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $admin = User::where('role', 'admin')->first();

        // 1. Pastikan kategori dasar ada
        $category = Category::firstOrCreate(
            ['name' => 'Peraga & Simulasi'],
            ['description' => 'Unit perlengkapan dan simulasi latihan operasional']
        );

        // 2. Pastikan unit / monster sesuai mockup ada
        $unitsData = [
            [
                'monster_code' => 'AT-SM-006',
                'name'         => 'Simulator latihan C',
                'description'  => 'Perangkat modul simulasi latihan taktikal C.',
                'condition'    => 'Ready',
                'threat_level' => 'Moderate',
                'origin_type'  => 'Simulator',
                'status'       => 'borrowed',
            ],
            [
                'monster_code' => 'AT-RP-011',
                'name'         => 'Replika peraga D',
                'description'  => 'Replika peraga taktis untuk pelatihan lapangan D.',
                'condition'    => 'Contained',
                'threat_level' => 'Low',
                'origin_type'  => 'Replika',
                'status'       => 'borrowed',
            ],
            [
                'monster_code' => 'AT-PL-015',
                'name'         => 'Rompi peraga',
                'description'  => 'Rompi taktis peraga perlindungan latihan.',
                'condition'    => 'Ready',
                'threat_level' => 'Low',
                'origin_type'  => 'Peraga',
                'status'       => 'available',
            ],
            [
                'monster_code' => 'AT-KS-003',
                'name'         => 'Kotak penyimpanan',
                'description'  => 'Kotak penyimpanan material dan modul keamanan.',
                'condition'    => 'Secured',
                'threat_level' => 'Low',
                'origin_type'  => 'Penyimpanan',
                'status'       => 'available',
            ],
        ];

        $monsters = [];
        foreach ($unitsData as $u) {
            $m = Monster::updateOrCreate(
                ['monster_code' => $u['monster_code']],
                $u
            );
            $m->categories()->syncWithoutDetaching([$category->id]);
            $monsters[$u['monster_code']] = $m;
        }

        // 3. Pastikan anggota sesuai mockup ada
        $membersData = [
            [
                'name'  => 'Nadia Putri',
                'email' => 'nadia.putri@arsenaltrack.test',
            ],
            [
                'name'  => 'Dimas Saputra',
                'email' => 'dimas.saputra@arsenaltrack.test',
            ],
            [
                'name'  => 'Raka Pratama',
                'email' => 'raka.pratama@arsenaltrack.test',
            ],
        ];

        $users = [];
        foreach ($membersData as $mb) {
            $u = User::updateOrCreate(
                ['email' => $mb['email']],
                [
                    'name'      => $mb['name'],
                    'password'  => Hash::make('Password123!'),
                    'role'      => 'member',
                    'is_active' => true,
                ]
            );
            $u->profile()->updateOrCreate(
                ['user_id' => $u->id],
                [
                    'phone'   => '0812-3456-7890',
                    'address' => 'Asrama Pelatihan Sektor Timur',
                ]
            );
            $users[$mb['name']] = $u;
        }

        // 4. Buat Peminjaman Aktif Terlambat (Active Overdue)
        // Transaksi 1: AT-PJM-261017-012, Nadia Putri, Simulator latihan C, terlambat 2 hari
        $due1 = $today->copy()->subDays(2);
        $loanDate1 = $due1->copy()->subDays(5);

        $loan1 = Loan::updateOrCreate(
            ['loan_code' => 'AT-PJM-261017-012'],
            [
                'user_id'   => $users['Nadia Putri']->id,
                'loan_date' => $loanDate1,
                'due_date'  => $due1,
                'status'    => 'borrowed',
                'notes'     => 'Peminjaman unit simulator latihan C.',
            ]
        );
        LoanDetail::updateOrCreate(
            ['loan_id' => $loan1->id],
            [
                'monster_id'  => $monsters['AT-SM-006']->id,
                'returned_at' => null,
            ]
        );

        // Transaksi 2: AT-PJM-261018-013, Dimas Saputra, Replika peraga D, terlambat 1 hari
        $due2 = $today->copy()->subDays(1);
        $loanDate2 = $due2->copy()->subDays(5);

        $loan2 = Loan::updateOrCreate(
            ['loan_code' => 'AT-PJM-261018-013'],
            [
                'user_id'   => $users['Dimas Saputra']->id,
                'loan_date' => $loanDate2,
                'due_date'  => $due2,
                'status'    => 'borrowed',
                'notes'     => 'Peminjaman unit replika peraga D.',
            ]
        );
        LoanDetail::updateOrCreate(
            ['loan_id' => $loan2->id],
            [
                'monster_id'  => $monsters['AT-RP-011']->id,
                'returned_at' => null,
            ]
        );

        // 5. Buat Riwayat Peminjaman Selesai Terlambat (Returned Overdue)
        // Transaksi 3: AT-PJM-261008-007, Dimas Saputra, Rompi peraga, due 13 Okt, returned 14 Okt (1 hari)
        $due3 = Carbon::parse('2026-10-13');
        $returned3 = Carbon::parse('2026-10-14 10:30:00');

        $loan3 = Loan::updateOrCreate(
            ['loan_code' => 'AT-PJM-261008-007'],
            [
                'user_id'     => $users['Dimas Saputra']->id,
                'returned_by' => $admin?->id,
                'loan_date'   => Carbon::parse('2026-10-08'),
                'due_date'    => $due3,
                'status'      => 'returned',
                'notes'       => 'Peminjaman rompi peraga taktis.',
            ]
        );
        $detail3 = LoanDetail::updateOrCreate(
            ['loan_id' => $loan3->id],
            [
                'monster_id'  => $monsters['AT-PL-015']->id,
                'returned_at' => $returned3,
            ]
        );
        Fine::updateOrCreate(
            ['loan_detail_id' => $detail3->id],
            [
                'amount'       => Fine::amountForOverdueDays(1),
                'overdue_days' => 1,
                'status'       => 'waived',
            ]
        );

        // Transaksi 4: AT-PJM-261001-005, Raka Pratama, Kotak penyimpanan, due 6 Okt, returned 8 Okt (2 hari)
        $due4 = Carbon::parse('2026-10-06');
        $returned4 = Carbon::parse('2026-10-08 14:15:00');

        $loan4 = Loan::updateOrCreate(
            ['loan_code' => 'AT-PJM-261001-005'],
            [
                'user_id'     => $users['Raka Pratama']->id,
                'returned_by' => $admin?->id,
                'loan_date'   => Carbon::parse('2026-10-01'),
                'due_date'    => $due4,
                'status'      => 'returned',
                'notes'       => 'Peminjaman kotak penyimpanan modul.',
            ]
        );
        $detail4 = LoanDetail::updateOrCreate(
            ['loan_id' => $loan4->id],
            [
                'monster_id'  => $monsters['AT-KS-003']->id,
                'returned_at' => $returned4,
            ]
        );
        Fine::updateOrCreate(
            ['loan_detail_id' => $detail4->id],
            [
                'amount'       => Fine::amountForOverdueDays(2),
                'overdue_days' => 2,
                'status'       => 'waived',
            ]
        );

        // Transaksi 5: contoh riwayat dengan denda setelah melewati masa tenggang 5 hari.
        $due5 = Carbon::today()->subDays(8);
        $returned5 = Carbon::today()->subDay()->setTime(14, 0);
        $loan5 = Loan::updateOrCreate(
            ['loan_code' => 'AT-PJM-DEMO-FINE-001'],
            [
                'user_id'     => $users['Raka Pratama']->id,
                'returned_by' => $admin?->id,
                'loan_date'   => $due5->copy()->subDays(5),
                'due_date'    => $due5,
                'status'      => 'returned',
                'notes'       => 'Contoh riwayat pengembalian terlambat dengan denda Ether.',
            ]
        );
        $detail5 = LoanDetail::updateOrCreate(
            ['loan_id' => $loan5->id],
            [
                'monster_id'  => $monsters['AT-PL-015']->id,
                'returned_at' => $returned5,
            ]
        );
        Fine::updateOrCreate(
            ['loan_detail_id' => $detail5->id],
            [
                'amount'       => Fine::amountForOverdueDays(7),
                'overdue_days' => 7,
                'status'       => 'unpaid',
            ]
        );
    }
}
