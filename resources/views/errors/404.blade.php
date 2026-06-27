@extends('layouts.publik')
@section('title', '404 — Halaman Tidak Ditemukan')
@section('content')
<div style="max-width:500px;margin:4rem auto;text-align:center;padding:2rem;">
    <div style="font-size:5rem;color:var(--kuning);margin-bottom:1rem;">
        <i class="bi bi-map"></i>
    </div>
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-weight:700;color:var(--tanah);margin-bottom:0.5rem;">404</h1>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--tanah);margin-bottom:1rem;">Halaman Tidak Ditemukan</h2>
    <p style="color:var(--abu-gelap);margin-bottom:1.5rem;font-size:0.9rem;">Halaman yang Anda cari tidak dapat ditemukan. Mungkin telah dipindahkan, dihapus, atau alamat yang dimasukkan kurang tepat.</p>
    <a href="{{ route('publik.dashboard') }}" class="btn" style="background:var(--emas);color:var(--tanah);font-weight:600;padding:8px 20px;text-decoration:none;">
        <i class="bi bi-house me-2"></i>Kembali ke Beranda
    </a>
</div>
@endsection
