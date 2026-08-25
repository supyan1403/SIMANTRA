<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMANTRA BPS Kab. Tasikmalaya</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_kendi_trans.png') }}">
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --bps-blue: #2563eb;
            --bps-navy: #0f172a;
            --bps-green: #16a34a;
            --bps-orange: #f97316;
            --bps-dark: #0f172a;
        }

        html, body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden !important; /* Strict No Scrollbar */
            font-family: var(--bs-body-font-family);
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }

        /* 2-Column Split Card (Clean & Minimalist BPS) */
        .login-split-card {
            width: 900px;
            max-width: 94vw;
            height: 540px;
            max-height: 94vh;
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12);
            border: 1px solid #cbd5e1;
            display: flex;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* Left Column: Form Inputs (52% width) */
        .split-left {
            width: 52%;
            padding: 2.25rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
        }

        /* Right Column: Minimalist BPS Banner (48% width) */
        .split-right {
            width: 48%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            color: #ffffff;
            overflow: hidden;
        }

        .split-right::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -30%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        /* Brand Elements */
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.25rem;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .welcome-title {
            font-weight: 800;
            font-size: 1.3rem;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin-bottom: 0.2rem;
        }

        .welcome-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 1.15rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.775rem;
            color: #334155;
            margin-bottom: 0.3rem;
        }

        .input-group {
            border-radius: 0.55rem;
            overflow: hidden;
        }

        .input-group-text {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            color: #2563eb;
            font-size: 0.95rem;
            padding-left: 0.85rem;
            padding-right: 0.65rem;
        }

        .form-control {
            border-color: #cbd5e1;
            padding: 0.55rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #0f172a;
            background-color: #f8fafc;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .btn-toggle-eye {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            color: #64748b;
        }

        .btn-toggle-eye:hover {
            color: #2563eb;
            background-color: #ffffff;
        }

        .btn-login {
            background: #0f172a;
            border: 1px solid #0f172a;
            border-radius: 0.55rem;
            padding: 0.7rem 1rem;
            font-weight: 700;
            font-size: 0.875rem;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: #1e293b;
            border-color: #1e293b;
            color: #ffffff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.35);
            transform: translateY(-1px);
        }

        /* Demo Helper Card */
        .demo-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.6rem;
            padding: 0.6rem 0.75rem;
            margin-top: 0.85rem;
        }

        .demo-title {
            font-size: 0.675rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.35rem;
        }

        .demo-btn {
            border-radius: 0.45rem;
            padding: 0.3rem 0.5rem;
            font-weight: 600;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .demo-btn:hover {
            border-color: #0284c7;
            color: #0284c7;
            background: #f0f9ff;
        }

        .footer-text {
            font-size: 0.675rem;
            color: #94a3b8;
            margin-top: 0.65rem;
        }

        /* Right Banner Content */
        .bps-logo-hero-box {
            width: 72px;
            height: 72px;
            border-radius: 1.15rem;
            background: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .hero-title {
            font-weight: 800;
            font-size: 1.75rem;
            line-height: 1.2;
            letter-spacing: -0.4px;
            margin-bottom: 0.6rem;
        }

        .hero-description {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.5;
            font-weight: 500;
        }

        .hero-pill {
            background: rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 0.4rem 0.85rem;
            border-radius: 2rem;
            font-size: 0.725rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            width: fit-content;
        }

        .login-page-container {
            width: 900px;
            max-width: 94vw;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .login-back-link {
            color: #475569;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
            transition: all 0.2s ease;
            padding: 0.2rem 0;
        }

        .login-back-link:hover {
            color: #2563eb;
            transform: translateX(-4px);
        }
    </style>
</head>
<body>
    <div class="login-page-container">
        <div class="mb-3">
            <a href="{{ route('landing') }}" class="login-back-link">
                <i class="bi bi-arrow-left text-primary fs-6"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <div class="login-split-card">
        <!-- LEFT SIDE: Form Inputs -->
        <div class="split-left">
            <div>
                <div class="brand-badge mb-3">
                    <img src="{{ asset('images/logo_kendi_trans.png') }}" alt="SIMANTRA" style="width: 40px; height: 40px; object-fit: contain;">
                    <span>SIMANTRA</span>
                </div>
                
                <h1 class="welcome-title">Selamat Datang</h1>
                <p class="welcome-subtitle">Masukkan akun Anda untuk mengelola alokasi honor & mitra.</p>

                @if(session('status'))
                    <div class="alert alert-success border-0 small py-1.5 px-2.5 rounded-3 mb-2 d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <div style="font-size: 0.775rem;">{{ session('status') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Email Input -->
                    <div class="mb-2.5">
                        <label for="email" class="form-label">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="contoh@bps.go.id" required autofocus autocomplete="username">
                        </div>
                        @error('email') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Password Input with Eye Toggle -->
                    <div class="mb-2.5">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Masukkan password..." required autocomplete="current-password">
                            <button type="button" class="btn btn-toggle-eye" id="togglePasswordBtn" title="Lihat/Sembunyikan Password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password') <div class="text-danger small mt-1" style="font-size: 0.75rem;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="d-flex align-items-center justify-content-between mb-3 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" style="cursor: pointer;">
                            <label class="form-check-label small text-secondary" for="remember" style="cursor: pointer; font-size: 0.75rem;">
                                Ingat Sesi Login Saya
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-login w-100 d-flex align-items-center justify-content-center">
                        <span>Masuk Aplikasi</span>
                        <i class="bi bi-arrow-right-short fs-5 ms-1"></i>
                    </button>
                </form>
            </div>

            <div>
                <!-- Quick Demo Login Helper Badges -->
                <div class="demo-box">
                    <div class="demo-title">
                        <i class="bi bi-key-fill text-muted me-1"></i> Login Pengujian Cepat:
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="demo-btn" onclick="autoFillAndHighlight('admin@bps.go.id', 'password')" title="Klik untuk isi otomatis Admin">
                                <i class="bi bi-shield-lock text-danger"></i>
                                <span>Administrator</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="demo-btn" onclick="autoFillAndHighlight('operator@bps.go.id', 'password')" title="Klik untuk isi otomatis Operator">
                                <i class="bi bi-person text-success"></i>
                                <span>Operator</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-text">
                    &copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Tasikmalaya
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Minimalist BPS Banner -->
        <div class="split-right">
            <div>
                <div class="bps-logo-hero-box">
                    <img src="{{ asset('images/logo_kendi_trans.png') }}" alt="SIMANTRA" style="width: 46px; height: 46px; object-fit: contain;">
                </div>
                <h2 class="hero-title">Monitoring Alokasi Pekerjaan & Honor</h2>
                <p class="hero-description">
                    Sistem terintegrasi BPS Kabupaten Tasikmalaya untuk memantau alokasi honor mitra statistik secara akurat, efisien, dan transparan.
                </p>
            </div>

            <div>
                <div class="hero-pill">
                    <span>BPS KABUPATEN TASIKMALAYA</span>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                if (type === 'text') {
                    togglePasswordIcon.className = 'bi bi-eye-slash text-primary';
                } else {
                    togglePasswordIcon.className = 'bi bi-eye text-muted';
                }
            });
        }
    });

    function autoFillAndHighlight(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
    </script>
</body>
</html>
