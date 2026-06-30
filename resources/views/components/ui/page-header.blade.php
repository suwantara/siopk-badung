@props(['title', 'subtitle' => null, 'actionLabel' => null, 'actionUrl' => null, 'actionIcon' => 'bi-plus-circle', 'actionTarget' => null])

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">{!! $title !!}</h1>
        @if($subtitle)
            <p class="text-muted mb-0 t-body">{!! $subtitle !!}</p>
        @endif
    </div>
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}"
           @if($actionTarget) target="{{ $actionTarget }}" @endif
           class="btn btn-emas btn-sm">
            <i class="bi {{ $actionIcon }} me-1"></i>{{ $actionLabel }}
        </a>
    @elseif(isset($action))
        <div>{{ $action }}</div>
    @endif
</div>
