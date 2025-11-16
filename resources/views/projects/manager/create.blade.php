@extends('layouts.internal-dashboard')
@section('title', 'Create New Project')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">Projects</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>Create New Project</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>Client *</label>
                        <select name="client_id" class="form-control" required>
                            <option value="">Select Client...</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        @error('client_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Project Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g., Mobile App Development">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="4" class="form-control" required placeholder="Brief project overview..."></textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Scope</label>
                        <textarea name="scope" rows="4" class="form-control" placeholder="Detailed project scope and deliverables..."></textarea>
                        @error('scope')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Budget ($)</label>
                                <input type="number" name="budget" class="form-control" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project Manager</label>
                                <select name="project_manager_id" class="form-control">
                                    <option value="">Select PM...</option>
                                    @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                    @endforeach
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} (Employee)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Team Members</label>
                        <select name="team_members[]" class="form-control" multiple size="5">
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Project
                        </button>
                        <a href="{{ route('projects.manager.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
