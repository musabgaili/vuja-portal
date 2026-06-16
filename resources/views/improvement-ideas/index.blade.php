@extends(auth()->user()?->isInternal() ? 'layouts.internal-dashboard' : 'layouts.dashboard')
@section('title', __('portal.improvement_ideas.my_ideas'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-rocket"></i> {{ __('portal.improvement_ideas.my_ideas') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.improvement_ideas.my_ideas_subtitle') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()?->isManager())
        <a href="{{ route('improvement-ideas.manager.index') }}" class="btn btn-light"><i class="fas fa-clipboard-check"></i> {{ __('portal.improvement_ideas.review_all') }}</a>
        @endif
        <a href="{{ route('improvement-ideas.create') }}" class="btn btn-light"><i class="fas fa-plus"></i> {{ __('portal.improvement_ideas.new_idea') }}</a>
    </div>
</div>

@if($points)
<div class="alert alert-info"><i class="fas fa-bolt"></i> {{ __('portal.improvement_ideas.points_hint', ['points' => $points]) }}</div>
@endif

@if($ideas->count())
<div class="card"><div class="card-content p-0">
    <table class="table mb-0">
        <thead><tr>
            <th>{{ __('portal.improvement_ideas.field_title') }}</th>
            <th>{{ __('portal.improvement_ideas.field_category') }}</th>
            <th>{{ __('portal.improvement_ideas.col_status') }}</th>
            <th>{{ __('portal.improvement_ideas.col_submitted') }}</th>
        </tr></thead>
        <tbody>
        @foreach($ideas as $idea)
        <tr>
            <td><a href="{{ route('improvement-ideas.show', $idea) }}" style="color:var(--primary-color); text-decoration:none; font-weight:600;">{{ $idea->title }}</a></td>
            <td>{{ $idea->category }}</td>
            <td><span class="badge bg-{{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span></td>
            <td>{{ $idea->created_at->translatedFormat('M d, Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
<div class="d-flex justify-content-center mt-4">{{ $ideas->links('pagination::bootstrap-5') }}</div>
@else
<div class="card"><div class="card-content text-center py-5">
    <i class="fas fa-rocket fa-3x text-muted mb-3"></i>
    <h4>{{ __('portal.improvement_ideas.empty_title') }}</h4>
    <p class="text-muted">{{ __('portal.improvement_ideas.empty_body') }}</p>
    <a href="{{ route('improvement-ideas.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('portal.improvement_ideas.new_idea') }}</a>
</div></div>
@endif
@endsection
