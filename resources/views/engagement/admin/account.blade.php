@extends('layouts.internal-dashboard')
@section('title', $account->client?->name)

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-user"></i> {{ $account->client?->name ?? '—' }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ $account->client?->email }} · {{ __('engagement.your_code') }}: {{ $account->referral_code }}</p>
    </div>
    <a href="{{ route('engagement.admin.accounts') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.accounts') }}</a>
</div>


<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-content">
            <div class="d-flex justify-content-between"><span class="text-muted">{{ __('engagement.balance') }}</span><strong>{{ number_format($account->balance) }}</strong></div>
            <div class="d-flex justify-content-between"><span class="text-muted">{{ __('engagement.lifetime_points') }}</span><strong>{{ number_format($account->lifetime_points) }}</strong></div>
            <div class="d-flex justify-content-between"><span class="text-muted">{{ __('engagement.current_tier') }}</span><span class="badge bg-primary">{{ $account->tier?->localizedName() ?? '—' }}</span></div>
        </div></div>

        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-pen"></i> {{ __('engagement.admin.manual_adjust') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('engagement.admin.account.adjust', $account) }}" onsubmit="return confirm('{{ __('engagement.admin.adjust_confirm') }}')">@csrf
                <label class="form-label">{{ __('engagement.admin.adjust_points') }}</label>
                <input type="number" name="points" class="form-control mb-2" required placeholder="e.g. 50 or -20">
                <label class="form-label">{{ __('engagement.admin.reason') }}</label>
                <textarea name="reason" class="form-control mb-2" rows="2" required maxlength="500"></textarea>
                <button class="btn btn-primary w-100">{{ __('engagement.admin.adjust') }}</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-clock-rotate-left"></i> {{ __('engagement.admin.account_ledger') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0">
                <thead><tr><th>{{ __('engagement.date') }}</th><th>{{ __('engagement.description') }}</th><th class="text-end">{{ __('engagement.points') }}</th></tr></thead>
                <tbody>
                @foreach($transactions as $t)
                    <tr>
                        <td style="white-space:nowrap;">{{ $t->created_at->format('Y-m-d') }}</td>
                        <td>
                            {{ $t->description ?: __('engagement.direction.'.$t->direction) }}
                            <span class="badge bg-secondary" style="font-size:.65rem;">{{ __('engagement.direction.'.$t->direction) }}</span>
                            @if($t->status === 'pending')<span class="badge bg-warning" style="font-size:.65rem;">{{ __('engagement.pending_review') }}</span>@endif
                        </td>
                        <td class="text-end fw-bold" style="color: {{ $t->points >= 0 ? 'var(--success-color)' : 'var(--danger-color)' }};">{{ $t->points >= 0 ? '+' : '' }}{{ number_format($t->points) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())<div class="card-content pt-2">{{ $transactions->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
