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

