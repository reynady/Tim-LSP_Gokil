# Sistem Peminjaman Senjata Modern

## 1. Nama Kelompok

**LSP Gokil**

## 2. Nama Team

**LSP Gokil**

## 3. Nama Project

**Sistem Peminjaman Senjata Modern**

Sistem Peminjaman Senjata Modern merupakan aplikasi berbasis web yang digunakan untuk mengelola data unit, anggota, kategori, serta transaksi peminjaman dan pengembalian senjata.

Sistem memiliki dua jenis pengguna, yaitu **Admin** dan **Anggota**, dengan hak akses yang berbeda sesuai dengan kebutuhan sistem.

4. List Fitur

A. Autentikasi dan Hak Akses
- Login dan logout pengguna.
- Hak akses Admin dan Anggota.
- Validasi akun saat login.
- Pembatasan akses halaman berdasarkan peran pengguna.

B. Fitur Anggota
- Melihat dan mengubah profil pribadi.
- Mencari unit berdasarkan nama.
- Melihat informasi dan ketersediaan unit.
- Melakukan peminjaman maksimal 2 unit.
- Melihat daftar peminjaman milik sendiri.
- Melihat status dan riwayat peminjaman.

C. Fitur Admin
- Mengelola data unit (tambah, lihat, ubah, dan hapus).
- Mengelola kategori unit.
- Mengelola data anggota.
- Melihat seluruh transaksi peminjaman.
- Memproses pengembalian unit.
- Melihat dan mencetak riwayat peminjaman anggota.
- Mengelola denda keterlambatan.

D. Aturan Peminjaman
- Anggota harus terdaftar untuk melakukan peminjaman.
- Setiap unit memiliki kode unik.
- Satu unit dapat memiliki lebih dari satu kategori.
- Batas awal peminjaman adalah 2 transaksi aktif per anggota dan durasi maksimal 5 hari; Admin dapat mengaturnya pada menu Aturan Peminjaman.
- Peminjaman yang terlambat dikembalikan dikenakan denda.
- Pengembalian diproses oleh Admin.

E. Validasi dan Database

- Validasi input pada setiap formulir yang diperlukan.
- Penerapan relasi antar tabel database.
- Laravel Migration untuk struktur database.
- Seeder untuk data awal, seperti akun Admin, kategori, dan unit.

## 5. Screen Capture Skema Database
<img width="920" height="1795" alt="ERD_peminjaman" src="https://github.com/user-attachments/assets/be66ce12-6d9d-4f53-8468-d66e5251b806" />




## 6. Screen Recording Demo Penjelasan Website

## 7. Penjadwalan Peminjaman

Anggota memilih tanggal/jam mulai dan selesai pada halaman detail unit. Seluruh input, validasi, penyimpanan jadwal, dan tampilan menggunakan **WIB (`Asia/Jakarta`)**, terlepas dari zona waktu perangkat. Mulai tidak boleh di masa lalu; selesai harus setelah mulai. Batas awal durasi adalah **5 hari (120 jam)** dan maksimal **2 transaksi aktif**, termasuk reservasi mendatang, per anggota; Admin dapat mengubah kedua nilai tersebut.

Setiap record `monsters` adalah satu unit berkode unik, bukan produk dengan kuantitas stok. Karena itu ketersediaan jadwal bernilai 0 atau 1; tidak ada tabel stok atau sistem peminjaman baru. Status `borrowed` mencakup reservasi mendatang, bukan hanya penguasaan fisik saat ini. Unit dengan reservasi tetap bisa dipilih untuk jadwal lain; unit dalam perawatan atau ditandai dipinjam tanpa transaksi pendukung tetap tidak dapat dijadwalkan.

### Database dan pemasangan

Cadangkan database sebelum menjalankan:

```sh
php artisan config:clear
php artisan migrate
```

Migration `2026_10_26_000000_add_schedule_to_loans_table.php` menambahkan `loans.starts_at`, `loans.ends_at` (datetime WIB), dan indeks status/jadwal. Untuk data lama, mulai diisi **00.00 pada `loan_date`**, selesai **00.00 sehari setelah `due_date`**. Tanggal, status, dan timestamp audit lama tidak diubah. Kolom nullable mendukung impor/seeder lama; accessor model memberikan interpretasi yang sama saat nilainya kosong. Jadwal baru wajib lengkap melalui frontend dan backend. Rollback migration menghapus kedua kolom jadwal.

Zona waktu aplikasi berubah dari UTC menjadi WIB. Timestamp audit lama tidak dikonversi oleh migration; bila sistem lain membaca timestamp audit historis, tetap perhatikan bahwa nilai lama sebelumnya dicatat menurut konfigurasi UTC. Nomor transaksi baru mempertahankan awalan `AT-PJM-<tanggal>-` dengan akhiran ULID untuk menghindari benturan nomor pada permintaan bersamaan; nomor lama tidak berubah.

### Ketersediaan dan pengembalian

Jadwal bertabrakan bila `mulai_baru < selesai_lama` dan `selesai_baru > mulai_lama`. Jadwal yang bersentuhan tepat di batas diperbolehkan. Transaksi berstatus `active`, `borrowed`, `approved`, atau `pending` mengunci unit selama rentang jadwalnya jika detail belum dikembalikan; transaksi selesai/dibatalkan tidak mengunci. Jadwal yang telah lewat tidak diperpanjang otomatis walaupun pengembalian belum dicatat. Pengembalian fisik tetap diproses admin, dan status unit mempertimbangkan reservasi lain yang belum selesai. Perhitungan hari keterlambatan/denda yang sudah ada tetap berbasis tanggal dan tidak diubah menjadi tarif per jam.

Pratinjau ketersediaan tersedia melalui GET `/peminjaman/ketersediaan` untuk member terautentikasi. Penyimpanan selalu memeriksa ulang waktu mulai, kuota, dan jadwal di dalam transaksi. Baris member dan unit dikunci untuk database yang mendukung row lock; SQLite memperoleh writer lock sebelum membaca ketersediaan. Respons pratinjau bukan jaminan pemesanan sampai penyimpanan berhasil.

### Pengujian

```sh
php artisan test --compact
php artisan test --compact --filter LoanSchedule
node --test tests/Frontend/loan-schedule.test.mjs
```

Pengujian PHP biasa menggunakan SQLite in-memory. Uji konkurensi menggunakan dua proses PHP nyata yang mengakses satu database SQLite sementara, lalu membersihkannya; database aplikasi tidak dimigrasikan atau diubah oleh pengujian. Pengujian konkurensi khusus MySQL/PostgreSQL belum dijalankan.

Uji manual:
1. Login member dan buka katalog → detail unit. Pilih jadwal mendatang paling lama 120 jam; pastikan ringkasan dan jumlah tersedia tampil dalam WIB.
2. Konfirmasi, lalu periksa halaman berhasil, daftar aktif, riwayat/cetak, serta daftar/detail pengembalian admin.
3. Gunakan member lain untuk mencoba unit yang sama pada jadwal bertabrakan: harus ditolak. Mulai tepat pada jam selesai reservasi pertama: harus diterima jika kuota masih tersedia.
4. Coba tanggal/jam kosong, mulai di masa lalu, selesai sama/sebelum mulai, dan durasi lebih dari 120 jam: harus ditolak dengan pesan Indonesia. Backend tetap menolak jika validasi browser dilewati.
5. Buat dua reservasi mendatang milik satu member; transaksi ketiga ditolak. Kembalikan satu transaksi melalui admin lalu pastikan reservasi lain tetap tercatat dan unit tidak dilepas sebagai tanpa reservasi.
6. Ubah zona waktu perangkat ke luar WIB; input dan ringkasan harus tetap mengacu WIB.

## 8. Penggunaan Library Dompdf

Laporan riwayat admin dan anggota dibuat menjadi berkas PDF menggunakan **barryvdh/laravel-dompdf** (Dompdf), tercatat sebagai dependency di `composer.json` dan `composer.lock`. Library dipilih agar server menghasilkan PDF yang bisa langsung diunduh tanpa bergantung pada fitur cetak browser/Windows Print; dibanding membangun generator PDF sendiri, integrasi ini mengurangi kode format dan menyediakan pengaturan kertas melalui Laravel.

Integrasi menggunakan facade `Barryvdh\DomPDF\Facade\Pdf` pada controller riwayat dan template khusus `resources/views/shared/pdf-history.blade.php`. Dokumen diatur ke kertas A4 lanskap dan dikirim sebagai attachment. Pengujian `tests/Feature/PrintHistoryTest.php` memeriksa tipe konten, nama file, dan signature PDF.

Limitasi: Dompdf tidak mendukung seluruh fitur CSS/browser dan rendering JavaScript, sehingga template PDF menggunakan CSS sederhana tanpa aset eksternal. Untuk laporan yang sangat besar, pembuatan PDF dapat memakai memori dan waktu server lebih besar; laporan saat ini memuat seluruh riwayat sesuai cakupan pengguna.

## 9. Penggunaan SQL dan Konversi Mata Uang

- **DML:** operasi simpan, ubah, dan query memakai Eloquent/Query Builder pada controller, misalnya `LoanController::store` untuk `INSERT`, dan proses pengembalian admin untuk `UPDATE`/query.
- **Index:** migration jadwal menambahkan indeks gabungan `loans(status, starts_at, ends_at)`; migration tabel lain juga mendefinisikan indeks dan unique key.
- **Commit/rollback:** `DB::transaction` membungkus proses peminjaman dan pengembalian. Laravel melakukan commit jika callback berhasil dan rollback jika terjadi exception.
- **View, stored procedure, SQL function, trigger:** belum diimplementasikan dalam database proyek. `CurrencyConverter::convertFromRupiah` adalah fungsi aplikasi PHP yang memanggil Frankfurter, bukan SQL function/stored function.

Nominal denda disimpan dan dihitung dalam **Rupiah**, dengan tarif default **Rp25.000 per hari**. Migration `2026_10_27_100000_convert_fines_from_ether_to_rupiah.php` mengalikan nominal lama 1.000 kali agar nilainya mengikuti tarif Rupiah baru. Tombol konversi di halaman denda admin dan riwayat anggota memanggil endpoint terautentikasi yang mengambil kurs IDR ke USD, EUR, GBP, JPY, SGD, AUD, CAD, atau MYR dari Frankfurter. Kurs dicache selama satu jam; hasil konversi hanya perkiraan sesuai tanggal kurs API dan tidak mengubah nominal yang disimpan. Jika API gagal, halaman menampilkan pesan bahwa kurs tidak tersedia dan nominal Rupiah tetap menjadi sumber utama.

Saat pengembalian terlambat, admin wajib mencentang konfirmasi bahwa denda sudah diverifikasi lunas sebelum transaksi bisa diselesaikan. Setelah dikonfirmasi, denda disimpan dengan status `paid` dan waktu `paid_at`; aplikasi tidak memproses pembayaran atau menghubungi penyedia pembayaran.

## 10. Algoritma dan Validasi Pendaftaran Anggota (J.620100.022.02)

Alur pendaftaran baru dibuat menjadi `pending` dan `is_active = false`. Login hanya berhasil jika kredensial valid, `approval_status` bernilai `approved`, dan akun aktif. Admin membuka **Daftar Anggota**, memfilter **Menunggu Validasi**, lalu memilih **Setujui** atau **Tolak**. Persetujuan mengubah status menjadi `approved` dan mengaktifkan akun; penolakan menyimpan status `rejected` dan akun tetap nonaktif. Akun seed lama/admin yang tidak memiliki pilihan status eksplisit tetap `approved` melalui default migration.

Implementasi utama ada di `AuthController::register` dan `AuthController::login`, `MemberController::reviewRegistration`, route `admin.members.review-registration`, serta kolom `users.approval_status` pada migration `2026_10_27_110000_add_approval_status_to_users_table.php`. `User` mendefinisikan konstanta status dan `canLogin()` sebagai fungsi keputusan.

Contoh data dan alur algoritma: `$validated` berupa array hasil validasi; nama/email/password berupa string; `approval_status` berupa string status; `is_active` berupa boolean. Percabangan login membandingkan kredensial, status validasi, dan status aktif sebelum memberi akses. Loop hasil query pada tampilan anggota memproses setiap baris, sedangkan pencarian nama/email menggunakan filter query dan daftar diurutkan dengan `orderBy('name')`.

Kompleksitas pendaftaran/validasi satu akun adalah O(1) selain validasi keunikan email yang bergantung pada indeks unique database. Pencarian substring tanpa indeks khusus dapat memindai N anggota (O(N)); pengurutan hasil umumnya O(N log N), tetapi algoritma fisik dan biayanya ditentukan query planner/database. Hasil pengujian workflow ada di `tests/Feature/AuthRegistrationTest.php`.

## 11. Pengaturan Batas Peminjaman

Admin dapat mengatur jumlah maksimal peminjaman aktif per anggota dan durasi setiap peminjaman melalui **Aturan peminjaman** di sidebar. Kedua nilai harus berupa bilangan bulat minimal 1, tanpa batas maksimum aplikasi. Nilai awal tetap 2 transaksi aktif dan 5 hari. Aturan disimpan pada tabel `loan_policies`, diperiksa kembali oleh backend ketika anggota mengajukan peminjaman, dan durasi yang sama dipakai untuk validasi jadwal di browser. Perubahan berlaku pada pengajuan baru; transaksi yang sudah tersimpan tidak diubah. Implementasi: `Admin\LoanPolicyController`, `LoanPolicy`, dan `Member\LoanController`; tes: `LoanPolicyTest` serta `LoanScheduleTest`.
