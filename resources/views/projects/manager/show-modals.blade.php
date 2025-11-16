<!-- Milestone Modal -->
<div class="modal fade" id="milestoneModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.milestones.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Milestone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div class="modal fade" id="taskModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Milestone</label>
                                <select name="milestone_id" class="form-control">
                                    <option value="">None</option>
                                    @foreach($project->milestones as $m)
                                    <option value="{{ $m->id }}">{{ $m->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Assign To</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">Unassigned</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Task
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
                <h5>Update Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="taskStatusForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group" id="task_title_field" style="display: {{ $canManageTasks ? 'block' : 'none' }};">
                        <label>Title *</label>
                        <input type="text" name="title" id="edit_task_title" class="form-control">
                    </div>
                    <div class="form-group" id="task_description_field" style="display: {{ $canManageTasks ? 'block' : 'none' }};">
                        <label>Description</label>
                        <textarea name="description" id="edit_task_description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-{{ $canManageTasks ? '6' : '12' }}">
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" id="edit_task_status" class="form-control" required>
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="completed">Completed</option>
                                    <option value="blocked">Blocked</option>
                                </select>
                            </div>
                        </div>
                        @if($canManageTasks)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" id="edit_task_priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($canManageTasks)
                    <div class="form-group">
                        <label>Milestone</label>
                        <select name="milestone_id" id="edit_task_milestone" class="form-control">
                            <option value="">None</option>
                            @foreach($project->milestones as $m)
                            <option value="{{ $m->id }}">{{ $m->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" id="edit_task_assigned" class="form-control">
                            <option value="">Unassigned</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" name="due_date" id="edit_task_due_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Actual Hours</label>
                                <input type="number" name="actual_hours" id="edit_task_hours" class="form-control">
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> As a team member, you can only update the task status.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Task
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
                <h5>Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" class="form-control" value="{{ $project->title }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="3" class="form-control" required>{{ $project->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Client</label>
                        <select name="client_id" class="form-control">
                            <option value="">-- No Client --</option>
                            @foreach(\App\Models\User::where('type', 'client')->get() as $c)
                            <option value="{{ $c->id }}" {{ $project->client_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email }})
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">You can change or remove the client</small>
                    </div>
                    <div class="form-group">
                        <label>Scope</label>
                        <textarea name="scope" rows="3" class="form-control">{{ $project->scope }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="quoted" {{ $project->status === 'quoted' ? 'selected' : '' }}>Quoted</option>
                            <option value="awarded" {{ $project->status === 'awarded' ? 'selected' : '' }}>Awarded</option>
                            <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="paused" {{ $project->status === 'paused' ? 'selected' : '' }}>Paused</option>
                            <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="lost" {{ $project->status === 'lost' ? 'selected' : '' }}>Lost</option>
                            <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Budget ($)</label>
                        <input type="number" name="budget" class="form-control" value="{{ $project->budget }}" step="0.01">
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Progress percentage is auto-calculated from milestones & tasks.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $project->start_date?->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $project->end_date?->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Project
                    </button>
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
                <h5>Add Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.team.add', $project) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>User *</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Select...</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="employee">Employee</option>
                            @if(!$project->projectPeople()->where('role', 'project_manager')->exists())
                            <option value="project_manager">Project Manager</option>
                            @endif
                            <option value="account_manager">Account Manager</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="can_edit_add" name="can_edit" value="1">
                            <label class="form-check-label" for="can_edit_add">Can Edit Project</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Member
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
                <h5>Edit Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTeamForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" id="edit_member_role" class="form-control" required>
                            <option value="employee">Employee</option>
                            <option value="project_manager">Project Manager</option>
                            <option value="account_manager">Account Manager</option>
                            <option value="client">Client</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_member_can_edit" name="can_edit" value="1">
                            <label class="form-check-label" for="edit_member_can_edit">Can Edit Project</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
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
                <h5>Upload Deliverable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.deliverables.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>File *</label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Max: 50MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload
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
                <h5><i class="fas fa-reply"></i> Respond to Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="respondRequestForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="fw-bold">Your Response *</label>
                        <textarea name="response" class="form-control" rows="4" required placeholder="Provide a detailed response..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Response
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
                <h5><i class="fas fa-check-circle"></i> Resolve Complaint</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="resolveComplaintForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> Client will be notified via email
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">Resolution Note *</label>
                        <textarea name="resolution_note" class="form-control" rows="4" required placeholder="Explain how resolved..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Resolved
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
