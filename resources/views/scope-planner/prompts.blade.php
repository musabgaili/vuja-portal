@extends('layouts.internal-dashboard')
@section('title', __('portal.scope_prompts.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('scope-planner.create') }}">{{ __('portal.nav.scope_planner') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.scope_prompts.breadcrumb') }}</li>
@endsection

@php
    $placeholders = [
        'generate_system' => ['tier', 'lang', 'length', 'budget'],
        'generate_user' => ['brief', 'components', 'services', 'tier', 'length', 'structure', 'sections', 'company_scope_rule'],
        'suggest_system' => ['tier'],
    ];
@endphp

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_prompts.title') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.scope_prompts.subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="alert alert-info">
    <i class="fas fa-circle-info"></i> {{ __('portal.scope_prompts.note') }}
</div>

{{-- Jump-to-tier tabs --}}
<ul class="nav nav-pills mb-3" role="tablist">
    @foreach($tiers as $i => $tier)
    <li class="nav-item">
        <button class="nav-link {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tier-{{ $tier }}" type="button">
            {{ __('portal.scope_planner.tier_'.$tier) }}
        </button>
    </li>
    @endforeach
</ul>

<form method="POST" action="{{ route('scope-prompts.update') }}">
    @csrf @method('PUT')
    <div class="tab-content">
    @foreach($tiers as $i => $tier)
        <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tier-{{ $tier }}" role="tabpanel">
            @foreach($types as $type)
                @php $t = $templates[$tier][$type]; @endphp
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="card-title">
                            <i class="fas fa-pen"></i> {{ __('portal.scope_prompts.key.'.$type) }}
                            @if($t['custom'])<span class="badge bg-warning text-dark">{{ __('portal.scope_prompts.custom') }}</span>
                            @else<span class="badge bg-secondary">{{ __('portal.scope_prompts.default') }}</span>@endif
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="mb-2">
                            <small class="text-muted">{{ __('portal.scope_prompts.placeholders') }}:</small>
                            @foreach($placeholders[$type] ?? [] as $ph)
                                <code class="me-1">&#123;{{ $ph }}&#125;</code>
                            @endforeach
                        </div>
                        <textarea name="prompts[{{ $tier }}][{{ $type }}]" rows="8" class="form-control" style="font-family:monospace;font-size:.85rem;">{{ $t['current'] }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
    </div>

    <div class="d-flex gap-2 flex-wrap mb-4">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.scope_prompts.save') }}</button>
        <a href="{{ route('scope-planner.create') }}" class="btn btn-outline-secondary">{{ __('portal.team.cancel') }}</a>
    </div>
</form>

{{-- Reset a single (tier, type) back to the shipped default --}}
<div class="card">
    <div class="card-header"><span class="card-title">{{ __('portal.scope_prompts.reset_title') }}</span></div>
    <div class="card-content">
        <p class="text-muted">{{ __('portal.scope_prompts.reset_help') }}</p>
        @foreach($tiers as $tier)
        <div class="mb-2">
            <strong class="d-block mb-1">{{ __('portal.scope_planner.tier_'.$tier) }}</strong>
            <div class="d-flex gap-2 flex-wrap">
                @foreach($types as $type)
                <form method="POST" action="{{ route('scope-prompts.reset') }}" onsubmit="return confirm(@js(__('portal.scope_prompts.reset_confirm')))" class="m-0">
                    @csrf
                    <input type="hidden" name="tier" value="{{ $tier }}">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <button class="btn btn-sm btn-outline-danger" @disabled(! $templates[$tier][$type]['custom'])>
                        <i class="fas fa-rotate-left"></i> {{ __('portal.scope_prompts.key.'.$type) }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
