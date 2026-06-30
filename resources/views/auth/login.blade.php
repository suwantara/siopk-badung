<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIOPK Badung</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="login-page-body">
<div class="login-card">
    <div class="login-header">
        <div class="login-logo" style="background: transparent; overflow: hidden; padding: 0;">
            <img src="{{ asset('img/logo.png') }}" alt="Logo SIOPK Badung" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
                <label style="color:var(--abu-gelap);display:flex;align-items:center;cursor:pointer" class="gap-xs" class="t-body">
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
</body>
</html>
