@extends('layouts.dashboard')
@section('title', __('engagement.title'))

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-award"></i> {{ __('engagement.title') }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9; max-width:60ch;">{{ __('engagement.intro') }}</p>
        </div>
        <a href="{{ route('engagement.client.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.back_to_dashboard') }}</a>
    </div>
</div>

<div class="row g-3">
    {{-- Earn --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><span class="card-title"><i class="fas fa-circle-plus"></i> {{ __('engagement.earn') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('engagement.earn_intro') }}</p>
                <table class="table table-sm mb-0">
                    <tbody>
                    @foreach($rules as $r)
                        <tr>
                            <td>
                                {{ $r->localizedName() }}
                                <span class="badge bg-{{ $r->auto_award ? 'success' : 'info' }}" style="font-size:.65rem;">{{ $r->auto_award ? __('engagement.auto') : __('engagement.claim') }}</span>
                            </td>
                            <td class="text-end fw-bold" style="white-space:nowrap; color:var(--primary-color);">+{{ number_format($r->points) }} {{ __('engagement.pts') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Redeem --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><span class="card-title"><i class="fas fa-gift"></i> {{ __('engagement.redeem') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('engagement.redeem_intro') }}</p>
                <table class="table table-sm mb-0">
                    <tbody>
                    @foreach($options as $o)
                        <tr>
                            <td>{{ $o->localizedName() }}</td>
                            <td class="text-end fw-bold" style="white-space:nowrap;">{{ number_format($o->cost_points) }} {{ __('engagement.pts') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tiers --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><span class="card-title"><i class="fas fa-medal"></i> {{ __('engagement.tiers') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('engagement.tiers_intro') }}</p>
                @foreach($tiers as $tier)
                    @php $isMine = $account->tier_id === $tier->id; @endphp
                    <div class="ep-tier {{ $isMine ? 'ep-tier--mine' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $tier->localizedName() }} @if($isMine)<span class="badge bg-primary">{{ __('engagement.your_status') }}</span>@endif</strong>
                            <span class="text-muted" style="font-size:.85rem;">{{ number_format($tier->min_lifetime_points) }}+ {{ __('engagement.min_lifetime') }}</span>
                        </div>
                        @php $perks = $tier->localizedPerks(); @endphp
                        @if(!empty($perks))
                            <div class="text-muted" style="font-size:.8rem;">{{ implode(' · ', $perks) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Referral + claim rules + FAQ --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-user-plus"></i> {{ __('engagement.referral') }}</span></div>
            <div class="card-content">
                <p style="font-size:.85rem;">{{ __('engagement.referral_help') }}</p>
                <div class="text-muted" style="font-size:.85rem;">{{ __('engagement.your_code') }}: <code>{{ $account->referral_code }}</code></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-circle-question"></i> {{ __('engagement.faq') }}</span></div>
            <div class="card-content">
                <ul style="font-size:.85rem; padding-inline-start:1.1rem; margin:0;">
                    <li>{{ __('engagement.faq_expiry') }}</li>
                    <li>{{ __('engagement.faq_tier') }}</li>
                    <li>{{ __('engagement.faq_review') }}</li>
                </ul>
                <p class="text-muted" style="font-size:.75rem; margin:.6rem 0 0;">{{ __('engagement.disclosure') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ep-tier{ border:1px solid var(--gray-200); border-radius:var(--radius); padding:.6rem .8rem; margin-bottom:.5rem; }
.ep-tier--mine{ border-color:var(--primary-bright); background:rgba(15,150,156,.06); }
</style>
@endpush
