@extends('layouts.internal-dashboard')
@section('title', $project->title)
@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $project->title }}</h3>
                <div class="d-flex gap-2 flex-wrap">
                    @if($canEdit)
                    <button class="btn btn-primary btn-sm" onclick="showEditModal()"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-success btn-sm" onclick="showMilestoneModal()"><i class="fas fa-plus"></i> Milestone</button>
                    <button class="btn btn-info btn-sm" onclick="showTaskModal()"><i class="fas fa-tasks"></i> Task</button>
                    @endif
                    <a href="{{ route('projects.expenses.index', $project) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-receipt"></i> Expenses
                    </a>
                    @if($project->scopeChanges()->where('status', 'pending')->count() > 0)
                    <a href="{{ route('projects.scope-changes.index') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-exclamation-circle"></i> Scope Changes ({{ $project->scopeChanges()->where('status', 'pending')->count() }})
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-content">
                <p>{{ $project->description }}</p>
                <div class="progress-bar mt-3"><div class="progress-fill" style="width:{{ $project->completion_percentage }}%;"></div></div>
                <small>{{ $project->completion_percentage }}% complete</small>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h3>Milestones ({{ $project->milestones->count() }})</h3></div>
            <div class="card-content">
                @forelse($project->milestones as $milestone)
                <div class="milestone-card mb-3">
                    <div class="d-flex justify-between">
                        <div style="flex:1;">
                            <h5>{{ $milestone->title }}</h5>
                            @if($milestone->description)<p class="text-muted">{{ $milestone->description }}</p>@endif
                            <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">{{ ucfirst($milestone->status) }}</span>
                            @if($milestone->due_date)<small class="text-muted">• Due: {{ $milestone->due_date->format('M d, Y') }}</small>@endif
                            
                            @php
                                $milestoneTasks = $milestone->tasks;
                                $totalTasks = $milestoneTasks->count();
                                $completedTasks = $milestoneTasks->where('status', 'completed')->count();
                                $taskProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                            @endphp
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    Tasks: {{ $completedTasks }}/{{ $totalTasks }} completed ({{ $taskProgress }}%)
                                </small>
                                <div class="progress-bar mt-1">
                                    <div class="progress-fill" style="width:{{ $taskProgress }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($milestone->tasks->count() > 0)
                    <div class="tasks-list mt-3">
                        @foreach($milestone->tasks as $task)
                        <div class="task-row">
                            <span class="status-badge {{ $task->getStatusBadgeColor() }}">{{ ucfirst($task->status) }}</span>
                            <span>{{ $task->title }}</span>
                            @if($task->assignedTo)<span class="badge bg-info">{{ $task->assignedTo->name }}</span>@endif
                            <button class="btn btn-sm btn-secondary" onclick="updateTaskStatus({{ $task->id }})"><i class="fas fa-edit"></i></button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-muted">No milestones. <a href="#" onclick="showMilestoneModal();return false;">Add first milestone</a></p>
                @endforelse
            </div>
        </div>

        <!-- ALL TASKS -->
        <div class="card mb-4">
            <div class="card-header"><h3>All Tasks ({{ $project->tasks->count() }})</h3></div>
            <div class="card-content">
                @forelse($project->tasks->sortByDesc('created_at') as $task)
                <div class="task-row mb-2" style="border-left:3px solid var(--{{ $task->getStatusBadgeColor() }}-color);padding-left:10px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong>{{ $task->title }}</strong>
                            <span class="status-badge {{ $task->getStatusBadgeColor() }}">{{ ucfirst($task->status) }}</span>
                            @if($task->priority === 'urgent')<span class="badge bg-danger">URGENT</span>@endif
                            @if($task->priority === 'high')<span class="badge bg-warning">High</span>@endif
                            <br>
                            @if($task->milestone)<small class="text-muted">📍 {{ $task->milestone->title }}</small>@endif
                            @if($task->assignedTo)<br><small>👤 {{ $task->assignedTo->name }}</small>@endif
                            @if($task->due_date)<br><small>📅 Due: {{ $task->due_date->format('M d, Y') }}</small>@endif
                        </div>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="updateTaskStatus({{ $task->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No tasks yet. <a href="#" onclick="showTaskModal();return false;">Create first task</a></p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card mb-3">
            <div class="card-header"><h3>Details</h3></div>
            <div class="card-content">
                <div class="stat-row"><strong>Client:</strong><span>{{ $project->client->name }}</span></div>
                <div class="stat-row"><strong>Status:</strong><span class="status-badge {{ $project->getStatusBadgeColor() }}">{{ $project->getStatusLabel() }}</span></div>
                @if($project->budget)<div class="stat-row"><strong>Budget:</strong><span>${{ number_format($project->budget, 2) }}</span></div>@endif
                @if($project->spent > 0)<div class="stat-row"><strong>Spent:</strong><span>${{ number_format($project->spent, 2) }}</span></div>@endif
                <div class="stat-row"><strong>Tasks:</strong><span>{{ $project->tasks->count() }}</span></div>
                <div class="stat-row"><strong>Team:</strong><span>{{ $project->getTeamMembers()->count() }}</span></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3>Team</h3>
                @if($canEdit)
                <button class="btn btn-sm btn-primary" onclick="showAddTeamModal()"><i class="fas fa-plus"></i></button>
                @endif
            </div>
            <div class="card-content">
                @foreach($project->projectPeople as $person)
                <div class="d-flex justify-content-between align-items-center mb-2" style="border-bottom:1px solid var(--gray-200);padding-bottom:8px;">
                    <div>
                        <strong>{{ $person->user->name }}</strong>
                        <br>
                        <small class="badge bg-{{ $person->role === 'project_manager' ? 'success' : ($person->role === 'client' ? 'info' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $person->role)) }}
                        </small>
                        @if($person->can_edit)<small class="badge bg-warning">Can Edit</small>@endif
                    </div>
                    @if($canEdit)
                    <div>
                        <button class="btn btn-sm btn-secondary" onclick="editTeamMember({{ $person->id }}, '{{ $person->role }}', {{ $person->can_edit ? 'true' : 'false' }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('projects.team.remove', $person) }}" style="display:inline;" onsubmit="return confirm('Remove {{ $person->user->name }} from this project?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- ACTIVITY LOG -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Activity Log</h3>
            </div>
            <div class="card-content">
                @forelse($activities as $activity)
                <div class="activity-item mb-3" style="border-left:3px solid var(--primary-color);padding-left:15px;">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $activity->description }}</strong>
                            @if($activity->causer)
                            <span class="text-muted">by {{ $activity->causer->name }}</span>
                            @endif
                            <br>
                            @if($activity->properties && $activity->properties->has('attributes'))
                            <small class="text-muted">
                                @foreach($activity->properties->get('attributes') as $key => $value)
                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? json_encode($value) : $value }}</span>
                                @endforeach
                            </small>
                            @endif
                        </div>
                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4">No activity yet.</p>
                @endforelse

                <div class="mt-3">
                    {{ $activities->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="milestoneModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add Milestone</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('projects.milestones.store', $project) }}">@csrf<div class="modal-body"><div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" required></div><div class="form-group"><label>Description</label><textarea name="description" rows="3" class="form-control"></textarea></div><div class="form-group"><label>Due Date</label><input type="date" name="due_date" class="form-control"></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Create</button></div></form></div></div></div>

<div class="modal fade" id="taskModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('projects.tasks.store', $project) }}">@csrf<div class="modal-body"><div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" required></div><div class="form-group"><label>Description</label><textarea name="description" rows="2" class="form-control"></textarea></div><div class="form-group"><label>Milestone</label><select name="milestone_id" class="form-control"><option value="">None</option>@foreach($project->milestones as $m)<option value="{{ $m->id }}">{{ $m->title }}</option>@endforeach</select></div><div class="form-group"><label>Assign To</label><select name="assigned_to" class="form-control"><option value="">Unassigned</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="form-group"><label>Priority</label><select name="priority" class="form-control"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div><div class="form-group"><label>Due Date</label><input type="date" name="due_date" class="form-control"></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Create Task</button></div></form></div></div></div>

<div class="modal fade" id="taskStatusModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5>Update Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" id="taskStatusForm">@csrf @method('PUT')<div class="modal-body"><div class="form-group"><label>Title *</label><input type="text" name="title" id="edit_task_title" class="form-control" required></div><div class="form-group"><label>Description</label><textarea name="description" id="edit_task_description" rows="2" class="form-control"></textarea></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Status</label><select name="status" id="edit_task_status" class="form-control"><option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="review">Review</option><option value="completed">Completed</option><option value="blocked">Blocked</option></select></div></div><div class="col-md-6"><div class="form-group"><label>Priority</label><select name="priority" id="edit_task_priority" class="form-control"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div></div></div><div class="form-group"><label>Milestone</label><select name="milestone_id" id="edit_task_milestone" class="form-control"><option value="">None</option>@foreach($project->milestones as $m)<option value="{{ $m->id }}">{{ $m->title }}</option>@endforeach</select></div><div class="form-group"><label>Assign To</label><select name="assigned_to" id="edit_task_assigned" class="form-control"><option value="">Unassigned</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Due Date</label><input type="date" name="due_date" id="edit_task_due_date" class="form-control"></div></div><div class="col-md-6"><div class="form-group"><label>Actual Hours</label><input type="number" name="actual_hours" id="edit_task_hours" class="form-control"></div></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update Task</button></div></form></div></div></div>

<div class="modal fade" id="editModal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5>Edit Project</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('projects.update', $project) }}">@csrf @method('PUT')<div class="modal-body"><div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" value="{{ $project->title }}" required></div><div class="form-group"><label>Description *</label><textarea name="description" rows="3" class="form-control" required>{{ $project->description }}</textarea></div><div class="form-group"><label>Scope</label><textarea name="scope" rows="3" class="form-control">{{ $project->scope }}</textarea></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>Planning</option><option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Active</option><option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>On Hold</option><option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option><option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option></select></div></div><div class="col-md-6"><div class="form-group"><label>Completion %</label><input type="number" name="completion_percentage" class="form-control" value="{{ $project->completion_percentage }}" min="0" max="100"></div></div></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Budget ($)</label><input type="number" name="budget" class="form-control" value="{{ $project->budget }}" step="0.01"></div></div><div class="col-md-6"><div class="form-group"><label>Start Date</label><input type="date" name="start_date" class="form-control" value="{{ $project->start_date?->format('Y-m-d') }}"></div></div></div><div class="form-group"><label>End Date</label><input type="date" name="end_date" class="form-control" value="{{ $project->end_date?->format('Y-m-d') }}"></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update Project</button></div></form></div></div></div>

<div class="modal fade" id="addTeamModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Add Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('projects.team.add', $project) }}">@csrf<div class="modal-body"><div class="form-group"><label>User *</label><select name="user_id" class="form-control" required><option value="">Select...</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="form-group"><label>Role *</label><select name="role" class="form-control" required><option value="employee">Employee</option><option value="project_manager">Project Manager</option></select></div><div class="form-group"><div class="form-check"><input type="checkbox" class="form-check-input" id="can_edit_add" name="can_edit" value="1"><label class="form-check-label" for="can_edit_add">Can Edit Project</label></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Add Member</button></div></form></div></div></div>

<div class="modal fade" id="editTeamModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Edit Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" id="editTeamForm">@csrf @method('PUT')<div class="modal-body"><div class="form-group"><label>Role *</label><select name="role" id="edit_member_role" class="form-control" required><option value="employee">Employee</option><option value="project_manager">Project Manager</option><option value="client">Client</option></select></div><div class="form-group"><div class="form-check"><input type="checkbox" class="form-check-input" id="edit_member_can_edit" name="can_edit" value="1"><label class="form-check-label" for="edit_member_can_edit">Can Edit Project</label></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>
function showMilestoneModal(){new bootstrap.Modal(document.getElementById('milestoneModal')).show();}
function showTaskModal(){new bootstrap.Modal(document.getElementById('taskModal')).show();}
function showEditModal(){new bootstrap.Modal(document.getElementById('editModal')).show();}
function updateTaskStatus(id){
    // Fetch task data
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
function showAddTeamModal(){new bootstrap.Modal(document.getElementById('addTeamModal')).show();}
function editTeamMember(id, role, canEdit){
    document.getElementById('edit_member_role').value = role;
    document.getElementById('edit_member_can_edit').checked = canEdit;
    document.getElementById('editTeamForm').action = `/internal/projects/team/${id}`;
    new bootstrap.Modal(document.getElementById('editTeamModal')).show();
}
</script>
@endpush

