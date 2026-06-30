@props(['label', 'value'])
@if($value)
<div style="display:flex;justify-content:space-between;align-items:flex-start;padding:6px 0;border-bottom:1px solid var(--garis-terang)" class="t-body">
    <span style="color:var(--abu);width:110px;flex-shrink:0;">{{ $label }}</span>
    <span style="font-weight:500;text-align:right;word-break:break-all;">{{ $value }}</span>
</div>
@endif
