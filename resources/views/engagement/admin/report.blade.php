@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.report'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-chart-column"></i> {{ __('engagement.admin.report') }}</h1>
    </div>
    <a href="{{ route('engagement.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.dashboard') }}</a>
</div>

<div class="row g-3 mb-3">
    @php
        $tiles = [
            [__('engagement.admin.liability_report'), number_format($report['liability_points']), 'scale-balanced', 'var(--warning-color)'],
            [__('engagement.admin.lifetime_awarded'), number_format($report['lifetime_awarded']), 'star', 'var(--primary-bright)'],
            [__('engagement.admin.points_spent'), number_format($report['spent']), 'cart-shopping', 'var(--primary-color)'],
            [__('engagement.admin.points_expired'), number_format($report['expired']), 'hourglass-end', 'var(--gray-500)'],
        ];
    @endphp
    @foreach($tiles as [$label,$value,$icon,$color])
        <div class="col-md-3 col-6">
            <div class="card h-100"><div class="card-content text-center">
                <i class="fas fa-{{ $icon }} fa-lg mb-2" style="color: {{ $color }};"></i>
                <div style="font-size:1.5rem; font-weight:800; color:var(--text-color);">{{ $value }}</div>
                <div class="text-muted" style="font-size:.75rem;">{{ $label }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-header"><span class="card-title">{{ __('engagement.admin.redemptions_by_type') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0">
                <thead><tr><th>{{ __('engagement.admin.type') }}</th><th class="text-end">{{ __('engagement.admin.count') }}</th><th class="text-end">{{ __('engagement.pts') }}</th></tr></thead>
                <tbody>
                @forelse($report['redemptions_by_type'] as $row)
                    <tr><td>{{ $row->type }}</td><td class="text-end">{{ number_format($row->n) }}</td><td class="text-end">{{ number_format($row->pts) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center py-3">—</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100"><div class="card-header"><span class="card-title">{{ __('engagement.admin.referral_conversion') }}</span></div>
        <div class="card-content">
            <div class="d-flex justify-content-between py-1"><span>{{ __('engagement.admin.referrals_total') }}</span><strong>{{ number_format($report['referrals_total']) }}</strong></div>
            <div class="d-flex justify-content-between py-1"><span>{{ __('engagement.admin.referrals_rewarded') }}</span><strong>{{ number_format($report['referrals_rewarded']) }}</strong></div>
            @php $conv = $report['referrals_total'] > 0 ? round($report['referrals_rewarded'] / $report['referrals_total'] * 100) : 0; @endphp
            <div class="ep-bar mt-2"><div class="ep-bar__fill" style="width: {{ $conv }}%;"></div></div>
            <div class="text-muted text-end" style="font-size:.78rem;">{{ $conv }}%</div>
        </div></div>
    </div>

    <div class="col-12">
        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-trophy"></i> {{ __('engagement.admin.top_advocates') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0">
                <thead><tr><th>{{ __('engagement.admin.client') }}</th><th>{{ __('engagement.admin.lifetime') }}</th><th>{{ __('engagement.admin.balance') }}</th><th>{{ __('engagement.current_tier') }}</th></tr></thead>
                <tbody>
                @foreach($topAdvocates as $a)
                    <tr>
                        <td>{{ $a->client?->name ?? '—' }}</td>
                        <td>{{ number_format($a->lifetime_points) }}</td>
                        <td>{{ number_format($a->balance) }}</td>
                        <td><span class="badge bg-primary">{{ $a->tier?->localizedName() ?? '—' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ep-bar{ height:8px; background:var(--gray-200); border-radius:99px; overflow:hidden; }
.ep-bar__fill{ height:100%; background:var(--primary-bright); border-radius:99px; }
</style>
@endpush
