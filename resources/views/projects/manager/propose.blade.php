@extends('layouts.internal-dashboard')
@section('title', __('portal.projects_propose.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">{{ __('portal.projects_manager.projects') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.projects_propose.breadcrumb') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lightbulb"></i> {{ __('portal.projects_propose.heading') }}</h3>
            </div>
            <div class="card-content">
                <p class="text-muted">{{ __('portal.projects_propose.intro') }}</p>

                <form method="POST" action="{{ route('projects.propose.store') }}">
                    @csrf

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.project_title') }} *</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title') }}" placeholder="{{ __('portal.projects_propose.project_title_placeholder') }}">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.description') }} *</label>
                        <textarea name="description" rows="4" class="form-control" required placeholder="{{ __('portal.projects_propose.description_placeholder') }}">{{ old('description') }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.justification') }}</label>
                        <textarea name="proposal_notes" rows="3" class="form-control" placeholder="{{ __('portal.projects_propose.justification_placeholder') }}">{{ old('proposal_notes') }}</textarea>
                        <small class="text-muted">{{ __('portal.projects_propose.justification_hint') }}</small>
                        @error('proposal_notes')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.scope') }}</label>
                        <textarea name="scope" rows="3" class="form-control" placeholder="{{ __('portal.projects_propose.scope_placeholder') }}">{{ old('scope') }}</textarea>
                        @error('scope')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.client') }}</label>
                        <select name="client_id" class="form-control">
                            <option value="">{{ __('portal.projects_propose.client_optional') }}</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id')==$client->id)>{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('portal.projects_propose.client_hint') }}</small>
                        @error('client_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.projects_propose.budget') }}</label>
                                <input type="number" name="budget" class="form-control" step="0.01" min="0" value="{{ old('budget') }}" placeholder="0.00">
                                @error('budget')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.projects_propose.start_date') }}</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                                @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.projects_propose.end_date') }}</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" style="font-size:.9rem;">
                        <i class="fas fa-circle-info"></i> {{ __('portal.projects_propose.review_note') }}
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> {{ __('portal.projects_propose.submit') }}
                        </button>
                        <a href="{{ route('projects.proposals.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
