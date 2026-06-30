@extends('layouts.publik')
@section('title', '403 — Akses Ditolak')
@section('content')
<div style="max-width:500px;margin:4rem auto;text-align:center;padding:2rem;">
    <div style="font-size:5rem;color:var(--merah);margin-bottom:1rem;">
        <i class="bi bi-shield-lock"></i>
    </div>
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-weight:700;color:var(--tanah);margin-bottom:0.5rem;">403</h1>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--tanah);margin-bottom:1rem;">Akses Ditolak</h2>
    <p style="color:var(--abu-gelap);margin-bottom:1.5rem" class="t-body-lg">Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator Dinas Kebudayaan Kabupaten Badung jika Anda merasa ini adalah kesalahan.</p>
     <a href="{{ route('publik.dashboard') }}" class="btn-emas btn-lg">
        <i class="bi bi-house me-2"></i>Kembali ke Beranda
    </a>
</div>
@endsection
