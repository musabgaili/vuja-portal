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
                    <button class="btn btn-primary btn-sm" onclick="showEditModal()"><i class="fas fa-edit"></i> {{ __('portal.projects_manager.show.edit') }}</button>
                    <button class="btn btn-success btn-sm" onclick="showMilestoneModal()"><i class="fas fa-plus"></i> {{ __('portal.projects_manager.show.milestone') }}</button>
                    <button class="btn btn-info btn-sm" onclick="showTaskModal()"><i class="fas fa-tasks"></i> {{ __('portal.projects_manager.show.task') }}</button>
                    @endif
                    <a href="{{ route('projects.expenses.index', $project) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-receipt"></i> {{ __('portal.projects_manager.show.expenses') }}
                    </a>
                    @if($project->scopeChanges()->where('status', 'pending')->count() > 0)
                    <a href="{{ route('projects.scope-changes.index') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-exclamation-circle"></i> {{ __('portal.projects_manager.show.scope_changes') }} ({{ $project->scopeChanges()->where('status', 'pending')->count() }})
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-content">
                <p>{{ $project->description }}</p>
                <div class="progress-bar mt-3"><div class="progress-fill" style="width:{{ $project->completion_percentage }}%;"></div></div>
                <small>{{ $project->completion_percentage }}% {{ __('portal.projects_manager.show.complete') }}</small>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h3>{{ __('portal.projects_manager.show.milestones') }} ({{ $project->milestones->count() }})</h3></div>
            <div class="card-content">
                @forelse($project->milestones as $milestone)
                <div class="milestone-card mb-3">
                    <div class="d-flex justify-between">
                        <div style="flex:1;">
                            <h5>{{ $milestone->title }}</h5>
                            @if($milestone->description)<p class="text-muted">{{ $milestone->description }}</p>@endif
                            <span class="status-badge {{ $milestone->getStatusBadgeColor() }}">{{ ucfirst($milestone->status) }}</span>
                            @if($milestone->due_date)<small class="text-muted">• {{ __('portal.projects_manager.show.due') }}: {{ $milestone->due_date->format('M d, Y') }}</small>@endif
                            
                            @php
                                $milestoneTasks = $milestone->tasks;
                                $totalTasks = $milestoneTasks->count();
                                $completedTasks = $milestoneTasks->where('status', 'completed')->count();
                                $taskProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                            @endphp
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    {{ __('portal.projects_manager.show.tasks') }}: {{ $completedTasks }}/{{ $totalTasks }} {{ __('portal.projects_manager.show.completed_lc') }} ({{ $taskProgress }}%)
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
                <p class="text-muted">{{ __('portal.projects_manager.show.no_milestones') }} <a href="#" onclick="showMilestoneModal();return false;">{{ __('portal.projects_manager.show.add_first_milestone') }}</a></p>
                @endforelse
            </div>
        </div>

        <!-- ALL TASKS -->
        <div class="card mb-4">
            <div class="card-header"><h3>{{ __('portal.projects_manager.show.all_tasks') }} ({{ $project->tasks->count() }})</h3></div>
            <div class="card-content">
                @forelse($project->tasks->sortByDesc('created_at') as $task)
                <div class="task-row mb-2" style="border-left:3px solid var(--{{ $task->getStatusBadgeColor() }}-color);padding-left:10px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong>{{ $task->title }}</strong>
                            <span class="status-badge {{ $task->getStatusBadgeColor() }}">{{ ucfirst($task->status) }}</span>
                            @if($task->priority === 'urgent')<span class="badge bg-danger">{{ __('portal.projects_manager.show.urgent') }}</span>@endif
                            @if($task->priority === 'high')<span class="badge bg-warning">{{ __('portal.projects_manager.show.high') }}</span>@endif
                            <br>
                            @if($task->milestone)<small class="text-muted">📍 {{ $task->milestone->title }}</small>@endif
                            @if($task->assignedTo)<br><small>👤 {{ $task->assignedTo->name }}</small>@endif
                            @if($task->due_date)<br><small>📅 {{ __('portal.projects_manager.show.due') }}: {{ $task->due_date->format('M d, Y') }}</small>@endif
                        </div>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="updateTaskStatus({{ $task->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">{{ __('portal.projects_manager.show.no_tasks_yet') }} <a href="#" onclick="showTaskModal();return false;">{{ __('portal.projects_manager.show.create_first_task') }}</a></p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.projects_manager.show.details') }}</h3></div>
            <div class="card-content">
                <div class="stat-row"><strong>{{ __('portal.projects_manager.show.client') }}:</strong><span>{{ $project->client->name }}</span></div>
                <div class="stat-row"><strong>{{ __('portal.projects_manager.show.status') }}:</strong><span class="status-badge {{ $project->getStatusBadgeColor() }}">{{ $project->getStatusLabel() }}</span></div>
                @if($project->budget)<div class="stat-row"><strong>{{ __('portal.projects_manager.show.budget') }}:</strong><span>${{ number_format($project->budget, 2) }}</span></div>@endif
                @if($project->spent > 0)<div class="stat-row"><strong>{{ __('portal.projects_manager.show.spent') }}:</strong><span>${{ number_format($project->spent, 2) }}</span></div>@endif
                <div class="stat-row"><strong>{{ __('portal.projects_manager.show.tasks') }}:</strong><span>{{ $project->tasks->count() }}</span></div>
                <div class="stat-row"><strong>{{ __('portal.projects_manager.show.team') }}:</strong><span>{{ $project->getTeamMembers()->count() }}</span></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3>{{ __('portal.projects_manager.show.team') }}</h3>
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
                        @if($person->can_edit)<small class="badge bg-warning">{{ __('portal.projects_manager.show.can_edit') }}</small>@endif
                    </div>
                    @if($canEdit)
                    <div>
                        <button class="btn btn-sm btn-secondary" onclick="editTeamMember({{ $person->id }}, '{{ $person->role }}', {{ $person->can_edit ? 'true' : 'false' }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('projects.team.remove', $person) }}" style="display:inline;" onsubmit="return confirm(@js(__('portal.projects_manager.show.remove_from_project_confirm', ['name' => $person->user->name])))">
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
                <h3><i class="fas fa-history"></i> {{ __('portal.projects_manager.show.activity_log') }}</h3>
            </div>
            <div class="card-content">
                @forelse($activities as $activity)
                <div class="activity-item mb-3" style="border-left:3px solid var(--primary-color);padding-left:15px;">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $activity->description }}</strong>
                            @if($activity->causer)
                            <span class="text-muted">{{ __('portal.projects_manager.show.by') }} {{ $activity->causer->name }}</span>
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
                <p class="text-muted text-center py-4">{{ __('portal.projects_manager.show.no_activity') }}</p>
                @endforelse

                <div class="mt-3">
                    {{ $activities->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@include('projects.manager.show-modals')
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

