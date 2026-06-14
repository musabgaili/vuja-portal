@extends('layouts.dashboard')

@section('title', $prototype->title)
@section('page-title', __('portal.prototypes.detail_title'))

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $prototype->title }}</h3>
        <span class="badge bg-{{ $prototype->getStatusBadgeColor() }}">{{ $prototype->getStatusLabel() }}</span>
    </div>
    <div class="card-content">
        <div class="row mb-3">
            <div class="col-md-4"><small class="text-muted">{{ __('portal.prototypes.field.category') }}</small><div>{{ $prototype->category ?: '—' }}</div></div>
            <div class="col-md-4"><small class="text-muted">{{ __('portal.prototypes.field.budget') }}</small><div>{{ $prototype->budget_range ?: '—' }}</div></div>
            <div class="col-md-4"><small class="text-muted">{{ __('portal.prototypes.field.timeline') }}</small><div>{{ $prototype->timeline ?: '—' }}</div></div>
        </div>

        <h6>{{ __('portal.prototypes.field.description') }}</h6>
        <p style="white-space:pre-line;">{{ $prototype->description }}</p>

        @if($prototype->goals)
            <h6>{{ __('portal.prototypes.field.goals') }}</h6>
            <p style="white-space:pre-line;">{{ $prototype->goals }}</p>
        @endif

        <h6>{{ __('portal.prototypes.field.files') }}</h6>
        @forelse($prototype->files as $f)
            <a href="{{ route('prototypes.files.download', $f) }}" class="d-inline-flex align-items-center gap-2 me-2 mb-2 badge bg-light text-dark border" style="text-decoration:none;">
                <i class="fas fa-paperclip"></i> {{ $f->original_name }} <small class="text-muted">({{ $f->size_label }})</small>
            </a>
        @empty
            <p class="text-muted">{{ __('portal.prototypes.no_files') }}</p>
        @endforelse

        @if($prototype->assignedTo)
            <hr><p class="text-muted mb-0"><i class="fas fa-user"></i> {{ __('portal.prototypes.handled_by') }}: <strong>{{ $prototype->assignedTo->name }}</strong></p>
        @endif

        <div class="mt-3"><a href="{{ route('prototypes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.prototypes.back_to_list') }}</a></div>
    </div>
</div>
@endsection
