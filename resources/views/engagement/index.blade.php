@extends('layouts.internal-dashboard')
@section('title', __('portal.engagement.nav'))

@section('content')
<div class="page-hero" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-bolt"></i> {{ __('portal.engagement.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.engagement.subtitle') }}</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@error('thank_you')<div class="alert alert-danger">{{ $message }}</div>@enderror

<div class="row g-3 mb-3">
    {{-- My Impact --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><span class="card-title">{{ __('portal.engagement.my_impact') }}</span></div>
            <div class="card-content text-center">
                <span class="ip-level-pill mb-3"><i class="fas fa-award"></i> {{ __('portal.engagement.level.'.$me['level']['index']) }}</span>
                <div style="font-size:2.5rem; font-weight:800; color:var(--primary-color); line-height:1;">{{ number_format($me['points']) }}</div>
                <div class="text-muted" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.06em;">{{ __('portal.engagement.impact_points') }}</div>
                <div class="xp-track mt-3 mx-auto" style="width:100%; height:8px; background:var(--bg-tertiary); border-radius:999px; overflow:hidden;">
                    <div style="height:100%; width:{{ $me['progress'] }}%; background:var(--grad-header); border-radius:999px;"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:.75rem;">{{ $me['progress'] }}% {{ __('portal.engagement.to_next_level') }}@if($me['level']['perk']) &middot; {{ __('portal.engagement.unlocks') }} {{ __('portal.engagement.perk.'.$me['level']['index']) }}@endif</div>
            </div>
        </div>
    </div>

    {{-- Send a Thank-You --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><span class="card-title">{{ __('portal.engagement.say_thank_you') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('portal.engagement.thank_you_desc') }} <strong>{{ $me['thankYouRemaining'] }}</strong> {{ __('portal.engagement.left_this_month') }}</p>
                <form method="POST" action="{{ route('engagement.thank-you') }}">
                    @csrf
                    <select name="to_user_id" class="form-select mb-2" required @disabled($me['thankYouRemaining'] < 1)>
                        <option value="">{{ __('portal.engagement.choose_colleague') }}</option>
                        @foreach($colleagues as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="message" class="form-control mb-2" maxlength="255" placeholder="{{ __('portal.engagement.optional_note') }}" @disabled($me['thankYouRemaining'] < 1)>
                    <button type="submit" class="btn btn-primary w-100" @disabled($me['thankYouRemaining'] < 1)>
                        <i class="fas fa-heart"></i> {{ __('portal.engagement.send_thank_you') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><span class="card-title">{{ __('portal.engagement.recent_activity') }}</span></div>
            <div class="card-content">
                @forelse($me['recent'] as $log)
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid var(--gray-200);">
                        <small>{{ __('portal.engagement.action.'.$log->action) }}</small>
                        <span class="badge {{ $log->points >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('portal.engagement.no_activity') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Leaderboard --}}
<div class="row g-3">
    <div class="col-lg-{{ $isManager ? 7 : 12 }}">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-trophy"></i> {{ __('portal.engagement.leaderboard') }}</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th style="width:60px;">#</th><th>{{ __('portal.engagement.name') }}</th><th>{{ __('portal.engagement.level_col') }}</th><th class="text-end">{{ __('portal.engagement.ip') }}</th></tr></thead>
                    <tbody>
                        @foreach($leaderboard as $i => $row)
                            <tr>
                                <td><span class="ip-rank {{ $i === 0 ? 'top' : '' }}">{{ $i + 1 }}</span></td>
                                <td>{{ $row['user']->name }}</td>
                                <td><span class="ip-badge">{{ __('portal.engagement.level.'.$row['level']['index']) }}</span></td>
                                <td class="text-end" style="font-weight:700;">{{ number_format($row['points']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($isManager)
    <div class="col-lg-5">
        {{-- Burnout alerts --}}
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-heart-pulse"></i> {{ __('portal.engagement.burnout') }}</span></div>
            <div class="card-content">
                @forelse($burnoutAlerts as $u)
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span>{{ $u->name }}</span>
                        <span class="badge bg-warning">{{ __('portal.engagement.check_in') }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('portal.engagement.no_burnout') }}</p>
                @endforelse
            </div>
        </div>
        {{-- Hidden gems --}}
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-gem"></i> {{ __('portal.engagement.hidden_gems') }}</span></div>
            <div class="card-content">
                @forelse($hiddenGems as $g)
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span>{{ $g['user']->name }}</span>
                        <small class="text-muted">{{ number_format($g['help_points']) }} {{ __('portal.engagement.help_ip') }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('portal.engagement.no_collab') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
