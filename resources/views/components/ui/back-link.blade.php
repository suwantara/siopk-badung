@props(['href', 'label' => 'Kembali'])

<div class="mb-3">
    <a href="{{ $href }}" style="color:var(--emas);text-decoration:none" class="t-body">
        <i class="bi bi-arrow-left me-1"></i>{{ $label }}
    </a>
</div>
