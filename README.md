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
- Maksimal peminjaman adalah 2 unit per anggota.
- Durasi peminjaman maksimal 5 hari.
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

Anggota memilih tanggal/jam mulai dan selesai pada halaman detail unit. Seluruh input, validasi, penyimpanan jadwal, dan tampilan menggunakan **WIB (`Asia/Jakarta`)**, terlepas dari zona waktu perangkat. Mulai tidak boleh di masa lalu; selesai harus setelah mulai; durasi maksimal **5 hari (120 jam)**. Maksimal **2 transaksi aktif**, termasuk reservasi mendatang, per anggota.

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

