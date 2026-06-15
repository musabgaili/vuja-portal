@extends('layouts.internal-dashboard')
@section('title', __('portal.improvement_ideas.manager_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.improvement_ideas.manager_title') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-clipboard-check"></i> {{ __('portal.improvement_ideas.manager_title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.improvement_ideas.manager_subtitle') }}</p>
    </div>
    <div class="text-end">
        <h2 style="margin:0; font-size:2.2rem; font-weight:700;">{{ $ideas->total() }}</h2>
        <small style="opacity:.9;">{{ __('portal.improvement_ideas.total') }}</small>
    </div>
</div>

@if($points)
<div class="alert alert-info" data-persist><i class="fas fa-bolt"></i> {{ __('portal.improvement_ideas.manager_points_hint', ['points' => $points]) }}</div>
@endif

<div class="card mb-3"><div class="card-content">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label class="form-label fw-bold">{{ __('portal.improvement_ideas.filter_status') }}</label>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">{{ __('portal.improvement_ideas.all') }}</option>
                @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ __('portal.improvement_ideas.status.'.$s) }}</option>
                @endforeach
            </select>
        </div>
        @if(request('status'))
        <a href="{{ route('improvement-ideas.manager.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> {{ __('portal.improvement_ideas.clear') }}</a>
        @endif
    </form>
</div></div>

@if($ideas->count())
<div class="card"><div class="card-content p-0">
    <table class="table mb-0">
        <thead><tr>
            <th>{{ __('portal.improvement_ideas.field_title') }}</th>
            <th>{{ __('portal.improvement_ideas.submitted_by') }}</th>
            <th>{{ __('portal.improvement_ideas.field_category') }}</th>
            <th>{{ __('portal.improvement_ideas.col_status') }}</th>
            <th>{{ __('portal.improvement_ideas.col_submitted') }}</th>
        </tr></thead>
        <tbody>
        @foreach($ideas as $idea)
        <tr>
            <td><a href="{{ route('improvement-ideas.manager.show', $idea) }}" style="color:var(--primary-color); text-decoration:none; font-weight:600;">{{ $idea->title }}</a></td>
            <td>{{ $idea->user?->name }}</td>
            <td>{{ $idea->category }}</td>
            <td><span class="badge bg-{{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span></td>
            <td>{{ $idea->created_at->format('M d, Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
<div class="d-flex justify-content-center mt-4">{{ $ideas->links('pagination::bootstrap-5') }}</div>
@else
<div class="card"><div class="card-content text-center py-5">
    <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
    <h4>{{ __('portal.improvement_ideas.manager_empty_title') }}</h4>
    <p class="text-muted">{{ __('portal.improvement_ideas.manager_empty_body') }}</p>
</div></div>
@endif
@endsection
