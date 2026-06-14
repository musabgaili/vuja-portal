@extends('layouts.internal-dashboard')
@section('title', __('portal.crm_rep.title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-chart-line"></i> {{ __('portal.crm_rep.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.crm_rep.subtitle') }}</p>
</div>

{{-- Summary --}}
<div class="row g-3 mb-3">
    @foreach([
        [__('portal.crm_rep.open_value'), '$'.number_format($summary['open_value'],0)],
        [__('portal.crm_rep.weighted'), '$'.number_format($summary['weighted'],0)],
        [__('portal.crm_rep.won_value'), '$'.number_format($summary['won_value'],0)],
        [__('portal.crm_rep.win_rate'), $summary['win_rate'].'%'],
    ] as $c)
        <div class="col-md-3"><div class="card"><div class="card-content">
            <div class="text-muted" style="font-size:.78rem; text-transform:uppercase; letter-spacing:.05em;">{{ $c[0] }}</div>
            <div style="font-size:1.75rem; font-weight:800; color:var(--primary-color);">{{ $c[1] }}</div>
        </div></div></div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Pipeline funnel --}}
    <div class="col-lg-7">
        <div class="card"><div class="card-header"><span class="card-title">{{ __('portal.crm_rep.pipeline_by_stage') }}</span></div>
        <div class="card-content">
            @foreach($byStage as $s)
                <div class="mb-3">
                    <div class="d-flex justify-content-between" style="font-size:.85rem;">
                        <span>{{ $s['label'] }} <span class="text-muted">({{ $s['count'] }})</span></span>
                        <strong>${{ number_format($s['value'],0) }}</strong>
                    </div>
                    <div style="height:12px; border-radius:999px; background:var(--bg-tertiary); overflow:hidden; margin-top:4px;">
                        <div style="height:100%; width:{{ $maxStageValue>0 ? round($s['value']/$maxStageValue*100) : 0 }}%; background:var(--grad-header); border-radius:999px;"></div>
                    </div>
                </div>
            @endforeach
        </div></div>
    </div>

    {{-- Win rate donut --}}
    <div class="col-lg-5">
        <div class="card"><div class="card-header"><span class="card-title">{{ __('portal.crm_rep.win_rate') }}</span></div>
        <div class="card-content text-center">
            <div style="width:160px; height:160px; border-radius:50%; margin:0 auto;
                        background: conic-gradient(var(--primary-color) 0 {{ $summary['win_rate'] }}%, var(--gray-200) {{ $summary['win_rate'] }}% 100%);
                        display:flex; align-items:center; justify-content:center;">
                <div style="width:104px; height:104px; border-radius:50%; background:var(--bg-primary); display:flex; align-items:center; justify-content:center; flex-direction:column;">
                    <strong style="font-size:1.6rem;">{{ $summary['win_rate'] }}%</strong>
                    <small class="text-muted">{{ __('portal.crm_rep.win_rate') }}</small>
                </div>
            </div>
            <div class="mt-3" style="font-size:.85rem;">
                <span class="badge bg-success">{{ __('portal.crm_rep.won') }}: {{ $summary['won_count'] }}</span>
                <span class="badge bg-danger">{{ __('portal.crm_rep.lost') }}: {{ $summary['lost_count'] }}</span>
            </div>
        </div></div>
    </div>
</div>

<div class="row g-3 mt-1">
    {{-- Forecast by month --}}
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><span class="card-title">{{ __('portal.crm_rep.forecast_month') }}</span></div>
        <div class="card-content">
            @php $fmax = collect($forecast)->max() ?: 1; @endphp
            @forelse($forecast as $month => $val)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="text-muted" style="width:72px; font-size:.8rem;">{{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</span>
                    <div style="flex:1; height:18px; background:var(--bg-tertiary); border-radius:6px; overflow:hidden;">
                        <div style="height:100%; width:{{ round($val/$fmax*100) }}%; background:var(--primary-color);"></div>
                    </div>
                    <span style="width:80px; text-align:end; font-weight:600;">${{ number_format($val,0) }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('portal.crm_rep.no_forecast') }}</p>
            @endforelse
        </div></div>
    </div>

    {{-- Per-rep performance --}}
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><span class="card-title">{{ __('portal.crm_rep.per_rep') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0">
                <thead><tr><th>{{ __('portal.crm_rep.rep') }}</th><th class="text-end">{{ __('portal.crm_rep.open') }}</th><th class="text-end">{{ __('portal.crm_rep.won') }}</th><th class="text-end">{{ __('portal.crm_rep.win_rate') }}</th></tr></thead>
                <tbody>
                @forelse($perRep as $r)
                    <tr>
                        <td>{{ $r['owner']->name }}</td>
                        <td class="text-end">${{ number_format($r['open_value'],0) }} <span class="text-muted">({{ $r['open'] }})</span></td>
                        <td class="text-end">${{ number_format($r['won_value'],0) }} <span class="text-muted">({{ $r['won'] }})</span></td>
                        <td class="text-end"><span class="badge bg-primary">{{ $r['win_rate'] }}%</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">{{ __('portal.crm_rep.no_data') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>
@endsection
