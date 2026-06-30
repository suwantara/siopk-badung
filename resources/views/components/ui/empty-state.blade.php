@props(['icon' => 'bi-inbox', 'message' => 'Tidak ada data.', 'colspan' => null])

@if($colspan)
<tr>
    <td colspan="{{ $colspan }}" class="text-center py-5 text-muted">
        <i class="bi {{ $icon }}" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
        {{ $message }}
    </td>
</tr>
@else
<div style="text-align:center;padding:3rem 1rem;color:var(--abu);">
    <i class="bi {{ $icon }}" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
    <span class="t-body">{{ $message }}</span>
</div>
@endif
