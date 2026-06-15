@extends('layouts.internal-dashboard')
@section('title', $prototype->title)

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-cube"></i> {{ $prototype->title }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.prototypes.from') }} {{ $prototype->user->name ?? '—' }}</p>
    </div>
    <span class="badge bg-{{ $prototype->getStatusBadgeColor() }}" style="font-size:.8rem;">{{ $prototype->getStatusLabel() }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.prototypes.detail_title') }}</span></div>
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
                    <a href="{{ route('prototypes.manager.files.download', $f) }}" class="d-inline-flex align-items-center gap-2 me-2 mb-2 badge bg-light text-dark border" style="text-decoration:none;">
                        <i class="fas fa-download"></i> {{ $f->original_name }} <small class="text-muted">({{ $f->size_label }})</small>
                    </a>
                @empty
                    <p class="text-muted">{{ __('portal.prototypes.no_files') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.prototypes.assign_employee') }}</span></div>
            <div class="card-content">
                <form method="POST" action="{{ route('prototypes.assign', $prototype) }}">
                    @csrf
                    <select name="assigned_to" class="form-select mb-2" required>
                        <option value="">{{ __('portal.prototypes.select_employee') }}</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected($prototype->assigned_to === $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary w-100"><i class="fas fa-user-check"></i> {{ __('portal.prototypes.assign') }}</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.prototypes.update_status') }}</span></div>
            <div class="card-content">
                <form method="POST" action="{{ route('prototypes.update-status', $prototype) }}">
                    @csrf
                    <select name="status" class="form-select mb-2">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected($prototype->status === $s)>{{ __('portal.prototypes.status.'.$s) }}</option>
                        @endforeach
                    </select>
                    <textarea name="manager_notes" rows="3" class="form-control mb-2" placeholder="{{ __('portal.prototypes.notes_ph') }}">{{ $prototype->manager_notes }}</textarea>
                    <button class="btn btn-primary w-100"><i class="fas fa-save"></i> {{ __('portal.prototypes.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($prototype->status === 'completed')
@php $convertedProject = $prototype->convertedProject(); @endphp
<div class="card mt-3"><div class="card-content d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><strong>{{ __('portal.services.convert_to_project') }}</strong><br><small class="text-muted">{{ __('portal.services.convert_hint') }}</small></div>
    @if($convertedProject)
    <a href="{{ route('projects.manager.show', $convertedProject) }}" class="btn btn-outline-primary"><i class="fas fa-diagram-project"></i> {{ __('portal.services.view_project') }}</a>
    @else
    <form method="POST" action="{{ route('prototypes.convert-to-project', $prototype) }}">@csrf<button class="btn btn-primary"><i class="fas fa-diagram-project"></i> {{ __('portal.services.convert_to_project') }}</button></form>
    @endif
</div></div>
@endif

<div class="mt-3"><a href="{{ route('prototypes.manager.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.prototypes.back_to_queue') }}</a></div>
@endsection
