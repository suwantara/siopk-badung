@props(['title' => null, 'score' => null, 'kondisi' => null])

<div class="ai-panel p-3">
    <div class="d-flex align-items-center gap-2 mb-2 pb-2" style="border-bottom:1px solid var(--border-emas);">
        <span class="ai-blink" style="width:7px;height:7px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>
        <span style="font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em" class="t-caption">
            {{ $title ?? 'AI · Analisis & Rekomendasi' }}
        </span>
        @if($score !== null)
        <span class="ms-auto">
            <x-ui.ai-score :score="$score" :kondisi="$kondisi" size="lg" />
            <span style="color:rgba(247,241,232,0.4)" class="t-caption">/10</span>
        </span>
        @endif
    </div>
    {{ $slot }}
</div>
