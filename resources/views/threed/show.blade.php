@extends('layouts.dashboard')

@section('title', $threed->tr('title'))
@section('page-title', __('portal.threed.detail_title'))

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $threed->tr('title') }}</h3>
        <div class="d-flex gap-2">
            <span class="badge bg-{{ $threed->isPrinting() ? 'info' : 'secondary' }}"><i class="fas fa-{{ $threed->isPrinting() ? 'print' : 'pen-ruler' }}"></i> {{ $threed->typeLabel() }}</span>
            <span class="badge bg-{{ $threed->getStatusBadgeColor() }}">{{ $threed->getStatusLabel() }}</span>
        </div>
    </div>
    <div class="card-content">
        <h6>{{ __('portal.threed.field.description') }}</h6>
        <p style="white-space:pre-line;">{{ $threed->tr('description') }}</p>

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
            <a href="{{ route('threed.files.download', $f) }}" class="d-inline-flex align-items-center gap-2 me-2 mb-2 badge bg-light text-dark border" style="text-decoration:none;">
                <i class="fas fa-paperclip"></i> {{ $f->original_name }} <small class="text-muted">({{ $f->size_label }})</small>
            </a>
        @empty
            <p class="text-muted">{{ __('portal.threed.no_files') }}</p>
        @endforelse

        @if($threed->assignedTo)
            <hr><p class="text-muted mb-0"><i class="fas fa-user"></i> {{ __('portal.threed.handled_by') }}: <strong>{{ $threed->assignedTo->name }}</strong></p>
        @endif

        <div class="mt-3"><a href="{{ route('threed.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.threed.back_to_list') }}</a></div>
    </div>
</div>
@endsection
