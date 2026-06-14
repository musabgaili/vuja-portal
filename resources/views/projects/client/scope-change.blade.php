@extends('layouts.dashboard')
@section('title', __('portal.projects_client.scope_change.title'))
@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('portal.projects_client.scope_change.heading') }}</h3>
                <p class="text-muted mb-0">{{ __('portal.projects_client.feedback.project_label') }}: {{ $project->title }}</p>
            </div>
            <div class="card-content">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>{{ __('portal.team.note_label') }}</strong> {{ __('portal.projects_client.scope_change.note_body') }}
                </div>

                <form method="POST" action="{{ route('projects.client.scope-change.store', $project) }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>{{ __('portal.projects_client.scope_change.change_title') }} *</label>
                        <input type="text" name="title" class="form-control" required placeholder="{{ __('portal.projects_client.scope_change.change_title_placeholder') }}">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_client.scope_change.detailed_description') }} *</label>
                        <textarea name="description" rows="5" class="form-control" required placeholder="{{ __('portal.projects_client.scope_change.detailed_description_placeholder') }}"></textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_client.scope_change.justification') }}</label>
                        <textarea name="justification" rows="3" class="form-control" placeholder="{{ __('portal.projects_client.scope_change.justification_placeholder') }}"></textarea>
                        @error('justification')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.change_req.budget_impact') }}</label>
                        <input type="number" step="0.01" name="budget_delta" class="form-control" placeholder="0.00">
                        <small class="text-muted">{{ __('portal.change_req.budget_hint') }}</small>
                        @error('budget_delta')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> {{ __('portal.projects_client.show.submit_request') }}
                        </button>
                        <a href="{{ route('projects.client.show', $project) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

