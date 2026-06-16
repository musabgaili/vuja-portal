@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.admin_title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-gauge-high"></i> {{ __('targets.cap.admin_title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.admin_subtitle', ['month' => $month->translatedFormat('F Y')]) }}</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control form-control-sm" style="max-width:160px;">
        <button class="btn btn-light btn-sm">{{ __('targets.admin.view_month') }}</button>
    </form>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="{{ route('capacity.admin.engineers') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-gear"></i> {{ __('targets.cap.engineers') }}</a>
    <a href="{{ route('capacity.admin.assignments') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-diagram-project"></i> {{ __('targets.cap.assignments') }}</a>
    <a href="{{ route('capacity.admin.leave') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-plane-departure"></i> {{ __('targets.cap.leave') }}</a>
    <a href="{{ route('capacity.admin.submissions') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-clipboard-check"></i> {{ __('targets.cap.submissions') }}</a>
</div>

<div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-table-cells"></i> {{ __('targets.cap.heatmap') }}</span></div>
<div class="card-content p-0" style="overflow-x:auto;">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>{{ __('targets.cap.engineer') }}</th>
            <th>{{ __('targets.cap.utilization') }}</th>
            @foreach($categories as $c)<th style="font-size:.72rem; white-space:nowrap;">{{ $c->localizedName() }}</th>@endforeach
            <th>{{ __('targets.cap.revenue') }}</th>
            <th>{{ __('targets.cap.quotes') }}</th>
        </tr></thead>
        <tbody>
        @forelse($rows as $row)
            @php $uRag = $row->utilization >= $utilTarget ? 'success' : ($row->utilization >= $utilTarget*0.8 ? 'warning' : 'danger'); @endphp
            <tr>
                <td><strong>{{ $row->member->display_name }}</strong>
                    <div class="text-muted" style="font-size:.7rem;">{{ rtrim(rtrim(number_format($row->available,1),'0'),'.') }}h {{ __('targets.cap.available') }}</div></td>
                <td><span class="badge bg-{{ $uRag }}">{{ (int) $row->utilization }}%</span></td>
                @foreach($categories as $c)
                    @php $pct = $row->alloc[$c->id]['pct'] ?? 0; @endphp
                    <td style="text-align:center; {{ $pct > 0 ? 'background: rgba(15,150,156,'.min(0.85, $pct/100 + 0.12).'); color:'.($pct >= 45 ? '#fff' : 'inherit').';' : '' }} font-size:.8rem;">
                        {{ $pct > 0 ? (int) $pct.'%' : '—' }}
                    </td>
                @endforeach
                <td style="font-size:.82rem;">{{ number_format($row->revenue) }}</td>
                <td style="font-size:.82rem;">{{ $row->output['count'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ 4 + $categories->count() }}" class="text-muted text-center py-4">{{ __('targets.cap.no_engineers') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>

<div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-scale-balanced"></i> {{ __('targets.cap.delivery_vs_presales') }}</span></div>
<div class="card-content">
    @php $preId = optional($categories->firstWhere('key','pre_sales'))->id; @endphp
    @forelse($rows as $row)
        @php
            $delivery = 0; $pre = 0;
            foreach($categories as $c){ $p = $row->alloc[$c->id]['pct'] ?? 0; if($c->kind==='delivery') $delivery += $p; elseif($c->id === $preId) $pre += $p; }
        @endphp
        <div class="mb-2">
            <div class="d-flex justify-content-between" style="font-size:.82rem;">
                <span>{{ $row->member->display_name }}</span>
                <span class="text-muted">{{ __('targets.cap.delivery') }} {{ (int) $delivery }}% · {{ __('targets.cap.presales') }} {{ (int) $pre }}%</span>
            </div>
            <div class="cap-split">
                <div style="width: {{ min(100,$delivery) }}%; background:var(--primary-bright);"></div>
                <div style="width: {{ min(100,$pre) }}%; background:var(--warning-color);"></div>
            </div>
        </div>
    @empty
        <p class="text-muted mb-0" style="font-size:.85rem;">{{ __('targets.cap.no_engineers') }}</p>
    @endforelse
    <small class="text-muted">{{ __('targets.cap.balance_hint') }}</small>
</div></div>
@endsection

@push('styles')
<style>
.cap-split{ display:flex; height:10px; border-radius:999px; overflow:hidden; background:var(--gray-200); margin-top:.2rem; }
.cap-split > div{ height:100%; }
</style>
@endpush
