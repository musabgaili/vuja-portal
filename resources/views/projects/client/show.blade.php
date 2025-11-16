@extends('layouts.dashboard')
@section('title', $project->title)
@section('content')

<style>
/* Modern Client Project View */
.client-project-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 2.5rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(79, 172, 254, 0.3);
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
    border-bottom: 3px solid #4facfe;
}
.milestone-card-client {
    background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
    border-left: 5px solid #4facfe;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    transition: all 0.3s;
}
.milestone-card-client:hover {
    box-shadow: 0 4px 16px rgba(79, 172, 254, 0.2);
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
    border-color: #4facfe;
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.15);
}
.team-avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
}
.comment-modern {
    background: #f8fafc;
    border-left: 4px solid #4facfe;
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
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
}
.action-btn-client:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
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
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
    background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
    transition: width 0.5s ease;
    box-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
}
</style>

<!-- Project Header -->
<div class="client-project-header">
    <div class="row align-items-center">
        <div class="col-md-9">
            <h1>{{ $project->title }}</h1>
            <p style="opacity: 0.95; font-size: 1.1rem; margin-bottom: 1.5rem;">{{ $project->description }}</p>
            <div class="d-flex gap-2">
                <button class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid white;" onclick="showRequestModal()">
                    <i class="fas fa-hand-paper"></i> Submit Request
                </button>
                <button class="btn btn-sm" style="background: rgba(255,100,100,0.3); color: white; border: 1px solid white;" onclick="showComplaintModal()">
                    <i class="fas fa-exclamation-triangle"></i> Submit Complaint
                </button>
            </div>
            
            <div class="d-flex gap-3 align-items-center flex-wrap">
                <span class="badge" style="background: rgba(255,255,255,0.3); font-size: 1rem; padding: 0.5rem 1rem;">
                    <i class="fas fa-circle" style="color: {{ $project->isActive() ? '#10b981' : '#f59e0b' }};"></i>
                    {{ $project->getStatusLabel() }}
                </span>
                @if($project->start_date)
                <span style="opacity: 0.9;">
                    <i class="fas fa-calendar"></i> 
                    {{ $project->start_date->format('M d, Y') }}
                    @if($project->end_date)
                    → {{ $project->end_date->format('M d, Y') }}
                    @endif
                </span>
                @endif
            </div>
        </div>
        <div class="col-md-3 text-center">
            <div class="progress-circle mx-auto">
                <div class="progress-number">{{ $project->completion_percentage }}%</div>
                <small style="opacity: 0.8;">Complete</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mb-4" style="display: flex; gap: 1rem; flex-wrap: wrap;">
    @if($project->isActive())
    <a href="{{ route('projects.client.scope-change.create', $project) }}" class="action-btn-client">
        <i class="fas fa-edit"></i> Request Change
    </a>
    @endif
    @if($project->isCompleted() && !$project->feedback)
    <a href="{{ route('projects.client.feedback.create', $project) }}" class="action-btn-client" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
        <i class="fas fa-star"></i> Rate Project
    </a>
    @endif
</div>

<!-- Stats Grid -->
<div class="stats-modern">
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->milestones->count() }}</div>
        <div class="stat-modern-label">Milestones</div>
    </div>
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->tasks->count() }}</div>
        <div class="stat-modern-label">Total Tasks</div>
    </div>
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->tasks->where('status', 'completed')->count() }}</div>
        <div class="stat-modern-label">Completed</div>
    </div>
    <div class="stat-modern">
        <div class="stat-modern-value">{{ $project->getTeamMembers()->count() }}</div>
        <div class="stat-modern-label">Team Members</div>
    </div>
</div>

<!-- Project Scope -->
@if($project->scope)
<div class="section-modern">
    <h3><i class="fas fa-bullseye"></i> Project Scope</h3>
    <p style="white-space: pre-line; color: #4b5563; line-height: 1.8;">{{ $project->scope }}</p>
</div>
@endif

<!-- Milestones & Timeline -->
<div class="section-modern">
    <h3><i class="fas fa-flag"></i> Milestones & Timeline</h3>
    @forelse($project->milestones as $milestone)
    <div class="milestone-card-client">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div style="flex: 1;">
                <h5 style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">
                    {{ $milestone->title }}
                    @if($milestone->client_approved)
                    <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Approved</span>
                    @endif
                </h5>
                @if($milestone->description)
                <p style="color: #64748b; margin-bottom: 1rem;">{{ $milestone->description }}</p>
                @endif
            </div>
            <div class="d-flex gap-2 align-items-start flex-wrap">
                <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">
                    {{ ucfirst($milestone->status) }}
                </span>
                
                @php
                    $totalTasks = $milestone->tasks->count();
                    $completedOrReviewTasks = $milestone->tasks->whereIn('status', ['completed', 'review'])->count();
                    $canReview = $totalTasks === 0 || $completedOrReviewTasks === $totalTasks;
                @endphp
                
                @if($milestone->client_approved === true)
                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Approved</span>
                @elseif($milestone->client_approved === false)
                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                @elseif($canReview)
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success" onclick="approveMilestone({{ $milestone->id }}, 'approve')">
                            <i class="fas fa-thumbs-up"></i> Approve
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="approveMilestone({{ $milestone->id }}, 'reject')">
                            <i class="fas fa-thumbs-down"></i> Reject
                        </button>
                    </div>
                @else
                    <button class="btn btn-sm btn-warning" disabled title="Waiting for tasks">
                        <i class="fas fa-clock"></i> {{ $completedOrReviewTasks }}/{{ $totalTasks }} ready
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
                <i class="fas fa-tasks"></i> {{ $completedTasks }}/{{ $totalTasks }} tasks completed
            </small>
            @if($milestone->due_date)
            <small style="color: #64748b;">
                <i class="fas fa-calendar"></i> Due: {{ $milestone->due_date->format('M d, Y') }}
            </small>
            @endif
        </div>
        
        <div class="progress-modern">
            <div class="progress-modern-fill" style="width:{{ $taskProgress }}%;"></div>
        </div>
        <small style="color: #64748b;">{{ $taskProgress }}% complete</small>
    </div>
    @empty
    <p class="text-muted text-center py-4">No milestones have been set yet.</p>
    @endforelse
</div>

<!-- Deliverables & Comments Row -->
<div class="row">
    <div class="col-lg-6">
        <div class="section-modern">
            <h3><i class="fas fa-box-open"></i> Project Deliverables</h3>
            @forelse($project->deliverables as $deliverable)
            <div class="doc-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <h5 class="mb-1">
                            <i class="fas fa-file-archive text-success"></i> {{ $deliverable->title }}
                            @if($deliverable->client_confirmed)
                            <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Confirmed</span>
                            @endif
                        </h5>
                        @if($deliverable->description)
                        <p class="text-muted small">{{ $deliverable->description }}</p>
                        @endif
                        <small class="text-muted">
                            Uploaded by {{ $deliverable->uploadedBy->name }} • {{ $deliverable->created_at->format('M d, Y') }}
                        </small>
                    </div>
                    <div class="d-flex gap-2 flex-column">
                        <a href="{{ route('projects.client.deliverables.download', $deliverable) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                        @if(!$deliverable->client_confirmed)
                        <form method="POST" action="{{ route('projects.client.deliverables.confirm', $deliverable) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Confirm receipt of this deliverable?')">
                                <i class="fas fa-check"></i> Confirm
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center text-muted py-4">No deliverables uploaded yet.</p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-6">
        <div class="section-modern">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="fas fa-comments"></i> Comments & Files</h3>
                <button class="btn btn-sm btn-primary" onclick="showUploadFileModal()">
                    <i class="fas fa-upload"></i> Upload File
                </button>
            </div>
            
            <!-- Add Comment Form -->
            <div class="mb-4" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px;">
                <form method="POST" action="{{ route('projects.client.add-comment', $project) }}">
                    @csrf
                    <input type="hidden" name="commentable_type" value="App\Models\Project">
                    <input type="hidden" name="commentable_id" value="{{ $project->id }}">
                    <div class="form-group">
                        <label style="font-weight: 600; color: #1e293b;">Add a Comment</label>
                        <textarea name="comment" class="form-control" rows="3" 
                            placeholder="Share updates, ask questions, or provide feedback..." 
                            required 
                            style="border-radius: 12px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                    <button type="submit" class="action-btn-client">
                        <i class="fas fa-paper-plane"></i> Post Comment
                    </button>
                </form>
            </div>

            <!-- Comments List -->
            <div style="max-height: 500px; overflow-y: auto;">
                @forelse($project->comments->sortByDesc('created_at') as $comment)
                <div class="comment-modern {{ $comment->is_internal ? 'internal' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong style="color: #1e293b; font-size: 1.05rem;">{{ $comment->user->name }}</strong>
                            @if($comment->is_internal)
                            <span class="badge bg-warning" style="font-size: 0.75rem;">
                                <i class="fas fa-shield-alt"></i> VujaDe Team
                            </span>
                            @else
                            <span class="badge bg-info" style="font-size: 0.75rem;">
                                <i class="fas fa-user"></i> Client
                            </span>
                            @endif
                            @if($comment->user_id === auth()->id())
                            <span class="badge bg-success" style="font-size: 0.75rem;">You</span>
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
                    <p class="text-muted">No comments yet. Start the conversation!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Project Documents -->
<div class="section-modern">
    <h3><i class="fas fa-folder-open"></i> Project Documents</h3>
    @forelse($project->documents as $doc)
    <div class="doc-card">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex: 1;">
                <h5 class="mb-1">
                    <i class="fas fa-file-{{ $doc->file_type === 'pdf' ? 'pdf text-danger' : 'alt text-primary' }}"></i> {{ $doc->title }}
                </h5>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="badge" style="background: {{ match($doc->tag) { 'initial' => '#8b5cf6', 'design' => '#3b82f6', 'development' => '#10b981', 'final' => '#f59e0b', default => '#6b7280' } }};">
                        {{ ucfirst($doc->tag) }}
                    </span>
                    <small class="text-muted">
                        <i class="fas fa-user"></i> {{ $doc->uploadedBy->name }}
                    </small>
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> {{ $doc->created_at->format('M d, Y') }}
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
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
    @empty
    <p class="text-center text-muted py-4">No documents uploaded yet.</p>
    @endforelse
</div>

<!-- Project Team -->
<div class="section-modern">
    <h3><i class="fas fa-users"></i> Project Team</h3>
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
                        <span class="badge bg-success">You</span>
                        @endif
                    </div>
                    <div class="mt-1">
                        @if($person->role === 'project_manager')
                        <span class="badge bg-success" style="font-size: 0.85rem;">
                            <i class="fas fa-star"></i> Project Manager
                        </span>
                        @elseif($person->role === 'client')
                        <span class="badge bg-info" style="font-size: 0.85rem;">
                            <i class="fas fa-user"></i> Client
                        </span>
                        @else
                        <span class="badge bg-secondary" style="font-size: 0.85rem;">
                            <i class="fas fa-user-tie"></i> Team Member
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
    <h3><i class="fas fa-exchange-alt"></i> My Scope Change Requests</h3>
    @foreach($project->scopeChanges->where('requested_by', auth()->id())->sortByDesc('created_at') as $change)
    <div class="milestone-card-client" style="border-left-color: {{ $change->getStatusBadgeColor() === 'success' ? '#10b981' : ($change->getStatusBadgeColor() === 'danger' ? '#ef4444' : '#f59e0b') }};">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex: 1;">
                <h5 style="font-weight: 600; color: #1e293b;">{{ $change->title }}</h5>
                <p style="color: #64748b; margin-bottom: 0.5rem;">{{ $change->description }}</p>
                <small style="color: #94a3b8;">Submitted: {{ $change->created_at->format('M d, Y') }}</small>
            </div>
            <span class="status-badge {{ $change->getStatusBadgeColor() }}">
                {{ ucfirst($change->status) }}
            </span>
        </div>
        @if($change->review_notes)
        <div class="mt-2 p-2" style="background: rgba(0,0,0,0.05); border-radius: 8px;">
            <small><strong>Review:</strong> {{ $change->review_notes }}</small>
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
                <h5 id="approveModalTitle"><i class="fas fa-check-circle"></i> Review Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="approveMilestoneForm">
                @csrf
                <input type="hidden" name="action" id="approvalAction" value="approve">
                <div class="modal-body">
                    <div class="alert alert-info" id="approveAlert">
                        <i class="fas fa-info-circle"></i> <span id="approveAlertText">Confirm that deliverables meet your expectations.</span>
                    </div>
                    <div class="form-group">
                        <label id="noteLabel">Note (Optional)</label>
                        <textarea name="approval_note" id="approvalNote" class="form-control" rows="3" placeholder="Any feedback or comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn" id="approveSubmitBtn">
                        <i class="fas fa-check"></i> <span id="approveSubmitText">Approve</span>
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
                <h5><i class="fas fa-exclamation-triangle"></i> Submit Complaint</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.client.complaints.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> This will alert the Super Manager, Account Manager, and Project Manager.
                    </div>
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Complaint Details *</label>
                        <textarea name="complaint" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-paper-plane"></i> Submit Complaint
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
                <h5><i class="fas fa-upload"></i> Upload File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.client.documents.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="fw-bold">Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter file title..." required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">File *</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Tag *</label>
                        <select name="tag" class="form-control" required>
                            <option value="">Select a tag...</option>
                            <option value="initial">Initial Draft</option>
                            <option value="design">Design File</option>
                            <option value="development">Development</option>
                            <option value="final">Final Version</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">Comment (Optional)</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Add a note about this file..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload File
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
            <div class="modal-header bg-info text-white">
                <h5><i class="fas fa-hand-paper"></i> Submit Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.client.requests.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This will be sent to the Account Manager and Project Manager.
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold">Subject *</label>
                        <input type="text" name="subject" class="form-control" required placeholder="e.g., Please edit the design">
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">Request Details *</label>
                        <textarea name="request" class="form-control" rows="4" required placeholder="Describe your request in detail..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane"></i> Submit Request
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
        title.innerHTML = '<i class="fas fa-thumbs-up"></i> Approve Milestone';
        alert.className = 'alert alert-success';
        alertText.textContent = 'Confirm that deliverables meet your expectations.';
        noteLabel.textContent = 'Note (Optional)';
        noteField.placeholder = 'Any positive feedback...';
        noteField.required = false;
        submitBtn.className = 'btn btn-success';
        submitText.textContent = 'Approve';
    } else {
        header.className = 'modal-header bg-danger text-white';
        title.innerHTML = '<i class="fas fa-thumbs-down"></i> Reject Milestone';
        alert.className = 'alert alert-danger';
        alertText.textContent = 'Please explain why you are rejecting this milestone.';
        noteLabel.innerHTML = 'Rejection Reason <span class="text-danger">*</span>';
        noteField.placeholder = 'Explain what needs to be fixed or changed...';
        noteField.required = true;
        submitBtn.className = 'btn btn-danger';
        submitText.textContent = 'Reject';
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

