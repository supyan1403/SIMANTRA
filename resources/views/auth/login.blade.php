<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMANTRA BPS Kab. Tasikmalaya</title>
    
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
            --bps-blue: #0284c7;
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
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
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
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
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
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            color: #ffffff;
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
            color: #0284c7;
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
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }

        .btn-toggle-eye {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            color: #64748b;
        }

        .btn-toggle-eye:hover {
            color: #0284c7;
            background-color: #ffffff;
        }

        .btn-login {
            background: #0284c7;
            border: none;
            border-radius: 0.55rem;
            padding: 0.65rem 1rem;
            font-weight: 700;
            font-size: 0.875rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
            transition: all 0.15s ease;
        }

        .btn-login:hover {
            background: #0369a1;
            color: #ffffff;
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
            gap: 0.4rem;
            width: fit-content;
        }
    </style>
</head>
<body>
    <div class="login-split-card">
        <!-- LEFT SIDE: Form Inputs -->
        <div class="split-left">
            <div>
                <div class="brand-badge mb-3">
                    <!-- Neat Sketch Vector Emblem (Kendi Statistik BPS) -->
                    <svg width="40" height="40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="50" cy="50" rx="44" ry="20" transform="rotate(-25 50 50)" stroke="#0284c7" stroke-width="2.5" stroke-dasharray="6 3 18 3" fill="none"/>
                        <path d="M40 15 H60 V24 C60 24 74 34 74 58 C74 78 62 88 50 88 C38 88 26 78 26 58 C26 34 40 24 40 24 Z" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.25"/>
                        <path d="M42 17 H58 V26 C58 26 72 36 72 60 C72 78 60 86 50 86 C40 86 28 78 28 60 C28 36 42 26 42 26 Z" fill="#f0fdf4" stroke="#0f172a" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M65 44 L80 37 V47 L69 51 Z" fill="#f97316" stroke="#0f172a" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M31 35 C20 35 18 54 28 62" stroke="#16a34a" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                        <path d="M43 37 H57 L48 48 L57 59 H43" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        <path d="M37 71 H45 M41 67 V75" stroke="#16a34a" stroke-width="3" stroke-linecap="round"/>
                        <path d="M55 71 H63 M59 66 A1 1 0 1 1 59 65 M59 76 A1 1 0 1 1 59 75" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                    </svg>
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
                    <!-- Neat Sketch Vector Emblem (Kendi Statistik BPS) -->
                    <svg width="46" height="46" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="50" cy="50" rx="44" ry="20" transform="rotate(-25 50 50)" stroke="#0284c7" stroke-width="2.5" stroke-dasharray="6 3 18 3" fill="none"/>
                        <path d="M40 15 H60 V24 C60 24 74 34 74 58 C74 78 62 88 50 88 C38 88 26 78 26 58 C26 34 40 24 40 24 Z" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.3"/>
                        <path d="M42 17 H58 V26 C58 26 72 36 72 60 C72 78 60 86 50 86 C40 86 28 78 28 60 C28 36 42 26 42 26 Z" fill="#ffffff" stroke="#0f172a" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M65 44 L80 37 V47 L69 51 Z" fill="#f97316" stroke="#0f172a" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M31 35 C20 35 18 54 28 62" stroke="#16a34a" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                        <path d="M43 37 H57 L48 48 L57 59 H43" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        <path d="M37 71 H45 M41 67 V75" stroke="#16a34a" stroke-width="3" stroke-linecap="round"/>
                        <path d="M55 71 H63 M59 66 A1 1 0 1 1 59 65 M59 76 A1 1 0 1 1 59 75" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <h2 class="hero-title">Monitoring Alokasi Pekerjaan & Honor</h2>
                <p class="hero-description">
                    Sistem terintegrasi BPS Kabupaten Tasikmalaya untuk memantau alokasi honor mitra statistik secara akurat, efisien, dan transparan.
                </p>
            </div>

            <div>
                <div class="hero-pill">
                    <i class="bi bi-building"></i>
                    <span>BPS KABUPATEN TASIKMALAYA</span>
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
