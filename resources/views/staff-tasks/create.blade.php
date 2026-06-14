@extends('layouts.internal-dashboard')
@section('title', __('portal.staff_tasks.create_title'))

@section('content')
<div class="row">
    <div class="col-lg-9 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('portal.staff_tasks.create_title') }}</h3>
            </div>
            <div class="card-content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error){{ $error }}<br>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('staff-tasks.store') }}">
                    @csrf

                    <div class="form-group">
                        <label>{{ __('portal.staff_tasks.field.title') }} *</label>
                        <input type="text" name="title" class="form-control" required maxlength="200"
                               value="{{ old('title') }}" placeholder="{{ __('portal.staff_tasks.field.title_ph') }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.staff_tasks.field.description') }}</label>
                        <textarea name="description" rows="3" class="form-control"
                                  placeholder="{{ __('portal.staff_tasks.field.description_ph') }}">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.staff_tasks.field.assignee') }} *</label>
                                <select name="assigned_to" class="form-control" required>
                                    <option value="">{{ __('portal.staff_tasks.field.assignee_ph') }}</option>
                                    @foreach($staff as $person)
                                    <option value="{{ $person->id }}" @selected(old('assigned_to') == $person->id)>{{ $person->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.staff_tasks.field.category') }} *</label>
                                <select name="category" class="form-control" required>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat }}" @selected(old('category', 'management') === $cat)>
                                        {{ __('portal.staff_tasks.category.'.$cat) }} (+{{ $rates['staff_task_'.$cat] ?? 0 }} {{ __('portal.staff_tasks.ip') }})
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ __('portal.staff_tasks.field.category_hint') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.staff_tasks.field.priority') }} *</label>
                                <select name="priority" class="form-control" required>
                                    @foreach($priorities as $p)
                                    <option value="{{ $p }}" @selected(old('priority', 'normal') === $p)>{{ __('portal.staff_tasks.priority.'.$p) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.staff_tasks.field.due_date') }}</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted" style="font-size:.85rem;">{{ __('portal.staff_tasks.field.link_hint') }}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.staff_tasks.field.project') }}</label>
                                <select name="project_id" class="form-control">
                                    <option value="">{{ __('portal.staff_tasks.field.none') }}</option>
                                    @foreach($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('portal.staff_tasks.field.opportunity') }}</label>
                                <select name="opportunity_id" class="form-control">
                                    <option value="">{{ __('portal.staff_tasks.field.none') }}</option>
                                    @foreach($opportunities as $opp)
                                    <option value="{{ $opp->id }}" @selected(old('opportunity_id') == $opp->id)>{{ $opp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> {{ __('portal.staff_tasks.assign_btn') }}
                        </button>
                        <a href="{{ route('staff-tasks.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
