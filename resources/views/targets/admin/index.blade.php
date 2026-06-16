@extends('layouts.internal-dashboard')
@section('title', __('targets.admin.title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-bullseye"></i> {{ __('targets.admin.title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.admin.subtitle') }}</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control form-control-sm" style="max-width:170px;">
        <button class="btn btn-light btn-sm">{{ __('targets.admin.view_month') }}</button>
        <a href="{{ route('targets.admin.metrics') }}" class="btn btn-light btn-sm"><i class="fas fa-sliders"></i> {{ __('targets.admin.metrics') }}</a>
    </form>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="row g-3">
    {{-- Set / adjust a target --}}
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-plus"></i> {{ __('targets.admin.set_target') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('targets.admin.set') }}">@csrf
                <input type="hidden" name="period" value="{{ $month->toDateString() }}">
                <label class="form-label">{{ __('targets.admin.staff') }}</label>
                <select name="user_id" class="form-select mb-2" required>
                    <option value="">—</option>
                    @foreach($rows as $r)<option value="{{ $r->user->id }}">{{ $r->user->name }}</option>@endforeach
                </select>
                <label class="form-label">{{ __('targets.admin.metric') }}</label>
                <select name="target_metric_id" class="form-select mb-2" required id="tgt-metric">
                    @foreach($metrics as $m)<option value="{{ $m->id }}" data-manual="{{ $m->isAuto() ? 0 : 1 }}">{{ $m->localizedName() }} ({{ $m->isAuto() ? __('targets.auto') : __('targets.manual') }})</option>@endforeach
                </select>
                <label class="form-label">{{ __('targets.admin.target_value') }}</label>
                <input type="number" step="0.01" min="0" name="target_value" class="form-control mb-2" required>
                <div id="tgt-manual-cur" style="display:none;">
                    <label class="form-label">{{ __('targets.admin.current_value') }}</label>
                    <input type="number" step="0.01" min="0" name="current_value" class="form-control mb-2">
                    <small class="text-muted">{{ __('targets.admin.manual_hint') }}</small>
                </div>
                <label class="form-label">{{ __('targets.admin.notes') }}</label>
                <input type="text" name="notes" class="form-control mb-2" maxlength="500">
                <button class="btn btn-primary w-100">{{ __('targets.admin.save_target') }}</button>
            </form>
        </div></div>

        <div class="card"><div class="card-content d-flex flex-column gap-2">
            <form method="POST" action="{{ route('targets.admin.record-month') }}" onsubmit="return confirm('{{ __('targets.admin.record_confirm') }}')">@csrf
                <input type="hidden" name="period" value="{{ $month->toDateString() }}">
                <button class="btn btn-success w-100"><i class="fas fa-camera"></i> {{ __('targets.admin.record_month') }}</button>
            </form>
            <form method="POST" action="{{ route('targets.admin.carry-forward') }}" onsubmit="return confirm('{{ __('targets.admin.carry_confirm') }}')">@csrf
                <input type="hidden" name="period" value="{{ $month->toDateString() }}">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-forward"></i> {{ __('targets.admin.carry_forward') }}</button>
            </form>
            <small class="text-muted">{{ __('targets.admin.record_hint') }}</small>
        </div></div>
    </div>

    {{-- Team overview --}}
    <div class="col-lg-8">
        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-users"></i> {{ __('targets.admin.team_overview') }}</span></div>
        <div class="card-content p-0" style="overflow-x:auto;">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>{{ __('targets.admin.staff') }}</th>
                    @foreach($metrics as $m)<th style="font-size:.78rem;">{{ $m->localizedName() }}</th>@endforeach
                    <th>{{ __('targets.overall') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        @php $roleVal = $r->user->role->value ?? ''; $roleKey = 'targets.admin.role.'.$roleVal; @endphp
                        <td><strong>{{ $r->user->name }}</strong><div class="text-muted" style="font-size:.72rem;">{{ \Illuminate\Support\Facades\Lang::has($roleKey) ? __($roleKey) : ucfirst(str_replace('_', ' ', $roleVal)) }}</div></td>
                        @foreach($metrics as $m)
                            @php $cell = $r->summary->get($m->id); @endphp
                            <td>
                                @if($cell)
                                    <div style="font-size:.82rem; white-space:nowrap;">
                                        {{ rtrim(rtrim(number_format($cell->actual, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($cell->target_value, 2), '0'), '.') }}
                                    </div>
                                    <span class="badge bg-{{ $cell->rag === 'green' ? 'success' : ($cell->rag === 'amber' ? 'warning' : 'danger') }}" style="font-size:.65rem;">{{ (int) $cell->attainment }}%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td>
                            @if($r->overall !== null)
                                <strong style="color: {{ $r->overall >= 100 ? 'var(--success-color)' : ($r->overall >= 70 ? 'var(--warning-color)' : 'var(--danger-color)') }};">{{ (int) $r->overall }}%</strong>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td class="text-end"><a href="{{ route('targets.admin.show', $r->user) }}" class="btn btn-sm btn-light"><i class="fas fa-chart-line"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    var sel = document.getElementById('tgt-metric'), cur = document.getElementById('tgt-manual-cur');
    function sync(){ if(!sel||!cur) return; var opt = sel.options[sel.selectedIndex]; cur.style.display = (opt && opt.dataset.manual === '1') ? 'block' : 'none'; }
    if(sel){ sel.addEventListener('change', sync); sync(); }
})();
</script>
@endpush
@endsection
