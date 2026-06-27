<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIOPK Badung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --tanah:var(--tanah); --emas:var(--emas); --emas-muda:var(--emas-muda); }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh; margin: 0;
            background: linear-gradient(135deg, var(--tanah) 0%, #3d2410 60%, var(--tanah-gelap) 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: white; border-radius: 6px;
            width: 100%; max-width: 420px;
            border-top: 4px solid var(--emas);
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            overflow: hidden;
        }
        .login-header {
            background: var(--tanah);
            padding: 2rem; text-align: center;
        }
        .login-logo {
            width: 52px; height: 52px; border-radius: 50%;
            background: var(--emas); margin: 0 auto 1rem;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-weight: 700; font-size: 1.4rem; color: var(--tanah);
        }
        .login-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem; font-weight: 700; color: var(--krem);
        }
        .login-sub { font-size: 0.75rem; color: rgba(232,184,75,0.7); margin-top: 4px; }
        .login-body { padding: 2rem; }
        .form-label { font-size: 0.78rem; font-weight: 600; color: var(--tanah); text-transform: uppercase; letter-spacing: 0.06em; }
        .form-control {
            border: 1px solid var(--garis); border-radius: 3px;
            font-size: 0.88rem; padding: 10px 14px;
            background: var(--input-bg);
        }
        .form-control:focus { border-color: var(--emas); box-shadow: 0 0 0 3px rgba(200,146,42,0.12); }
        .btn-login {
            background: var(--emas); color: var(--tanah);
            border: none; width: 100%; padding: 12px;
            font-weight: 700; font-size: 0.9rem;
            border-radius: 3px; letter-spacing: 0.05em;
            text-transform: uppercase; cursor: pointer;
            transition: all 0.2s;
        }
        .btn-login:hover { background: var(--emas-muda); }
        .alert-danger { background: rgba(192,57,43,0.08); border: none; border-left: 3px solid var(--merah); color: var(--merah); font-size: 0.82rem; border-radius: 3px; }
        .login-footer { text-align: center; padding: 1rem 2rem 1.5rem; font-size: 0.75rem; color: var(--abu); }
        .login-footer a { color: var(--emas); text-decoration: none; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <div class="login-logo" style="background: transparent; overflow: hidden; padding: 0;">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>
        <div class="login-title">SIOPK Badung</div>
        <div class="login-sub">Sistem Informasi OPK Kabupaten Badung</div>
    </div>
    <div class="login-body">
        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="email@siopk-badung.id" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <label style="font-size:0.78rem;color:var(--abu-gelap);display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>
    </div>
    <div class="login-footer">
        Bukan petugas Dinas? &nbsp;
        <a href="{{ route('publik.lapor.index') }}">Lapor OPK sebagai masyarakat →</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
