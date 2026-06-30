@props(['resetUrl' => null])

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" {{ $attributes->except('resetUrl') }}>
            <div class="row g-2 align-items-end filter-row">
                {{ $slot }}
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-emas me-1">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    @if($resetUrl)
                        <a href="{{ $resetUrl }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
