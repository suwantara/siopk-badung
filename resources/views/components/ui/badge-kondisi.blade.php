@props(['kondisi', 'size' => 'sm'])

@php
$sizeClass = $size === 'sm' ? 'font-size:0.68rem;' : 'font-size:0.72rem;';
@endphp
<span class="badge badge-{{ $kondisi }} rounded-pill px-2 py-1" style="{{ $sizeClass }}">
    {{ ucfirst($kondisi) }}
</span>
