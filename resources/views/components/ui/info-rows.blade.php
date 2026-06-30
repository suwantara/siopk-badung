@props(['rows', 'keyWidth' => '90px'])

@foreach($rows as $row)
    @php [$label, $value] = $row @endphp
    @if($value)
    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--garis-terang)" class="t-body">
        <span style="color:var(--abu);width:{{ $keyWidth }};flex-shrink:0;">{{ $label }}</span>
        <span style="font-weight:500;text-align:right;word-break:break-all;">{{ $value }}</span>
    </div>
    @endif
@endforeach
