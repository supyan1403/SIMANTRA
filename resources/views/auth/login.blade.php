<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMANTRA BPS Kab. Tasikmalaya</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo_kendi_trans.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_kendi_trans.png') }}">
    <script>
        // Anti-BFCache: Force page reload on Back/Forward navigation
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0] && window.performance.getEntriesByType('navigation')[0].type === 'back_forward')) {
                window.location.reload(true);
            }
        });
    </script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --bps-blue: #2563eb;
            --bps-blue-dark: #1d4ed8;
            --bps-navy: #0b1329;
            --bps-dark: #0f172a;
        }

        html, body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            font-family: var(--bs-body-font-family);
            background-color: #ffffff;
            color: #1e293b;
            overflow: hidden !important; /* Strict No Scrollbar */
        }

        .fullscreen-container {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* LEFT SIDE: Form (50% Width) */
        .split-form-side {
            width: 50%;
            height: 100vh;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2.5rem 3.5rem;
            position: relative;
            z-index: 5;
            overflow: hidden;
        }

        .form-content-wrapper {
            max-width: 420px;
            width: 100%;
        }

        .welcome-title {
            font-weight: 900;
            font-size: 1.75rem;
            color: #0f172a;
            letter-spacing: -0.6px;
            margin-bottom: 0.35rem;
        }

        .welcome-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.775rem;
            color: #334155;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Modern Unified Seamless Input Group */
        .seamless-input-box {
            display: flex;
            align-items: center;
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.25rem 0.85rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .seamless-input-box:focus-within {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .seamless-input-box.is-invalid-box {
            border-color: #ef4444;
            background-color: #fffafb;
        }

        .seamless-input-icon {
            color: #2563eb;
            font-size: 1.1rem;
            margin-right: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .seamless-input-field {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
            width: 100%;
            padding: 0.55rem 0;
            font-size: 0.9rem;
            font-weight: 500;
            color: #0f172a;
        }

        .seamless-input-field::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .btn-seamless-eye {
            border: none;
            background: transparent;
            color: #64748b;
            padding: 0.25rem 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 0.4rem;
            transition: all 0.15s ease;
            font-size: 1.1rem;
        }

        .btn-seamless-eye:hover {
            color: #2563eb;
            background-color: #f1f5f9;
        }

        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            font-weight: 700;
            font-size: 0.925rem;
            color: #ffffff;
            box-shadow: 0 6px 18px -2px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
            letter-spacing: 0.2px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #ffffff;
            box-shadow: 0 10px 24px -4px rgba(37, 99, 235, 0.45);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-back-home {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.8rem 1rem;
            font-weight: 700;
            font-size: 0.875rem;
            color: #475569;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            margin-top: 0.85rem;
        }

        .btn-back-home:hover {
            color: #2563eb;
            background: #f8fafc;
            border-color: #93c5fd;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .footer-text {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2rem;
            text-align: center;
        }

        /* RIGHT SIDE: Hero Showcase (50% Width) */
        .split-hero-side {
            width: 50%;
            height: 100vh;
            background: linear-gradient(135deg, #0b1329 0%, #1e293b 60%, #0f172a 100%);
            padding: 3.5rem 4.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            color: #ffffff;
            overflow: hidden;
            border-left: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Ambient Glow & Blurred Logo Watermark */
        .split-hero-side::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -20%;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.28) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
            z-index: 1;
        }

        .hero-watermark-logo {
            position: absolute;
            right: -80px;
            bottom: -60px;
            width: 460px;
            height: 460px;
            background-image: url("{{ asset('images/logo_kendi_trans.png') }}");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.11;
            filter: blur(10px) brightness(1.2);
            pointer-events: none;
            z-index: 1;
            transform: rotate(-10deg);
        }

        .hero-content-layer {
            position: relative;
            z-index: 5;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hero-brand-inline {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .hero-logo-inline {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .hero-top-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.35rem 0.85rem;
            border-radius: 50rem;
            font-size: 0.7rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            color: #93c5fd;
            letter-spacing: 0.8px;
            backdrop-filter: blur(6px);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .hero-main-title {
            font-weight: 900;
            font-size: 2.5rem;
            line-height: 1.1;
            letter-spacing: -0.8px;
            color: #ffffff;
            margin-bottom: 0.4rem;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .hero-main-subtitle {
            font-size: 1.05rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 1.75rem;
            line-height: 1.5;
            max-width: 480px;
        }

        /* Feature List Pills */
        .feature-item {
            display: flex;
            align-items: center;
            gap: 1.15rem;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.95rem 1.25rem;
            border-radius: 0.95rem;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateX(4px);
        }

        .feature-icon-box {
            width: 46px;
            height: 46px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .icon-box-blue {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon-box-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon-box-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero-agency-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            padding: 0.5rem 1.25rem;
            border-radius: 50rem;
            font-size: 0.775rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            color: #ffffff;
            letter-spacing: 0.8px;
            backdrop-filter: blur(6px);
            width: fit-content;
        }

        @media (max-width: 992px) {
            html, body {
                overflow: auto !important;
                height: auto;
            }
            .fullscreen-container {
                flex-direction: column;
                height: auto;
            }
            .split-form-side, .split-hero-side {
                width: 100%;
                height: auto;
                padding: 3rem 2rem;
            }
            .split-hero-side {
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                border-left: none;
            }
        }
    </style>
</head>
<body>
    <div class="fullscreen-container">
        <!-- SISI KIRI: Formulir Login (50% Layar Penuh - Center Aligned) -->
        <div class="split-form-side">
            <div class="form-content-wrapper">
                <h1 class="welcome-title">Autentikasi Pengguna</h1>
                <p class="welcome-subtitle">Silakan masukkan email dan kata sandi Anda untuk mengakses dashboard pengawasan.</p>

                @if(session('status'))
                    <div class="alert alert-success border-0 small py-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <div style="font-size: 0.825rem;">{{ session('status') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Email Input (Unified Seamless Box) -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <div class="seamless-input-box @error('email') is-invalid-box @enderror">
                            <span class="seamless-input-icon"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="seamless-input-field" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="contoh@bps.go.id" required autofocus autocomplete="username">
                        </div>
                        @error('email') <div class="text-danger small mt-1.5" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Password Input (Unified Seamless Box with Corrected Eye Toggle) -->
                    <div class="mb-2">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="seamless-input-box @error('password') is-invalid-box @enderror">
                            <span class="seamless-input-icon"><i class="bi bi-lock"></i></span>
                            <input type="password" class="seamless-input-field" 
                                   id="password" name="password" placeholder="Masukkan kata sandi..." required autocomplete="current-password">
                            <button type="button" class="btn-seamless-eye" id="togglePasswordBtn" title="Lihat/Sembunyikan Kata Sandi">
                                <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password') <div class="text-danger small mt-1.5" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Remember Me with Extra Spacing -->
                    <div class="d-flex align-items-center justify-content-between mb-4 mt-2.5">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" style="cursor: pointer;">
                            <label class="form-check-label small text-secondary" for="remember" style="cursor: pointer; font-size: 0.8rem;">
                                Ingat sesi login saya
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-login w-100 d-flex align-items-center justify-content-center">
                        <span>Masuk Aplikasi</span>
                        <i class="bi bi-arrow-right fs-5 ms-2"></i>
                    </button>

                    <!-- Back to Landing Button (Di Bawah Form) -->
                    <a href="{{ route('landing') }}" class="btn-back-home">
                        <i class="bi bi-arrow-left text-primary fs-6"></i>
                        <span>Kembali ke Beranda Publik</span>
                    </a>
                </form>

                <div class="footer-text">
                    &copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Tasikmalaya
                </div>
            </div>
        </div>

        <!-- SISI KANAN: Hero Showcase with Blurred Watermark (50% Layar Penuh) -->
        <div class="split-hero-side">
            <!-- Background Watermark Logo SIMANTRA Blur -->
            <div class="hero-watermark-logo"></div>

            <div class="hero-content-layer">
                <div>
                    <div class="hero-top-badge">
                        <span>Portal Manajemen Mitra</span>
                    </div>
                    <h2 class="hero-main-title">SIMANTRA</h2>
                    <h5 class="hero-main-subtitle">Sistem Informasi Monitoring Alokasi Pekerjaan & Honor Mitra</h5>
                </div>

                <!-- 3 Core Feature Items with Real Colored Icons -->
                <div class="my-auto py-3">
                    <div class="feature-item">
                        <div class="feature-icon-box icon-box-blue">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white mb-0.5" style="font-size: 0.95rem;">Monitoring Realisasi & SBML</div>
                            <div class="text-white-50 extra-small" style="line-height: 1.4;">Validasi otomatis batas honor per mitra untuk mencegah over-alokasi anggaran.</div>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon-box icon-box-green">
                            <i class="bi bi-file-earmark-check-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white mb-0.5" style="font-size: 0.95rem;">Otomatisasi Dokumen SPK & BAST</div>
                            <div class="text-white-50 extra-small" style="line-height: 1.4;">Pencetakan massal Surat Perintah Kerja dan BAST kegiatan statistik dalam hitungan detik.</div>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon-box icon-box-amber">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white mb-0.5" style="font-size: 0.95rem;">Database Mitra Statistik Terpusat</div>
                            <div class="text-white-50 extra-small" style="line-height: 1.4;">Pengelolaan riwayat penugasan mitra per bidang secara transparan dan akurat.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Scripts -->
    <script>
        // Password Eye Toggle: Default is password hidden (eye-slash), click reveals password (eye)
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPasswordHidden = passwordInput.type === 'password';
                passwordInput.type = isPasswordHidden ? 'text' : 'password';
                toggleIcon.className = isPasswordHidden ? 'bi bi-eye text-primary' : 'bi bi-eye-slash';
                toggleBtn.title = isPasswordHidden ? 'Sembunyikan Kata Sandi' : 'Lihat Kata Sandi';
            });
        }
    </script>
</body>
</html>
