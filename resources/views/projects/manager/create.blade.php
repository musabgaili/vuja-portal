@extends('layouts.internal-dashboard')
@section('title', __('portal.projects_manager.create.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">{{ __('portal.projects_manager.projects') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.projects_manager.create.breadcrumb') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('portal.projects_manager.create.heading') }}</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.client') }} *</label>
                        <div class="d-flex gap-2 align-items-start">
                            <select name="client_id" id="client_id" class="form-control" required>
                                <option value="">{{ __('portal.projects_manager.create.select_client') }}</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id')==$client->id)>{{ $client->name }} ({{ $client->email }})</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary text-nowrap" onclick="quickAddClient('client_id')">
                                <i class="fas fa-user-plus"></i> {{ __('portal.quick_client.add') }}
                            </button>
                        </div>
                        @error('client_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.project_title') }} *</label>
                        <input type="text" name="title" class="form-control" required placeholder="{{ __('portal.projects_manager.create.project_title_placeholder') }}">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.description') }} *</label>
                        <textarea name="description" rows="4" class="form-control" required placeholder="{{ __('portal.projects_manager.create.description_placeholder') }}"></textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.create.scope') }}</label>
                        <textarea name="scope" rows="4" class="form-control" placeholder="{{ __('portal.projects_manager.create.scope_placeholder') }}"></textarea>
                        @error('scope')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.budget') }}</label>
                                <input type="number" name="budget" class="form-control" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.project_manager') }}</label>
                                <select name="project_manager_id" class="form-control">
                                    <option value="">{{ __('portal.projects_manager.create.select_pm') }}</option>
                                    @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                    @endforeach
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ __('portal.projects_manager.create.employee') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.assign_employees') }}</label>
                                <div style="display:flex; flex-wrap:wrap; gap:10px; max-height:170px; overflow:auto; border:1px solid var(--gray-200); border-radius:8px; padding:12px;">
                                    @forelse($employees as $employee)
                                        <label class="d-flex align-items-center gap-2" style="min-width:210px; cursor:pointer;">
                                            <input type="checkbox" name="team_members[]" value="{{ $employee->id }}" @checked(collect(old('team_members'))->contains($employee->id))>
                                            <span>{{ $employee->name }}</span>
                                        </label>
                                    @empty
                                        <span class="text-muted">{{ __('portal.projects_manager.create.no_employees') }}</span>
                                    @endforelse
                                </div>
                                <small class="text-muted">{{ __('portal.projects_manager.create.assign_employees_hint') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.start_date') }}</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.projects_manager.create.end_date') }}</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('portal.projects_manager.create.create_project') }}
                        </button>
                        <a href="{{ route('projects.manager.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('partials.quick-add-client')
@endsection
