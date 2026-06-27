<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'SIOPK Badung') — Sistem Informasi OPK Kabupaten Badung</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
@auth
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo" style="background: transparent; overflow: hidden; padding: 0;">
			<img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
		</div>
        <div class="sidebar-title">
            SIOPK
            <small>Kabupaten Badung</small>
        </div>
    </div>

    <div class="sidebar-section">Utama</div>
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
    </a>
    <a href="{{ route('admin.opk.index') }}" class="sidebar-link {{ request()->routeIs('admin.opk.*') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> Data OPK
    </a>
    <a href="{{ route('admin.verifikasi.index') }}" class="sidebar-link {{ request()->routeIs('admin.verifikasi.*') ? 'active' : '' }}">
        <i class="bi bi-check2-circle"></i> Verifikasi
        @if(($sidebarAntrian ?? 0) > 0)
            <span class="badge bg-danger badge">{{ $sidebarAntrian }}</span>
        @endif
    </a>

    <div class="sidebar-section">Peta & Analitik</div>
    <a href="{{ route('admin.opk.peta') }}" class="sidebar-link {{ request()->routeIs('admin.opk.peta') ? 'active' : '' }}">
        <i class="bi bi-map"></i> Peta OPK
    </a>
    <a href="{{ route('admin.laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line"></i> Laporan
    </a>
    <a href="{{ route('admin.ai.ringkasan-halaman') }}" class="sidebar-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
        <i class="bi bi-robot"></i> AI Ringkasan
        @if(($sidebarAiKritis ?? 0) > 0)
            <span class="badge bg-danger badge">{{ $sidebarAiKritis }}</span>
        @endif
    </a>

    <div class="sidebar-section">Sistem</div>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.pengguna.index') }}" class="sidebar-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Pengguna
    </a>
    <a href="{{ route('admin.wilayah.index') }}" class="sidebar-link {{ request()->routeIs('admin.wilayah.*') ? 'active' : '' }}">
        <i class="bi bi-geo-alt"></i> Wilayah
    </a>
    <a href="{{ route('admin.kategori.index') }}" class="sidebar-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
        <i class="bi bi-tags"></i> Kategori OPK
    </a>
    <a href="{{ route('admin.opk.arsip') }}" class="sidebar-link {{ request()->routeIs('admin.opk.arsip') ? 'active' : '' }}">
        <i class="bi bi-archive"></i> Arsip OPK
    </a>
    @endif
    <a href="{{ route('publik.dashboard') }}" class="sidebar-link" target="_blank">
        <i class="bi bi-globe"></i> Portal Publik
    </a>

    <div class="mt-4" style="padding:1rem 1.5rem;border-top:1px solid rgba(var(--emas-rgb),0.15);">
        <div style="font-size:0.72rem;color:rgba(247,241,232,0.4);">Login sebagai</div>
        <div style="font-size:0.82rem;color:#e8b84b;font-weight:600;margin-top:2px;">
            {{ auth()->user()->name }}
        </div>
        <div style="font-size:0.68rem;color:rgba(247,241,232,0.35);text-transform:uppercase;letter-spacing:0.08em;">
            {{ auth()->user()->role }}
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" style="background:none;border:none;color:rgba(var(--emas-rgb),0.5);font-size:0.75rem;cursor:pointer;padding:0;">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    </div>
</div>
@endauth

{{-- Topbar --}}
@auth
<div class="topbar">
    <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
    <div class="d-flex align-items-center gap-3">
        <small class="text-muted">{{ now()->isoFormat('dddd, D MMMM Y') }}</small>
        <a href="{{ route('publik.lapor.index') }}" target="_blank"
           class="btn btn-sm btn-emas">
            <i class="bi bi-plus-circle"></i> Lapor OPK
        </a>
    </div>
</div>
@endauth

{{-- Main Content --}}
<div class="{{ auth()->check() ? 'main-wrapper' : '' }}">
    @yield('content')
</div>

<script>
'use strict';
window.swalKonfirmasi = function(opts) {
    Swal.fire({
        title: opts.title || 'Konfirmasi',
        text: opts.text || '',
        icon: opts.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: opts.confirmText || 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: opts.confirmColor || 'var(--emas)',
        cancelButtonColor: 'var(--abu-gelap)',
        reverseButtons: true,
        allowOutsideClick: false,
    }).then(function(result) {
        if (result.isConfirmed && opts.onConfirm) {
            opts.onConfirm();
        }
    });
};
window.swalToast = function(icon, title) {
    Swal.fire({ toast: true, position: 'top-end', icon: icon, title: title, showConfirmButton: false, timer: 4000, timerProgressBar: true });
};

document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    if (typeof Swal !== 'undefined') {
        swalToast('success', @json(session('success')));
    }
    @endif
    @if(session('error'))
    if (typeof Swal !== 'undefined') {
        swalToast('error', @json(session('error')));
    }
    @endif
});
</script>

@stack('scripts')
</body>
</html>
