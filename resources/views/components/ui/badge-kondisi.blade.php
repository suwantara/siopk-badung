@props(['kondisi', 'size' => 'sm'])

@php
$sizeClass = $size === 'sm' ? 'font-size:0.68rem;' : 'font-size:0.72rem;';
@endphp
<span class="badge-kondisi-{{ $kondisi }} rounded-pill px-2 py-1" style="{{ $sizeClass }}">
    @php
    $list = \Illuminate\Support\Facades\Cache::remember(
        \App\Helpers\CacheKeys::KONDISI_LIST,
        now()->addHours(24),
        fn () => \App\Models\KondisiOpk::orderBy('urutan')->get()
    );
    $kondisiOpk = $list->firstWhere('kode', $kondisi);
    @endphp
    {{ $kondisiOpk?->nama ?? ucfirst($kondisi) }}
</span>
