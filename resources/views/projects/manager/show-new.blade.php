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
                <div class="project-meta-item">
                    <i class="fas fa-user"></i>
                    <span>{{ $project->client->name }}</span>
                </div>
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
    <button class="btn btn-success" onclick="showMilestoneModal()">
        <i class="fas fa-flag"></i> Add Milestone
    </button>
    <button class="btn btn-info" onclick="showTaskModal()">
        <i class="fas fa-tasks"></i> Add Task
    </button>
    @endif
    <a href="{{ route('projects.expenses.index', $project) }}" class="btn btn-warning">
        <i class="fas fa-receipt"></i> Expenses
    </a>
    <button class="btn btn-secondary" onclick="showAddTeamModal()">
        <i class="fas fa-user-plus"></i> Add Member
    </button>
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
                        @if($milestone->due_date)
                        <small class="text-muted">• Due: {{ $milestone->due_date->format('M d, Y') }}</small>
                        @endif
                    </div>
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
                    @if($task->milestone)<span class="badge bg-secondary">{{ $milestone->title }}</span>@endif
                    @if($task->assignedTo)<span class="badge bg-info">{{ $task->assignedTo->name }}</span>@endif
                </div>
            </div>
            <button class="btn btn-sm btn-secondary" onclick="updateTaskStatus({{ $task->id }})">
                <i class="fas fa-edit"></i>
            </button>
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
            <button class="btn btn-sm btn-primary" onclick="showAddTeamModal()">
                <i class="fas fa-plus"></i> Add Member
            </button>
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
            @if($canEdit)
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
        <form method="POST" action="{{ route('projects.client.add-comment', $project) }}" class="mb-3">
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

        <!-- Comments List -->
        @forelse($project->comments->sortByDesc('created_at') as $comment)
        <div class="comment-box">
            <div class="comment-header">
                <span class="comment-author">{{ $comment->user->name }}</span>
                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p style="margin: 0;">{{ $comment->comment }}</p>
        </div>
        @empty
        <p class="text-muted text-center py-4">No comments yet. Be the first to comment!</p>
        @endforelse
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
    document.getElementById('edit_member_role').value = role;
    document.getElementById('edit_member_can_edit').checked = canEdit;
    document.getElementById('editTeamForm').action = `/internal/projects/team/${id}`;
    new bootstrap.Modal(document.getElementById('editTeamModal')).show();
}
</script>
@endpush

