@extends('layouts.internal-dashboard')
@section('title', $opportunity->name)

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 style="margin:0; font-size:1.4rem;">{{ $opportunity->name }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9;">{{ $opportunity->company_name ?: __('portal.crm.no_company') }}</p>
        </div>
        <span class="badge bg-{{ $opportunity->stageColor() }}" style="font-size:.85rem;">{{ $opportunity->stageLabel() }}</span>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card"><div class="card-content">
            <div class="row g-3">
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.expected_value') }}</div><div style="font-size:1.4rem; font-weight:800; color:var(--primary-color);">{{ number_format((float)$opportunity->expected_value,2) }} {{ config('scope.currency','SAR') }}</div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.weighted') }}</div><div style="font-size:1.4rem; font-weight:700;">{{ number_format($opportunity->weightedValue(),2) }} {{ config('scope.currency','SAR') }} <small class="text-muted">({{ $opportunity->probability }}%)</small></div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.owner') }}</div><div>{{ $opportunity->owner->name ?? '—' }}</div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.close_date') }}</div><div>{{ optional($opportunity->expected_close_date)->translatedFormat('M j, Y') ?? '—' }}</div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.contact') }}</div><div>{{ $opportunity->contact_name ?: '—' }}</div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.source') }}</div><div>{{ $opportunity->source ?: '—' }}</div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.email') }}</div><div dir="ltr">{{ $opportunity->email ?: '—' }}</div></div>
                <div class="col-6"><div class="text-muted small">{{ __('portal.crm.phone') }}</div><div dir="ltr">{{ $opportunity->phone ?: '—' }}</div></div>
            </div>
            @if($opportunity->description)
                <hr><div class="text-muted small">{{ __('portal.crm.description') }}</div><p style="white-space:pre-line;">{{ $opportunity->description }}</p>
            @endif
            @if($opportunity->isLost() && $opportunity->lost_reason)
                <div class="alert alert-danger mt-2 mb-0">{{ __('portal.crm.lost_reason') }}: {{ $opportunity->lost_reason }}</div>
            @endif
        </div></div>
    </div>

    <div class="col-lg-5">
        <div class="card"><div class="card-content">
            <h3 class="card-title mb-3">{{ __('portal.crm.actions') }}</h3>
            @if($opportunity->converted_project_id)
                <div class="alert alert-success" data-persist><i class="fas fa-circle-check"></i> {{ __('portal.crm.already_won') }}</div>
                <a href="{{ route('projects.manager.show', $opportunity->project) }}" class="btn btn-primary w-100 mb-2"><i class="fas fa-folder-open"></i> {{ __('portal.crm.open_project') }}</a>
            @elseif($opportunity->isOpen())
                <form method="POST" action="{{ route('crm.convert', $opportunity) }}" class="mb-2" onsubmit="return confirm('{{ __('portal.crm.confirm_win') }}')">
                    @csrf
                    <button class="btn btn-primary w-100"><i class="fas fa-trophy"></i> {{ __('portal.crm.win_convert') }}</button>
                </form>
                <form method="POST" action="{{ route('crm.lost', $opportunity) }}" class="mb-2">
                    @csrf
                    <input type="text" name="lost_reason" class="form-control mb-2" placeholder="{{ __('portal.crm.lost_reason') }}">
                    <button class="btn btn-outline-primary w-100"><i class="fas fa-xmark"></i> {{ __('portal.crm.mark_lost') }}</button>
                </form>
            @endif
            <a href="{{ route('crm.edit', $opportunity) }}" class="btn btn-secondary w-100 mb-2"><i class="fas fa-pen"></i> {{ __('portal.crm.edit') }}</a>
            <a href="{{ route('crm.index') }}" class="btn btn-secondary w-100">{{ __('portal.crm.back_pipeline') }}</a>
        </div></div>
    </div>
</div>

<div class="mt-3">
    @include('partials.activity-timeline', ['subject' => $opportunity, 'subjectKey' => 'opportunity'])
</div>
@endsection
