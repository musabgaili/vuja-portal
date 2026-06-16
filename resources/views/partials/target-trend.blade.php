{{-- Dependency-free 6-month trend chart (actual line + target line) as inline SVG.
     Expects: $points (collection of objects with ->label, ->target, ->actual). --}}
@php
    $pts = collect($points ?? []);
    $w = 320; $h = 110; $padL = 6; $padR = 6; $padT = 10; $padB = 18;
    $n = max(1, $pts->count());
    $max = max(1, (float) $pts->max(fn ($p) => max((float) $p->target, (float) $p->actual)));
    $stepX = $n > 1 ? ($w - $padL - $padR) / ($n - 1) : 0;
    $plotH = $h - $padT - $padB;
    $xy = function ($i, $val) use ($padL, $padT, $plotH, $stepX, $max) {
        $x = $padL + $i * $stepX;
        $y = $padT + $plotH - ($plotH * (min($val, $max * 1.0) / $max));
        return round($x, 1).','.round($y, 1);
    };
    $actualLine = $pts->values()->map(fn ($p, $i) => $xy($i, (float) $p->actual))->implode(' ');
    $targetLine = $pts->values()->map(fn ($p, $i) => $xy($i, (float) $p->target))->implode(' ');
@endphp
<svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" style="width:100%; height:120px; overflow:visible;" role="img">
    {{-- target line (dashed) --}}
    <polyline points="{{ $targetLine }}" fill="none" stroke="var(--gray-400)" stroke-width="1.5" stroke-dasharray="4 3"></polyline>
    {{-- actual line --}}
    <polyline points="{{ $actualLine }}" fill="none" stroke="var(--primary-bright, #0F969C)" stroke-width="2.5"></polyline>
    @foreach($pts->values() as $i => $p)
        @php [$ax, $ay] = explode(',', $xy($i, (float) $p->actual)); @endphp
        <circle cx="{{ $ax }}" cy="{{ $ay }}" r="3" fill="var(--primary-color, #0C7075)"></circle>
        <text x="{{ $ax }}" y="{{ $h - 5 }}" text-anchor="middle" font-size="9" fill="var(--gray-500)">{{ $p->label }}</text>
    @endforeach
</svg>
<div class="d-flex gap-3 mt-1" style="font-size:.72rem; color:var(--gray-500);">
    <span><span style="display:inline-block;width:14px;height:0;border-top:2.5px solid var(--primary-bright,#0F969C);vertical-align:middle;"></span> {{ __('targets.actual') }}</span>
    <span><span style="display:inline-block;width:14px;height:0;border-top:1.5px dashed var(--gray-400);vertical-align:middle;"></span> {{ __('targets.target') }}</span>
</div>
