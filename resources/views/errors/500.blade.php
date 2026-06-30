@extends('layouts.publik')
@section('title', '500 — Kesalahan Server')
@section('content')
<div style="max-width:500px;margin:4rem auto;text-align:center;padding:2rem;">
    <div style="font-size:5rem;color:var(--merah);margin-bottom:1rem;">
        <i class="bi bi-exclamation-triangle"></i>
    </div>
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-weight:700;color:var(--tanah);margin-bottom:0.5rem;">500</h1>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--tanah);margin-bottom:1rem;">Kesalahan Server</h2>
    <p style="color:var(--abu-gelap);margin-bottom:1.5rem" class="t-body-lg">Terjadi kesalahan pada server. Tim teknis Dinas Kebudayaan Kabupaten Badung telah diberitahu. Silakan coba lagi dalam beberapa saat.</p>
     <a href="{{ route('publik.dashboard') }}" class="btn-emas">
        <i class="bi bi-house me-2"></i>Kembali ke Beranda
    </a>
</div>
@endsection
