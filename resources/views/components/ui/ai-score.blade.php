@props(['score', 'kondisi' => 'baik', 'size' => 'sm', 'dark' => false])

@php
$sizeStyle = $size === 'lg' ? 'font-size:1.3rem;' : 'font-size:0.82rem;';
@endphp

@if($score !== null)
<span class="score-{{ $kondisi }}" style="font-family:'Courier New',monospace;{{ $sizeStyle }}font-weight:700;">
    {{ number_format($score, 1) }}
</span>
@else
<span style="color:var(--abu);" class="t-caption">—</span>
@endif
