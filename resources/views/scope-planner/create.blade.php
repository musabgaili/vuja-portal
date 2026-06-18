@extends('layouts.internal-dashboard')
@section('title', __('portal.scope_planner.new_document'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('scope-planner.index') }}">{{ __('portal.nav.scope_planner') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.scope_planner.new_document') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-file-signature"></i> {{ __('portal.scope_planner.new_document') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.scope_planner.new_document_sub') }}</p>
</div>

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@php $prefill = $prefill ?? ['subject' => '', 'brief' => '']; @endphp
@if(!empty($prefill['subject']) || !empty($prefill['brief']))
<div class="alert alert-info"><i class="fas fa-arrow-right-arrow-left me-1"></i> {{ __('portal.scope_planner.carried_over') }}</div>
@endif

<form method="POST" action="{{ route('scope-planner.store') }}" class="card">
    @csrf
    <div class="card-content">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.tier') }} *</label>
                <select name="tier" class="form-select" required>
                    @foreach($categories as $t)<option value="{{ $t }}" @selected(old('tier','company')===$t)>{{ __('portal.scope_planner.tier_'.$t) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.length') }} *</label>
                <select name="length" class="form-select" required>
                    @foreach($lengths as $l)<option value="{{ $l }}" @selected(old('length','medium')===$l)>{{ __('portal.scope_planner.length_'.$l) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.language') }} *</label>
                <select name="language" class="form-select" required>
                    <option value="en" @selected(old('language','en')==='en')>English</option>
                    <option value="ar" @selected(old('language')==='ar')>العربية</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.structure') }}</label>
                <select name="structure" class="form-select">
                    @foreach($structures as $s)<option value="{{ $s }}" @selected(old('structure','single')===$s)>{{ __('portal.scope_planner.structure_'.$s) }}</option>@endforeach
                </select>
                <small class="text-muted">{{ __('portal.scope_planner.structure_hint') }}</small>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.subject') }}</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $prefill['subject'] ?? '') }}" placeholder="{{ __('portal.scope_planner.subject_ph') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.client') }}</label>
                <select name="client_id" class="form-select">
                    <option value="">{{ __('portal.scope_planner.none') }}</option>
                    @foreach($clients as $cl)<option value="{{ $cl->id }}" @selected(old('client_id')==$cl->id)>{{ $cl->name }} ({{ $cl->email }})</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.beneficiary') }}</label>
                <input type="text" name="beneficiary" class="form-control" value="{{ old('beneficiary') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.opportunity') }}</label>
                <select name="opportunity_id" class="form-select">
                    <option value="">{{ __('portal.scope_planner.none') }}</option>
                    @foreach($opportunities as $o)<option value="{{ $o->id }}" @selected(old('opportunity_id')==$o->id)>{{ $o->name }}</option>@endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.scope_planner.brief') }} *</label>
                <textarea name="brief" rows="6" class="form-control" required placeholder="{{ __('portal.scope_planner.brief_ph') }}">{{ old('brief', $prefill['brief'] ?? '') }}</textarea>
                <small class="text-muted">{{ __('portal.scope_planner.brief_hint') }}</small>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('scope-planner.index') }}" class="btn btn-secondary">{{ __('portal.scope_planner.cancel') }}</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_planner.create_continue') }}</button>
    </div>
</form>
@endsection
