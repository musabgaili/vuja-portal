@extends('layouts.dashboard')
@section('title', $project->tr('title'))
@section('content')

<style>
/* Modern Client Project View */
.client-project-header {
    background: linear-gradient(135deg, #0C7075 0%, #2C3F43 100%);
    color: white;
    padding: 2.5rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(28, 87, 95, 0.25);
}
.client-project-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
}
.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    backdrop-filter: blur(10px);
}
.progress-number {
    font-size: 2.5rem;
    font-weight: 700;
}
.section-modern {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
}
.section-modern:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
.section-modern h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #0C7075;
}
.milestone-card-client {
    background: var(--bg-tertiary);
    border-left: 5px solid #0C7075;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    transition: all 0.3s;
}
.milestone-card-client:hover {
    box-shadow: 0 4px 16px rgba(28, 87, 95, 0.12);
    transform: translateX(5px);
}
.team-member-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s;
}
.team-member-card:hover {
    border-color: #0C7075;
    box-shadow: 0 4px 12px rgba(28, 87, 95, 0.12);
}
.team-avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0C7075 0%, #2C3F43 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(28, 87, 95, 0.25);
}
.comment-modern {
    background: #f8fafc;
    border-left: 4px solid #0C7075;
    padding: 1.25rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    transition: all 0.3s;
}
.comment-modern:hover {
    background: white;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.comment-modern.internal {
    border-left-color: #f59e0b;
    background: #fffbeb;
}
.action-btn-client {
    background: linear-gradient(135deg, #0C7075 0%, #2C3F43 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(28, 87, 95, 0.22);
}
.action-btn-client:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(28, 87, 95, 0.3);
}
.stats-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-modern {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    transition: all 0.3s;
}
.stat-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.stat-modern-value {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #0C7075 0%, #2C3F43 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.stat-modern-label {
    color: #64748b;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
.progress-modern {
    height: 12px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 0.75rem 0;
}
.progress-modern-fill {
    height: 100%;
    background: linear-gradient(90deg, #0C7075 0%, #2C3F43 100%);
    transition: width 0.5s ease;
    box-shadow: 0 0 10px rgba(28, 87, 95, 0.28);
}
</style>

<!-- Project Header -->
<div class="client-project-header">
    <div class="row align-items-center">
        <div class="col-md-9">
            <h1>{{ $project->tr('title') }}</h1>
            <p style="opacity: 0.95; font-size: 1.1rem; margin-bottom: 1.5rem;">{{ $project->tr('description') }}</p>
            <div class="d-flex gap-2">
                <button class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid white;" onclick="showRequestModal()">
                    <i class="fas fa-hand-paper"></i> {{ __('portal.projects_client.show.submit_request') }}
                </button>
                <button class="btn btn-sm" style="background: rgba(255,100,100,0.3); color: white; border: 1px solid white;" onclick="showComplaintModal()">
                    <i class="fas fa-exclamation-triangle"></i> {{ __('portal.projects_client.show.submit_complaint') }}
                </button>
            </div>
            
            <div class="d-flex gap-3 align-items-center flex-wrap">
                <span class="badge" style="background: rgba(255,255,255,0.3); font-size: 1rem; padding: 0.5rem 1rem;">
                    <i class="fas fa-circle" style="color: {{ $project->isActive() ? '#0C7075' : '#f59e0b' }};"></i>
                    {{ $project->getStatusLabel() }}
                </span>
                @if($project->start_date)
                <span style="opacity: 0.9;">
                    <i class="fas fa-calendar"></i> 
                    {{ $project->start_date->translatedFormat('M d, Y') }}
                    @if($project->end_date)
                    â†’ {{ $project->end_date->translatedFormat('M d, Y') }}
                    @endif
                </span>
                @endif
            </div>
        </div>
        <div class="col-md-3 text-center">
            <div class="progress-circle mx-auto">
                <div class="progress-number">{{ $project->completion_percentage }}%</div>
                <small style="opacity: 0.8;">{{ __('portal.projects_client.index.complete') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mb-4" style="display: flex; gap: 1rem; flex-wrap: wrap;">
    @if($project->isActive())
    <a href="{{ route('projects.client.scope-change.create', $project) }}" class="action-btn-client">
        <i class="fas fa-edit"></i> {{ __('portal.projects_client.show.request_change') }}
    </a>
    @endif
    @if($project->isCompleted() && !$project->feedback)
    <a href="{{ route('projects.client.feedback.create', $project) }}" class="action-btn-client" style="background: linear-gradient(135deg, #0F969C 0%, #0C7075 100%);">
        <i class="fas fa-star"></i> {{ __('portal.projects_client.show.rate_project') }}
    </a>
    @endif
</div>

<!-- Stats Grid -->
<div class="stats-modern">
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->milestones->count() }}</div>
        <div class="stat-modern-label">{{ __('portal.projects_client.index.milestones') }}</div>
    </div>
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->tasks->count() }}</div>
        <div class="stat-modern-label">{{ __('portal.projects_client.show.total_tasks') }}</div>
    </div>
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->tasks->where('status', 'completed')->count() }}</div>
        <div class="stat-modern-label">{{ __('portal.projects_client.index.completed') }}</div>
    </div>
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->getTeamMembers()->count() }}</div>
        <div class="stat-modern-label">{{ __('portal.projects_client.index.team_members') }}</div>
    </div>
</div>

<!-- Project Scope -->
@if($project->scope)
<div class="section-modern">
    <h3><i class="fas fa-bullseye"></i> {{ __('portal.projects_client.show.project_scope') }}</h3>
    <p style="white-space: pre-line; color: #4b5563; line-height: 1.8;">{{ $project->scope }}</p>
</div>
@endif

<!-- Milestones & Timeline -->
<div class="section-modern">
    <h3><i class="fas fa-flag"></i> {{ __('portal.projects_client.show.milestones_timeline') }}</h3>
    @forelse($project->milestones as $milestone)
    <div class="milestone-card-client">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div style="flex: 1;">
                <h5 style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">
                    {{ $milestone->tr('title') }}
                    @if($milestone->client_approved)
                    <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> {{ __('portal.projects_client.show.approved') }}</span>
                    @endif
                </h5>
                @if($milestone->description)
                <p style="color: #64748b; margin-bottom: 1rem;">{{ $milestone->tr('description') }}</p>
                @endif
            </div>
            <div class="d-flex gap-2 align-items-start flex-wrap">
                <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">
                    {{ $milestone->getStatusLabel() }}
                </span>
                
                @php
                    $totalTasks = $milestone->tasks->count();
                    $completedOrReviewTasks = $milestone->tasks->whereIn('status', ['completed', 'review'])->count();
                    $canReview = $totalTasks === 0 || $completedOrReviewTasks === $totalTasks;
                @endphp
                
                @if($milestone->client_approved === true)
                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> {{ __('portal.projects_client.show.approved') }}</span>
                @elseif($milestone->client_approved === false)
                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> {{ __('portal.projects_client.show.rejected') }}</span>
                @elseif($canReview)
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success" onclick="approveMilestone(@js($milestone->getRouteKey()), 'approve')">
                            <i class="fas fa-thumbs-up"></i> {{ __('portal.projects_client.show.approve') }}
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="approveMilestone(@js($milestone->getRouteKey()), 'reject')">
                            <i class="fas fa-thumbs-down"></i> {{ __('portal.projects_client.show.reject') }}
                        </button>
                    </div>
                @else
                    <button class="btn btn-sm btn-warning" disabled title="{{ __('portal.projects_client.show.waiting_for_tasks') }}">
                        <i class="fas fa-clock"></i> {{ $completedOrReviewTasks }}/{{ $totalTasks }} {{ __('portal.projects_client.show.ready') }}
                    </button>
                @endif
            </div>
        </div>
        
        @php
            $milestoneTasks = $milestone->tasks;
            $totalTasks = $milestoneTasks->count();
            $completedTasks = $milestoneTasks->where('status', 'completed')->count();
            $taskProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        @endphp
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small style="color: #64748b;">
                <i class="fas fa-tasks"></i> {{ $completedTasks }}/{{ $totalTasks }} {{ __('portal.projects_client.show.tasks_completed') }}
            </small>
            @if($milestone->due_date)
            <small style="color: #64748b;">
                <i class="fas fa-calendar"></i> {{ __('portal.projects_client.show.due') }}: {{ $milestone->due_date->translatedFormat('M d, Y') }}
            </small>
            @endif
        </div>
        
        <div class="progress-modern">
            <div class="progress-modern-fill" style="width:{{ $taskProgress }}%;"></div>
        </div>
        <small style="color: #64748b;">{{ $taskProgress }}% {{ __('portal.projects_client.index.complete') }}</small>
    </div>
    @empty
    <p class="text-muted text-center py-4">{{ __('portal.projects_client.show.no_milestones') }}</p>
    @endforelse
</div>

<!-- Deliverables & Comments Row -->
<div class="row">
    <div class="col-lg-6">
        <div class="section-modern">
            <h3><i class="fas fa-box-open"></i> {{ __('portal.projects_client.show.project_deliverables') }}</h3>
            @forelse($project->deliverables as $deliverable)
            <div class="doc-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h5 class="mb-1">
                            <i class="fas fa-file-archive text-success"></i> {{ $deliverable->title }}
                            @if($deliverable->client_confirmed)
                            <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> {{ __('portal.projects_client.show.confirmed') }}</span>
                            @endif
                        </h5>
                        @if($deliverable->description)
                        <p class="text-muted small">{{ $deliverable->description }}</p>
                        @endif
                        <small class="text-muted">
                            {{ __('portal.projects_client.show.uploaded_by') }} {{ $deliverable->uploadedBy->name }} â€¢ {{ $deliverable->created_at->translatedFormat('M d, Y') }}
                        </small>
                    </div>
                    <div class="d-flex gap-2 flex-column">
                        <a href="{{ route('projects.client.deliverables.download', $deliverable) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> {{ __('portal.pricing.download') }}
                        </a>
                        @if(!$deliverable->client_confirmed)
                        <form method="POST" action="{{ route('projects.client.deliverables.confirm', $deliverable) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('{{ __('portal.projects_client.show.confirm_deliverable_receipt_confirm') }}')">
                                <i class="fas fa-check"></i> {{ __('portal.projects_client.show.confirm') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center text-muted py-4">{{ __('portal.projects_client.show.no_deliverables') }}</p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-6">
        <div class="section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="fas fa-comments"></i> {{ __('portal.projects_client.show.comments_files') }}</h3>
                <button class="btn btn-sm btn-primary" onclick="showUploadFileModal()">
                    <i class="fas fa-upload"></i> {{ __('portal.projects_client.show.upload_file') }}
                </button>
            </div>
            
            <!-- Add Comment Form -->
            <div class="mb-4" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px;">
                <form method="POST" action="{{ route('projects.client.add-comment', $project) }}">
                    @csrf
                    <input type="hidden" name="commentable_type" value="App\Models\Project">
                    <input type="hidden" name="commentable_id" value="{{ $project->id }}">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #1e293b;">{{ __('portal.projects_client.show.add_comment') }}</label>
                        <textarea name="comment" class="form-control" rows="3" 
                            placeholder="{{ __('portal.projects_client.show.comment_placeholder') }}" 
                            required 
                            style="border-radius: 12px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                    <button type="submit" class="action-btn-client">
                        <i class="fas fa-paper-plane"></i> {{ __('portal.projects_client.show.post_comment') }}
                    </button>
                </form>
            </div>

            <!-- Comments List -->
            <div style="max-height: 500px; overflow-y: auto;">
                @forelse($project->comments->where('internal_note', false)->sortByDesc('created_at') as $comment)
                <div class="comment-modern {{ $comment->is_internal ? 'internal' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong style="color: #1e293b; font-size: 1.05rem;">{{ $comment->user->name }}</strong>
                            @if($comment->is_internal)
                            <span class="badge bg-warning" style="font-size: 0.75rem;">
                                <i class="fas fa-shield-alt"></i> {{ __('portal.projects_client.show.vujade_team') }}
                            </span>
                            @else
                            <span class="badge" style="font-size: 0.75rem; background: #2C3F43;">
                                <i class="fas fa-user"></i> {{ __('portal.projects_client.show.client') }}
                            </span>
                            @endif
                            @if($comment->user_id === auth()->id())
                            <span class="badge bg-success" style="font-size: 0.75rem;">{{ __('portal.projects_client.show.you') }}</span>
                            @endif
                        </div>
                        <small style="color: #94a3b8;">
                            <i class="fas fa-clock"></i> {{ $comment->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <p style="color: #475569; margin: 0; line-height: 1.6;">{{ $comment->comment }}</p>
                </div>
                @empty
                <div class="text-center py-5" style="background: #f8fafc; border-radius: 12px;">
                    <i class="fas fa-comments" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                    <p class="text-muted">{{ __('portal.projects_client.show.no_comments') }}</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Project Documents -->
<div class="section-modern">
    <h3><i class="fas fa-folder-open"></i> {{ __('portal.projects_client.show.project_documents') }}</h3>
    @forelse($project->documents as $doc)
    <div class="doc-card">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex: 1;">
                <h5 class="mb-1">
                    <i class="fas fa-file-{{ $doc->file_type === 'pdf' ? 'pdf text-danger' : 'alt text-primary' }}"></i> {{ $doc->title }}
                </h5>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="badge" style="background: {{ match($doc->tag) { 'initial' => '#0C7075', 'design' => '#3b82f6', 'development' => '#0C7075', 'final' => '#f59e0b', default => '#6b7280' } }};">
                        {{ ucfirst($doc->tag) }}
                    </span>
                    <small class="text-muted">
                        <i class="fas fa-user"></i> {{ $doc->uploadedBy->name }}
                    </small>
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> {{ $doc->created_at->translatedFormat('M d, Y') }}
                    </small>
                    <small class="text-muted">
                        <i class="fas fa-file"></i> {{ number_format($doc->file_size / 1024, 1) }} KB
                    </small>
                </div>
                @if($doc->comment)
                <p class="text-muted small mb-0"><i class="fas fa-comment"></i> {{ $doc->comment }}</p>
                @endif
            </div>
            <div class="ms-3">
                <a href="{{ route('projects.client.documents.download', $doc) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-download"></i> {{ __('portal.projects_client.show.download') }}
                </a>
            </div>
        </div>
    </div>
    @empty
    <p class="text-center text-muted py-4">{{ __('portal.projects_client.show.no_documents') }}</p>
    @endforelse
</div>

<!-- Project Team -->
<div class="section-modern">
    <h3><i class="fas fa-users"></i> {{ __('portal.projects_client.show.project_team') }}</h3>
    <div class="row">
        @foreach($project->projectPeople as $person)
        <div class="col-md-6 mb-3">
            <div class="team-member-card">
                <div class="team-avatar-large">
                    {{ strtoupper(substr($person->user->name, 0, 1)) }}
                </div>
                <div style="flex: 1;">
                    <div class="d-flex align-items-center gap-2">
                        <strong style="font-size: 1.1rem; color: #1e293b;">{{ $person->user->name }}</strong>
                        @if($person->user_id === auth()->id())
                        <span class="badge bg-success">{{ __('portal.projects_client.show.you') }}</span>
                        @endif
                    </div>
                    <div class="mt-1">
                        @if($person->role === 'project_manager')
                        <span class="badge bg-success" style="font-size: 0.85rem;">
                            <i class="fas fa-star"></i> {{ __('portal.projects_client.show.project_manager') }}
                        </span>
                        @elseif($person->role === 'client')
                        <span class="badge" style="font-size: 0.85rem; background: #2C3F43;">
                            <i class="fas fa-user"></i> {{ __('portal.projects_client.show.client') }}
                        </span>
                        @else
                        <span class="badge bg-secondary" style="font-size: 0.85rem;">
                            <i class="fas fa-user-tie"></i> {{ __('portal.projects_client.show.team_member') }}
                        </span>
                        @endif
                    </div>
                    <small style="color: #64748b;">
                        <i class="fas fa-envelope"></i> {{ $person->user->email }}
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>


<!-- Scope Change Requests (if any) -->
@if($project->scopeChanges->where('requested_by', auth()->id())->count() > 0)
<div class="section-modern">
    <h3><i class="fas fa-exchange-alt"></i> {{ __('portal.projects_client.show.my_scope_change_requests') }}</h3>
    @foreach($project->scopeChanges->where('requested_by', auth()->id())->sortByDesc('created_at') as $change)
    <div class="milestone-card-client" style="border-left-color: {{ $change->getStatusBadgeColor() === 'success' ? '#0C7075' : ($change->getStatusBadgeColor() === 'danger' ? '#ef4444' : '#f59e0b') }};">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex: 1;">
                <h5 style="font-weight: 600; color: #1e293b;">{{ $change->title }}</h5>
                <p style="color: #64748b; margin-bottom: 0.5rem;">{{ $change->description }}</p>
                <small style="color: #94a3b8;">{{ __('portal.projects_client.show.submitted') }}: {{ $change->created_at->translatedFormat('M d, Y') }}</small>
            </div>
            <span class="status-badge {{ $change->getStatusBadgeColor() }}">
                {{ $change->getStatusLabel() }}
            </span>
        </div>
        @if($change->review_notes)
        <div class="mt-2 p-2" style="background: rgba(0,0,0,0.05); border-radius: 8px;">
            <small><strong>{{ __('portal.projects_client.show.review') }}:</strong> {{ $change->review_notes }}</small>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

<!-- Approve Milestone Modal -->
<div class="modal fade" id="approveMilestoneModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="approveModalHeader">
                <h5 id="approveModalTitle"><i class="fas fa-check-circle"></i> {{ __('portal.projects_client.show.review_milestone') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="approveMilestoneForm">
                @csrf
                <input type="hidden" name="action" id="approvalAction" value="approve">
                <div class="modal-body">
                    <div class="alert alert-info" id="approveAlert">
                        <i class="fas fa-info-circle"></i> <span id="approveAlertText">{{ __('portal.projects_client.show.confirm_deliverables_expectations') }}</span>
                    </div>
                    <div class="form-group">
                        <label id="noteLabel">{{ __('portal.projects_client.show.note_optional') }}</label>
                        <textarea name="approval_note" id="approvalNote" class="form-control" rows="3" placeholder="{{ __('portal.projects_client.show.note_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" id="approveSubmitBtn">
                        <i class="fas fa-check"></i> <span id="approveSubmitText">{{ __('portal.projects_client.show.approve') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- Complaint Modal -->
<div class="modal fade" id="complaintModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
    <h5><i class="fas fa-exclamation-triangle"></i> {{ __('portal.projects_client.show.submit_complaint') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.client.complaints.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> {{ __('portal.projects_client.show.complaint_alert_recipients') }}
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_client.show.subject') }} *</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_client.show.complaint_details') }} *</label>
                        <textarea name="complaint" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-paper-plane"></i> {{ __('portal.projects_client.show.submit_complaint') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
    <h5><i class="fas fa-upload"></i> {{ __('portal.projects_client.show.upload_file') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.client.documents.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="fw-bold">{{ __('portal.projects_client.show.title') }} *</label>
                        <input type="text" name="title" class="form-control" placeholder="{{ __('portal.projects_client.show.enter_file_title') }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">{{ __('portal.projects_client.show.file') }} *</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">{{ __('portal.projects_client.show.tag') }} *</label>
                        <select name="tag" class="form-control" required>
                            <option value="">{{ __('portal.projects_client.show.select_tag') }}</option>
                            <option value="initial">{{ __('portal.projects_client.show.tag_initial_draft') }}</option>
                            <option value="design">{{ __('portal.projects_client.show.tag_design_file') }}</option>
                            <option value="development">{{ __('portal.projects_client.show.tag_development') }}</option>
                            <option value="final">{{ __('portal.projects_client.show.tag_final_version') }}</option>
                            <option value="other">{{ __('portal.projects_client.show.tag_other') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">{{ __('portal.projects_client.show.comment_optional') }}</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="{{ __('portal.projects_client.show.file_comment_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> {{ __('portal.projects_client.show.upload_file') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Modal -->
<div class="modal fade" id="requestModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: #2C3F43;">
    <h5><i class="fas fa-hand-paper"></i> {{ __('portal.projects_client.show.submit_request') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.client.requests.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ __('portal.projects_client.show.request_alert_recipients') }}
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">{{ __('portal.projects_client.show.subject') }} *</label>
                        <input type="text" name="subject" class="form-control" required placeholder="{{ __('portal.projects_client.show.subject_placeholder') }}">
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">{{ __('portal.projects_client.show.request_details') }} *</label>
                        <textarea name="request" class="form-control" rows="4" required placeholder="{{ __('portal.projects_client.show.request_details_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn text-white" style="background: #2C3F43; border-color: #2C3F43;">
                        <i class="fas fa-paper-plane"></i> {{ __('portal.projects_client.show.submit_request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveMilestone(milestoneId, action) {
    const form = document.getElementById('approveMilestoneForm');
    const header = document.getElementById('approveModalHeader');
    const title = document.getElementById('approveModalTitle');
    const alert = document.getElementById('approveAlert');
    const alertText = document.getElementById('approveAlertText');
    const noteLabel = document.getElementById('noteLabel');
    const noteField = document.getElementById('approvalNote');
    const submitBtn = document.getElementById('approveSubmitBtn');
    const submitText = document.getElementById('approveSubmitText');
    const actionInput = document.getElementById('approvalAction');
    
    form.action = `/projects/milestones/${milestoneId}/approve`;
    actionInput.value = action;
    
    if (action === 'approve') {
        header.className = 'modal-header bg-success text-white';
        title.innerHTML = '<i class="fas fa-thumbs-up"></i> ' + @js(__('portal.projects_client.show.approve_milestone'));
        alert.className = 'alert alert-success';
        alertText.textContent = @js(__('portal.projects_client.show.confirm_deliverables_expectations'));
        noteLabel.textContent = @js(__('portal.projects_client.show.note_optional'));
        noteField.placeholder = @js(__('portal.projects_client.show.any_positive_feedback'));
        noteField.required = false;
        submitBtn.className = 'btn btn-success';
        submitText.textContent = @js(__('portal.projects_client.show.approve'));
    } else {
        header.className = 'modal-header bg-danger text-white';
        title.innerHTML = '<i class="fas fa-thumbs-down"></i> ' + @js(__('portal.projects_client.show.reject_milestone'));
        alert.className = 'alert alert-danger';
        alertText.textContent = @js(__('portal.projects_client.show.explain_rejection'));
        noteLabel.innerHTML = @js(__('portal.projects_client.show.rejection_reason')) + ' <span class="text-danger">*</span>';
        noteField.placeholder = @js(__('portal.projects_client.show.explain_what_to_fix'));
        noteField.required = true;
        submitBtn.className = 'btn btn-danger';
        submitText.textContent = @js(__('portal.projects_client.show.reject'));
    }
    
    noteField.value = '';
    new bootstrap.Modal(document.getElementById('approveMilestoneModal')).show();
}

function showComplaintModal() {
    new bootstrap.Modal(document.getElementById('complaintModal')).show();
}

function showUploadFileModal() {
    new bootstrap.Modal(document.getElementById('uploadFileModal')).show();
}

function showRequestModal() {
    new bootstrap.Modal(document.getElementById('requestModal')).show();
}
</script>
@endpush

