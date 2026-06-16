@extends('layouts.internal-dashboard')
@section('title', __('portal.crm.pipeline'))

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-funnel-dollar"></i> {{ __('portal.crm.pipeline') }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.crm.subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->isManager())
            <a href="{{ route('imports.form', 'opportunities') }}" class="btn btn-light"><i class="fas fa-file-excel"></i> {{ __('portal.import.import') }}</a>
            @endif
            <a href="{{ route('crm.create') }}" class="btn" style="background:#fff; color:var(--primary-color); font-weight:600;"><i class="fas fa-plus"></i> {{ __('portal.crm.new') }}</a>
        </div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif

{{-- Forecast summary --}}
<div class="row g-3 mb-3">
    @foreach([
        ['k'=>'open_count','label'=>__('portal.crm.open_deals'),'val'=>$summary['open_count'],'money'=>false],
        ['k'=>'pipeline_value','label'=>__('portal.crm.pipeline_value'),'val'=>$summary['pipeline_value'],'money'=>true],
        ['k'=>'weighted','label'=>__('portal.crm.weighted_forecast'),'val'=>$summary['weighted'],'money'=>true],
        ['k'=>'won_value','label'=>__('portal.crm.won_value'),'val'=>$summary['won_value'],'money'=>true],
    ] as $c)
        <div class="col-md-3">
            <div class="card"><div class="card-content">
                <div class="text-muted" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;">{{ $c['label'] }}</div>
                <div style="font-size:1.75rem; font-weight:800; color:var(--primary-color);">{{ $c['money'] ? number_format($c['val'],0).' '.config('scope.currency','SAR') : $c['val'] }}</div>
            </div></div>
        </div>
    @endforeach
</div>

{{-- Kanban --}}
<div class="scroll-fade-x">
<div class="crm-board">
    @foreach($columns as $key => $col)
        <div class="crm-col" data-stage="{{ $key }}">
            <div class="crm-col-head">
                <span>{{ $col['label'] }} <span class="badge bg-secondary">{{ $col['items']->count() }}</span></span>
                <span class="text-muted" style="font-size:.8rem;">{{ number_format($col['value'], 0) }} {{ config('scope.currency','SAR') }}</span>
            </div>
            <div class="crm-col-body" data-stage="{{ $key }}">
                @foreach($col['items'] as $opp)
                    <div class="crm-card" draggable="true" data-id="{{ $opp->id }}">
                        <a href="{{ route('crm.show', $opp) }}" style="font-weight:600; color:var(--gray-900); text-decoration:none;">{{ $opp->name }}</a>
                        @if($opp->company_name)<div class="text-muted" style="font-size:.78rem;">{{ $opp->company_name }}</div>@endif
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span style="font-weight:700; color:var(--primary-color);">{{ number_format((float)$opp->expected_value, 0) }} {{ config('scope.currency','SAR') }}</span>
                            <span class="badge bg-secondary">{{ $opp->probability }}%</span>
                        </div>
                        @if($opp->owner)<div class="text-muted mt-1" style="font-size:.72rem;"><i class="fas fa-user"></i> {{ $opp->owner->name }}</div>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
</div>
<p class="text-muted mt-2" style="font-size:.8rem;">{{ __('portal.crm.drag_hint') }}</p>

<script>
(function () {
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    var dragId = null;
    document.querySelectorAll('.crm-card').forEach(function (card) {
        card.addEventListener('dragstart', function () { dragId = card.dataset.id; card.classList.add('dragging'); });
        card.addEventListener('dragend', function () { card.classList.remove('dragging'); });
    });
    document.querySelectorAll('.crm-col-body').forEach(function (col) {
        col.addEventListener('dragover', function (e) { e.preventDefault(); col.classList.add('drop-hint'); });
        col.addEventListener('dragleave', function () { col.classList.remove('drop-hint'); });
        col.addEventListener('drop', function (e) {
            e.preventDefault(); col.classList.remove('drop-hint');
            if (!dragId) return;
            var stage = col.dataset.stage;
            fetch("{{ url('internal/crm') }}/" + dragId + "/stage", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ stage: stage })
            }).then(function (r) { if (r.ok) location.reload(); });
        });
    });

    // Horizontal-scroll fade cue: only show while the board actually overflows
    // and is not scrolled to its end (RTL-safe via abs(scrollLeft)).
    document.querySelectorAll('.scroll-fade-x').forEach(function (wrap) {
        var sc = wrap.querySelector('.crm-board') || wrap.firstElementChild;
        if (!sc) return;
        function update() {
            var max = sc.scrollWidth - sc.clientWidth;
            var pos = Math.abs(sc.scrollLeft);
            wrap.classList.toggle('is-fade', max > 2 && pos < max - 2);
        }
        sc.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });
})();
</script>
@endsection
