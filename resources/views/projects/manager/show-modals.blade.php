<!-- Milestone Modal -->
<div class="modal fade" id="milestoneModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.modals.add_milestone') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            @if($project->isCompleted())
            <div class="modal-body">
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-lock me-1"></i> {{ __('portal.projects_manager.modals.milestones_locked_completed') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
            </div>
            @else
            <form method="POST" action="{{ route('projects.milestones.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.title') }} *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.description') }}</label>
                        <textarea name="description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.show.due') }}</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('portal.projects_manager.modals.create_milestone') }}
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Task Modal -->
<div class="modal fade" id="taskModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.modals.add_task') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.title') }} *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.description') }}</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.show.milestones') }}</label>
                                <select name="milestone_id" class="form-control">
                                    <option value="">{{ __('portal.projects_manager.modals.none') }}</option>
                                    @foreach($project->milestones as $m)
                                    <option value="{{ $m->id }}">{{ $m->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.modals.priority') }}</label>
                                <select name="priority" class="form-control">
                                    <option value="low">{{ __('portal.projects_manager.modals.priority_low') }}</option>
                                    <option value="medium" selected>{{ __('portal.projects_manager.modals.priority_medium') }}</option>
                                    <option value="high">{{ __('portal.projects_manager.show.high') }}</option>
                                    <option value="urgent">{{ __('portal.projects_manager.show.urgent') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.modals.assign_to') }}</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">{{ __('portal.projects_manager.modals.unassigned') }}</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.show.due') }}</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('portal.projects_manager.modals.create_task') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="taskStatusModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.modals.update_task') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="taskStatusForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group" id="task_title_field" style="display: {{ $canManageTasks ? 'block' : 'none' }};">
                        <label>{{ __('portal.projects_manager.modals.title') }} *</label>
                        <input type="text" name="title" id="edit_task_title" class="form-control">
                    </div>
                    <div class="form-group" id="task_description_field" style="display: {{ $canManageTasks ? 'block' : 'none' }};">
                        <label>{{ __('portal.projects_manager.modals.description') }}</label>
                        <textarea name="description" id="edit_task_description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-{{ $canManageTasks ? '6' : '12' }}">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.index.status') }} *</label>
                                <select name="status" id="edit_task_status" class="form-control" required>
                                    <option value="todo">{{ __('portal.projects_manager.modals.status_todo') }}</option>
                                    <option value="in_progress">{{ __('portal.projects_manager.status.in_progress') }}</option>
                                    <option value="review">{{ __('portal.projects_manager.modals.status_review') }}</option>
                                    <option value="completed">{{ __('portal.projects_manager.status.completed') }}</option>
                                    <option value="blocked">{{ __('portal.projects_manager.modals.status_blocked') }}</option>
                                </select>
                            </div>
                        </div>
                        @if($canManageTasks)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.modals.priority') }}</label>
                                <select name="priority" id="edit_task_priority" class="form-control">
                                    <option value="low">{{ __('portal.projects_manager.modals.priority_low') }}</option>
                                    <option value="medium">{{ __('portal.projects_manager.modals.priority_medium') }}</option>
                                    <option value="high">{{ __('portal.projects_manager.show.high') }}</option>
                                    <option value="urgent">{{ __('portal.projects_manager.show.urgent') }}</option>
                                </select>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($canManageTasks)
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.show.milestones') }}</label>
                        <select name="milestone_id" id="edit_task_milestone" class="form-control">
                            <option value="">{{ __('portal.projects_manager.modals.none') }}</option>
                            @foreach($project->milestones as $m)
                            <option value="{{ $m->id }}">{{ $m->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.assign_to') }}</label>
                        <select name="assigned_to" id="edit_task_assigned" class="form-control">
                            <option value="">{{ __('portal.projects_manager.modals.unassigned') }}</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.show.due') }}</label>
                                <input type="date" name="due_date" id="edit_task_due_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.modals.actual_hours') }}</label>
                                <input type="number" name="actual_hours" id="edit_task_hours" class="form-control">
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ __('portal.projects_manager.modals.team_member_status_only') }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('portal.projects_manager.modals.update_task') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.show.edit_project') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    @if($project->isBudgetLocked())
                    <div class="alert alert-warning">
                        <i class="fas fa-lock me-1"></i> {{ __('portal.projects_manager.modals.budget_locked_notice') }}
                    </div>
                    @endif
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.title') }} *</label>
                        <input type="text" name="title" class="form-control" value="{{ $project->title }}" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.description') }} *</label>
                        <textarea name="description" rows="3" class="form-control" required>{{ $project->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.client') }}</label>
                        <select name="client_id" class="form-control">
                            <option value="">{{ __('portal.projects_manager.modals.no_client') }}</option>
                            @foreach(\App\Models\User::where('type', 'client')->get() as $c)
                            <option value="{{ $c->id }}" {{ $project->client_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email }})
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('portal.projects_manager.modals.client_help') }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.scope') }}</label>
                        <textarea name="scope" rows="3" class="form-control">{{ $project->scope }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.index.status') }}</label>
                        <select name="status" class="form-control">
                            <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.planning') }}</option>
                            <option value="quoted" {{ $project->status === 'quoted' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.quoted') }}</option>
                            <option value="awarded" {{ $project->status === 'awarded' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.awarded') }}</option>
                            <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.in_progress') }}</option>
                            <option value="paused" {{ $project->status === 'paused' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.paused') }}</option>
                            <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.completed') }}</option>
                            <option value="lost" {{ $project->status === 'lost' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.lost') }}</option>
                            <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>{{ __('portal.projects_manager.status.cancelled') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.budget') }}</label>
                        <input type="number" name="budget" class="form-control" value="{{ $project->budget }}" step="0.01" {{ $project->isBudgetLocked() ? 'disabled' : '' }}>
                        @if($project->isBudgetLocked())
                        <small class="text-muted">{{ __('portal.projects_manager.modals.budget_scope_change_hint') }}</small>
                        @endif
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ __('portal.projects_manager.modals.progress_auto_calculated') }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.start_date') }}</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $project->start_date?->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.end_date') }}</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $project->end_date?->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('portal.projects_manager.modals.update_project') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Project Modal -->
<div class="modal fade" id="closeProjectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.show.close_project') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.close', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-circle-info me-1"></i> {{ __('portal.projects_manager.show.close_project_help') }}
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.show.close_project_status') }}</label>
                        <select name="status" class="form-control" required>
                            <option value="completed">{{ __('portal.projects_manager.status.completed') }}</option>
                            <option value="cancelled">{{ __('portal.projects_manager.status.cancelled') }}</option>
                            <option value="lost">{{ __('portal.projects_manager.status.lost') }}</option>
                        </select>
                    </div>
                    @if($project->hasPendingScopeChanges())
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-triangle-exclamation me-1"></i> {{ __('portal.projects_manager.show.close_project_blocked_scope_changes') }}
                    </div>
                    @elseif($project->hasIncompleteMilestones() || $project->hasOpenTasks())
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-list-check me-1"></i> {{ __('portal.projects_manager.show.close_project_completed_requires_work') }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('portal.projects_manager.show.close_project_submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.modals.add_team_member') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.team.add', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.user') }} *</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">{{ __('portal.projects_manager.modals.select') }}</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.team.col_role') }} *</label>
                        <select name="role" class="form-control" required>
                            <option value="employee">{{ __('portal.projects_manager.modals.role_employee') }}</option>
                            @if(!$project->projectPeople()->where('role', 'project_manager')->exists())
                            <option value="project_manager">{{ __('portal.projects_manager.modals.role_project_manager') }}</option>
                            @endif
                            <option value="account_manager">{{ __('portal.projects_manager.modals.role_account_manager') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="can_edit_add" name="can_edit" value="1">
                            <label class="form-check-label" for="can_edit_add">{{ __('portal.projects_manager.show.can_edit') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('portal.projects_manager.show.add_member') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Member Modal -->
<div class="modal fade" id="editTeamModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.modals.edit_team_member') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTeamForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.team.col_role') }} *</label>
                        <select name="role" id="edit_member_role" class="form-control" required>
                            <option value="employee">{{ __('portal.projects_manager.modals.role_employee') }}</option>
                            <option value="project_manager">{{ __('portal.projects_manager.modals.role_project_manager') }}</option>
                            <option value="account_manager">{{ __('portal.projects_manager.modals.role_account_manager') }}</option>
                            <option value="client">{{ __('portal.projects_client.show.client') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_member_can_edit" name="can_edit" value="1">
                            <label class="form-check-label" for="edit_member_can_edit">{{ __('portal.projects_manager.show.can_edit') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('portal.projects_manager.modals.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Deliverable Modal -->
<div class="modal fade" id="deliverableModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.show.upload_deliverable') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.deliverables.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.title') }} *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.description') }}</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.modals.file') }} *</label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">{{ __('portal.projects_manager.modals.max_50mb') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> {{ __('portal.projects_manager.modals.upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Respond to Request Modal -->
<div class="modal fade" id="respondRequestModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-reply"></i> {{ __('portal.projects_manager.modals.respond_to_request') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="respondRequestForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="fw-bold">{{ __('portal.projects_manager.modals.your_response') }} *</label>
                        <textarea name="response" class="form-control" rows="4" required placeholder="{{ __('portal.projects_manager.modals.response_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> {{ __('portal.projects_manager.modals.send_response') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resolve Complaint Modal -->
<div class="modal fade" id="resolveComplaintModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5><i class="fas fa-check-circle"></i> {{ __('portal.projects_manager.show.resolve_complaint') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="resolveComplaintForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> {{ __('portal.projects_manager.modals.client_notified_email') }}
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">{{ __('portal.projects_manager.modals.resolution_note') }} *</label>
                        <textarea name="resolution_note" class="form-control" rows="4" required placeholder="{{ __('portal.projects_manager.modals.resolution_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> {{ __('portal.projects_manager.modals.mark_as_resolved') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
