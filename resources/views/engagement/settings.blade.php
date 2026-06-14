@extends('layouts.internal-dashboard')
@section('title', __('portal.engagement_settings.title'))

@section('content')
<div class="page-hero" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-sliders"></i> {{ __('portal.engagement_settings.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.engagement_settings.subtitle') }}</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error){{ $error }}<br>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('engagement.settings.update') }}">
    @csrf
    @method('PUT')

    {{-- Per-action point values, grouped by category --}}
    @foreach($categories as $catKey => $catLabel)
        @php
            $actionsInCat = collect($actionMeta)->filter(fn($m) => ($m['category'] ?? null) === $catKey);
        @endphp
        @if($actionsInCat->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <span class="card-title">{{ __('portal.engagement_settings.category.'.$catKey) }}</span>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('portal.engagement_settings.action') }}</th>
                                <th style="width:120px;" class="text-center">{{ __('portal.engagement_settings.default') }}</th>
                                <th style="width:160px;">{{ __('portal.engagement_settings.points') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actionsInCat as $action => $meta)
                            <tr>
                                <td>{{ $meta['label'] }}</td>
                                <td class="text-center text-muted">{{ $defaults[$action] ?? 0 }}</td>
                                <td>
                                    <input type="number" name="points[{{ $action }}]"
                                           class="form-control form-control-sm"
                                           value="{{ old('points.'.$action, $effective[$action] ?? ($defaults[$action] ?? 0)) }}"
                                           step="1" min="-500" max="1000">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    {{-- Other engagement knobs --}}
    <div class="card mb-3">
        <div class="card-header">
            <span class="card-title">{{ __('portal.engagement_settings.other') }}</span>
        </div>
        <div class="card-content">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('portal.engagement_settings.thank_you_quota') }}</label>
                    <input type="number" name="thank_you_monthly_quota" class="form-control"
                           value="{{ old('thank_you_monthly_quota', $thankYouQuota) }}" min="0" max="100" required>
                    <small class="text-muted">{{ __('portal.engagement_settings.default') }}: {{ $thankYouDefault }} — {{ __('portal.engagement_settings.thank_you_quota_hint') }}</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('portal.engagement_settings.burnout_days') }}</label>
                    <input type="number" name="burnout_inactive_days" class="form-control"
                           value="{{ old('burnout_inactive_days', $burnoutDays) }}" min="1" max="90" required>
                    <small class="text-muted">{{ __('portal.engagement_settings.default') }}: {{ $burnoutDefault }} — {{ __('portal.engagement_settings.burnout_days_hint') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ __('portal.engagement_settings.save') }}
        </button>
        <a href="{{ route('engagement.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('portal.engagement_settings.back') }}
        </a>
    </div>
</form>

<form method="POST" action="{{ route('engagement.settings.reset') }}"
      onsubmit="return confirm('{{ __('portal.engagement_settings.reset_confirm') }}');">
    @csrf
    <button type="submit" class="btn btn-link text-danger p-0">
        <i class="fas fa-rotate-left"></i> {{ __('portal.engagement_settings.reset') }}
    </button>
</form>
@endsection
