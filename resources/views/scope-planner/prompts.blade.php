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

<form method="POST" action="{{ route('scope-prompts.update') }}">
    @csrf @method('PUT')
    @foreach($keys as $key)
        @php $t = $templates[$key]; @endphp
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="card-title">
                    <i class="fas fa-pen"></i> {{ __('portal.scope_prompts.key.'.$key) }}
                    @if($t['custom'])<span class="badge bg-warning text-dark">{{ __('portal.scope_prompts.custom') }}</span>
                    @else<span class="badge bg-secondary">{{ __('portal.scope_prompts.default') }}</span>@endif
                </span>
            </div>
            <div class="card-content">
                <div class="mb-2">
                    <small class="text-muted">{{ __('portal.scope_prompts.placeholders') }}:</small>
                    @foreach($placeholders[$key] ?? [] as $ph)
                        <code class="me-1">&#123;{{ $ph }}&#125;</code>
                    @endforeach
                </div>
                <textarea name="prompts[{{ $key }}]" rows="8" class="form-control" style="font-family:monospace;font-size:.85rem;">{{ $t['current'] }}</textarea>
            </div>
        </div>
    @endforeach

    <div class="d-flex gap-2 flex-wrap mb-4">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.scope_prompts.save') }}</button>
        <a href="{{ route('scope-planner.create') }}" class="btn btn-outline-secondary">{{ __('portal.team.cancel') }}</a>
    </div>
</form>

{{-- Reset-to-default per template (separate forms so each can revert independently) --}}
<div class="card">
    <div class="card-header"><span class="card-title">{{ __('portal.scope_prompts.reset_title') }}</span></div>
    <div class="card-content">
        <p class="text-muted">{{ __('portal.scope_prompts.reset_help') }}</p>
        <div class="d-flex gap-2 flex-wrap">
            @foreach($keys as $key)
            <form method="POST" action="{{ route('scope-prompts.reset') }}" onsubmit="return confirm(@js(__('portal.scope_prompts.reset_confirm')))" class="m-0">
                @csrf
                <input type="hidden" name="key" value="{{ $key }}">
                <button class="btn btn-sm btn-outline-danger" @disabled(! $templates[$key]['custom'])>
                    <i class="fas fa-rotate-left"></i> {{ __('portal.scope_prompts.key.'.$key) }}
                </button>
            </form>
            @endforeach
        </div>
    </div>
</div>
@endsection
