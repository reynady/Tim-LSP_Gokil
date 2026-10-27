
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Anggota | ArsenalTrack</title>

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
                    Kendali inventaris.<br>
                    Tertib dalam setiap<br>
                    peminjaman.
                </h1>

                <p class="intro-description">
                    Platform pencatatan dan pengelolaan peminjaman
                    yang terstruktur, transparan, dan mudah dipantau.
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
                <span>AREA ANGGOTA</span>
                <span>AUTH / 001</span>
            </div>

            <form
                class="login-form"
                method="POST"
                action="{{ route('login.process') }}"
            > @csrf

                <div class="form-intro">
                    <h2>Selamat datang kembali.</h2>
                    <p>
                        Masuk menggunakan akun yang telah divalidasi admin
                        untuk melanjutkan ke ArsenalTrack.
                    </p>
                </div>

                @if(session('status'))
                    <div class="success-message">{{ session('status') }}</div>
                @endif

                <div class="form-fields">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@institusi.ac.id"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Kata sandi</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                @if(isset($errors) && $errors->any())
                    <div class="error-message">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <button class="login-button" type="submit">
                    <span>Masuk ke akun</span>
                    <i data-lucide="arrow-right"></i>
                </button>

                <p class="auth-switch">
                    Belum punya akun? <a href="{{ route('register') }}">Sign up</a>
                </p>

                <div class="access-help">
                    <h3>Mengalami kendala akses?</h3>
                    <p>
                        Hubungi administrator untuk bantuan akun atau
                        jika kamu tidak dapat masuk ke sistem.
                    </p>
                    <small>ArsenalTrack · Sistem Informasi Inventaris</small>
                </div>
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
