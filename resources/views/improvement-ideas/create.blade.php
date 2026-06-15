@extends(auth()->user()?->isInternal() ? 'layouts.internal-dashboard' : 'layouts.dashboard')
@section('title', __('portal.improvement_ideas.create_title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-rocket"></i> {{ __('portal.improvement_ideas.create_title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.improvement_ideas.create_subtitle') }}</p>
    </div>
    <a href="{{ route('improvement-ideas.index') }}" class="btn btn-light"><i class="fas fa-list"></i> {{ __('portal.improvement_ideas.my_ideas') }}</a>
</div>

@if($points)
<div class="alert alert-info"><i class="fas fa-bolt"></i> {{ __('portal.improvement_ideas.points_hint', ['points' => $points]) }}</div>
@endif

<div class="card">
    <div class="card-content">
        <form method="POST" action="{{ route('improvement-ideas.store') }}">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label fw-bold">{{ __('portal.improvement_ideas.field_title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" maxlength="200" required>
            </div>
            <div class="form-group mb-3">
                <label class="form-label fw-bold">{{ __('portal.improvement_ideas.field_category') }} *</label>
                <select name="category" class="form-control" required>
                    <option value="">{{ __('portal.improvement_ideas.choose_category') }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="form-label fw-bold">{{ __('portal.improvement_ideas.field_description') }} *</label>
                <textarea name="description" rows="5" class="form-control" required minlength="20" placeholder="{{ __('portal.improvement_ideas.description_ph') }}">{{ old('description') }}</textarea>
            </div>
            <div class="form-group mb-3">
                <label class="form-label fw-bold">{{ __('portal.improvement_ideas.field_technology') }}</label>
                <textarea name="technology_used" rows="3" class="form-control" placeholder="{{ __('portal.improvement_ideas.technology_ph') }}">{{ old('technology_used') }}</textarea>
            </div>
            <div class="form-group mb-3">
                <label class="form-label fw-bold">{{ __('portal.improvement_ideas.field_benefit') }} *</label>
                <textarea name="benefit" rows="3" class="form-control" required placeholder="{{ __('portal.improvement_ideas.benefit_ph') }}">{{ old('benefit') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.improvement_ideas.submit') }}</button>
        </form>
    </div>
</div>
@endsection
