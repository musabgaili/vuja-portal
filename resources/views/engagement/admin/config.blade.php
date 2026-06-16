@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.config'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-sliders"></i> {{ __('engagement.admin.config') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('engagement.admin.subtitle') }}</p>
    </div>
    <a href="{{ route('engagement.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.dashboard') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

{{-- Forms live outside the tables; inputs associate via the HTML5 form="" attribute. --}}
@foreach($rules as $rule)<form id="rule-{{ $rule->id }}" method="POST" action="{{ route('engagement.admin.rules.update', $rule) }}" class="d-none">@csrf @method('PUT')</form>@endforeach
@foreach($options as $opt)<form id="opt-{{ $opt->id }}" method="POST" action="{{ route('engagement.admin.redemptions.update', $opt) }}" class="d-none">@csrf @method('PUT')</form>@endforeach
@foreach($tiers as $tier)<form id="tier-{{ $tier->id }}" method="POST" action="{{ route('engagement.admin.tiers.update', $tier) }}" class="d-none">@csrf @method('PUT')</form>@endforeach

{{-- Earning rules --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-circle-plus"></i> {{ __('engagement.admin.earning_rules') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>{{ __('engagement.admin.name_en') }}</th>
                <th style="width:90px;">{{ __('engagement.admin.points') }}</th>
                <th style="width:80px;">{{ __('engagement.admin.cap_count') }}</th>
                <th style="width:120px;">{{ __('engagement.admin.cap_period') }}</th>
                <th>{{ __('engagement.admin.active') }} / {{ __('engagement.admin.auto_award') }} / {{ __('engagement.admin.requires_review') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($rules as $rule)
                @php $f = 'rule-'.$rule->id; @endphp
                <tr>
                    <td><strong>{{ $rule->name }}</strong><div class="text-muted" style="font-size:.75rem;">{{ $rule->key }}</div></td>
                    <td><input form="{{ $f }}" type="number" name="points" value="{{ $rule->points }}" class="form-control form-control-sm" min="0" required></td>
                    <td><input form="{{ $f }}" type="number" name="cap_count" value="{{ $rule->cap_count }}" class="form-control form-control-sm" min="0"></td>
                    <td>
                        <select form="{{ $f }}" name="cap_period" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach(['month','quarter','year','lifetime'] as $p)
                                <option value="{{ $p }}" @selected($rule->cap_period === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap" style="font-size:.8rem;">
                            <label><input form="{{ $f }}" type="checkbox" name="is_active" value="1" @checked($rule->is_active)> {{ __('engagement.admin.active') }}</label>
                            <label><input form="{{ $f }}" type="checkbox" name="auto_award" value="1" @checked($rule->auto_award)> {{ __('engagement.admin.auto_award') }}</label>
                            <label><input form="{{ $f }}" type="checkbox" name="requires_review" value="1" @checked($rule->requires_review)> {{ __('engagement.admin.requires_review') }}</label>
                        </div>
                    </td>
                    <td class="text-end"><button form="{{ $f }}" class="btn btn-sm btn-primary">{{ __('engagement.admin.save') }}</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Redemption options --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-gift"></i> {{ __('engagement.admin.redemption_options') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>{{ __('engagement.admin.name_en') }}</th>
                <th style="width:120px;">{{ __('engagement.admin.cost_points') }}</th>
                <th>{{ __('engagement.redeem') }}</th>
                <th style="width:70px;">{{ __('engagement.admin.active') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($options as $opt)
                @php $f = 'opt-'.$opt->id; @endphp
                <tr>
                    <td><strong>{{ $opt->name }}</strong><div class="text-muted" style="font-size:.75rem;">{{ $opt->key }} · {{ $opt->type }}</div></td>
                    <td><input form="{{ $f }}" type="number" name="cost_points" value="{{ $opt->cost_points }}" class="form-control form-control-sm" min="0" required></td>
                    <td>
                        @if($opt->type === 'service_discount')
                            <div class="d-flex gap-1">
                                <input form="{{ $f }}" type="number" step="0.01" name="percent" value="{{ data_get($opt->value_meta,'percent') }}" class="form-control form-control-sm" placeholder="%" title="{{ __('engagement.admin.percent') }}">
                                <input form="{{ $f }}" type="number" step="0.01" name="cap_sar" value="{{ data_get($opt->value_meta,'cap_sar') }}" class="form-control form-control-sm" placeholder="SAR" title="{{ __('engagement.admin.cap_sar') }}">
                            </div>
                        @elseif($opt->type === 'ai_access')
                            <input form="{{ $f }}" type="number" name="days" value="{{ data_get($opt->value_meta,'days') }}" class="form-control form-control-sm" placeholder="{{ __('engagement.admin.days') }}">
                        @elseif($opt->type === 'consultation')
                            <input form="{{ $f }}" type="number" name="minutes" value="{{ data_get($opt->value_meta,'minutes') }}" class="form-control form-control-sm" placeholder="{{ __('engagement.admin.minutes') }}">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><input form="{{ $f }}" type="checkbox" name="is_active" value="1" @checked($opt->is_active)></td>
                    <td class="text-end"><button form="{{ $f }}" class="btn btn-sm btn-primary">{{ __('engagement.admin.save') }}</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Tiers --}}
<div class="card">
    <div class="card-header"><span class="card-title"><i class="fas fa-medal"></i> {{ __('engagement.admin.status_tiers') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0 align-middle">
            <thead><tr>
                <th>{{ __('engagement.admin.name_en') }}</th>
                <th>{{ __('engagement.admin.name_ar') }}</th>
                <th style="width:160px;">{{ __('engagement.admin.min_lifetime') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($tiers as $tier)
                @php $f = 'tier-'.$tier->id; @endphp
                <tr>
                    <td><input form="{{ $f }}" type="text" name="name" value="{{ $tier->name }}" class="form-control form-control-sm" required></td>
                    <td><input form="{{ $f }}" type="text" name="name_ar" value="{{ $tier->name_ar }}" class="form-control form-control-sm" dir="rtl"></td>
                    <td><input form="{{ $f }}" type="number" name="min_lifetime_points" value="{{ $tier->min_lifetime_points }}" class="form-control form-control-sm" min="0" required></td>
                    <td class="text-end"><button form="{{ $f }}" class="btn btn-sm btn-primary">{{ __('engagement.admin.save') }}</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
