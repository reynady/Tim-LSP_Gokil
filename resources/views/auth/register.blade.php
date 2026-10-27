<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | ArsenalTrack</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <main class="login-page">
        <aside class="intro-panel">
            <div class="brand">
                <div class="brand-icon">
                    <i data-lucide="layers"></i>
                </div>
                <span>ArsenalTrack</span>
            </div>

            <section class="intro-copy">
                <p class="eyebrow">SISTEM INFORMASI INVENTARIS</p>

                <h1>
                    Mulai kelola<br>
                    peminjamanmu<br>
                    dengan tertib.
                </h1>

                <p class="intro-description">
                    Buat akun anggota untuk melihat inventaris dan
                    mengelola transaksi peminjaman.
                </p>
            </section>

            <section class="inventory-card">
                <div class="card-heading">
                    <span>ALUR PENGELOLAAN</span>
                    <i data-lucide="scan-line"></i>
                </div>

                <div class="inventory-modules">
                    <div class="inventory-module">
                        <i data-lucide="boxes"></i>
                        <span class="archive-line"></span>
                    </div>
                    <div class="inventory-module">
                        <i data-lucide="clipboard-check"></i>
                        <span class="archive-line"></span>
                    </div>
                    <div class="inventory-module">
                        <i data-lucide="calendar-check"></i>
                        <span class="archive-line"></span>
                    </div>
                </div>

                <div class="loan-flow">
                    <span>Inventaris</span>
                    <i data-lucide="arrow-right"></i>
                    <span>Peminjaman</span>
                    <i data-lucide="arrow-right"></i>
                    <span>Pengembalian</span>
                </div>
            </section>

            <section class="institution">
                <div class="institution-name">
                    <i data-lucide="landmark"></i>
                    <span>ArsenalTrack System</span>
                </div>
                <p>
                    Sistem informasi pengelolaan inventaris dan
                    pencatatan transaksi peminjaman berbasis web.
                </p>
            </section>
        </aside>

        <section class="login-panel">
            <div class="access-label">
                <span>DAFTAR ANGGOTA</span>
                <span>AUTH / 002</span>
            </div>

            <form class="login-form" method="POST" action="{{ route('register.process') }}">
                @csrf

                <div class="form-intro">
                    <h2>Buat akun baru.</h2>
                    <p>Daftar sebagai anggota. Akun perlu disetujui admin sebelum bisa digunakan.</p>
                </div>

                <div class="form-fields">
                    <div class="form-group">
                        <label for="name">Nama lengkap</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Nama lengkap"
                            autocomplete="name"
                            maxlength="255"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@institusi.ac.id"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Kata sandi</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi kata sandi</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ulangi kata sandi"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                    </div>
                </div>

                @if(isset($errors) && $errors->any())
                    <div class="error-message" role="alert">
                        <div>
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button class="login-button" type="submit">
                    <span>Buat akun</span>
                    <i data-lucide="arrow-right"></i>
                </button>

                <p class="auth-switch">
                    Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                </p>
            </form>
        </section>
    </main>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>
</body>
</html>
