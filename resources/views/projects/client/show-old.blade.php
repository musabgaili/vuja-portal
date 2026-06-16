@extends('layouts.dashboard')
@section('title', $project->title)
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $project->title }}</h3>
                <span class="status-badge {{ $project->getStatusBadgeColor() }}">{{ $project->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <p>{{ $project->description }}</p>
                @if($project->scope)<div class="mb-3"><strong>{{ __('portal.projects_client.show_old.scope') }}:</strong><p>{{ $project->scope }}</p></div>@endif
                <div class="progress-section">
                    <strong>{{ __('portal.projects_client.show_old.overall_progress') }}: {{ $project->completion_percentage }}%</strong>
                    <div class="progress-bar mt-2"><div class="progress-fill" style="width:{{ $project->completion_percentage }}%;"></div></div>
                </div>
                
                <div class="mt-3 d-flex gap-2">
                    @if($project->isActive())
                    <a href="{{ route('projects.client.scope-change.create', $project) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> {{ __('portal.projects_client.show_old.request_scope_change') }}
                    </a>
                    @endif
                    @if($project->isCompleted() && !$project->feedback)
                    <a href="{{ route('projects.client.feedback.create', $project) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-star"></i> {{ __('portal.projects_client.show.rate_project') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h3>{{ __('portal.projects_client.show.milestones_timeline') }}</h3></div>
            <div class="card-content">
                @forelse($project->milestones as $milestone)
                <div class="milestone-item mb-4">
                    <div class="milestone-header">
                        <h5>{{ $milestone->title }}</h5>
                        <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">{{ ucfirst($milestone->status) }}</span>
                    </div>
                    @if($milestone->description)<p class="text-muted">{{ $milestone->description }}</p>@endif
                    <div class="progress-bar mt-2"><div class="progress-fill" style="width:{{ $milestone->completion_percentage }}%;"></div></div>
                    @if($milestone->tasks->count() > 0)
                    <div class="tasks-list mt-3">
                        @foreach($milestone->tasks as $task)
                        <div class="task-item">
                            <span class="status-badge {{ $task->getStatusBadgeColor() }}">{{ ucfirst($task->status) }}</span>
                            <span>{{ $task->title }}</span>
                            @if($task->assignedTo)<small class="text-muted">- {{ $task->assignedTo->name }}</small>@endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-muted">{{ __('portal.projects_client.show.no_milestones') }}</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>{{ __('portal.projects_client.show_old.comments_updates') }}</h3></div>
            <div class="card-content">
                @forelse($project->comments()->where('is_internal', false)->latest()->get() as $comment)
                <div class="comment-item">
                    <strong>{{ $comment->user->name }}</strong> <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    <p>{{ $comment->comment }}</p>
                </div>
                @empty
                <p class="text-muted">{{ __('portal.projects_client.show_old.no_comments_yet') }}</p>
                @endforelse
                <form method="POST" action="{{ route('projects.add-comment', $project) }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="commentable_type" value="App\Models\Project">
                    <input type="hidden" name="commentable_id" value="{{ $project->id }}">
                    <textarea name="comment" rows="3" class="form-control mb-2" placeholder="{{ __('portal.projects_client.show_old.add_comment_placeholder') }}" required></textarea>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> {{ __('portal.projects_client.show.post_comment') }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h3>{{ __('portal.projects_client.show_old.project_details') }}</h3></div>
            <div class="card-content">
                <div class="detail-item"><strong>{{ __('portal.projects_client.show_old.status') }}:</strong><span class="status-badge {{ $project->getStatusBadgeColor() }}">{{ $project->getStatusLabel() }}</span></div>
                <div class="detail-item"><strong>{{ __('portal.projects_client.show_old.start_date') }}:</strong><span>{{ $project->start_date?->translatedFormat('M d, Y') ?? __('portal.projects_client.show_old.tbd') }}</span></div>
                <div class="detail-item"><strong>{{ __('portal.projects_client.show_old.end_date') }}:</strong><span>{{ $project->end_date?->translatedFormat('M d, Y') ?? __('portal.projects_client.show_old.tbd') }}</span></div>
                @if($project->projectManager)<div class="detail-item"><strong>{{ __('portal.projects_client.show_old.pm') }}:</strong><span>{{ $project->projectManager->name }}</span></div>@endif
                @if($project->budget)<div class="detail-item"><strong>{{ __('portal.projects_client.show_old.budget') }}:</strong><span>${{ number_format($project->budget, 2) }}</span></div>@endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>{{ __('portal.projects_client.show_old.team') }}</h3></div>
            <div class="card-content">
                @forelse($project->getTeamMembers() as $member)
                <div class="team-member"><div class="user-avatar-sm">{{ $member->initials() }}</div><span>{{ $member->name }}</span></div>
                @empty
                <p class="text-muted">{{ __('portal.projects_client.show_old.no_team_assigned') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.milestone-item{padding:var(--space-md);background:var(--bg-tertiary);border-radius:var(--radius-md);border-left:3px solid var(--primary-color);}
.milestone-header{display:flex;justify-content:space-between;margin-bottom:var(--space-sm);}
.task-item{display:flex;gap:var(--space-sm);align-items:center;padding:var(--space-xs) 0;border-bottom:1px solid var(--gray-200);}
.comment-item{padding:var(--space-md);background:var(--bg-tertiary);border-radius:var(--radius-md);margin-bottom:var(--space-sm);}
.detail-item{display:flex;justify-content:space-between;padding:var(--space-sm) 0;border-bottom:1px solid var(--gray-200);}
.team-member{display:flex;align-items:center;gap:var(--space-sm);padding:var(--space-sm) 0;}
.user-avatar-sm{width:30px;height:30px;border-radius:50%;background:var(--primary-color);color:white;display:flex;align-items:center;justify-content:center;font-size:var(--font-size-xs);}
</style>
@endpush

