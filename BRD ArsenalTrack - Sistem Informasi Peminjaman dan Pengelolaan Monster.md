# Business Requirements Document (BRD)

**MonsterTrack: Sistem Informasi Peminjaman dan Pengelolaan Monster Berbasis Web**

Dokumen Kebutuhan Bisnis untuk Proyek Akademik | Versi 1.1 | Status: Draft untuk Review

---

## 1. Cover

| Item | Keterangan |
| --- | --- |
| Nama Project | MonsterTrack |
| Judul | Sistem Informasi Peminjaman dan Pengelolaan Monster Berbasis Web |
| Jenis Dokumen | Business Requirements Document (BRD) |
| Konteks | Simulasi akademik pada lembaga pelatihan pawang monster fiktif |
| Versi | 1.1 |
| Tanggal | 23 Oktober 2026 |
| Bahasa | Indonesia |

> **Catatan penting:** MonsterTrack hanya merupakan simulasi manajemen inventaris dan peminjaman untuk kebutuhan akademik. Seluruh monster dan data bersifat fiktif/dummy. Sistem tidak memuat fitur jual beli, marketplace, panduan pertarungan atau taktik, rekayasa/modifikasi, maupun pembiakan monster.

## 2. Document Control

**Riwayat Revisi**

| Versi | Tanggal | Penulis | Perubahan |
| --- | --- | --- | --- |
| 0.1 | 22 Okt 2026 | Tim Proyek | Penyusunan draf awal |
| 1.0 | 22 Okt 2026 | Tim Proyek | Draf lengkap untuk review |
| 1.1 | 23 Okt 2026 | Tim Proyek | Tema proyek diganti menjadi peminjaman monster (nama, istilah, contoh data, dan ruang lingkup disesuaikan; aturan bisnis tidak berubah) |

**Persetujuan**

| Peran | Nama | Tanda Tangan | Tanggal |
| --- | --- | --- | --- |
| Project Owner / Dosen Pembimbing | (TBD) |  |  |
| Business Analyst | (TBD) |  |  |
| Ketua Tim Pengembang | (TBD) |  |  |

**Daftar Istilah**

| Istilah | Arti |
| --- | --- |
| Monster | Satu ekor monster fiktif yang tercatat dalam inventaris lembaga untuk latihan/simulasi, dengan kode unik (sebelumnya disebut "unit") |
| Kode Monster | Identitas unik satu monster, contoh `SL-001` |
| Anggota | Pengguna terdaftar yang berhak meminjam monster |
| Peminjaman Aktif | Peminjaman yang belum dikembalikan (belum diproses Admin) |
| Jatuh Tempo | Batas akhir pengembalian, maksimal 5 hari sejak tanggal peminjaman |
| TBD | To Be Decided, perlu keputusan project owner |
| BR / FR / NFR | Business Rule / Functional Requirement / Non-Functional Requirement |

**Perbedaan jenis requirement dalam dokumen ini**

| Jenis | Fokus | Penomoran |
| --- | --- | --- |
| Business Requirement | Tujuan dan kebutuhan bisnis yang ingin dicapai (Bagian 6) | BReq-xx |
| Functional Requirement | Apa yang harus dilakukan sistem | FR-xxx |
| Business Rule | Aturan/kebijakan yang membatasi perilaku sistem | BR-xxx |
| Non-Functional Requirement | Kualitas sistem (keamanan, performa, dll.) | NFR-xxx |

## 3. Project Overview

MonsterTrack adalah aplikasi web untuk mengelola inventaris dan proses peminjaman monster untuk kebutuhan latihan dan simulasi pada sebuah lembaga pelatihan pawang monster fiktif. Sistem menggantikan pencatatan manual dengan pencatatan digital yang terstruktur.

Sistem memiliki dua jenis pengguna:

1. **Admin**: mengelola data inventaris (monster dan kategori), anggota, peminjaman, pengembalian, denda, serta riwayat transaksi.
2. **Anggota/User**: melihat dan mencari monster yang tersedia, mengajukan peminjaman, melihat peminjaman yang sedang berlangsung, dan mengelola profil sendiri.

Sistem hanya menangani **manajemen data dan transaksi peminjaman**. Sistem tidak mengontrol perangkat fisik apa pun.

## 4. Background

Lembaga pelatihan pawang monster fiktif dalam proyek ini memiliki sejumlah monster yang dipinjamkan kepada anggota selama sesi latihan dan simulasi. Selama ini pencatatan dilakukan manual (buku catatan atau lembar kerja terpisah). Seiring bertambahnya jumlah monster dan anggota, cara ini menimbulkan kesulitan pelacakan, kesalahan pencatatan, dan lemahnya pengawasan batas peminjaman.

Pihak pengelola membutuhkan sistem berbasis web yang menyediakan satu sumber data terpusat tentang inventaris, anggota, dan peminjaman, sekaligus menerapkan aturan peminjaman (batas jumlah, batas waktu, denda) secara konsisten. Proyek ini dikerjakan sebagai tugas akademik dengan data dummy.

## 5. Business Problem

| No | Permasalahan | Dampak | Kebutuhan yang Menjawab |
| --- | --- | --- | --- |
| P-01 | Data inventaris monster tersebar dan sulit dilacak | Monster sulit ditemukan, data tidak konsisten | FR-005, FR-006, FR-007 |
| P-02 | Sulit mengetahui monster yang tersedia atau sedang dipinjam | Anggota tidak tahu monster mana yang bisa dipinjam, Admin harus mengecek manual | FR-005, FR-007, FR-008 |
| P-03 | Data anggota dan profil tidak terorganisir | Identitas peminjam sulit diverifikasi | FR-003, FR-004 |
| P-04 | Riwayat peminjaman sulit dicari | Penelusuran transaksi lama memakan waktu | FR-011, FR-012 |
| P-05 | Risiko peminjaman melebihi batas | Satu anggota dapat menguasai terlalu banyak monster | FR-008 (BR-010) |
| P-06 | Keterlambatan pengembalian sulit diketahui | Monster tertahan tanpa pengawasan | FR-009, FR-010 (BR-011, BR-012) |
| P-07 | Perhitungan denda tidak terdokumentasi | Denda tidak konsisten dan sulit dipertanggungjawabkan | FR-010 |
| P-08 | Kesalahan pencatatan manual | Data ganda, salah tulis, atau hilang | Seluruh modul |

## 6. Business Objective

| ID | Tujuan Bisnis (Business Requirement) | Ukuran Keberhasilan | Terkait |
| --- | --- | --- | --- |
| BReq-01 | Digitalisasi pengelolaan inventaris monster | Seluruh monster tercatat di sistem dengan kode unik dan status | FR-005, FR-006 |
| BReq-02 | Mempermudah Admin mengelola data monster | Admin dapat menambah, mengubah, menghapus, dan melihat monster tanpa pencatatan manual | FR-005 |
| BReq-03 | Mempermudah anggota mencari monster yang tersedia | Anggota dapat mencari berdasarkan nama dan melihat status monster | FR-007 |
| BReq-04 | Mengelola peminjaman dan pengembalian secara terstruktur | Setiap transaksi tercatat lengkap dengan tanggal dan status | FR-008, FR-009 |
| BReq-05 | Membatasi jumlah monster yang dapat dipinjam anggota | Tidak ada anggota dengan lebih dari 2 peminjaman aktif | FR-008 |
| BReq-06 | Mencatat riwayat peminjaman | Seluruh transaksi dapat ditelusuri | FR-011 |
| BReq-07 | Mengurangi kesalahan pencatatan manual | Status monster berubah otomatis mengikuti transaksi | FR-008, FR-009 |
| BReq-08 | Menyediakan data dan laporan peminjaman bagi Admin | Riwayat dapat dilihat dan dicetak | FR-011, FR-012 |

## 7. Stakeholder

| Stakeholder | Peran | Tanggung Jawab / Kepentingan |
| --- | --- | --- |
| **Admin** | Pengelola sistem dan inventaris | Mengelola data anggota, monster, dan kategori; memantau seluruh peminjaman; memproses pengembalian; memantau keterlambatan dan denda; melihat dan mencetak riwayat; menjaga keakuratan data |
| **Anggota/User** | Peminjam monster | Login; mengelola profil sendiri; melihat dan mencari monster; mengajukan peminjaman sesuai aturan; melihat peminjaman aktif dan riwayat miliknya; menghubungi Admin untuk pengembalian |
| **Project Owner / Dosen Pembimbing** | Pemilik kebijakan proyek | Menentukan keputusan bisnis yang masih TBD (nominal denda, detail identitas anggota); menyetujui BRD |
| **Tim Pengembang** | Pelaksana | Menerjemahkan BRD ke flowchart, ERD, PRD, desain, dan kode |

Stakeholder lain tidak ditambahkan karena tidak relevan dengan lingkup sistem.

## 8. User Roles

Hak akses dibatasi berdasarkan peran (role-based). Tanda ✔ berarti diizinkan, ✘ berarti tidak diizinkan.

| No | Hak Akses / Aktivitas | Admin | Anggota |
| --- | --- | --- | --- |
| 1 | Login | ✔ | ✔ |
| 2 | Melihat dan mengelola profil | ✔ (profil sendiri) | ✔ (hanya profil sendiri) |
| 3 | Melihat data anggota | ✔ | ✘ |
| 4 | Menambah, mengubah, menghapus anggota | ✔ | ✘ |
| 5 | Melihat daftar monster | ✔ | ✔ |
| 6 | Menambah, mengubah, menghapus monster | ✔ | ✘ |
| 7 | Melihat dan mencari monster berdasarkan nama | ✔ | ✔ |
| 8 | Melihat detail monster | ✔ | ✔ |
| 9 | Melihat kategori | ✔ | ✔ (sebagai bagian informasi monster) |
| 10 | Menambah, mengubah, menghapus kategori | ✔ | ✘ |
| 11 | Melakukan peminjaman | ✘ | ✔ (jika memenuhi ketentuan) |
| 12 | Melihat seluruh peminjaman / monster yang sedang dipinjam | ✔ | ✘ |
| 13 | Melihat monster yang sedang dipinjam oleh dirinya | ✘ (tidak relevan) | ✔ |
| 14 | Memproses pengembalian | ✔ | ✘ |
| 15 | Melihat riwayat seluruh peminjaman | ✔ | ✘ |
| 16 | Melihat riwayat peminjaman miliknya | ✘ (tidak relevan) | ✔ (jika diperlukan) |
| 17 | Melihat keterlambatan dan denda | ✔ | ✘ (TBD: apakah anggota boleh melihat denda miliknya) |
| 18 | Mencetak riwayat peminjaman | ✔ | ✘ |
| 19 | Mengakses fitur Admin | ✔ | ✘ |

**Anggota tidak dapat:** mengelola data anggota lain, mengelola monster, mengelola kategori, menghapus monster, memproses pengembalian secara langsung, melihat transaksi anggota lain, dan mengakses fitur Admin.

**TBD / perlu keputusan:** (a) apakah Admin boleh meminjam monster (diasumsikan tidak, sesuai BR-002), (b) apakah anggota dapat melihat denda miliknya sendiri (rekomendasi: boleh, bersifat read-only).

## 9. Scope

### 9.1 In Scope

| No | Fitur | Modul |
| --- | --- | --- |
| 1 | Login dan logout, validasi role | Authentication |
| 2 | Pengelolaan data anggota oleh Admin (CRUD) | User Management |
| 3 | Pengelolaan profil (satu user satu profil) | Profile Management |
| 4 | Pengelolaan monster (CRUD) dengan kode unik dan status | Monster Management |
| 5 | Pengelolaan kategori (CRUD) dan relasi many-to-many dengan monster | Category Management |
| 6 | Pencarian monster berdasarkan nama | Monster Search |
| 7 | Pengajuan dan pencatatan peminjaman dengan batas 2 monster aktif dan 5 hari | Loan Management |
| 8 | Pemrosesan pengembalian oleh Admin | Return Management |
| 9 | Penetapan keterlambatan dan pencatatan denda | Fine Management |
| 10 | Riwayat peminjaman (Admin: seluruhnya, Anggota: miliknya) | Loan History |
| 11 | Cetak riwayat peminjaman oleh Admin | Reporting / Printing |

### 9.2 Out of Scope

| No | Di Luar Lingkup | Alasan |
| --- | --- | --- |
| 1 | Jual beli monster dan marketplace | Bukan fungsi sistem peminjaman |
| 2 | Pembayaran online (termasuk pembayaran denda) | Denda hanya dicatat, pembayaran dilakukan di luar sistem |
| 3 | Tracking GPS | Sistem tidak memantau lokasi fisik |
| 4 | Sistem keamanan kandang/fasilitas fisik | Di luar sistem informasi |
| 5 | Panduan pertarungan atau taktik penggunaan monster | Tidak diperlukan dalam sistem peminjaman |
| 6 | Rekayasa atau modifikasi monster | Tidak diperlukan dalam sistem peminjaman |
| 7 | Pembiakan atau penciptaan monster | Tidak diperlukan dalam sistem peminjaman |
| 8 | Panduan perawatan dan pelatihan monster | Sistem hanya mencatat data peminjaman |
| 9 | Integrasi perangkat keras (scanner, sensor, dll.) | Sistem hanya mengelola data |

Item out of scope tidak memengaruhi fungsi utama aplikasi karena seluruh alur inti (inventaris, peminjaman, pengembalian, riwayat) berjalan sepenuhnya di dalam data sistem.

## 10. Functional Requirements

Seluruh FR di bawah ini adalah kebutuhan fungsional (apa yang dilakukan sistem). Aturan bisnis dirujuk dengan ID BR-xxx dan dirinci di Bagian 11.

### FR-001 Login

| Atribut | Isi |
| --- | --- |
| Aktor | Admin, Anggota |
| Deskripsi | Pengguna masuk ke sistem menggunakan email dan password untuk mengakses fitur sesuai role. |
| Pre-condition | Akun pengguna sudah terdaftar. |
| Main flow | 1) Pengguna membuka halaman login. 2) Mengisi email dan password. 3) Sistem memvalidasi kredensial. 4) Sistem memeriksa role. 5) Pengguna diarahkan ke dashboard sesuai role (Admin atau Anggota). |
| Alternative flow | A1: Kredensial salah, sistem menampilkan pesan error generik tanpa menyebutkan field mana yang salah. A2: Field kosong, sistem menampilkan pesan required. A3: Pengguna mengakses halaman tanpa login, sistem mengarahkan ke halaman login. |
| Post-condition | Sesi pengguna aktif dan dibatasi sesuai role. |
| Validation / Business rules | BR-001, BR-019. Password tidak ditampilkan dalam bentuk teks biasa. |

### FR-002 Logout

| Atribut | Isi |
| --- | --- |
| Aktor | Admin, Anggota |
| Deskripsi | Pengguna mengakhiri sesi. |
| Pre-condition | Pengguna sedang login. |
| Main flow | 1) Pengguna memilih Logout. 2) Sistem mengakhiri sesi. 3) Sistem mengarahkan ke halaman login. |
| Alternative flow | A1: Sesi sudah berakhir (timeout), sistem langsung mengarahkan ke halaman login. |
| Post-condition | Sesi tidak aktif; halaman terproteksi tidak dapat diakses. |
| Validation / Business rules | BR-001. |

### FR-003 Pengelolaan Profil

| Atribut | Isi |
| --- | --- |
| Aktor | Admin, Anggota |
| Deskripsi | Pengguna melihat dan mengubah profil miliknya sendiri. Satu user hanya memiliki satu profil. |
| Pre-condition | Pengguna login. |
| Main flow | 1) Pengguna membuka menu Profil. 2) Sistem menampilkan data profil milik pengguna. 3) Pengguna mengubah data. 4) Sistem memvalidasi dan menyimpan. 5) Sistem menampilkan pesan berhasil. |
| Alternative flow | A1: Data tidak valid atau kosong, sistem menolak dan menampilkan pesan per field. A2: Pengguna mencoba mengakses profil user lain, sistem menolak (akses ditolak). |
| Post-condition | Data profil terbarui. |
| Validation / Business rules | BR-003, BR-004, BR-019. Detail field profil: TBD (lihat Bagian 19). |

### FR-004 Pengelolaan Anggota

| Atribut | Isi |
| --- | --- |
| Aktor | Admin |
| Deskripsi | Admin melihat, menambah, mengubah, dan menghapus data anggota (CRUD). |
| Pre-condition | Admin login. |
| Main flow | 1) Admin membuka menu Anggota. 2) Sistem menampilkan daftar anggota. 3) Admin memilih aksi tambah/ubah/hapus/lihat. 4) Sistem memvalidasi input. 5) Sistem menyimpan dan menampilkan hasil. |
| Alternative flow | A1: Email sudah terdaftar, sistem menolak. A2: Anggota yang akan dihapus masih memiliki peminjaman aktif, sistem menolak penghapusan. A3: Anggota memiliki riwayat transaksi, penanganan penghapusan (soft delete atau penonaktifan) adalah **TBD / perlu keputusan** agar riwayat tidak hilang (lihat NFR-008). |
| Post-condition | Data anggota terbarui; profil terkait mengikuti. |
| Validation / Business rules | BR-002, BR-003, BR-009, BR-019. |

### FR-005 Pengelolaan Monster

| Atribut | Isi |
| --- | --- |
| Aktor | Admin (kelola); Anggota (hanya lihat, lihat FR-007) |
| Deskripsi | Admin melihat, menambah, mengubah, dan menghapus data monster, termasuk kode unik, nama, status, dan kategori. |
| Pre-condition | Admin login; minimal satu kategori sudah tersedia untuk monster baru. |
| Main flow | 1) Admin membuka menu Monster. 2) Sistem menampilkan daftar monster dengan status. 3) Admin memilih aksi. 4) Admin mengisi nama, kode monster, status, dan memilih satu atau lebih kategori. 5) Sistem memvalidasi dan menyimpan. |
| Alternative flow | A1: Kode monster duplikat, sistem menolak. A2: Tidak ada kategori dipilih, sistem menolak. A3: Monster berstatus dipinjam akan dihapus, sistem menolak. A4: Monster yang pernah dipinjam akan dihapus, penanganan menjaga riwayat adalah TBD (lihat NFR-008). |
| Post-condition | Data monster terbarui. |
| Validation / Business rules | BR-005, BR-006, BR-007, BR-009, BR-019. |

### FR-006 Pengelolaan Kategori

| Atribut | Isi |
| --- | --- |
| Aktor | Admin |
| Deskripsi | Admin melihat, menambah, mengubah, dan menghapus kategori monster (misalnya berdasarkan elemen atau jenis). |
| Pre-condition | Admin login. |
| Main flow | 1) Admin membuka menu Kategori. 2) Admin memilih aksi. 3) Sistem memvalidasi nama kategori. 4) Sistem menyimpan. |
| Alternative flow | A1: Nama kategori kosong atau duplikat, sistem menolak. A2: Kategori masih dipakai oleh monster, sistem menolak penghapusan atau meminta konfirmasi (**TBD**: pilih salah satu). |
| Post-condition | Data kategori terbarui. |
| Validation / Business rules | BR-005, BR-009, BR-019. |

### FR-007 Pencarian Monster

| Atribut | Isi |
| --- | --- |
| Aktor | Admin, Anggota |
| Deskripsi | Pengguna melihat daftar monster, mencari monster berdasarkan nama, dan melihat detail monster (kode, nama, kategori, status). |
| Pre-condition | Pengguna login. |
| Main flow | 1) Pengguna membuka daftar monster. 2) Memasukkan kata kunci nama monster. 3) Sistem menampilkan monster yang sesuai beserta status. 4) Pengguna memilih monster untuk melihat detail. |
| Alternative flow | A1: Tidak ada hasil, sistem menampilkan pesan data tidak ditemukan. A2: Kata kunci kosong, sistem menampilkan seluruh monster. |
| Post-condition | Tidak ada perubahan data. |
| Validation / Business rules | BR-001, BR-006, BR-008. Karena nama boleh sama, hasil pencarian dapat memuat banyak monster dengan nama sama dan dibedakan oleh kode monster. |

### FR-008 Peminjaman Monster

| Atribut | Isi |
| --- | --- |
| Aktor | Anggota |
| Deskripsi | Anggota meminjam monster yang tersedia selama maksimal 5 hari, dengan batas 2 peminjaman aktif. |
| Pre-condition | Anggota login dan berstatus terdaftar sebagai anggota; monster berstatus tersedia. |
| Main flow | Lihat Bagian 17.2 (Flow Peminjaman). |
| Alternative flow | A1: Monster sedang dipinjam, ditolak. A2: Anggota sudah memiliki 2 monster aktif, ditolak. A3: User belum terdaftar sebagai anggota, ditolak. A4: Data tidak valid, ditolak dengan pesan. |
| Post-condition | Transaksi tercatat; status monster menjadi dipinjam; tanggal jatuh tempo tersimpan. |
| Validation / Business rules | BR-001, BR-002, BR-010, BR-011, BR-016, BR-019. |

### FR-009 Pengembalian Monster

| Atribut | Isi |
| --- | --- |
| Aktor | Admin (anggota menghubungi Admin di luar sistem) |
| Deskripsi | Admin memproses pengembalian dan sistem mencatat tanggal kembali serta status keterlambatan. |
| Pre-condition | Admin login; terdapat peminjaman aktif atas monster tersebut. |
| Main flow | Lihat Bagian 17.3 (Flow Pengembalian). |
| Alternative flow | A1: Transaksi tidak ditemukan atau sudah dikembalikan, ditolak. A2: Anggota mencoba memproses sendiri, akses ditolak. |
| Post-condition | Tanggal kembali tercatat; status monster menjadi tersedia; transaksi masuk riwayat; denda tercatat bila terlambat. |
| Validation / Business rules | BR-012, BR-013, BR-014, BR-015, BR-019. |

### FR-010 Denda

| Atribut | Isi |
| --- | --- |
| Aktor | Sistem (otomatis), Admin (melihat) |
| Deskripsi | Sistem menandai keterlambatan dan mencatat denda bila pengembalian melewati batas 5 hari. |
| Pre-condition | Pengembalian sedang diproses atau peminjaman melewati jatuh tempo. |
| Main flow | 1) Sistem membandingkan tanggal kembali dengan jatuh tempo. 2) Bila lebih dari jatuh tempo, status menjadi terlambat. 3) Sistem mencatat denda sesuai kebijakan. 4) Admin dapat melihat daftar keterlambatan dan denda. |
| Alternative flow | A1: Dikembalikan pada atau sebelum jatuh tempo, tidak ada denda. A2: Nominal belum ditetapkan, sistem hanya mencatat keterlambatan (lihat Bagian 17.4). |
| Post-condition | Data keterlambatan dan denda tersimpan. |
| Validation / Business rules | BR-011, BR-012. **Nominal dan rumus denda: Business Decision / TBD.** |

### FR-011 Riwayat Peminjaman

| Atribut | Isi |
| --- | --- |
| Aktor | Admin (seluruh), Anggota (miliknya) |
| Deskripsi | Menampilkan riwayat dan peminjaman aktif. Admin melihat semua transaksi dan monster yang sedang dipinjam; anggota hanya melihat miliknya. |
| Pre-condition | Pengguna login. |
| Main flow | 1) Pengguna membuka menu Riwayat atau Peminjaman Aktif. 2) Sistem menampilkan data sesuai role. 3) Admin dapat memfilter (filter yang tersedia: TBD, misalnya periode, anggota, status). |
| Alternative flow | A1: Belum ada data, sistem menampilkan pesan kosong. A2: Anggota mencoba melihat transaksi anggota lain, ditolak. |
| Post-condition | Tidak ada perubahan data. |
| Validation / Business rules | BR-015, BR-016, BR-017. |

### FR-012 Cetak Riwayat

| Atribut | Isi |
| --- | --- |
| Aktor | Admin |
| Deskripsi | Admin mencetak riwayat peminjaman. |
| Pre-condition | Admin login; data riwayat tersedia. |
| Main flow | 1) Admin membuka riwayat. 2) Menentukan filter bila diperlukan. 3) Memilih Cetak. 4) Sistem menampilkan format cetak. 5) Admin mencetak. |
| Alternative flow | A1: Tidak ada data pada filter, sistem menampilkan pesan dan tidak mencetak. |
| Post-condition | Tidak ada perubahan data. |
| Validation / Business rules | BR-017, BR-018. Format keluaran (cetak browser atau PDF) adalah TBD. |

## 11. Business Rules

| ID | Aturan Bisnis | Dipenuhi oleh |
| --- | --- | --- |
| BR-001 | Setiap user wajib melakukan login untuk mengakses fitur sistem. | FR-001, FR-002, NFR-002 |
| BR-002 | Hanya user yang terdaftar sebagai anggota yang dapat melakukan peminjaman. | FR-004, FR-008 |
| BR-003 | Setiap user hanya memiliki satu profile. | FR-003, FR-004 |
| BR-004 | User hanya dapat mengubah profile miliknya sendiri. | FR-003 |
| BR-005 | Satu monster dapat memiliki lebih dari satu kategori. | FR-005, FR-006 |
| BR-006 | Nama monster boleh sama. | FR-005, FR-007 |
| BR-007 | Setiap monster harus memiliki kode monster yang unik. | FR-005 |
| BR-008 | Pencarian monster dilakukan berdasarkan nama monster. | FR-007 |
| BR-009 | Admin memiliki akses CRUD terhadap data monster, kategori, dan anggota. | FR-004, FR-005, FR-006 |
| BR-010 | Setiap anggota maksimal memiliki 2 monster yang sedang dipinjam secara aktif. | FR-008 |
| BR-011 | Durasi maksimal peminjaman adalah 5 hari. | FR-008, FR-010 |
| BR-012 | Jika pengembalian melewati batas maksimal 5 hari, anggota dikenakan denda. | FR-009, FR-010 |
| BR-013 | Hanya Admin yang dapat memproses pengembalian monster melalui sistem. | FR-009 |
| BR-014 | Anggota harus menghubungi Admin apabila ingin mengembalikan monster. | FR-009 |
| BR-015 | Admin dapat melihat seluruh monster yang sedang dipinjam. | FR-009, FR-011 |
| BR-016 | Anggota hanya dapat melihat monster yang sedang dipinjam olehnya. | FR-008, FR-011 |
| BR-017 | Admin dapat melihat riwayat seluruh peminjaman. | FR-011, FR-012 |
| BR-018 | Admin dapat mencetak riwayat peminjaman. | FR-012 |
| BR-019 | Setiap field penting harus memiliki validasi required dan validasi lain yang relevan. | Seluruh FR, Bagian 12 |

**Catatan interpretasi (TBD):** "Hari" pada BR-011 dihitung sebagai hari kalender. Peminjaman pada hari ke-0 dan jatuh tempo pada hari ke-5 (tanggal pinjam + 5 hari). Pengembalian pada hari jatuh tempo dianggap tepat waktu. Hal ini perlu dikonfirmasi project owner.

## 12. Validation Rules

Seluruh validasi dijalankan pada sisi server dan juga ditampilkan di sisi form agar mudah dipahami pengguna (BR-019).

### 12.1 User

| ID | Field | Aturan Validasi |
| --- | --- | --- |
| VAL-U-01 | Nama | Wajib diisi |
| VAL-U-02 | Email | Wajib diisi dan berformat valid |
| VAL-U-03 | Email | Harus unik |
| VAL-U-04 | Password | Wajib diisi saat pembuatan akun; aturan panjang minimal: TBD (rekomendasi: minimal 8 karakter) |
| VAL-U-05 | Role | Harus bernilai valid (Admin atau Anggota) |

### 12.2 Profile

| ID | Aturan Validasi |
| --- | --- |
| VAL-P-01 | Data profil wajib diisi sesuai kebutuhan (field wajib: TBD, lihat Bagian 19) |
| VAL-P-02 | Satu profil hanya dimiliki satu user |
| VAL-P-03 | Hanya pemilik profil yang dapat mengubahnya |

### 12.3 Monster

| ID | Field | Aturan Validasi |
| --- | --- | --- |
| VAL-N-01 | Nama monster | Wajib diisi (boleh sama dengan monster lain) |
| VAL-N-02 | Kode monster | Wajib diisi |
| VAL-N-03 | Kode monster | Harus unik |
| VAL-N-04 | Status | Harus valid (misalnya: tersedia, dipinjam; daftar status lain seperti sedang istirahat/perawatan: TBD) |
| VAL-N-05 | Kategori | Monster harus memiliki minimal satu kategori |

### 12.4 Category

| ID | Field | Aturan Validasi |
| --- | --- | --- |
| VAL-C-01 | Nama kategori | Wajib diisi |
| VAL-C-02 | Nama kategori | Harus unik (tidak boleh ada dua kategori bernama sama, tanpa membedakan huruf besar/kecil). Aturan ini diusulkan, **TBD** konfirmasi. |

### 12.5 Loan

| ID | Aturan Validasi |
| --- | --- |
| VAL-L-01 | User wajib terdaftar sebagai anggota |
| VAL-L-02 | Monster wajib berstatus tersedia |
| VAL-L-03 | Jumlah peminjaman aktif anggota tidak boleh lebih dari 2 |
| VAL-L-04 | Tanggal peminjaman wajib valid |
| VAL-L-05 | Tanggal jatuh tempo maksimal 5 hari dari tanggal peminjaman |

## 13. Non-Functional Requirements

| ID | Kategori | Kebutuhan | Cara Verifikasi |
| --- | --- | --- | --- |
| NFR-001 | Security | Password disimpan secara aman (di-hash, tidak disimpan sebagai teks biasa) | Pemeriksaan basis data dan kode |
| NFR-002 | Security | Otorisasi berbasis role (Admin dan Anggota); halaman dan fitur hanya dapat diakses setelah login | Uji akses per role |
| NFR-003 | Security | Anggota tidak boleh mengakses endpoint/halaman Admin, baik melalui menu maupun dengan mengetik alamat langsung | Uji akses langsung ke URL Admin sebagai anggota |
| NFR-004 | Security | User hanya dapat mengakses profil dan transaksi miliknya sendiri | Uji akses data milik user lain |
| NFR-005 | Performance | Pencarian monster responsif dan memberikan hasil dalam waktu wajar pada data uji (target waktu spesifik: TBD) | Uji dengan data dummy dalam jumlah besar |
| NFR-006 | Performance | Basis data menggunakan indexing pada field yang relevan (misalnya kode monster, nama monster, email, status peminjaman) | Tinjauan struktur data |
| NFR-007 | Usability | UI mudah dipahami; status monster (tersedia/dipinjam) ditampilkan jelas; seluruh form memiliki validasi dengan pesan yang mudah dimengerti | Uji pengguna saat UAT |
| NFR-008 | Reliability | Data transaksi tidak boleh hilang ketika status monster berubah atau ketika data anggota/monster/kategori diubah; riwayat tetap utuh | Uji skenario ubah dan hapus data |
| NFR-009 | Maintainability | Struktur kode modular sesuai modul sistem (lihat Bagian 14.1) | Code review |
| NFR-010 | Maintainability | Migration dan seeder terdokumentasi (termasuk data dummy awal) | Tinjauan dokumentasi proyek |

## 14. Data Requirements

### 14.1 Modul Sistem

| No | Modul | Tujuan | Fitur Utama | FR |
| --- | --- | --- | --- | --- |
| 1 | Authentication | Mengamankan akses sistem | Login, logout, validasi role | FR-001, FR-002 |
| 2 | User Management | Mengelola data anggota | CRUD anggota oleh Admin | FR-004 |
| 3 | Profile Management | Mengelola data diri pengguna | Lihat dan ubah profil sendiri | FR-003 |
| 4 | Monster Management | Mengelola inventaris monster | CRUD monster, kode unik, status | FR-005 |
| 5 | Category Management | Mengelompokkan monster | CRUD kategori, relasi dengan monster | FR-006 |
| 6 | Monster Search | Memudahkan menemukan monster | Daftar monster, cari nama, detail | FR-007 |
| 7 | Loan Management | Mencatat peminjaman | Ajukan peminjaman, cek batas, tentukan jatuh tempo | FR-008 |
| 8 | Return Management | Mencatat pengembalian | Proses pengembalian oleh Admin | FR-009 |
| 9 | Fine Management | Mencatat keterlambatan dan denda | Penentuan terlambat, pencatatan denda | FR-010 |
| 10 | Loan History | Menyediakan jejak transaksi | Riwayat dan peminjaman aktif | FR-011 |
| 11 | Reporting / Printing | Menyediakan laporan | Cetak riwayat peminjaman | FR-012 |

### 14.2 Data Utama

Bagian ini hanya mendefinisikan kebutuhan data dari sisi bisnis, bukan skema basis data.

| Data | Fungsi | Hubungan |
| --- | --- | --- |
| User | Menyimpan akun (nama, email, password, role) untuk login dan otorisasi | Satu user memiliki satu Profile; satu user dapat memiliki banyak Loan |
| Profile | Menyimpan data diri pengguna | Dimiliki tepat satu User |
| Monster | Menyimpan inventaris (kode unik, nama, status) | Banyak ke banyak dengan Category; muncul pada banyak Loan Detail sepanjang waktu |
| Category | Mengelompokkan monster | Banyak ke banyak dengan Monster |
| Monster Category | Menghubungkan monster dengan kategorinya | Penghubung Monster dan Category |
| Loan | Menyimpan transaksi peminjaman (anggota, tanggal pinjam, jatuh tempo, tanggal kembali, status) | Dimiliki satu User; memiliki satu atau lebih Loan Detail |
| Loan Detail | Menyimpan monster yang dipinjam pada suatu transaksi | Milik satu Loan; mengacu satu Monster |
| Fine | Menyimpan keterlambatan dan denda | Terkait satu Loan (atau satu Loan Detail, TBD) |

## 15. Entity & Relationship Candidate

Bagian ini adalah bahan persiapan ERD. Nama field bersifat kandidat dan dapat disesuaikan pada tahap desain.

| Entity | Tujuan | Primary Key | Candidate Fields | Relationship |
| --- | --- | --- | --- | --- |
| User | Akun dan role | id\_user | nama, email (unik), password (hash), role, status akun (TBD), timestamp | 1 User ke 1 Profile; 1 User ke banyak Loan |
| Profile | Data diri | id\_profile | id\_user (unik, FK), field identitas (TBD: nomor anggota, telepon, alamat, dll.), timestamp | 1 Profile milik 1 User |
| Monster | Inventaris monster | id\_monster | kode\_monster (unik), nama\_monster, status, deskripsi (opsional), timestamp | Banyak ke banyak dengan Category; 1 Monster ke banyak Loan Detail |
| Category | Pengelompokan | id\_category | nama\_kategori, deskripsi (opsional), timestamp | Banyak ke banyak dengan Monster |
| Monster Category | Penghubung | id\_monster\_category atau PK gabungan (id\_monster, id\_category) | id\_monster (FK), id\_category (FK) | Banyak ke 1 Monster; banyak ke 1 Category |
| Loan | Transaksi peminjaman | id\_loan | id\_user (FK), tanggal\_pinjam, tanggal\_jatuh\_tempo, tanggal\_kembali (kosong selama belum kembali), status (aktif, selesai, terlambat), timestamp | Banyak ke 1 User; 1 Loan ke banyak Loan Detail; 1 Loan ke 0 atau 1 Fine |
| Loan Detail | Monster pada transaksi | id\_loan\_detail | id\_loan (FK), id\_monster (FK), status item (TBD) | Banyak ke 1 Loan; banyak ke 1 Monster |
| Fine | Denda | id\_fine | id\_loan (FK), jumlah hari terlambat, nominal (TBD, dari kebijakan), status denda (TBD) | 1 Fine milik 1 Loan |

**Alasan relasi:**

| Relasi | Alasan |
| --- | --- |
| 1 User ke 1 Profile | BR-003: setiap user hanya memiliki satu profil; memisahkan data akun dari data diri |
| 1 User ke banyak Loan | Satu anggota dapat melakukan banyak peminjaman sepanjang waktu (dibatasi 2 aktif oleh BR-010) |
| 1 Loan ke banyak Loan Detail | Satu transaksi dapat memuat lebih dari satu monster (maksimal 2 sesuai BR-010); **TBD**: apakah satu transaksi boleh memuat 2 monster atau setiap monster selalu satu transaksi terpisah |
| 1 Monster ke banyak Loan Detail | Satu monster dapat dipinjam berkali-kali sepanjang waktu, sehingga riwayat tetap terjaga |
| Monster dan Category banyak ke banyak | BR-005: satu monster dapat memiliki lebih dari satu kategori dan satu kategori dimiliki banyak monster; diwujudkan melalui entity penghubung Monster Category |
| 1 Loan ke 0 atau 1 Fine | Denda hanya ada bila terlambat |

**Monster dan Kategori:** satu monster memiliki satu kode unik; nama monster tidak harus unik; satu nama monster dapat memiliki beberapa ekor monster. Contoh:

| Nama Monster | Kode Monster | Kategori (contoh) |
| --- | --- | --- |
| Slime Latihan | SL-001 | Air, Jinak |
| Slime Latihan | SL-002 | Air, Jinak |
| Slime Latihan | SL-003 | Air, Jinak |

Karena nama tidak unik, kode monster menjadi pembeda antar monster pada pencarian, peminjaman, dan riwayat. Satu monster dapat memiliki banyak kategori (contoh: Air dan Jinak), dan kategori yang sama dimiliki banyak monster.

## 16. Use Case

### 16.1 Admin

| ID | Use Case | Terkait |
| --- | --- | --- |
| UC-A01 | Login | FR-001 |
| UC-A02 | Manage User (lihat, tambah, ubah, hapus anggota) | FR-004 |
| UC-A03 | Manage Profile | FR-003 |
| UC-A04 | Manage Monster | FR-005 |
| UC-A05 | Manage Category | FR-006 |
| UC-A06 | View / Search Monster | FR-007 |
| UC-A07 | View Active Loans | FR-011 |
| UC-A08 | Process Return | FR-009 |
| UC-A09 | View Late Loans and Fines | FR-010 |
| UC-A10 | View Loan History | FR-011 |
| UC-A11 | Print Loan History | FR-012 |
| UC-A12 | Logout | FR-002 |

### 16.2 Anggota

| ID | Use Case | Terkait |
| --- | --- | --- |
| UC-M01 | Login | FR-001 |
| UC-M02 | View Profile | FR-003 |
| UC-M03 | Edit Profile | FR-003 |
| UC-M04 | Browse Monster | FR-007 |
| UC-M05 | Search Monster | FR-007 |
| UC-M06 | View Monster Detail | FR-007 |
| UC-M07 | Borrow Monster | FR-008 |
| UC-M08 | View My Active Loans | FR-011 |
| UC-M09 | View My Loan History | FR-011 |
| UC-M10 | Logout | FR-002 |

Pengembalian oleh anggota dilakukan di luar sistem dengan menghubungi Admin (BR-014) dan bukan merupakan use case anggota.

## 17. Business Flow

### 17.1 Main System Flow

1. Pengguna membuka sistem dan melakukan **Login**.
2. Sistem melakukan **Role Validation**.
3. **Jika Admin:** Admin Dashboard, lalu Manage User / Monster / Category, lalu Manage Loan (lihat peminjaman aktif), lalu Process Return, lalu View & Print History.
4. **Jika Anggota:** Member Dashboard, lalu View/Search Monster, lalu Borrow Monster, lalu View My Loan.
5. Pengguna melakukan Logout.

### 17.2 Flow Peminjaman (FR-008)

1. Anggota login.
2. Anggota melihat daftar monster dan mencari monster berdasarkan nama.
3. Anggota memilih monster dan melihat detail.
4. Sistem mengecek apakah user terdaftar sebagai anggota.
5. Sistem mengecek ketersediaan monster.
6. Sistem mengecek jumlah peminjaman aktif anggota.
7. Jika kurang dari 2 monster aktif, peminjaman dapat dilakukan.
8. Sistem mencatat transaksi dengan tanggal pinjam.
9. Status monster menjadi dipinjam.
10. Sistem menentukan tanggal jatuh tempo maksimal 5 hari dari tanggal pinjam.
11. Sistem menampilkan konfirmasi dengan tanggal jatuh tempo.

**Kondisi pengecualian:**

| Kondisi | Perilaku Sistem |
| --- | --- |
| Monster sedang dipinjam | Sistem menolak dan menampilkan pesan bahwa monster tidak tersedia; transaksi tidak dibuat |
| Anggota sudah memiliki 2 monster aktif | Sistem menolak dan menampilkan pesan batas maksimal tercapai; anggota dapat meminjam lagi setelah ada monster dikembalikan |
| User belum terdaftar sebagai anggota | Sistem menolak peminjaman (BR-002); user diarahkan menghubungi Admin |
| Data peminjaman tidak valid | Sistem menolak dan menampilkan pesan validasi (misalnya tanggal tidak valid atau jatuh tempo lebih dari 5 hari); tidak ada perubahan status monster |

**Catatan:** untuk mencegah dua anggota meminjam monster yang sama secara bersamaan, pengecekan ketersediaan dan pencatatan transaksi harus dilakukan sebagai satu proses yang konsisten (NFR-008).

### 17.3 Flow Pengembalian (FR-009)

1. Anggota menghubungi Admin (di luar sistem) untuk mengembalikan monster.
2. Admin mencari transaksi peminjaman aktif (berdasarkan anggota atau kode monster).
3. Admin melakukan proses pengembalian.
4. Sistem mencatat tanggal pengembalian.
5. Sistem mengecek apakah terlambat (tanggal kembali melebihi jatuh tempo).
6. Jika terlambat, sistem menetapkan status terlambat dan menghitung/mencatat denda.
7. Status monster kembali menjadi tersedia.
8. Transaksi masuk ke riwayat.

### 17.4 Business Requirement Denda (FR-010)

| Kondisi | Hasil |
| --- | --- |
| Dikembalikan pada atau sebelum batas 5 hari | Tidak ada denda; status selesai |
| Dikembalikan setelah batas 5 hari | Status terlambat; dikenakan denda |
| Belum dikembalikan dan sudah lewat jatuh tempo | Ditandai terlambat; dapat dilihat Admin pada daftar keterlambatan |

**Business Decision / TBD:** nominal denda belum ditentukan oleh project owner. Hal-hal berikut perlu diputuskan sebelum implementasi final:

| No | Keputusan yang Dibutuhkan | Opsi (contoh, bukan keputusan) |
| --- | --- | --- |
| 1 | Nominal denda | Nominal tetap atau per hari keterlambatan, jumlah: **TBD** |
| 2 | Batas maksimal denda | Ada atau tidak: **TBD** |
| 3 | Status pembayaran denda | Dicatat di sistem atau di luar sistem (pembayaran online out of scope): **TBD** |
| 4 | Dampak denda belum lunas pada peminjaman berikutnya | Boleh atau diblokir: **TBD** |

Selama nominal belum ditentukan, sistem minimal harus mencatat keterlambatan dan jumlah hari terlambat sehingga nominal dapat dihitung kemudian.

## 18. Acceptance Criteria

Setiap kriteria harus dapat diuji (lulus/gagal) pada tahap UAT.

| ID | Fitur | Kriteria Penerimaan |
| --- | --- | --- |
| AC-01 | Login (FR-001) | Pengguna terdaftar dengan kredensial benar masuk ke dashboard sesuai role; kredensial salah ditolak dengan pesan; halaman terproteksi tidak dapat dibuka tanpa login |
| AC-02 | Logout (FR-002) | Setelah logout, sesi berakhir dan halaman terproteksi tidak dapat diakses |
| AC-03 | Profil (FR-003) | Pengguna dapat melihat dan mengubah profil miliknya; tidak dapat melihat atau mengubah profil user lain; satu user hanya memiliki satu profil |
| AC-04 | Anggota (FR-004) | Admin dapat menambah, mengubah, menghapus, dan melihat anggota; email duplikat ditolak; anggota tidak dapat mengakses menu ini |
| AC-05 | Monster (FR-005) | Admin dapat CRUD monster; kode monster duplikat ditolak; nama yang sama diterima selama kode berbeda; monster tanpa kategori ditolak; monster dapat memiliki beberapa kategori |
| AC-06 | Kategori (FR-006) | Admin dapat CRUD kategori; nama kosong ditolak; aturan keunikan nama berlaku sesuai keputusan |
| AC-07 | Pencarian Monster (FR-007) | Pencarian berdasarkan nama menampilkan semua monster yang sesuai beserta kode dan status; detail monster dapat dibuka; hasil kosong menampilkan pesan |
| AC-08 | **Peminjaman Monster (FR-008)** | (1) User harus login. (2) User harus berstatus anggota. (3) Monster harus tersedia. (4) User tidak boleh memiliki lebih dari 2 monster aktif. (5) Sistem mencatat tanggal peminjaman. (6) Sistem menentukan batas pengembalian maksimal 5 hari. (7) Status monster menjadi dipinjam setelah transaksi berhasil. (8) Upaya yang melanggar aturan ditolak dengan pesan dan tidak mengubah data |
| AC-09 | **Pengembalian (FR-009)** | (1) Hanya Admin yang dapat melakukan pengembalian. (2) Sistem mencatat tanggal pengembalian. (3) Sistem mengecek keterlambatan. (4) Sistem mencatat denda apabila terlambat. (5) Status monster kembali menjadi tersedia. (6) Transaksi masuk ke riwayat |
| AC-10 | Denda (FR-010) | Pengembalian pada atau sebelum jatuh tempo tidak menghasilkan denda; pengembalian setelah jatuh tempo ditandai terlambat dan denda tercatat; Admin dapat melihat daftar keterlambatan dan denda; nominal mengikuti keputusan project owner |
| AC-11 | Riwayat (FR-011) | Admin melihat seluruh peminjaman aktif dan riwayat; anggota hanya melihat data miliknya |
| AC-12 | Cetak (FR-012) | Admin dapat mencetak riwayat; anggota tidak memiliki akses fitur cetak |
| AC-13 | Keamanan (NFR-001 sampai NFR-004) | Password tidak tersimpan sebagai teks biasa; anggota yang membuka URL Admin ditolak; user tidak dapat mengakses data user lain |

## 19. Assumptions & Constraints

### 19.1 Asumsi

| ID | Asumsi |
| --- | --- |
| AS-01 | Proyek merupakan simulasi akademik pada lembaga pelatihan pawang monster fiktif |
| AS-02 | Seluruh monster, anggota, dan data lain bersifat fiktif/dummy |
| AS-03 | Sistem tidak mengontrol perangkat fisik dan hanya menangani manajemen data serta transaksi peminjaman |
| AS-04 | Admin sudah tersedia (dibuat melalui seeder); pendaftaran anggota dilakukan oleh Admin, bukan registrasi mandiri |
| AS-05 | Pengembalian fisik dilakukan di luar sistem; Admin kemudian mencatatnya (BR-014) |
| AS-06 | Hari dihitung sebagai hari kalender; pengembalian tepat pada hari jatuh tempo dianggap tidak terlambat |
| AS-07 | Peminjaman langsung tercatat saat anggota mengajukan, tanpa persetujuan Admin (lihat TBD-07) |

### 19.2 Batasan

| ID | Batasan |
| --- | --- |
| CN-01 | Aplikasi berbasis web |
| CN-02 | Fitur terbatas pada In Scope (Bagian 9.1) |
| CN-03 | Tidak ada pembayaran online, GPS, atau integrasi perangkat keras |
| CN-04 | Sistem tidak memuat konten panduan pertarungan, rekayasa, pembiakan, atau jual beli monster |

### 19.3 Daftar TBD / Perlu Keputusan

| ID | Hal yang Perlu Diputuskan | Terkait | Pemutus |
| --- | --- | --- | --- |
| TBD-01 | Nominal dan rumus denda | FR-010, BR-012 | Project owner |
| TBD-02 | Field wajib pada profil dan identitas anggota | FR-003, VAL-P-01 | Project owner |
| TBD-03 | Aturan keunikan nama kategori | FR-006, VAL-C-02 | Project owner |
| TBD-04 | Penanganan penghapusan anggota/monster/kategori yang sudah punya riwayat (soft delete atau ditolak) | FR-004, FR-005, FR-006, NFR-008 | Project owner |
| TBD-05 | Daftar status monster (misalnya sedang istirahat atau perawatan) | VAL-N-04 | Project owner |
| TBD-06 | Satu transaksi boleh memuat 2 monster atau satu monster per transaksi | Bagian 15 | Project owner |
| TBD-07 | Peminjaman langsung otomatis atau perlu persetujuan Admin | FR-008 | Project owner |
| TBD-08 | Apakah anggota boleh melihat denda miliknya | Bagian 8 | Project owner |
| TBD-09 | Dampak denda belum lunas pada peminjaman berikutnya | FR-010 | Project owner |
| TBD-10 | Format cetak (cetak browser atau PDF) dan filter laporan | FR-012 | Project owner |
| TBD-11 | Aturan password (panjang minimal) dan target waktu pencarian | VAL-U-04, NFR-005 | Project owner |

## 20. MoSCoW Prioritization

| Prioritas | Requirement |
| --- | --- |
| **Must Have** | FR-001 Login; FR-002 Logout; FR-003 Pengelolaan Profil; FR-004 Pengelolaan Anggota; FR-005 Pengelolaan Monster; FR-006 Pengelolaan Kategori; FR-007 Pencarian Monster; FR-008 Peminjaman Monster; FR-009 Pengembalian Monster; FR-010 Denda (pencatatan keterlambatan dan denda); FR-011 Riwayat Peminjaman; seluruh BR-001 sampai BR-019; NFR-001 sampai NFR-004 (Security); NFR-008 (Reliability) |
| **Should Have** | FR-012 Cetak Riwayat; NFR-005 sampai NFR-007 (Performance dan Usability); NFR-009 dan NFR-010 (Maintainability); daftar keterlambatan khusus untuk Admin |
| **Could Have** | Filter lanjutan riwayat (periode, anggota, status); ekspor riwayat ke PDF/Excel; dashboard ringkasan untuk Admin dan anggota; pagination dan pengurutan daftar |
| **Won't Have (periode ini)** | Notifikasi otomatis (email/WhatsApp); pembayaran online; marketplace dan jual beli; GPS tracking; integrasi perangkat keras; pemesanan monster di muka (reservasi); seluruh item Out of Scope di Bagian 9.2 |

Catatan: FR-012 berstatus Should Have karena meskipun diminta dalam requirement utama (BR-018), sistem tetap dapat beroperasi tanpa fitur cetak. Project owner dapat menaikkannya menjadi Must Have.

## 21. Future Development

Pengembangan berikut tidak termasuk lingkup saat ini dan baru dipertimbangkan setelah sistem inti stabil:

| No | Ide Pengembangan | Catatan |
| --- | --- | --- |
| 1 | Notifikasi pengingat jatuh tempo kepada anggota | Membantu mengurangi keterlambatan |
| 2 | Dashboard statistik peminjaman | Untuk Admin (jumlah monster tersedia, dipinjam, terlambat) |
| 3 | Ekspor laporan ke PDF/Excel | Pengembangan dari FR-012 |
| 4 | Registrasi mandiri anggota dengan verifikasi Admin | Saat ini anggota didaftarkan Admin |
| 5 | Status monster tambahan (istirahat, perawatan) | Bergantung pada TBD-05 |
| 6 | Log aktivitas (audit trail) Admin | Meningkatkan akuntabilitas |
| 7 | Aplikasi mobile atau tampilan responsif lanjutan | Memudahkan akses |

Pengembangan di atas tidak boleh mencakup item yang tercantum pada Bagian 9.2.

## 22. Conclusion

BRD ini mendefinisikan kebutuhan bisnis MonsterTrack sebagai sistem informasi peminjaman dan pengelolaan inventaris monster pada lembaga pelatihan pawang monster fiktif, dalam konteks simulasi akademik. Dokumen telah mencakup tujuan, permasalahan, stakeholder, hak akses, lingkup, 12 functional requirement, 19 business rule, validasi, kebutuhan non-fungsional, kebutuhan data, kandidat entity dan relasi, use case, alur bisnis, acceptance criteria, serta prioritas MoSCoW.

Seluruh hal yang belum pasti, terutama **nominal denda**, telah ditandai sebagai TBD pada Bagian 19.3 dan perlu diputuskan project owner sebelum implementasi final.

**Tahap lanjutan yang disarankan:** Flowchart, lalu ERD (berdasarkan Bagian 15), lalu PRD, lalu Design, lalu Development, lalu UAT (berdasarkan Bagian 18), lalu Manual Book, lalu Dokumentasi Project.

## Lampiran A. Matriks Keterlacakan (Traceability Matrix)

Matriks ini memastikan seluruh business rule dan hak akses dalam requirement awal tercakup dan dapat ditelusuri dari tujuan bisnis hingga pengujian.

| Requirement Awal | Business Objective | Functional Requirement | Validasi | Acceptance Criteria |
| --- | --- | --- | --- | --- |
| BR-001 Login wajib | BReq-04 | FR-001, FR-002 | VAL-U-02 | AC-01, AC-02, AC-13 |
| BR-002 Hanya anggota boleh meminjam | BReq-04 | FR-004, FR-008 | VAL-L-01 | AC-08 |
| BR-003 Satu user satu profil | BReq-01 | FR-003 | VAL-P-02 | AC-03 |
| BR-004 Ubah profil sendiri | BReq-01 | FR-003 | VAL-P-03 | AC-03, AC-13 |
| BR-005 Monster multi-kategori | BReq-01 | FR-005, FR-006 | VAL-N-05 | AC-05 |
| BR-006 Nama monster boleh sama | BReq-01 | FR-005, FR-007 | VAL-N-01 | AC-05, AC-07 |
| BR-007 Kode monster unik | BReq-01 | FR-005 | VAL-N-02, VAL-N-03 | AC-05 |
| BR-008 Cari berdasarkan nama | BReq-03 | FR-007 | - | AC-07 |
| BR-009 CRUD Admin | BReq-02 | FR-004, FR-005, FR-006 | VAL-U, VAL-N, VAL-C | AC-04, AC-05, AC-06 |
| BR-010 Maksimal 2 monster aktif | BReq-05 | FR-008 | VAL-L-03 | AC-08 |
| BR-011 Maksimal 5 hari | BReq-04 | FR-008, FR-010 | VAL-L-05 | AC-08, AC-10 |
| BR-012 Denda bila terlambat | BReq-04 | FR-009, FR-010 | - | AC-09, AC-10 |
| BR-013 Pengembalian oleh Admin | BReq-04 | FR-009 | - | AC-09 |
| BR-014 Anggota menghubungi Admin | BReq-04 | FR-009 | - | AC-09 |
| BR-015 Admin lihat monster dipinjam | BReq-06 | FR-011 | - | AC-11 |
| BR-016 Anggota lihat miliknya | BReq-06 | FR-008, FR-011 | - | AC-11, AC-13 |
| BR-017 Admin lihat seluruh riwayat | BReq-06 | FR-011 | - | AC-11 |
| BR-018 Admin cetak riwayat | BReq-08 | FR-012 | - | AC-12 |
| BR-019 Validasi required dan relevan | BReq-07 | Seluruh FR | Bagian 12 | AC-04 sampai AC-08 |
| Denda (konsep tanpa nominal) | BReq-04 | FR-010 | - | AC-10, TBD-01 |
