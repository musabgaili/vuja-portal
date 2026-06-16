@extends('layouts.dashboard')
@section('title', __('engagement.my_points'))

@section('content')
@php
    $life = (int) $account->lifetime_points;
    $base = $account->tier?->min_lifetime_points ?? 0;
    $pct = 100;
    if ($nextTier) {
        $span = max(1, $nextTier->min_lifetime_points - $base);
        $pct = max(0, min(100, round(($life - $base) / $span * 100)));
    }
@endphp

<div class="page-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-award"></i> {{ __('engagement.title') }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9;">{{ __('engagement.intro') }}</p>
        </div>
        <a href="{{ route('engagement.client.about') }}" class="btn btn-light btn-sm"><i class="fas fa-circle-info"></i> {{ __('engagement.learn_more') }}</a>
    </div>

    <div class="ep-stats">
        <div class="ep-stat">
            <div class="ep-stat__label">{{ __('engagement.balance') }}</div>
            <div class="ep-stat__value">{{ number_format($account->balance) }} <span>{{ __('engagement.pts') }}</span></div>
        </div>
        <div class="ep-stat">
            <div class="ep-stat__label">{{ __('engagement.lifetime_points') }}</div>
            <div class="ep-stat__value">{{ number_format($life) }} <span>{{ __('engagement.pts') }}</span></div>
        </div>
        <div class="ep-stat">
            <div class="ep-stat__label">{{ __('engagement.current_tier') }}</div>
            <div class="ep-stat__value" style="font-size:1.25rem;">
                <i class="fas fa-medal"></i> {{ $account->tier?->localizedName() ?? '—' }}
            </div>
        </div>
    </div>

    <div class="ep-progress">
        @if($nextTier)
            <div class="d-flex justify-content-between" style="font-size:.8rem; opacity:.9;">
                <span>{{ __('engagement.progress_to', ['tier' => $nextTier->localizedName()]) }}</span>
                <span>{{ __('engagement.points_to_go', ['n' => number_format(max(0, $nextTier->min_lifetime_points - $life))]) }}</span>
            </div>
            <div class="ep-progress__track"><div class="ep-progress__fill" style="width: {{ $pct }}%;"></div></div>
        @else
            <div style="font-size:.85rem; opacity:.9;"><i class="fas fa-crown"></i> {{ __('engagement.max_tier') }}</div>
        @endif
    </div>
</div>


<div class="row g-3">
    {{-- Left column: redeem + claim --}}
    <div class="col-lg-7">
        {{-- Redeem catalog --}}
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-gift"></i> {{ __('engagement.redeem_title') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('engagement.redeem_sub') }}</p>
                <div class="row g-2">
                    @foreach($options as $opt)
                        @php $afford = $account->balance >= $opt->cost_points; @endphp
                        <div class="col-md-6">
                            <div class="ep-reward {{ $afford ? '' : 'ep-reward--locked' }}">
                                <div class="ep-reward__name">{{ $opt->localizedName() }}</div>
                                <div class="ep-reward__cost">{{ number_format($opt->cost_points) }} {{ __('engagement.pts') }}</div>
                                <form method="POST" action="{{ route('engagement.client.redeem', $opt) }}" onsubmit="return confirm('{{ $opt->localizedName() }} — {{ $opt->cost_points }} {{ __('engagement.pts') }}?')">
                                    @csrf
                                    <button class="btn btn-sm btn-primary w-100" {{ $afford ? '' : 'disabled' }}>
                                        {{ $afford ? __('engagement.redeem_now') : __('engagement.not_enough') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Claim form --}}
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-bullhorn"></i> {{ __('engagement.claim_title') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('engagement.claim_sub') }}</p>
                <form method="POST" action="{{ route('engagement.client.claims.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('engagement.claim_action') }}</label>
                            <select name="earning_rule_key" class="form-select" required>
                                @foreach($claimableRules as $r)
                                    <option value="{{ $r->key }}">{{ $r->localizedName() }} (+{{ $r->points }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('engagement.platform') }}</label>
                            <select name="platform" class="form-select">
                                @foreach(['linkedin','x','instagram','google','other'] as $p)
                                    <option value="{{ $p }}">{{ __('engagement.platforms.'.$p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('engagement.post_url') }}</label>
                            <input type="url" name="post_url" class="form-control" placeholder="https://…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('engagement.posted_at') }}</label>
                            <input type="date" name="posted_at" class="form-control" max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('engagement.screenshot') }}</label>
                            <input type="file" name="screenshot" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3"><i class="fas fa-paper-plane"></i> {{ __('engagement.submit_claim') }}</button>
                </form>

                <div class="ep-rubric mt-3">
                    <strong style="font-size:.8rem;">{{ __('engagement.rubric_title') }}</strong>
                    <ul style="font-size:.8rem; margin:.4rem 0 0; padding-inline-start:1.1rem;">
                        @foreach(['public','mention','positive','substantive','recent'] as $k)
                            <li>{{ __('engagement.rubric.'.$k) }}</li>
                        @endforeach
                    </ul>
                    <p class="text-muted" style="font-size:.75rem; margin:.4rem 0 0;">{{ __('engagement.disclosure') }}</p>
                </div>

                @if($claims->isNotEmpty())
                    <hr>
                    <strong style="font-size:.85rem;">{{ __('engagement.your_claims') }}</strong>
                    <ul class="list-unstyled mt-2 mb-0">
                        @foreach($claims as $c)
                            <li class="d-flex justify-content-between align-items-center py-1" style="font-size:.85rem; border-bottom:1px solid var(--gray-200);">
                                <span>{{ optional($c->rule())->localizedName() ?? $c->earning_rule_key }} @if($c->platform)· {{ __('engagement.platforms.'.$c->platform) }}@endif</span>
                                <span class="badge bg-{{ $c->statusColor() }}">{{ __('engagement.claim_status.'.$c->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column: vouchers + referral + ledger --}}
    <div class="col-lg-5">
        {{-- Vouchers --}}
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-ticket"></i> {{ __('engagement.your_vouchers') }}</span></div>
            <div class="card-content">
                @forelse($vouchers as $v)
                    <div class="ep-voucher">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $v->option->localizedName() }}</strong>
                            <span class="badge bg-{{ $v->isApplied() ? 'secondary' : ($v->isExpired() ? 'danger' : 'success') }}">
                                {{ __('engagement.voucher_status.'.($v->isExpired() && $v->isIssued() ? 'expired' : $v->status)) }}
                            </span>
                        </div>
                        @if($v->voucher_code)<div class="text-muted" style="font-size:.8rem;">{{ __('engagement.voucher_code') }}: <code>{{ $v->voucher_code }}</code></div>@endif
                        @if($v->expires_at && $v->isIssued())<div class="text-muted" style="font-size:.75rem;">{{ __('engagement.expires') }} {{ $v->expires_at->translatedFormat('M d, Y') }}</div>@endif

                        @if($v->isIssued() && ! $v->isExpired() && $v->option->type === 'service_discount')
                            @if($eligibleQuotes->isNotEmpty())
                                <form method="POST" action="{{ route('engagement.client.voucher.apply', $v) }}" class="d-flex gap-1 mt-2">
                                    @csrf
                                    <select name="quote_id" class="form-select form-select-sm" required>
                                        <option value="">{{ __('engagement.choose_quote') }}</option>
                                        @foreach($eligibleQuotes as $q)
                                            <option value="{{ $q->id }}">{{ $q->quote_number ?? ('#'.$q->id) }} — {{ \Illuminate\Support\Str::limit($q->title, 28) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">{{ __('engagement.apply') }}</button>
                                </form>
                            @else
                                <div class="text-muted mt-1" style="font-size:.75rem;">{{ __('engagement.no_eligible_quotes') }}</div>
                            @endif
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0" style="font-size:.85rem;">{{ __('engagement.no_vouchers') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Referral --}}
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-user-plus"></i> {{ __('engagement.referral') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('engagement.referral_help') }}</p>
                <label class="form-label mb-1">{{ __('engagement.your_code') }}</label>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" class="form-control" value="{{ $account->referral_code }}" id="ep-code" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('ep-link').value)"><i class="fas fa-copy"></i> {{ __('engagement.copy') }}</button>
                </div>
                <input type="text" class="form-control form-control-sm" value="{{ $account->referralLink() }}" id="ep-link" readonly>

                <div class="mt-3">
                    <strong style="font-size:.85rem;">{{ __('engagement.referral_stats') }}</strong>
                    @forelse($referrals as $ref)
                        <div class="d-flex justify-content-between py-1" style="font-size:.85rem; border-bottom:1px solid var(--gray-200);">
                            <span>{{ $ref->referredClient?->name ?? $ref->referred_email ?? '—' }}</span>
                            <span class="badge bg-{{ $ref->statusColor() }}">{{ __('engagement.ref_status.'.$ref->status) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mt-1 mb-0" style="font-size:.8rem;">{{ __('engagement.no_referrals') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Ledger --}}
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-clock-rotate-left"></i> {{ __('engagement.recent_activity') }}</span></div>
            <div class="card-content p-0">
                @if($transactions->isEmpty())
                    <p class="text-muted p-3 mb-0" style="font-size:.85rem;">{{ __('engagement.no_activity') }}</p>
                @else
                    <table class="table table-sm mb-0">
                        <tbody>
                        @foreach($transactions as $t)
                            <tr>
                                <td>
                                    <div style="font-size:.85rem;">{{ $t->description ?: __('engagement.direction.'.$t->direction) }}</div>
                                    <small class="text-muted">{{ $t->created_at->translatedFormat('M d, Y') }}@if($t->status === 'pending') · {{ __('engagement.pending_review') }}@endif</small>
                                </td>
                                <td class="text-end" style="white-space:nowrap; font-weight:700; color: {{ $t->points >= 0 ? 'var(--success-color)' : 'var(--danger-color)' }};">
                                    {{ $t->points >= 0 ? '+' : '' }}{{ number_format($t->points) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if($transactions->hasPages())<div class="card-content pt-2">{{ $transactions->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ep-stats{ display:flex; gap:1rem; flex-wrap:wrap; margin-top:1.1rem; }
.ep-stat{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.22); border-radius:var(--radius-lg); padding:.7rem 1.2rem; min-width:140px; }
.ep-stat__label{ font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.85; }
.ep-stat__value{ font-size:1.6rem; font-weight:800; line-height:1.1; }
.ep-stat__value span{ font-size:.8rem; font-weight:600; opacity:.85; }
.ep-progress{ margin-top:1rem; }
.ep-progress__track{ height:8px; background:rgba(255,255,255,.2); border-radius:99px; margin-top:.35rem; overflow:hidden; }
.ep-progress__fill{ height:100%; background:#fff; border-radius:99px; }
.ep-reward{ border:1px solid var(--gray-200); border-radius:var(--radius); padding:.7rem; height:100%; }
.ep-reward--locked{ opacity:.6; }
.ep-reward__name{ font-weight:600; font-size:.9rem; }
.ep-reward__cost{ color:var(--primary-color); font-weight:700; font-size:.85rem; margin:.2rem 0 .5rem; }
.ep-voucher{ border:1px dashed var(--primary-bright); border-radius:var(--radius); padding:.6rem .7rem; margin-bottom:.6rem; }
.ep-rubric{ background:var(--gray-100); border-radius:var(--radius); padding:.7rem .9rem; }
</style>
@endpush
