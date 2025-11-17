@extends('layouts.internal-dashboard')
@section('title', $project->title)
@section('content')

<style>
/* Professional Project View Styles */
.project-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}
.project-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}
.project-meta {
    display: flex;
    gap: 2rem;
    align-items: center;
    flex-wrap: wrap;
}
.project-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.project-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--gray-200);
}
.project-tab {
    padding: 1rem 1.5rem;
    cursor: pointer;
    border: none;
    background: none;
    color: var(--gray-600);
    font-weight: 500;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
}
.project-tab.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
.section-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s;
}
.section-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-100);
}
.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
}
.milestone-item {
    background: var(--gray-50);
    border-left: 4px solid var(--primary-color);
    padding: 1.25rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: all 0.3s;
}
.milestone-item:hover {
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.task-item {
    background: white;
    border: 1px solid var(--gray-200);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}
.task-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}
.comment-box {
    background: var(--gray-50);
    border-left: 3px solid var(--info-color);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.comment-header {
    display: flex;
    justify-content: between;
    margin-bottom: 0.5rem;
}
.comment-author {
    font-weight: 600;
    color: var(--primary-color);
}
.comment-time {
    font-size: 0.875rem;
    color: var(--gray-500);
}
.team-member {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--gray-50);
    border-radius: 8px;
    margin-bottom: 0.5rem;
}
.team-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}
.progress-bar-modern {
    height: 8px;
    background: var(--gray-200);
    border-radius: 10px;
    overflow: hidden;
    margin: 0.5rem 0;
}
.progress-fill-modern {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.5s ease;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.stat-box {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    text-align: center;
}
.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}
.stat-label {
    color: var(--gray-600);
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
</style>

<!-- Project Header -->
<div class="project-header">
    <div class="d-flex justify-content-between align-items-start">
        <div style="flex: 1;">
            <h1>{{ $project->title }}</h1>
            <p style="opacity: 0.9; margin-bottom: 1rem;">{{ $project->description }}</p>
            <div class="project-meta">
                @if($project->client)
                <div class="project-meta-item">
                    <i class="fas fa-user"></i>
                    <span>{{ $project->client->name }}</span>
                </div>
                @else
                <div class="project-meta-item">
                    <i class="fas fa-user"></i>
                    <span class="text-muted">No Client</span>
                </div>
                @endif
                @if($project->projectManager)
                <div class="project-meta-item">
                    <i class="fas fa-user-tie"></i>
                    <span>PM: {{ $project->projectManager->name }}</span>
                </div>
                @endif
                <div class="project-meta-item">
                    <i class="fas fa-users"></i>
                    <span>{{ $project->getTeamMembers()->count() }} Members</span>
                </div>
                @if($project->budget)
                <div class="project-meta-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>${{ number_format($project->budget, 0) }}</span>
                </div>
                @endif
            </div>
        </div>
        <div>
            <span class="status-badge {{ $project->getStatusBadgeColor() }}" style="font-size: 1rem; padding: 0.5rem 1rem;">
                {{ $project->getStatusLabel() }}
            </span>
        </div>
    </div>
    
    <div class="progress-bar-modern mt-3">
        <div class="progress-fill-modern" style="width:{{ $project->completion_percentage }}%;"></div>
    </div>
    <small style="opacity: 0.8;">{{ $project->completion_percentage }}% Complete</small>
</div>

<!-- Action Buttons -->
<div class="mb-3" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
    @if($canEdit)
    <button class="btn btn-primary" onclick="showEditModal()">
        <i class="fas fa-edit"></i> Edit Project
    </button>
    @endif
    
    @if($canManageMilestones)
    <button class="btn btn-success" onclick="showMilestoneModal()">
        <i class="fas fa-flag"></i> Add Milestone
    </button>
    @endif
    
    @if($canManageTasks)
    <button class="btn btn-info" onclick="showTaskModal()">
        <i class="fas fa-tasks"></i> Add Task
    </button>
    @endif
    
    @if($canManageExpenses)
    <a href="{{ route('projects.expenses.index', $project) }}" class="btn btn-warning">
        <i class="fas fa-receipt"></i> Expenses
    </a>
    @endif
    
    @if($canManageTeam)
    <button class="btn btn-secondary" onclick="showAddTeamModal()">
        <i class="fas fa-user-plus"></i> Add Member
    </button>
    @endif
</div>

<!-- Tabs -->
<div class="project-tabs">
    <button class="project-tab active" onclick="switchTab('overview')">
        <i class="fas fa-th-large"></i> Overview
    </button>
    <button class="project-tab" onclick="switchTab('milestones')">
        <i class="fas fa-flag"></i> Milestones
    </button>
    <button class="project-tab" onclick="switchTab('tasks')">
        <i class="fas fa-tasks"></i> Tasks ({{ $project->tasks->count() }})
    </button>
    <button class="project-tab" onclick="switchTab('team')">
        <i class="fas fa-users"></i> Team
    </button>
    <button class="project-tab" onclick="switchTab('comments')">
        <i class="fas fa-comments"></i> Comments
    </button>
    <button class="project-tab" onclick="switchTab('documents')">
        <i class="fas fa-folder-open"></i> Documents
    </button>
    <button class="project-tab" onclick="switchTab('deliverables')">
        <i class="fas fa-box-open"></i> Deliverables
    </button>
    <button class="project-tab" onclick="switchTab('requests')">
        <i class="fas fa-hand-paper"></i> Requests
        @if($project->requests->where('status', 'open')->count() > 0)
        <span class="badge bg-warning">{{ $project->requests->where('status', 'open')->count() }}</span>
        @endif
    </button>
    <button class="project-tab" onclick="switchTab('complaints')">
        <i class="fas fa-exclamation-triangle"></i> Complaints
        @if($project->complaints->where('status', 'open')->count() > 0)
        <span class="badge bg-danger">{{ $project->complaints->where('status', 'open')->count() }}</span>
        @endif
    </button>
    <button class="project-tab" onclick="switchTab('feedback')">
        <i class="fas fa-star"></i> Feedback
        @if($project->feedback)
        <span class="badge bg-success ms-1">{{ $project->feedback->rating }}/5</span>
        @endif
    </button>
    <button class="project-tab" onclick="switchTab('activity')">
        <i class="fas fa-history"></i> Activity
    </button>
</div>

<!-- Overview Tab -->
<div id="overview-tab" class="tab-content active">
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ $project->milestones->count() }}</div>
            <div class="stat-label">Milestones</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $project->tasks->count() }}</div>
            <div class="stat-label">Total Tasks</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $project->tasks->where('status', 'completed')->count() }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $project->completion_percentage }}%</div>
            <div class="stat-label">Progress</div>
        </div>
    </div>

    @if($project->scope)
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Project Scope</div>
        </div>
        <p style="white-space: pre-line;">{{ $project->scope }}</p>
    </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">Recent Tasks</div>
                </div>
                @forelse($project->tasks->sortByDesc('created_at')->take(5) as $task)
                <div class="task-item">
                    <div>
                        <strong>{{ $task->title }}</strong>
                        <br>
                        <span class="status-badge {{ $task->getStatusBadgeColor() }}">{{ ucfirst($task->status) }}</span>
                    </div>
                    <button class="btn btn-sm btn-secondary" onclick="updateTaskStatus({{ $task->id }})">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
                @empty
                <p class="text-muted">No tasks yet.</p>
                @endforelse
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">Upcoming Milestones</div>
                </div>
                @forelse($project->milestones->where('status', '!=', 'completed')->take(3) as $milestone)
                <div class="milestone-item">
                    <strong>{{ $milestone->title }}</strong>
                    <br>
                    <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">{{ ucfirst($milestone->status) }}</span>
                    @if($milestone->due_date)
                    <small class="text-muted">• Due: {{ $milestone->due_date->format('M d') }}</small>
                    @endif
                </div>
                @empty
                <p class="text-muted">No upcoming milestones.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Milestones Tab -->
<div id="milestones-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">All Milestones</div>
        </div>
        @forelse($project->milestones as $milestone)
        <div class="milestone-item">
            <div class="d-flex justify-content-between align-items-start">
                <div style="flex: 1;">
                    <h5>{{ $milestone->title }}</h5>
                    @if($milestone->description)
                    <p class="text-muted mb-2">{{ $milestone->description }}</p>
                    @endif
                    <div class="mb-2">
                        <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">{{ ucfirst($milestone->status) }}</span>
                        @if($milestone->client_approved === true)
                        <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Client Approved</span>
                        @elseif($milestone->client_approved === false)
                        <span class="badge bg-danger ms-2"><i class="fas fa-times-circle"></i> Client Rejected</span>
                        @endif
                        @if($milestone->due_date)
                        <small class="text-muted">• Due: {{ $milestone->due_date->format('M d, Y') }}</small>
                        @endif
                    </div>
                    @if($milestone->client_approved === false && $milestone->approval_note)
                    <div class="alert alert-danger mt-2">
                        <strong><i class="fas fa-exclamation-triangle"></i> Rejection Reason:</strong>
                        <p class="mb-0 mt-1">"{{ $milestone->approval_note }}"</p>
                    </div>
                    @elseif($milestone->client_approved === true && $milestone->approval_note)
                    <div class="alert alert-success mt-2">
                        <strong><i class="fas fa-comment"></i> Client Note:</strong>
                        <p class="mb-0 mt-1">"{{ $milestone->approval_note }}"</p>
                    </div>
                    @endif
                    @php
                        $milestoneTasks = $milestone->tasks;
                        $totalTasks = $milestoneTasks->count();
                        $completedTasks = $milestoneTasks->where('status', 'completed')->count();
                        $taskProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    @endphp
                    <small class="text-muted">Tasks: {{ $completedTasks }}/{{ $totalTasks }} ({{ $taskProgress }}%)</small>
                    <div class="progress-bar-modern">
                        <div class="progress-fill-modern" style="width:{{ $taskProgress }}%;"></div>
                    </div>
                    @if($milestone->status !== 'completed' && $canManageMilestones)
                    <div class="mt-3">
                        <button class="btn btn-sm btn-success" onclick="markMilestoneCompleted({{ $milestone->id }})">
                            <i class="fas fa-check-circle"></i> Mark as Completed
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-4">No milestones yet. <a href="#" onclick="showMilestoneModal(); return false;">Create first milestone</a></p>
        @endforelse
    </div>
</div>

<!-- Tasks Tab -->
<div id="tasks-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">All Tasks ({{ $project->tasks->count() }})</div>
        </div>
        @forelse($project->tasks->sortByDesc('created_at') as $task)
        <div class="task-item">
            <div style="flex: 1;">
                <strong>{{ $task->title }}</strong>
                <div class="mt-1">
                    <span class="status-badge {{ $task->getStatusBadgeColor() }}">{{ ucfirst($task->status) }}</span>
                    @if($task->priority === 'urgent')<span class="badge bg-danger">URGENT</span>@endif
                    @if($task->priority === 'high')<span class="badge bg-warning">High</span>@endif
                    @if($task->milestone)<span class="badge bg-secondary">{{ $task->milestone->title }}</span>@endif
                    @if($task->assignedTo)<span class="badge bg-info">{{ $task->assignedTo->name }}</span>@endif
                </div>
            </div>
            @if($canManageTasks || $task->assigned_to === auth()->id())
            <button class="btn btn-sm btn-secondary" onclick="updateTaskStatus({{ $task->id }})">
                <i class="fas fa-edit"></i> {{ $canManageTasks ? 'Edit' : 'Update Status' }}
            </button>
            @endif
        </div>
        @empty
        <p class="text-muted text-center py-4">No tasks yet.</p>
        @endforelse
    </div>
</div>

<!-- Team Tab -->
<div id="team-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Project Team</div>
            @if($canManageTeam)
            <button class="btn btn-sm btn-primary" onclick="showAddTeamModal()">
                <i class="fas fa-plus"></i> Add Member
            </button>
            @endif
        </div>
        @foreach($project->projectPeople as $person)
        <div class="team-member">
            <div class="team-avatar">
                {{ strtoupper(substr($person->user->name, 0, 1)) }}
            </div>
            <div style="flex: 1;">
                <strong>{{ $person->user->name }}</strong>
                <br>
                <span class="badge bg-{{ $person->role === 'project_manager' ? 'success' : ($person->role === 'client' ? 'info' : 'secondary') }}">
                    {{ ucfirst(str_replace('_', ' ', $person->role)) }}
                </span>
                @if($person->can_edit)<span class="badge bg-warning">Can Edit</span>@endif
            </div>
            @if($canManageTeam)
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-sm btn-secondary" onclick="editTeamMember({{ $person->id }}, '{{ $person->role }}', {{ $person->can_edit ? 'true' : 'false' }})">
                    <i class="fas fa-edit"></i>
                </button>
                <form method="POST" action="{{ route('projects.team.remove', $person) }}" style="display:inline;" onsubmit="return confirm('Remove {{ $person->user->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<!-- Comments Tab -->
<div id="comments-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Project Comments</div>
        </div>
        
        <!-- Add Comment Form -->
        @if($canAddComments)
        <form method="POST" action="{{ route('projects.add-comment', $project) }}" class="mb-3">
            @csrf
            <input type="hidden" name="commentable_type" value="App\Models\Project">
            <input type="hidden" name="commentable_id" value="{{ $project->id }}">
            <div class="form-group">
                <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Post Comment
            </button>
        </form>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You can only view comments. Contact your project manager to add comments.
        </div>
        @endif

        <!-- Comments List -->
        @forelse($project->comments->sortByDesc('created_at') as $comment)
        <div class="comment-box" style="border-left-color: {{ $comment->is_internal ? 'var(--warning-color)' : 'var(--info-color)' }};">
            <div class="comment-header">
                <span class="comment-author">
                    {{ $comment->user->name }}
                    @if($comment->is_internal)
                    <span class="badge bg-warning" style="font-size: 0.7rem;">Team</span>
                    @else
                    <span class="badge bg-info" style="font-size: 0.7rem;">Client</span>
                    @endif
                </span>
                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p style="margin: 0;">{{ $comment->comment }}</p>
        </div>
        @empty
        <p class="text-muted text-center py-4">No comments yet. Be the first to comment!</p>
        @endforelse
    </div>
</div>

<!-- Documents Tab -->
<div id="documents-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Project Documents</div>
        </div>
        <iframe src="{{ route('projects.client.documents.index', $project) }}" style="width: 100%; height: 600px; border: none; border-radius: 8px;"></iframe>
    </div>
</div>

<!-- Deliverables Tab -->
<div id="deliverables-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Project Deliverables</div>
            @if($canManageTasks)
            <button class="btn btn-sm btn-success" onclick="showDeliverableModal()">
                <i class="fas fa-upload"></i> Upload Deliverable
            </button>
            @endif
        </div>
        @forelse($project->deliverables as $deliverable)
        <div class="task-item">
            <div style="flex: 1;">
                <strong>{{ $deliverable->title }}</strong>
                @if($deliverable->client_confirmed)
                <span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Confirmed</span>
                @endif
                <br>
                <small class="text-muted">{{ $deliverable->description }}</small>
                <br>
                <small class="text-muted">
                    Uploaded by {{ $deliverable->uploadedBy->name }} • {{ $deliverable->created_at->format('M d, Y') }}
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('projects.client.deliverables.download', $deliverable) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-download"></i>
                </a>
                @if(auth()->user()->isManager())
                <form method="POST" action="{{ route('projects.deliverables.destroy', $deliverable) }}" style="display: inline;" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-4">No deliverables uploaded yet.</p>
        @endforelse
    </div>
</div>

<!-- Requests Tab -->
<div id="requests-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Client Requests</div>
        </div>
        @forelse($project->requests->sortByDesc('created_at') as $req)
        <div class="milestone-item" style="border-left-color: {{ $req->status === 'open' ? '#f59e0b' : '#10b981' }};">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div style="flex:1;">
                    <h5 style="font-weight:600;color:#1e293b;">{{ $req->subject }}</h5>
                    <p style="color:#64748b;margin:0.5rem 0;">{{ $req->request }}</p>
                    <small class="text-muted">By {{ $req->client->name }} • {{ $req->created_at->format('M d, Y g:i A') }}</small>
                </div>
                <span class="status-badge {{ $req->status === 'open' ? 'warning' : 'success' }}">{{ ucfirst($req->status) }}</span>
            </div>
            @if($req->response)
            <div class="mt-3 p-3" style="background:#f0fdf4;border-left:4px solid #10b981;border-radius:8px;">
                <strong style="color:#1C575F;">Response:</strong>
                <p style="margin:0.5rem 0 0 0;color:#065f46;">{{ $req->response }}</p>
                <small class="text-muted">By {{ $req->handledBy->name }} • {{ $req->handled_at->format('M d, Y') }}</small>
            </div>
            @else
            <div class="mt-3">
                <button class="btn btn-sm btn-primary" onclick="respondToRequest({{ $req->id }})">
                    <i class="fas fa-reply"></i> Respond
                </button>
            </div>
            @endif
        </div>
        @empty
        <p class="text-muted text-center py-4">No client requests yet.</p>
        @endforelse
    </div>
</div>

<!-- Complaints Tab -->
<div id="complaints-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Client Complaints</div>
        </div>
        @forelse($project->complaints->sortByDesc('created_at') as $complaint)
        <div class="milestone-item" style="border-left-color: {{ $complaint->status === 'open' ? '#ef4444' : '#10b981' }};">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div style="flex:1;">
                    <h5 style="font-weight:600;color:#1e293b;">{{ $complaint->subject }}</h5>
                    <p style="color:#64748b;margin:0.5rem 0;">{{ $complaint->complaint }}</p>
                    <small class="text-muted">By {{ $complaint->client->name }} • {{ $complaint->created_at->format('M d, Y g:i A') }}</small>
                </div>
                <span class="status-badge {{ $complaint->status === 'open' ? 'error' : 'success' }}">{{ ucfirst($complaint->status) }}</span>
            </div>
            @if($complaint->resolution_note)
            <div class="mt-3 p-3" style="background:#f0fdf4;border-left:4px solid #10b981;border-radius:8px;">
                <strong style="color:#1C575F;">Resolution:</strong>
                <p style="margin:0.5rem 0 0 0;color:#065f46;">{{ $complaint->resolution_note }}</p>
                <small class="text-muted">By {{ $complaint->resolvedBy->name }} • {{ $complaint->resolved_at->format('M d, Y') }}</small>
            </div>
            @elseif($user->isManager())
            <div class="mt-3">
                <button class="btn btn-sm btn-danger" onclick="resolveComplaint({{ $complaint->id }})">
                    <i class="fas fa-check"></i> Resolve Complaint
                </button>
            </div>
            @endif
        </div>
        @empty
        <p class="text-muted text-center py-4">No complaints yet.</p>
        @endforelse
    </div>
</div>

<!-- Feedback Tab -->
<div id="feedback-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Client Feedback</div>
        </div>
        @if($project->feedback)
        <div class="milestone-item" style="border-left-color: #f59e0b;">
            <div class="mb-3">
                <h5 class="mb-3">
                    <i class="fas fa-star text-warning"></i> Overall Rating: 
                    <span class="badge bg-warning" style="font-size: 1.2rem;">{{ $project->feedback->rating }}/5</span>
                </h5>
                <div class="mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= $project->feedback->rating ? 'text-warning' : 'text-muted' }}"></i>
                    @endfor
                </div>
            </div>
            
            @if($project->feedback->feedback)
            <div class="alert alert-info">
                <strong><i class="fas fa-comment"></i> Client Comment:</strong>
                <p class="mb-0 mt-2">{{ $project->feedback->feedback }}</p>
            </div>
            @endif
            
            <div class="row mt-3">
                @if($project->feedback->communication_rating)
                <div class="col-md-4 mb-3">
                    <strong><i class="fas fa-comments"></i> Communication:</strong>
                    <div>
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $project->feedback->communication_rating ? 'text-primary' : 'text-muted' }}"></i>
                        @endfor
                        <span class="ms-2">{{ $project->feedback->communication_rating }}/5</span>
                    </div>
                </div>
                @endif
                
                @if($project->feedback->quality_rating)
                <div class="col-md-4 mb-3">
                    <strong><i class="fas fa-gem"></i> Quality:</strong>
                    <div>
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $project->feedback->quality_rating ? 'text-success' : 'text-muted' }}"></i>
                        @endfor
                        <span class="ms-2">{{ $project->feedback->quality_rating }}/5</span>
                    </div>
                </div>
                @endif
                
                @if($project->feedback->timeline_rating)
                <div class="col-md-4 mb-3">
                    <strong><i class="fas fa-clock"></i> Timeline:</strong>
                    <div>
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $project->feedback->timeline_rating ? 'text-info' : 'text-muted' }}"></i>
                        @endfor
                        <span class="ms-2">{{ $project->feedback->timeline_rating }}/5</span>
                    </div>
                </div>
                @endif
            </div>
            
            @if($project->feedback->would_recommend)
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle"></i> Client would recommend VujaDe to others
            </div>
            @endif
            
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted">
                    <i class="fas fa-calendar"></i> Submitted: {{ $project->feedback->created_at->format('F j, Y \a\t g:i A') }}
                </small>
            </div>
        </div>
        @else
        <div class="alert alert-light text-center py-4">
            <i class="fas fa-star fa-3x text-muted mb-3"></i>
            <p class="mb-0 text-muted">No feedback submitted yet</p>
        </div>
        @endif
    </div>
</div>

<!-- Activity Tab -->
<div id="activity-tab" class="tab-content">
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">Activity Log</div>
        </div>
        @forelse($activities as $activity)
        <div class="comment-box">
            <div class="comment-header">
                <span class="comment-author">
                    {{ $activity->causer ? $activity->causer->name : 'System' }}
                </span>
                <span class="comment-time">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
            <strong>{{ $activity->description }}</strong>
            @if($activity->properties && $activity->properties->has('attributes'))
            <div class="mt-1">
                @foreach($activity->properties->get('attributes') as $key => $value)
                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? json_encode($value) : $value }}</span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <p class="text-muted text-center py-4">No activity yet.</p>
        @endforelse
        <div class="mt-3">{{ $activities->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

@include('projects.manager.show-modals')

@endsection
@push('scripts')
<script>
function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.project-tab').forEach(t => t.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tab + '-tab').classList.add('active');
    event.target.classList.add('active');
}

function showMilestoneModal(){new bootstrap.Modal(document.getElementById('milestoneModal')).show();}
function showTaskModal(){new bootstrap.Modal(document.getElementById('taskModal')).show();}
function showEditModal(){new bootstrap.Modal(document.getElementById('editModal')).show();}
function showAddTeamModal(){new bootstrap.Modal(document.getElementById('addTeamModal')).show();}

function markMilestoneCompleted(milestoneId) {
    if (!confirm('Mark this milestone as completed? This will notify the client.')) return;
    
    fetch(`/internal/projects/milestones/${milestoneId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error marking milestone as completed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error marking milestone as completed');
    });
}

function updateTaskStatus(id){
    fetch(`/internal/projects/tasks/${id}/data`)
        .then(r => r.json())
        .then(task => {
            document.getElementById('edit_task_title').value = task.title || '';
            document.getElementById('edit_task_description').value = task.description || '';
            document.getElementById('edit_task_status').value = task.status || 'todo';
            document.getElementById('edit_task_priority').value = task.priority || 'medium';
            document.getElementById('edit_task_milestone').value = task.milestone_id || '';
            document.getElementById('edit_task_assigned').value = task.assigned_to || '';
            document.getElementById('edit_task_due_date').value = task.due_date || '';
            document.getElementById('edit_task_hours').value = task.actual_hours || '';
            document.getElementById('taskStatusForm').action = `/internal/projects/tasks/${id}`;
            new bootstrap.Modal(document.getElementById('taskStatusModal')).show();
        });
}

function editTeamMember(id, role, canEdit){
    const roleSelect = document.getElementById('edit_member_role');
    const projectManagerOption = roleSelect.querySelector('option[value="project_manager"]');
    
    // Check if project already has a project manager (and it's not the current user)
    const hasProjectManager = {{ $project->projectPeople()->where('role', 'project_manager')->exists() ? 'true' : 'false' }};
    const isCurrentUserPM = role === 'project_manager';
    
    // Show/hide project manager option
    if (hasProjectManager && !isCurrentUserPM) {
        if (projectManagerOption) projectManagerOption.style.display = 'none';
    } else {
        if (projectManagerOption) projectManagerOption.style.display = 'block';
    }
    
    roleSelect.value = role;
    document.getElementById('edit_member_can_edit').checked = canEdit;
    document.getElementById('editTeamForm').action = `/internal/projects/team/${id}`;
    new bootstrap.Modal(document.getElementById('editTeamModal')).show();
}

function showDeliverableModal(){
    new bootstrap.Modal(document.getElementById('deliverableModal')).show();
}

function respondToRequest(requestId) {
    document.getElementById('respondRequestForm').action = `/internal/projects/requests/${requestId}/respond`;
    new bootstrap.Modal(document.getElementById('respondRequestModal')).show();
}

function resolveComplaint(complaintId) {
    document.getElementById('resolveComplaintForm').action = `/internal/projects/complaints/${complaintId}/resolve`;
    new bootstrap.Modal(document.getElementById('resolveComplaintModal')).show();
}
</script>
@endpush

