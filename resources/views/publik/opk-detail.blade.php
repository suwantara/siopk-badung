@extends('layouts.publik')

@section('title', $opk->nama_opk . ' — SIOPK Badung')

@push('styles')
<style>
    .container-detail { max-width: 900px; margin: 0 auto; padding: 1.5rem 1rem; }
    .card-detail { background: white; border: 1px solid var(--garis); border-radius: 4px; margin-bottom: 1.2rem; overflow: hidden; }
    .section-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--abu); margin-bottom: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--garis-terang); font-size: 0.83rem; }
    .info-row:last-child { border-bottom: none; }
    .info-key { color: var(--abu); width: 130px; flex-shrink: 0; }
    .badge-kritis  { background: rgba(192,57,43,0.1); color: var(--merah); border: 1px solid rgba(192,57,43,0.2); padding: 3px 12px; border-radius: 10px; font-size: 0.72rem; font-weight: 600; }
    .badge-waspada { background: rgba(212,160,23,0.1); color: var(--kuning); border: 1px solid rgba(212,160,23,0.2); padding: 3px 12px; border-radius: 10px; font-size: 0.72rem; font-weight: 600; }
    .badge-baik    { background: rgba(45,90,39,0.1); color: var(--hijau); border: 1px solid rgba(45,90,39,0.2); padding: 3px 12px; border-radius: 10px; font-size: 0.72rem; font-weight: 600; }
    .foto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
    .foto-item { aspect-ratio: 1; border-radius: 3px; overflow: hidden; cursor: pointer; background: var(--placeholder); }
    .foto-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s; }
    .foto-item:hover img { transform: scale(1.05); }

    .foto-slider { position: relative; }
    .foto-slider-main { position: relative; width: 100%; aspect-ratio: 16/11; border-radius: 4px; overflow: hidden; background: var(--tanah-gelap); cursor: pointer; }
    .foto-slider-main img { width: 100%; height: 100%; object-fit: contain; transition: opacity 0.3s; }
    .foto-slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.45); color: white; border: none; width: 38px; height: 38px; border-radius: 50%; font-size: 1rem; cursor: pointer; z-index: 2; transition: background 0.2s; display: flex; align-items: center; justify-content: center; }
    .foto-slider-arrow:hover { background: rgba(0,0,0,0.7); }
    .foto-slider-prev { left: 10px; }
    .foto-slider-next { right: 10px; }
    .foto-slider-counter { position: absolute; bottom: 10px; right: 14px; background: rgba(0,0,0,0.55); color: white; font-size: 0.72rem; padding: 3px 10px; border-radius: 10px; }
    .foto-slider-thumbs { display: flex; gap: 6px; margin-top: 8px; overflow-x: auto; padding-bottom: 4px; }
    .foto-slider-thumb { width: 64px; height: 48px; border-radius: 3px; overflow: hidden; cursor: pointer; flex-shrink: 0; border: 2px solid transparent; transition: border-color 0.2s; opacity: 0.55; }
    .foto-slider-thumb.active { border-color: var(--emas); opacity: 1; }
    .foto-slider-thumb:hover { opacity: 0.85; }
    .foto-slider-thumb img { width: 100%; height: 100%; object-fit: cover; }
    @media (max-width: 576px) {
        .foto-slider-thumb { width: 48px; height: 36px; }
        .foto-slider-arrow { width: 32px; height: 32px; font-size: 0.85rem; }
    }
</style>
@endpush

@section('content')
<div class="container-detail">

    <!-- Header -->
    <div class="card-detail">
        <div style="padding:1.5rem;">
            <div class="d-flex gap-3 align-items-start">
                <div style="width:80px;height:80px;border-radius:4px;overflow:hidden;flex-shrink:0;background:var(--placeholder);display:flex;align-items:center;justify-content:center;font-size:2rem;">
                    @if($opk->fotoUtama)
                        <img src="{{ asset('storage/'.$opk->fotoUtama->path) }}" style="width:100%;height:100%;object-fit:cover;">
                    @else {{ $opk->kategori?->ikon ?? '🏛️' }} @endif
                </div>
                <div class="flex-grow-1">
                    <div style="font-size:0.65rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">{{ $opk->kode_laporan }}</div>
                    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;margin-bottom:8px;line-height:1.2;">{{ $opk->nama_opk }}</h1>
                    <div class="d-flex gap-2 flex-wrap">
                        <span style="background:rgba(200,146,42,0.1);color:var(--emas-gelap);padding:3px 10px;border-radius:2px;font-size:0.75rem;font-weight:500;">
                            {{ $opk->kategori?->ikon }} {{ $opk->kategori?->nama }}
                        </span>
                        <span class="badge-{{ $opk->kondisi }}">{{ ucfirst($opk->kondisi) }}</span>
                        <span style="background:rgba(45,90,39,0.1);color:var(--hijau);padding:3px 10px;border-radius:2px;font-size:0.72rem;font-weight:500;">
                            <i class="bi bi-check-circle"></i> Terverifikasi Dinas Kebudayaan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">

            @if($opk->fotos->count() > 0)
            <div class="card-detail">
                <div style="padding:1.2rem;">
                    <div class="section-label">Foto Dokumentasi ({{ $opk->fotos->count() }})</div>
                    <div id="foto-slider" class="foto-slider">
                        <div class="foto-slider-main" onclick="openFoto(document.getElementById('sliderImg').src)">
                            <button class="foto-slider-arrow foto-slider-prev" onclick="event.stopPropagation(); sliderPrev()">&#10094;</button>
                            <img id="sliderImg" src="{{ asset('storage/'.$opk->fotos->first()->path) }}" alt="">
                            <button class="foto-slider-arrow foto-slider-next" onclick="event.stopPropagation(); sliderNext()">&#10095;</button>
                            <div class="foto-slider-counter" id="sliderCounter">1 / {{ $opk->fotos->count() }}</div>
                        </div>
                        <div class="foto-slider-thumbs" id="sliderThumbs">
                            @foreach($opk->fotos as $i => $foto)
                            <div class="foto-slider-thumb {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" onclick="sliderGo({{ $i }})">
                                <img src="{{ asset('storage/'.$foto->path) }}" alt="">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card-detail">
                <div style="padding:1.2rem;">
                    <div class="section-label">Deskripsi Umum</div>
                    <p style="font-size:0.88rem;line-height:1.8;color:var(--teks);margin-bottom:1rem;">{{ $opk->deskripsi_umum }}</p>

                    @if($opk->sejarah_asal_usul)
                    <div class="section-label" style="margin-top:1rem;">Sejarah & Asal-Usul</div>
                    <p style="font-size:0.88rem;line-height:1.8;color:var(--teks);margin-bottom:0;">{{ $opk->sejarah_asal_usul }}</p>
                    @endif

                    @if($opk->nilai_makna_budaya)
                    <div class="section-label" style="margin-top:1rem;">Nilai & Makna Budaya</div>
                    <p style="font-size:0.88rem;line-height:1.8;color:var(--teks);margin-bottom:0;">{{ $opk->nilai_makna_budaya }}</p>
                    @endif
                </div>
            </div>

            @if($opk->videos->count() > 0)
            <div class="card-detail">
                <div style="padding:1.2rem;">
                    <div class="section-label">Video Dokumentasi</div>
                    @foreach($opk->videos as $video)
                        @if($video->link_eksternal)
                        <a href="{{ $video->link_eksternal }}" target="_blank" rel="noopener"
                           class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none"
                           style="background:var(--krem);border:1px solid var(--garis);color:var(--tanah);">
                            <i class="bi bi-play-circle" style="color:var(--emas);font-size:1.2rem;"></i>
                            <span style="font-size:0.82rem;">Buka Video Dokumentasi</span>
                            <i class="bi bi-box-arrow-up-right ms-auto" style="font-size:0.75rem;color:var(--abu);"></i>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <div class="col-md-4">

            <div class="card-detail">
                <div style="padding:1.2rem 1.2rem 0;">
                    <div class="section-label">Lokasi</div>
                </div>
                @if($opk->latitude && $opk->longitude)
                <div id="petaMini" style="height:160px;"></div>
                @endif
                <div style="padding:0.75rem 1.2rem;">
                    @foreach([
                        ['Kecamatan', $opk->kecamatan?->nama],
                        ['Desa Dinas', $opk->desaDinas?->nama],
                        ['Desa Adat',  $opk->nama_desa_adat],
                        ['Banjar Adat',$opk->banjar_adat],
                        ['Lokasi',     $opk->lokasi_spesifik],
                    ] as [$k, $v])
                        @if($v)
                        <div class="info-row">
                            <span class="info-key">{{ $k }}</span>
                            <span style="font-weight:500;text-align:right;">{{ $v }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="card-detail">
                <div style="padding:1.2rem 1.2rem 0;"><div class="section-label">Informasi OPK</div></div>
                <div style="padding:0 1.2rem 0.75rem;">
                    @foreach([
                        ['Tahun',        $opk->tahun_keterangan ?? ($opk->tahun_diketahui ? (string)$opk->tahun_diketahui : null)],
                        ['Pelindungan',  ucwords(str_replace('_',' ',$opk->status_pelindungan))],
                        ['Bahasa',       $opk->bahasa_digunakan],
                        ['Aksara',       $opk->aksara_digunakan],
                        ['Frekuensi',    $opk->frekuensi_pelaksanaan ? ucwords(str_replace('_',' ',$opk->frekuensi_pelaksanaan)) : null],
                        ['Kepemilikan',  $opk->status_kepemilikan ? ucwords(str_replace('_',' ',$opk->status_kepemilikan)) : null],
                    ] as [$k,$v])
                        @if($v)
                        <div class="info-row">
                            <span class="info-key">{{ $k }}</span>
                            <span style="font-weight:500;text-align:right;">{{ $v }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div style="background:linear-gradient(135deg,var(--tanah),#3d2410);border-radius:4px;padding:1.2rem;text-align:center;">
                <i class="bi bi-heart" style="font-size:1.3rem;color:var(--emas-muda);margin-bottom:6px;display:block;"></i>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:600;color:var(--krem);margin-bottom:6px;">Tahu OPK lain?</div>
                <p style="font-size:0.75rem;color:rgba(247,241,232,0.65);margin-bottom:12px;line-height:1.6;">Bantu kami memetakan lebih banyak warisan budaya Bali.</p>
                <a href="{{ route('publik.lapor.index') }}"
                   style="display:block;background:var(--emas);color:var(--tanah);padding:8px;border-radius:3px;text-decoration:none;font-size:0.82rem;font-weight:600;">
                    Lapor OPK Sekarang
                </a>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:var(--tanah-gelap);border:none;">
            <div class="modal-header" style="border-bottom:1px solid rgba(200,146,42,0.2);">
                <span id="modalCounter" style="color:var(--abu);font-size:0.78rem;"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2" style="position:relative;">
                <img id="fotoSrc" src="" style="width:100%;border-radius:3px;max-height:75vh;object-fit:contain;">
                <button class="foto-slider-arrow foto-slider-prev" style="left:6px;" onclick="modalPrev()">&#10094;</button>
                <button class="foto-slider-arrow foto-slider-next" style="right:6px;" onclick="modalNext()">&#10095;</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const fotoList = [
    @foreach($opk->fotos as $foto)
    '{{ asset('storage/'.$foto->path) }}'@if(!$loop->last),@endif
    @endforeach
];
let sliderIndex = 0;
const totalFoto = fotoList.length;

function sliderGo(idx) {
    if (idx < 0) idx = totalFoto - 1;
    if (idx >= totalFoto) idx = 0;
    sliderIndex = idx;
    document.getElementById('sliderImg').src = fotoList[idx];
    document.getElementById('sliderCounter').textContent = (idx + 1) + ' / ' + totalFoto;
    document.querySelectorAll('.foto-slider-thumb').forEach(function(el, i) {
        el.classList.toggle('active', i === idx);
    });
    var activeThumb = document.querySelector('.foto-slider-thumb.active');
    if (activeThumb) activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
}
function sliderPrev() { sliderGo(sliderIndex - 1); }
function sliderNext() { sliderGo(sliderIndex + 1); }

document.getElementById('foto-slider').addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') { e.preventDefault(); sliderPrev(); }
    if (e.key === 'ArrowRight') { e.preventDefault(); sliderNext(); }
});

@if($opk->fotos->count() > 1)
var touchStartX = 0;
document.getElementById('foto-slider').addEventListener('touchstart', function(e) { touchStartX = e.touches[0].clientX; });
document.getElementById('foto-slider').addEventListener('touchend', function(e) {
    var diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) { diff > 0 ? sliderNext() : sliderPrev(); }
});
@endif

function openFoto(src) {
    document.getElementById('fotoSrc').src = src;
    document.getElementById('modalCounter').textContent = (sliderIndex + 1) + ' / ' + totalFoto;
    new bootstrap.Modal(document.getElementById('modalFoto')).show();
}
function modalPrev() { sliderPrev(); document.getElementById('fotoSrc').src = fotoList[sliderIndex]; document.getElementById('modalCounter').textContent = (sliderIndex + 1) + ' / ' + totalFoto; }
function modalNext() { sliderNext(); document.getElementById('fotoSrc').src = fotoList[sliderIndex]; document.getElementById('modalCounter').textContent = (sliderIndex + 1) + ' / ' + totalFoto; }

document.getElementById('foto-slider').setAttribute('tabindex', '0');

document.addEventListener('keydown', function(e) {
    var modal = document.getElementById('modalFoto');
    if (!modal.classList.contains('show')) return;
    if (e.key === 'ArrowLeft') { e.preventDefault(); modalPrev(); }
    if (e.key === 'ArrowRight') { e.preventDefault(); modalNext(); }
});

@if($opk->latitude && $opk->longitude)
const m = L.map('petaMini').setView([{{ $opk->latitude }}, {{ $opk->longitude }}], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19 }).addTo(m);
L.marker([{{ $opk->latitude }}, {{ $opk->longitude }}]).addTo(m);
@endif
</script>
@endpush
