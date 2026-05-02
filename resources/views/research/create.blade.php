@extends('layouts.dashboard')
@section('title', __('portal.research.create.page_title'))
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔍 {{ __('portal.research.create.heading') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.ip.back') }}</a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('research.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('portal.research.create.research_title') }} *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.research.create.research_topic') }} *</label>
                <textarea name="research_topic" rows="4" class="form-control @error('research_topic') is-invalid @enderror" placeholder="{{ __('portal.research.create.research_topic_placeholder') }}" required>{{ old('research_topic') }}</textarea>
                @error('research_topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.research.create.additional_details') }}</label>
                <textarea name="research_details" rows="3" class="form-control" placeholder="{{ __('portal.research.create.research_details_placeholder') }}">{{ old('research_details') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.research.create.relevant_links') }}</label>
                <textarea name="relevant_links" rows="3" class="form-control" placeholder="{{ __('portal.research.create.relevant_links_placeholder') }}">{{ old('relevant_links') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.research.create.upload_files') }}</label>
                <input type="file" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.png">
                <small class="text-muted">{{ __('portal.research.create.max_per_file') }}</small>
            </div>
            <div class="alert alert-warning">
                <i class="fas fa-file-signature me-2"></i>
                <strong>{{ __('portal.research.create.nda_notice_title') }}</strong> {{ __('portal.research.create.nda_notice_body') }}
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.research.create.submit') }}</button>
        </form>
    </div>
</div>
@endsection
