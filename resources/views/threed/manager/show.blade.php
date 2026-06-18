@extends('layouts.internal-dashboard')
@section('title', $threed->title)

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-cubes"></i> {{ $threed->title }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.threed.from') }} {{ $threed->user->name ?? '—' }}</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $threed->isPrinting() ? 'info' : 'secondary' }}" style="font-size:.8rem;"><i class="fas fa-{{ $threed->isPrinting() ? 'print' : 'pen-ruler' }}"></i> {{ $threed->typeLabel() }}</span>
        <span class="badge bg-{{ $threed->getStatusBadgeColor() }}" style="font-size:.8rem;">{{ $threed->getStatusLabel() }}</span>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.threed.detail_title') }}</span></div>
            <div class="card-content">
                <h6>{{ __('portal.threed.field.description') }}</h6>
                <p style="white-space:pre-line;">{{ $threed->description }}</p>

                @if($threed->isPrinting())
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted">{{ __('portal.threed.field.material') }}</small><div>{{ $threed->material ?: '—' }}</div></div>
                        <div class="col-md-4"><small class="text-muted">{{ __('portal.threed.field.color') }}</small><div>{{ $threed->color ?: '—' }}</div></div>
                        <div class="col-md-4"><small class="text-muted">{{ __('portal.threed.field.quantity') }}</small><div>{{ $threed->quantity ?: '—' }}</div></div>
                        <div class="col-md-4 mt-2"><small class="text-muted">{{ __('portal.threed.field.dimensions') }}</small><div>{{ $threed->dimensions ?: '—' }}</div></div>
                        <div class="col-md-4 mt-2"><small class="text-muted">{{ __('portal.threed.field.finish') }}</small><div>{{ $threed->finish ?: '—' }}</div></div>
                    </div>
                @else
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted">{{ __('portal.threed.field.output_format') }}</small><div>{{ $threed->output_format ?: '—' }}</div></div>
                        <div class="col-md-4"><small class="text-muted">{{ __('portal.threed.field.complexity') }}</small><div>{{ $threed->complexity ? __('portal.threed.complexity.'.$threed->complexity) : '—' }}</div></div>
                    </div>
                    @if($threed->reference_links)
                        <h6>{{ __('portal.threed.field.reference_links') }}</h6>
                        <p style="white-space:pre-line;">{{ $threed->reference_links }}</p>
                    @endif
                @endif

                <div class="row mb-3">
                    <div class="col-md-6"><small class="text-muted">{{ __('portal.threed.field.budget') }}</small><div>{{ $threed->budget_range ?: '—' }}</div></div>
                    <div class="col-md-6"><small class="text-muted">{{ __('portal.threed.field.timeline') }}</small><div>{{ $threed->timeline ?: '—' }}</div></div>
                </div>

                <h6>{{ __('portal.threed.field.files') }}</h6>
                @forelse($threed->files as $f)
                    <a href="{{ route('threed.manager.files.download', $f) }}" class="d-inline-flex align-items-center gap-2 me-2 mb-2 badge bg-light text-dark border" style="text-decoration:none;">
                        <i class="fas fa-download"></i> {{ $f->original_name }} <small class="text-muted">({{ $f->size_label }})</small>
                    </a>
                @empty
                    <p class="text-muted">{{ __('portal.threed.no_files') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.threed.assign_employee') }}</span></div>
            <div class="card-content">
                <form method="POST" action="{{ route('threed.assign', $threed) }}">
                    @csrf
                    <select name="assigned_to" class="form-select mb-2" required>
                        <option value="">{{ __('portal.threed.select_employee') }}</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected($threed->assigned_to === $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary w-100"><i class="fas fa-user-check"></i> {{ __('portal.threed.assign') }}</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.threed.update_status') }}</span></div>
            <div class="card-content">
                <form method="POST" action="{{ route('threed.update-status', $threed) }}">
                    @csrf
                    <select name="status" class="form-select mb-2">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected($threed->status === $s)>{{ __('portal.threed.status.'.$s) }}</option>
                        @endforeach
                    </select>
                    <textarea name="manager_notes" rows="3" class="form-control mb-2" placeholder="{{ __('portal.threed.notes_ph') }}">{{ $threed->manager_notes }}</textarea>
                    <button class="btn btn-primary w-100"><i class="fas fa-save"></i> {{ __('portal.threed.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($threed->status === 'completed')
@php $convertedProject = $threed->convertedProject(); @endphp
<div class="card mt-3"><div class="card-content d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><strong>{{ __('portal.services.convert_to_project') }}</strong><br><small class="text-muted">{{ __('portal.services.convert_hint') }}</small></div>
    @if($convertedProject)
    <a href="{{ route('projects.manager.show', $convertedProject) }}" class="btn btn-outline-primary"><i class="fas fa-diagram-project"></i> {{ __('portal.services.view_project') }}</a>
    @else
    <form method="POST" action="{{ route('threed.convert-to-project', $threed) }}">@csrf<button class="btn btn-primary"><i class="fas fa-diagram-project"></i> {{ __('portal.services.convert_to_project') }}</button></form>
    @endif
</div></div>
@endif

<div class="mt-3"><a href="{{ route('threed.manager.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.threed.back_to_queue') }}</a></div>
@endsection
