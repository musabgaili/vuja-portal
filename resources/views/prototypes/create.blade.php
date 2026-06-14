@extends('layouts.dashboard')

@section('title', __('portal.prototypes.create.title'))
@section('page-title', __('portal.prototypes.create.title'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cube"></i> {{ __('portal.prototypes.create.heading') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('portal.prototypes.back') }}
        </a>
    </div>
    <div class="card-content">
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>
        @endif

        <form method="POST" action="{{ route('prototypes.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('portal.prototypes.field.title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="200"
                       placeholder="{{ __('portal.prototypes.field.title_ph') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.prototypes.field.category') }}</label>
                <select name="category" class="form-control">
                    <option value="">{{ __('portal.prototypes.field.select_category') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.prototypes.field.description') }} *</label>
                <textarea name="description" rows="6" class="form-control" required
                          placeholder="{{ __('portal.prototypes.field.description_ph') }}">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.prototypes.field.goals') }}</label>
                <textarea name="goals" rows="3" class="form-control"
                          placeholder="{{ __('portal.prototypes.field.goals_ph') }}">{{ old('goals') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.prototypes.field.budget') }}</label>
                        <input type="text" name="budget_range" class="form-control" value="{{ old('budget_range') }}"
                               placeholder="{{ __('portal.prototypes.field.budget_ph') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.prototypes.field.timeline') }}</label>
                        <input type="text" name="timeline" class="form-control" value="{{ old('timeline') }}"
                               placeholder="{{ __('portal.prototypes.field.timeline_ph') }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.prototypes.field.files') }}</label>
                <input type="file" name="files[]" class="form-control" multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.webp,.zip,.txt,.csv,.dwg,.svg">
                <small class="text-muted">{{ __('portal.prototypes.field.files_hint') }}</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>{{ __('portal.prototypes.create.what_next') }}:</strong> {{ __('portal.prototypes.create.what_next_body') }}
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.prototypes.create.submit') }}</button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> {{ __('portal.prototypes.create.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
