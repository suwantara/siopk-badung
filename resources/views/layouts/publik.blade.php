<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIOPK Badung') — Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung</title>

    <meta name="description" content="@yield('meta-description', 'SIOPK Badung — Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung. Jelajahi peta interaktif OPK, laporkan objek kebudayaan baru, dan cek status laporan Anda secara online.')">
    <meta name="keywords" content="SIOPK Badung, OPK Badung, Objek Pemajuan Kebudayaan, kebudayaan Bali, warisan budaya Badung, peta budaya, cagar budaya Badung, inventarisasi budaya, lapor budaya, Disbud Badung, Dinas Kebudayaan Badung, budaya Bali, tradisi Badung">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="@yield('title', 'SIOPK Badung') — Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung">
    <meta property="og:description" content="@yield('meta-description', 'SIOPK Badung — Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung. Jelajahi peta interaktif OPK, laporkan objek kebudayaan baru, dan cek status laporan Anda secara online.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SIOPK Badung">
    <meta property="og:locale" content="id_ID">
    <meta property="og:image" content="{{ asset('img/logo.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="Logo SIOPK Badung">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'SIOPK Badung') — Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung">
    <meta name="twitter:description" content="@yield('meta-description', 'SIOPK Badung — Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung. Jelajahi peta interaktif OPK, laporkan objek kebudayaan baru, dan cek status laporan Anda.')">
    <meta name="twitter:image" content="{{ asset('img/logo.png') }}">
    <meta name="twitter:image:alt" content="Logo SIOPK Badung">

    <link rel="alternate" hreflang="id" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <script type="application/ld+json">
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@@type' => 'WebSite',
            'name' => 'SIOPK Badung',
            'alternateName' => 'Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung',
            'url' => url('/'),
            'description' => 'Sistem Informasi Objek Pemajuan Kebudayaan Kabupaten Badung — portal resmi inventarisasi dan pemetaan objek kebudayaan di Kabupaten Badung, Bali.',
            'publisher' => [
                '@type' => 'GovernmentOrganization',
                'name' => 'Dinas Kebudayaan Kabupaten Badung',
                'url' => url('/'),
            ],
            'inLanguage' => 'id',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/') . '?cari={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body>

<nav class="publik-nav" role="navigation" aria-label="Navigasi Utama">
        <a href="{{ route('publik.dashboard') }}" class="nav-brand" aria-label="SIOPK Badung — Beranda">
            <div class="nav-logo" style="background: transparent; overflow: hidden; padding: 0;">
                <img src="{{ asset('img/logo.png') }}" alt="Logo SIOPK Badung" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </div>
            <div class="nav-title">SIOPK <span>Badung</span></div>
        </a>
    <button class="nav-toggle" id="navToggle" onclick="document.getElementById('navLinks').classList.toggle('open')" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>
    <div class="nav-links" id="navLinks">
        <a href="{{ route('publik.dashboard') }}" class="{{ request()->routeIs('publik.dashboard') ? 'active' : '' }}" onclick="document.getElementById('navLinks').classList.remove('open')">
            <i class="bi bi-map"></i> Peta OPK
        </a>
        <a href="{{ route('publik.daftar-opk') }}" class="{{ request()->routeIs('publik.daftar-opk') ? 'active' : '' }}" onclick="document.getElementById('navLinks').classList.remove('open')">
            <i class="bi bi-list-ul"></i> Daftar OPK
        </a>
        <a href="{{ route('publik.lapor.index') }}" class="{{ request()->routeIs('publik.lapor.index') ? 'active' : '' }}" onclick="document.getElementById('navLinks').classList.remove('open')">
            <i class="bi bi-plus-circle"></i> Lapor OPK
        </a>
        <a href="{{ route('publik.lapor.status') }}" class="{{ request()->routeIs('publik.lapor.status') ? 'active' : '' }}" onclick="document.getElementById('navLinks').classList.remove('open')">
            <i class="bi bi-search"></i> Cek Status
        </a>
        @php
            $loggedIn = auth()->check();
            $showLaporBtn = !request()->routeIs('publik.lapor.*');
        @endphp
        @if($loggedIn)
        <a href="{{ route('admin.dashboard') }}" class="d-md-none" onclick="document.getElementById('navLinks').classList.remove('open')">
            <i class="bi bi-speedometer2"></i> Panel Admin
        </a>
        @else
        <a href="{{ route('login') }}" class="d-md-none" onclick="document.getElementById('navLinks').classList.remove('open')">
            <i class="bi bi-shield-lock"></i> Login Dinas
        </a>
        @endif
    </div>
    <div class="nav-actions">
        @if($loggedIn)
            <a href="{{ route('admin.dashboard') }}" class="nav-login-link d-none d-md-inline">
                <i class="bi bi-speedometer2"></i> Panel Admin
            </a>
        @else
            <a href="{{ route('login') }}" class="nav-login-link d-none d-md-inline">
                <i class="bi bi-shield-lock"></i> Login Dinas
            </a>
        @endif
        @if($showLaporBtn)
            <a href="{{ route('publik.lapor.index') }}" class="btn-lapor d-none d-md-inline-block">
                <i class="bi bi-plus-circle"></i> Lapor Sekarang
            </a>
        @endif
    </div>
</nav>

@yield('content')

@stack('scripts')
</body>
</html>
