# 📘 MANUAL BOOK

## MonsterTrack — Sistem Informasi Peminjaman dan Pengelolaan Monster Berbasis Web

**Tim LSP Gokil** | Versi 1.0 | 26 Oktober 2026

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Persyaratan Sistem](#2-persyaratan-sistem)
3. [Cara Mengakses Sistem](#3-cara-mengakses-sistem)
4. [Login dan Logout](#4-login-dan-logout)
5. [Panduan Admin](#5-panduan-admin)
   - 5.1 [Dashboard Admin](#51-dashboard-admin)
   - 5.2 [Kelola Data Anggota](#52-kelola-data-anggota)
   - 5.3 [Kelola Data Monster](#53-kelola-data-monster)
   - 5.4 [Kelola Kategori](#54-kelola-kategori)
   - 5.5 [Peminjaman Aktif](#55-peminjaman-aktif)
   - 5.6 [Proses Pengembalian](#56-proses-pengembalian)
   - 5.7 [Riwayat Peminjaman](#57-riwayat-peminjaman)
   - 5.8 [Cetak Riwayat](#58-cetak-riwayat)
6. [Panduan Anggota](#6-panduan-anggota)
   - 6.1 [Dashboard Anggota](#61-dashboard-anggota)
   - 6.2 [Katalog Monster](#62-katalog-monster)
   - 6.3 [Detail Monster & Peminjaman](#63-detail-monster--peminjaman)
   - 6.4 [Peminjaman Aktif Saya](#64-peminjaman-aktif-saya)
   - 6.5 [Riwayat Peminjaman Saya](#65-riwayat-peminjaman-saya)
   - 6.6 [Profil Saya](#66-profil-saya)
7. [Aturan Bisnis & Batasan Sistem](#7-aturan-bisnis--batasan-sistem)
8. [Pesan Error & Penanganannya](#8-pesan-error--penanganannya)
9. [Pertanyaan yang Sering Diajukan (FAQ)](#9-pertanyaan-yang-sering-diajukan-faq)
10. [Informasi Tim Pengembang](#10-informasi-tim-pengembang)

---

## 1. Pendahuluan

**MonsterTrack** adalah aplikasi web untuk mengelola inventaris dan proses peminjaman monster pada lembaga pelatihan pawang monster fiktif. Sistem ini menggantikan pencatatan manual dengan pencatatan digital yang terstruktur dan terpusat.

### Tujuan Sistem
- Mendigitalisasi pengelolaan inventaris monster
- Memudahkan Admin mengelola data monster, anggota, dan transaksi peminjaman
- Memudahkan anggota mencari dan meminjam monster yang tersedia
- Mencatat riwayat peminjaman secara akurat dan dapat ditelusuri
- Menerapkan aturan peminjaman (batas jumlah, batas waktu, denda) secara konsisten

### Peran Pengguna

| Peran | Deskripsi |
|---|---|
| **Admin** | Pengelola sistem. Dapat mengelola data anggota, monster, kategori, memproses pengembalian, melihat dan mencetak seluruh riwayat peminjaman. |
| **Anggota** | Peminjam monster. Dapat melihat katalog, meminjam monster, melihat peminjaman aktif dan riwayat miliknya sendiri. |

> **Catatan:** Seluruh data monster dan anggota dalam sistem ini bersifat fiktif untuk keperluan simulasi akademik.

---

## 2. Persyaratan Sistem

### Persyaratan Pengguna (Client)
| Komponen | Persyaratan Minimum |
|---|---|
| **Browser** | Google Chrome 90+, Mozilla Firefox 88+, Microsoft Edge 90+, Safari 14+ |
| **Koneksi Internet** | Diperlukan untuk mengakses aplikasi |
| **Resolusi Layar** | Minimum 1024 x 768 piksel |

### Persyaratan Server (untuk Tim Pengembang)
| Komponen | Versi |
|---|---|
| **PHP** | 8.1 atau lebih baru |
| **Laravel** | 10.x |
| **Database** | MySQL 8.0 / MariaDB 10.6+ |
| **Node.js** | 18 LTS (untuk build aset) |
| **Composer** | 2.x |

---

## 3. Cara Mengakses Sistem

1. Buka browser web Anda.
2. Ketikkan alamat URL sistem pada bilah alamat (URL akan diberikan oleh administrator sistem atau dosen pembimbing).
3. Anda akan otomatis diarahkan ke halaman **Login**.

> **Jika mengakses secara lokal (localhost):** Ketik `http://localhost:8000` atau sesuai konfigurasi server lokal.

---

## 4. Login dan Logout

### 4.1 Login

Login digunakan untuk masuk ke sistem dan mengakses fitur sesuai peran pengguna.

**Langkah-langkah Login:**

1. Buka halaman login (halaman utama sistem).
2. Masukkan **Email** pada kolom yang tersedia.
3. Masukkan **Password** pada kolom password (karakter akan disembunyikan).
4. Klik tombol **"Login"** atau tekan Enter.
5. Sistem akan memverifikasi kredensial:
   - Jika berhasil: Anda akan diarahkan ke **Dashboard** sesuai peran (Admin atau Anggota).
   - Jika gagal: Sistem menampilkan pesan kesalahan. Periksa kembali email dan password Anda.

**Ketentuan Login:**
- Email dan password wajib diisi (tidak boleh kosong).
- Pesan error tidak menyebutkan field mana yang salah (demi keamanan).
- Jika Anda mencoba mengakses halaman lain tanpa login, sistem akan mengalihkan Anda kembali ke halaman login.

**Akun Default (Seeder):**

| Peran | Email | Password |
|---|---|---|
| Admin | *(lihat file seeder atau hubungi pengembang)* | *(lihat file seeder)* |

> **Keamanan:** Password disimpan dalam bentuk terenkripsi (hashed) dan tidak dapat dilihat oleh siapapun, termasuk Admin.

---

### 4.2 Logout

Logout digunakan untuk mengakhiri sesi dan keluar dari sistem.

**Langkah-langkah Logout:**

1. Cari menu atau tombol **"Logout"** (biasanya terletak di pojok kanan atas halaman / sidebar navigasi).
2. Klik tombol **"Logout"**.
3. Sistem akan mengakhiri sesi Anda secara otomatis.
4. Anda akan diarahkan kembali ke halaman **Login**.

> **Penting:** Setelah logout, halaman-halaman yang memerlukan login tidak dapat diakses. Selalu logout setelah selesai menggunakan sistem, terutama pada komputer bersama.

---

## 5. Panduan Admin

Panduan ini ditujukan untuk pengguna dengan peran **Admin**.

### 5.1 Dashboard Admin

Setelah berhasil login, Admin akan diarahkan ke halaman **Dashboard Admin**.

Dashboard menampilkan:
- **Ringkasan statistik** sistem (jumlah monster tersedia, sedang dipinjam, jumlah anggota aktif, peminjaman aktif, dll.)
- **Menu navigasi** untuk mengakses seluruh fitur Admin

---

### 5.2 Kelola Data Anggota

Menu ini memungkinkan Admin untuk mengelola seluruh data akun anggota (CRUD: Create, Read, Update, Delete).

**Cara Membuka:** Klik menu **"Anggota"** atau **"Data Anggota"** pada navigasi Admin.

---

#### 5.2.1 Melihat Daftar Anggota

1. Buka menu **Anggota**.
2. Sistem menampilkan daftar seluruh anggota beserta informasi:
   - Nama
   - Email
   - Status akun (Aktif / Nonaktif)
   - Jumlah peminjaman aktif saat ini
3. **Mencari Anggota:** Gunakan kolom pencarian untuk mencari berdasarkan **nama** atau **email** anggota.
4. **Filter Status:** Gunakan filter untuk menampilkan anggota **Aktif** atau **Nonaktif** saja.
5. Data ditampilkan secara terpaginasi (10 data per halaman).

---

#### 5.2.2 Menambah Anggota Baru

> Hanya Admin yang dapat menambahkan anggota. Tidak ada registrasi mandiri oleh anggota.

1. Buka menu **Anggota**, klik tombol **"Tambah Anggota"** atau **"+ Anggota Baru"**.
2. Isi formulir dengan data berikut:

   | Field | Keterangan | Wajib? |
   |---|---|---|
   | **Nama** | Nama lengkap anggota | Ya |
   | **Email** | Alamat email unik, digunakan untuk login | Ya |
   | **Password** | Kata sandi (minimal 8 karakter) | Ya |
   | **Telepon** | Nomor telepon anggota | Tidak |
   | **Alamat** | Alamat tempat tinggal anggota | Tidak |
   | **Status Akun** | Aktif atau Nonaktif | Ya |

3. Klik tombol **"Simpan"** atau **"Tambah Anggota"**.
4. Sistem akan menyimpan data dan menampilkan pesan berhasil.

**Catatan Validasi:**
- Email harus unik -- tidak boleh sama dengan email yang sudah terdaftar.
- Password minimal 8 karakter.
- Setiap anggota baru secara otomatis memiliki satu profil terkait.

---

#### 5.2.3 Mengubah Data Anggota

1. Pada daftar anggota, cari anggota yang ingin diedit.
2. Klik tombol **"Edit"** pada baris anggota tersebut.
3. Ubah data yang diperlukan pada formulir edit.
   - **Password**: Biarkan kosong jika tidak ingin mengubah password lama.
4. Klik tombol **"Simpan Perubahan"** atau **"Update"**.
5. Sistem akan menyimpan perubahan dan menampilkan pesan berhasil.

---

#### 5.2.4 Menonaktifkan / Mengaktifkan Akun Anggota

Fitur **Toggle Status** memungkinkan Admin mengaktifkan atau menonaktifkan akun anggota dengan cepat tanpa menghapus data.

1. Pada daftar anggota, cari anggota yang ingin diubah statusnya.
2. Klik tombol **"Aktifkan"** atau **"Nonaktifkan"** (tergantung status saat ini).
3. Status akun anggota akan berubah dan sistem menampilkan pesan konfirmasi.

> Anggota dengan status **Nonaktif** tidak dapat melakukan peminjaman.

---

#### 5.2.5 Menghapus Anggota

1. Pada daftar anggota, cari anggota yang ingin dihapus.
2. Klik tombol **"Hapus"** pada baris anggota tersebut.
3. Konfirmasi penghapusan jika muncul dialog konfirmasi.
4. Sistem akan memproses penghapusan dengan ketentuan berikut:

| Kondisi | Hasil |
|---|---|
| Anggota **masih memiliki peminjaman aktif** | Penghapusan **ditolak**. Selesaikan peminjaman terlebih dahulu. |
| Anggota **memiliki riwayat peminjaman** (sudah selesai) | Akun **dinonaktifkan** (bukan dihapus permanen) agar riwayat transaksi tetap tersimpan. |
| Anggota **belum pernah melakukan peminjaman** | Data anggota **dihapus permanen** dari sistem. |

---

### 5.3 Kelola Data Monster

Menu ini memungkinkan Admin mengelola inventaris monster (CRUD).

**Cara Membuka:** Klik menu **"Monster"** atau **"Data Monster"** pada navigasi Admin.

---

#### 5.3.1 Melihat Daftar Monster

1. Buka menu **Monster**.
2. Sistem menampilkan daftar monster dengan informasi:
   - Kode Monster (ID unik, contoh: `SL-001`)
   - Nama Monster
   - Kategori
   - Tingkat Ancaman (Threat Level)
   - Status (Tersedia / Dipinjam / Pemeliharaan)
3. **Pencarian:** Cari monster berdasarkan **nama** atau **kode monster**.
4. **Filter:** Saring berdasarkan **status**, **kategori**, atau **tingkat ancaman**.
5. Data ditampilkan 10 monster per halaman.

---

#### 5.3.2 Menambah Monster Baru

> Pastikan minimal satu kategori sudah tersedia sebelum menambah monster.

1. Buka menu **Monster**, klik tombol **"Tambah Monster"** atau **"+ Monster Baru"**.
2. Isi formulir dengan data berikut:

   | Field | Keterangan | Wajib? |
   |---|---|---|
   | **Kode Monster** | Kode unik monster, contoh: `SL-001` | Ya |
   | **Nama Monster** | Nama monster (boleh sama dengan monster lain) | Ya |
   | **Deskripsi** | Keterangan singkat tentang monster | Tidak |
   | **Kondisi** | Kondisi fisik monster | Tidak |
   | **Tingkat Ancaman** | Low / Medium / High / Extreme / Unknown | Ya |
   | **Tipe Asal Usul** | Jenis/asal monster | Ya |
   | **Status** | Available (Tersedia) / Borrowed (Dipinjam) / Maintenance (Pemeliharaan) | Ya |
   | **Gambar** | URL gambar monster (opsional) | Tidak |
   | **Kategori** | Pilih satu atau lebih kategori | Ya (min. 1) |

3. Klik tombol **"Simpan"** atau **"Tambah Monster"**.

**Catatan Validasi:**
- Kode monster harus **unik** -- tidak boleh sama dengan kode yang sudah ada.
- Nama monster **boleh sama** dengan monster lain, dibedakan oleh kode.
- Monster wajib memiliki **minimal satu kategori**.
- Satu monster dapat memiliki **lebih dari satu kategori** (multi-kategori).

---

#### 5.3.3 Mengubah Data Monster

1. Pada daftar monster, klik tombol **"Edit"** pada baris monster yang ingin diubah.
2. Ubah data yang diperlukan.
3. Klik tombol **"Simpan Perubahan"** atau **"Update"**.

---

#### 5.3.4 Menghapus Monster

1. Pada daftar monster, klik tombol **"Hapus"** pada baris monster yang ingin dihapus.
2. Konfirmasi penghapusan.
3. Sistem memproses dengan ketentuan:

| Kondisi | Hasil |
|---|---|
| Monster **berstatus "Dipinjam"** | Penghapusan **ditolak**. Monster harus dikembalikan terlebih dahulu. |
| Monster **memiliki transaksi aktif** yang belum dikembalikan | Penghapusan **ditolak**. |
| Monster **aman untuk dihapus** | Monster dihapus beserta relasi kategorinya. |

> **Perhatian:** Penghapusan monster yang pernah dipinjam dapat memengaruhi integritas riwayat. Pertimbangkan mengubah status ke "Maintenance" daripada menghapus.

---

### 5.4 Kelola Kategori

Menu ini memungkinkan Admin mengelola kategori monster (CRUD).

**Cara Membuka:** Klik menu **"Kategori"** pada navigasi Admin.

---

#### 5.4.1 Melihat Daftar Kategori

1. Buka menu **Kategori**.
2. Sistem menampilkan daftar kategori beserta nama dan deskripsi (jika ada).

---

#### 5.4.2 Menambah Kategori Baru

1. Klik tombol **"Tambah Kategori"** atau **"+ Kategori Baru"**.
2. Isi **Nama Kategori** (wajib diisi, tidak boleh kosong).
3. Isi **Deskripsi** (opsional).
4. Klik **"Simpan"**.

**Catatan Validasi:**
- Nama kategori tidak boleh kosong.
- Nama kategori tidak boleh duplikat (case-insensitive).

---

#### 5.4.3 Mengubah Kategori

1. Klik tombol **"Edit"** pada kategori yang ingin diubah.
2. Ubah nama atau deskripsi.
3. Klik **"Simpan Perubahan"**.

---

#### 5.4.4 Menghapus Kategori

1. Klik tombol **"Hapus"** pada kategori yang ingin dihapus.
2. **Catatan:** Kategori yang masih digunakan oleh monster tidak dapat dihapus langsung. Hapus relasi kategori dari monster terlebih dahulu.

---

### 5.5 Peminjaman Aktif

Menu ini memungkinkan Admin melihat seluruh peminjaman yang sedang aktif (belum dikembalikan).

**Cara Membuka:** Klik menu **"Peminjaman Aktif"** pada navigasi Admin.

**Informasi yang Ditampilkan:**
- Kode Transaksi (contoh: `AT-PJM-261026-001`)
- Nama & Email Anggota
- Monster yang dipinjam (beserta kode monster)
- Tanggal Peminjaman
- Tanggal Jatuh Tempo
- Status (Aktif / Terlambat)

---

### 5.6 Proses Pengembalian

Pengembalian monster **hanya dapat diproses oleh Admin** melalui sistem. Anggota harus menghubungi Admin terlebih dahulu secara langsung (di luar sistem) untuk mengembalikan monster.

**Langkah-langkah Memproses Pengembalian:**

1. Buka menu **"Peminjaman Aktif"** atau cari transaksi yang ingin dikembalikan.
2. Identifikasi transaksi berdasarkan nama anggota atau kode monster.
3. Klik tombol **"Proses Pengembalian"** atau **"Kembalikan"** pada transaksi yang sesuai.
4. Sistem akan memproses secara otomatis:
   - Mencatat tanggal pengembalian
   - Memeriksa apakah terlambat (melewati jatuh tempo 5 hari)
   - Jika **terlambat**: Status menjadi "Terlambat" dan denda dicatat
   - Jika **tepat waktu**: Status menjadi "Selesai"
   - Status monster kembali menjadi **"Tersedia"**
   - Transaksi dipindahkan ke **Riwayat Peminjaman**

**Ketentuan Keterlambatan:**
- Jatuh tempo dihitung berdasarkan **hari kalender**: tanggal pinjam + 5 hari.
- Pengembalian **pada hari jatuh tempo** dianggap **tepat waktu** (tidak terlambat).
- Pengembalian **setelah jatuh tempo** dianggap **terlambat** dan dikenakan denda.

---

### 5.7 Riwayat Peminjaman

Menu ini menampilkan seluruh riwayat transaksi peminjaman yang sudah selesai.

**Cara Membuka:** Klik menu **"Riwayat"** atau **"Riwayat Peminjaman"** pada navigasi Admin.

**Informasi yang Ditampilkan:**
- Kode Transaksi
- Nama Anggota
- Monster yang dipinjam
- Tanggal Peminjaman & Pengembalian
- Status Akhir (Selesai / Terlambat)
- Informasi denda (jika ada keterlambatan)

**Filter yang Tersedia:**
- Filter berdasarkan periode tanggal
- Filter berdasarkan anggota
- Filter berdasarkan status

---

### 5.8 Cetak Riwayat

Admin dapat mencetak riwayat peminjaman untuk keperluan pelaporan.

**Langkah-langkah Mencetak:**

1. Buka menu **"Riwayat Peminjaman"**.
2. Terapkan filter jika diperlukan (misalnya periode tertentu).
3. Klik tombol **"Cetak"** atau **"Print"**.
4. Sistem akan menampilkan format cetak (print preview browser).
5. Gunakan dialog cetak browser untuk mencetak atau menyimpan sebagai PDF.

---

## 6. Panduan Anggota

Panduan ini ditujukan untuk pengguna dengan peran **Anggota**.

### 6.1 Dashboard Anggota

Setelah login, Anggota akan diarahkan ke halaman **Dashboard Anggota**.

Dashboard menampilkan:
- Ringkasan peminjaman aktif Anda
- Informasi sisa kuota peminjaman (maksimal 2 monster aktif)
- Akses cepat ke katalog monster dan peminjaman aktif

---

### 6.2 Katalog Monster

Halaman ini menampilkan seluruh daftar monster yang ada dalam inventaris sistem.

**Cara Membuka:** Klik menu **"Katalog"** atau **"Daftar Monster"** pada navigasi.

**Fitur yang Tersedia:**

1. **Melihat Daftar Monster:** Semua monster ditampilkan dengan informasi:
   - Kode Monster
   - Nama Monster
   - Kategori
   - Tingkat Ancaman
   - Status (Tersedia / Dipinjam / Pemeliharaan)

2. **Pencarian Monster:** Ketik nama atau kode monster pada kolom pencarian, kemudian tekan Enter atau klik ikon pencarian.
   - Jika kata kunci tidak ditemukan, sistem menampilkan pesan "Data tidak ditemukan".
   - Jika kolom pencarian dikosongkan, seluruh monster ditampilkan kembali.

3. **Filter Status:** Saring monster berdasarkan status:
   - **Semua** -- menampilkan seluruh monster
   - **Tersedia** -- hanya monster yang dapat dipinjam
   - **Dipinjam** -- monster yang sedang dipinjam

4. **Lihat Detail:** Klik pada kartu monster atau tombol **"Detail"** untuk melihat informasi lengkap dan melakukan peminjaman.

---

### 6.3 Detail Monster & Peminjaman

Halaman Detail Monster menampilkan informasi lengkap satu monster dan memungkinkan Anggota melakukan peminjaman.

**Informasi yang Ditampilkan:**
- Nama Monster
- Kode Monster (ID unik)
- Kategori
- Tingkat Ancaman
- Tipe Asal Usul
- Kondisi
- Deskripsi
- Status Ketersediaan
- Gambar (jika tersedia)

**Cara Meminjam Monster:**

Syarat Peminjaman:
- Anda harus terdaftar sebagai **anggota aktif**.
- Monster yang dipilih harus berstatus **"Tersedia"** (available).
- Anda tidak boleh memiliki lebih dari **2 peminjaman aktif** secara bersamaan.

1. Buka halaman **Detail** monster yang ingin dipinjam.
2. Pastikan status monster menunjukkan **"Tersedia"**.
3. Klik tombol **"Pinjam Monster"** atau **"Ajukan Peminjaman"**.
4. Sistem akan memvalidasi semua syarat secara otomatis.
5. Jika semua syarat terpenuhi, sistem mencatat transaksi peminjaman dengan:
   - **Tanggal Peminjaman:** Tanggal hari ini
   - **Tanggal Jatuh Tempo:** Tanggal hari ini + 5 hari
   - **Kode Transaksi:** Unik, contoh `AT-PJM-261026-001`
6. Anda akan diarahkan ke halaman **Konfirmasi Peminjaman Berhasil**.

**Halaman Konfirmasi Peminjaman Berhasil menampilkan:**
- Kode transaksi peminjaman
- Detail monster yang dipinjam
- Tanggal peminjaman dan tanggal jatuh tempo
- Sisa kuota peminjaman Anda
- Tombol untuk kembali ke katalog atau melihat peminjaman aktif

---

### 6.4 Peminjaman Aktif Saya

Halaman ini menampilkan daftar peminjaman Anda yang **belum dikembalikan**.

**Cara Membuka:** Klik menu **"Peminjaman Aktif"** atau **"Peminjaman Saya"** pada navigasi.

**Informasi yang Ditampilkan:**
- Kode Transaksi
- Monster yang dipinjam (nama dan kode)
- Tanggal Peminjaman
- Tanggal Jatuh Tempo
- Status (Aktif / Terlambat)
- Sisa hari sebelum jatuh tempo

**Cara Mengembalikan Monster:**

> **Penting:** Pengembalian **tidak dapat dilakukan sendiri** melalui sistem. Anda harus:
> 1. Hubungi Admin secara langsung (telepon, pesan, atau datang langsung).
> 2. Admin akan memproses pengembalian melalui sistem.

---

### 6.5 Riwayat Peminjaman Saya

Halaman ini menampilkan seluruh riwayat peminjaman Anda yang telah selesai (sudah dikembalikan).

**Cara Membuka:** Klik menu **"Riwayat"** atau **"Riwayat Peminjaman"** pada navigasi Anggota.

**Informasi yang Ditampilkan:**
- Kode Transaksi
- Monster yang dipinjam
- Tanggal Peminjaman
- Tanggal Pengembalian
- Status Akhir (Selesai / Terlambat)
- Informasi denda (jika ada)

> Anda **hanya dapat melihat riwayat milik sendiri**. Riwayat anggota lain tidak dapat diakses.

---

### 6.6 Profil Saya

Halaman ini menampilkan dan memungkinkan Anda mengubah data profil pribadi.

**Cara Membuka:** Klik menu **"Profil"** atau ikon profil pada navigasi.

**Data yang Dapat Dilihat & Diubah:**
- Nama lengkap
- Email (digunakan untuk login)
- Telepon dan alamat

**Cara Mengubah Profil:**

1. Buka halaman **Profil**.
2. Klik tombol **"Edit Profil"** atau langsung ubah data pada form.
3. Ubah data yang diperlukan.
4. Klik tombol **"Simpan"** atau **"Perbarui Profil"**.
5. Sistem menampilkan pesan berhasil.

**Ketentuan:**
- Anda hanya dapat mengubah **profil milik sendiri**.
- Nama wajib diisi (tidak boleh kosong).
- Email harus format yang valid dan belum digunakan akun lain.

---

## 7. Aturan Bisnis & Batasan Sistem

Berikut adalah aturan-aturan yang diterapkan sistem secara otomatis:

### Aturan Peminjaman

| Aturan | Penjelasan |
|---|---|
| **Wajib Login** | Semua fitur hanya dapat diakses setelah login. |
| **Hanya Anggota** | Hanya user dengan peran "Anggota" (member) yang dapat melakukan peminjaman. |
| **Batas Aktif: 2 Monster** | Setiap anggota maksimal memiliki **2 peminjaman aktif** secara bersamaan. |
| **Durasi Maksimal: 5 Hari** | Jatuh tempo peminjaman dihitung otomatis: tanggal pinjam + 5 hari kalender. |
| **Monster Harus Tersedia** | Hanya monster berstatus "Tersedia" (available) yang dapat dipinjam. |
| **Kode Monster Unik** | Setiap monster memiliki kode unik sebagai identitas. |
| **Multi-Kategori** | Satu monster dapat memiliki lebih dari satu kategori. |

### Aturan Pengembalian

| Aturan | Penjelasan |
|---|---|
| **Hanya Admin** | Pengembalian hanya dapat diproses oleh Admin melalui sistem. |
| **Anggota Menghubungi Admin** | Anggota harus menghubungi Admin secara langsung untuk proses pengembalian. |
| **Pengembalian Tepat Waktu** | Dikembalikan **pada atau sebelum** hari jatuh tempo = tidak terlambat. |
| **Keterlambatan = Denda** | Dikembalikan **setelah** hari jatuh tempo = terlambat dan dikenakan denda. |

### Aturan Keamanan

| Aturan | Penjelasan |
|---|---|
| **Isolasi Data** | Anggota hanya dapat mengakses data dan transaksi miliknya sendiri. |
| **Pembatasan Akses Admin** | Anggota tidak dapat mengakses halaman atau fitur Admin. |
| **Password Terenkripsi** | Password tidak pernah disimpan atau ditampilkan dalam bentuk teks biasa. |

### Status Monster

| Status | Keterangan |
|---|---|
| **Tersedia** (`available`) | Monster dapat dipinjam oleh anggota. |
| **Dipinjam** (`borrowed`) | Monster sedang dalam masa peminjaman. |
| **Pemeliharaan** (`maintenance`) | Monster tidak dapat dipinjam sementara waktu. |

### Kode Transaksi

Format kode transaksi peminjaman: **`AT-PJM-YYMMDD-XXX`**
- `AT-PJM` = prefix tetap
- `YYMMDD` = tanggal peminjaman (tahun 2 digit, bulan, hari)
- `XXX` = nomor urut hari itu (tiga digit, diisi nol di kiri)

Contoh: `AT-PJM-261026-001` = peminjaman pertama pada 26 Oktober 2026.

---

## 8. Pesan Error & Penanganannya

Berikut daftar pesan error yang mungkin muncul beserta cara mengatasinya:

### Error saat Login

| Pesan Error | Kemungkinan Penyebab | Solusi |
|---|---|---|
| "Kredensial tidak valid" / "Login gagal" | Email atau password salah | Periksa kembali email dan password. Pastikan Caps Lock tidak aktif. |
| "Field wajib diisi" | Email atau password tidak diisi | Isi kedua field sebelum klik Login. |
| Diarahkan ke halaman login | Belum login atau sesi berakhir | Login kembali ke sistem. |

### Error saat Peminjaman (Anggota)

| Pesan Error | Penyebab | Solusi |
|---|---|---|
| "Anda telah mencapai batas maksimal 2 entitas monster aktif" | Sudah memiliki 2 peminjaman aktif | Kembalikan salah satu monster (hubungi Admin) sebelum meminjam lagi. |
| "Monster ini sedang tidak tersedia atau sudah dipinjam" | Monster dipinjam atau berstatus maintenance | Pilih monster lain yang berstatus "Tersedia". |
| "Peminjaman ditolak: User belum terdaftar sebagai anggota" | Akun tidak memiliki peran "Anggota" | Hubungi Admin untuk memverifikasi status akun. |

### Error saat Kelola Monster (Admin)

| Pesan Error | Penyebab | Solusi |
|---|---|---|
| "Kode monster sudah terdaftar" | Kode yang dimasukkan sudah dipakai monster lain | Gunakan kode yang berbeda dan unik. |
| "Monster wajib memiliki minimal 1 (satu) kategori" | Tidak ada kategori yang dipilih | Pilih minimal satu kategori. |
| "Monster tidak dapat dihapus karena sedang berstatus Dipinjam" | Monster masih dipinjam | Proses pengembalian monster terlebih dahulu. |

### Error saat Kelola Anggota (Admin)

| Pesan Error | Penyebab | Solusi |
|---|---|---|
| "Email sudah terdaftar di sistem" | Email yang dimasukkan sudah digunakan akun lain | Gunakan email yang berbeda. |
| "Kata sandi minimal terdiri dari 8 karakter" | Password terlalu pendek | Gunakan password minimal 8 karakter. |
| "Anggota masih memiliki transaksi peminjaman aktif" | Anggota belum mengembalikan monster | Proses pengembalian semua monster anggota tersebut terlebih dahulu. |

### Error Akses

| Situasi | Penjelasan |
|---|---|
| Anggota mencoba membuka URL Admin | Sistem menampilkan halaman "403 Akses Ditolak" |
| Mengakses halaman tanpa login | Sistem mengalihkan ke halaman Login |
| Mencoba mengakses data milik user lain | Sistem menampilkan "403 Akses Tidak Diizinkan" |

---

## 9. Pertanyaan yang Sering Diajukan (FAQ)

**Q: Apakah saya bisa mendaftar sendiri sebagai anggota?**
> A: Tidak. Pendaftaran akun anggota hanya dapat dilakukan oleh Admin. Hubungi Admin untuk mendapatkan akun.

**Q: Saya lupa password, bagaimana cara mereset?**
> A: Hubungi Admin sistem. Admin dapat mengubah password Anda melalui fitur Edit Anggota.

**Q: Berapa lama saya bisa meminjam monster?**
> A: Maksimal **5 hari kalender** terhitung dari tanggal peminjaman. Jatuh tempo dihitung otomatis oleh sistem.

**Q: Bagaimana cara saya mengembalikan monster?**
> A: Anda **tidak bisa** memproses pengembalian sendiri melalui sistem. Hubungi Admin secara langsung (telepon/pesan/datang langsung), lalu Admin akan memproses pengembalian di sistem.

**Q: Berapa banyak monster yang bisa saya pinjam sekaligus?**
> A: Maksimal **2 monster** secara bersamaan (aktif). Anda baru bisa meminjam lagi setelah salah satu monster dikembalikan.

**Q: Apakah saya bisa melihat data anggota lain?**
> A: Tidak. Anda hanya dapat melihat profil dan riwayat peminjaman milik Anda sendiri.

**Q: Apa yang terjadi jika saya terlambat mengembalikan monster?**
> A: Sistem secara otomatis menandai peminjaman sebagai "Terlambat" saat diproses pengembaliannya, dan denda akan dicatat. Besaran denda ditentukan oleh kebijakan lembaga (konfirmasi ke Admin).

**Q: Bolehkah nama monster sama dengan monster lain?**
> A: Ya. Nama monster boleh sama, namun setiap monster memiliki **kode unik** yang membedakannya (contoh: `SL-001`, `SL-002`, `SL-003` untuk tiga monster bernama "Slime Latihan").

**Q: Satu monster bisa masuk berapa kategori?**
> A: Tidak terbatas. Satu monster dapat memiliki **lebih dari satu kategori** (multi-kategori), contoh: kategori "Air" sekaligus "Jinak".

**Q: Apakah Admin bisa meminjam monster?**
> A: Tidak. Peminjaman hanya dapat dilakukan oleh pengguna dengan peran **Anggota**.

**Q: Mengapa data anggota yang sudah dihapus masih muncul di riwayat?**
> A: Untuk menjaga integritas data, anggota yang memiliki riwayat transaksi tidak dihapus permanen, melainkan **dinonaktifkan**. Ini memastikan riwayat peminjaman tetap dapat ditelusuri.

---

## 10. Informasi Tim Pengembang

| Item | Keterangan |
|---|---|
| **Nama Tim** | LSP Gokil |
| **Nama Proyek** | MonsterTrack -- Sistem Informasi Peminjaman dan Pengelolaan Monster |
| **Versi Sistem** | 1.0 |
| **Tanggal Rilis** | Oktober 2026 |
| **Teknologi** | Laravel (PHP), MySQL, Blade Template, Vite |
| **Jenis Proyek** | Simulasi Akademik |

---

> **Catatan Penting:** MonsterTrack adalah sistem simulasi akademik. Seluruh data monster, anggota, dan transaksi bersifat fiktif dan hanya digunakan untuk keperluan latihan dan pembelajaran. Sistem tidak mencakup fitur jual beli, marketplace, panduan pertarungan, rekayasa/modifikasi, maupun pembiakan monster.

---

*Manual Book ini disusun berdasarkan Business Requirements Document (BRD) MonsterTrack v1.1 dan implementasi sistem aktual.*

*Versi dokumen: 1.0 | Terakhir diperbarui: 26 Oktober 2026*
