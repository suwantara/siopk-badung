@props(['score', 'kondisi' => 'baik', 'size' => 'sm', 'dark' => false])

@php
if ($dark) {
    $color = match($kondisi) {
        'kritis'  => '#f08080',
        'waspada' => '#e8c55a',
        default   => '#7ec87e'
    };
} else {
    $color = match($kondisi) {
        'kritis'  => 'var(--merah)',
        'waspada' => 'var(--kuning)',
        default   => 'var(--hijau)'
    };
}
$sizeStyle = $size === 'lg' ? 'font-size:1.3rem;' : 'font-size:0.82rem;';
@endphp

@if($score !== null)
<span style="font-family:'Courier New',monospace;{{ $sizeStyle }}font-weight:700;color:{{ $color }}">
    {{ number_format($score, 1) }}
</span>
@else
<span style="color:var(--abu);" class="t-caption">—</span>
@endif
