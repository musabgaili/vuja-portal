@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-award"></i> {{ __('engagement.admin.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('engagement.admin.subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="row g-3 mb-3">
    @php
        $tiles = [
            ['label' => __('engagement.admin.total_accounts'), 'value' => number_format($stats['accounts']), 'icon' => 'users', 'color' => 'var(--primary-color)'],
            ['label' => __('engagement.admin.outstanding_liability'), 'value' => number_format($stats['liability']), 'icon' => 'scale-balanced', 'color' => 'var(--warning-color)'],
            ['label' => __('engagement.admin.lifetime_awarded'), 'value' => number_format($stats['lifetime_awarded']), 'icon' => 'star', 'color' => 'var(--primary-bright)'],
            ['label' => __('engagement.admin.pending_claims'), 'value' => number_format($stats['pending_claims']), 'icon' => 'bullhorn', 'color' => 'var(--info-color)', 'link' => route('engagement.admin.claims.index')],
            ['label' => __('engagement.admin.pending_grants'), 'value' => number_format($stats['pending_grants']), 'icon' => 'hand-holding-dollar', 'color' => 'var(--info-color)', 'link' => route('engagement.admin.grants.index')],
            ['label' => __('engagement.admin.pending_earns'), 'value' => number_format($stats['pending_earns']), 'icon' => 'hourglass-half', 'color' => 'var(--info-color)', 'link' => route('engagement.admin.pending-earns')],
            ['label' => __('engagement.admin.redemptions_made'), 'value' => number_format($stats['redemptions']), 'icon' => 'gift', 'color' => 'var(--success-color)'],
        ];
    @endphp
    @foreach($tiles as $t)
        <div class="col-md-4 col-lg-2">
            <a href="{{ $t['link'] ?? '#' }}" style="text-decoration:none;">
                <div class="card h-100"><div class="card-content text-center">
                    <i class="fas fa-{{ $t['icon'] }} fa-lg mb-2" style="color: {{ $t['color'] }};"></i>
                    <div style="font-size:1.5rem; font-weight:800; color:var(--text-color);">{{ $t['value'] }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ $t['label'] }}</div>
                </div></div>
            </a>
        </div>
    @endforeach
</div>

<div class="d-flex gap-2 flex-wrap mb-3">
    <a href="{{ route('engagement.admin.config') }}" class="btn btn-primary"><i class="fas fa-sliders"></i> {{ __('engagement.admin.config') }}</a>
    <a href="{{ route('engagement.admin.claims.index') }}" class="btn btn-outline-primary"><i class="fas fa-bullhorn"></i> {{ __('engagement.admin.claims_queue') }}</a>
    <a href="{{ route('engagement.admin.grants.index') }}" class="btn btn-outline-primary"><i class="fas fa-hand-holding-dollar"></i> {{ __('engagement.admin.grants') }}</a>
    <a href="{{ route('engagement.admin.pending-earns') }}" class="btn btn-outline-primary"><i class="fas fa-hourglass-half"></i> {{ __('engagement.admin.pending_earns') }}</a>
    <a href="{{ route('engagement.admin.accounts') }}" class="btn btn-outline-primary"><i class="fas fa-users"></i> {{ __('engagement.admin.accounts') }}</a>
    <a href="{{ route('engagement.admin.report') }}" class="btn btn-outline-primary"><i class="fas fa-chart-column"></i> {{ __('engagement.admin.report') }}</a>
</div>

<div class="card">
    <div class="card-header"><span class="card-title"><i class="fas fa-trophy"></i> {{ __('engagement.admin.top_advocates') }}</span></div>
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr><th>{{ __('engagement.admin.client') }}</th><th>{{ __('engagement.admin.balance') }}</th><th>{{ __('engagement.admin.lifetime') }}</th><th>{{ __('engagement.current_tier') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($topAdvocates as $a)
                <tr>
                    <td>{{ $a->client?->name ?? '—' }}<div class="text-muted" style="font-size:.78rem;">{{ $a->client?->email }}</div></td>
                    <td>{{ number_format($a->balance) }}</td>
                    <td>{{ number_format($a->lifetime_points) }}</td>
                    <td><span class="badge bg-primary">{{ $a->tier?->localizedName() ?? '—' }}</span></td>
                    <td class="text-end"><a href="{{ route('engagement.admin.account', $a) }}" class="btn btn-sm btn-light">{{ __('engagement.admin.view') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-4">{{ __('engagement.admin.no_accounts') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
