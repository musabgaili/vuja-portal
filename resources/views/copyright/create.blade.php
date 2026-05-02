@extends('layouts.dashboard')
@section('title', __('portal.copyright.create.title'))
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">©️ {{ __('portal.copyright.create.heading') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.ip.back') }}</a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('copyright.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('portal.copyright.create.work_type') }} *</label>
                <select name="work_type" class="form-control" required>
                    <option value="">{{ __('portal.copyright.create.select_type') }}</option>
                    @foreach($workTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.copyright.create.work_title') }} *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.copyright.create.work_description') }} *</label>
                <textarea name="work_description" rows="6" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.copyright.create.upload_work_files') }}</label>
                <input type="file" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.png,.mp3,.mp4">
                <small class="text-muted">{{ __('portal.copyright.create.upload_help') }}</small>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-calendar me-2"></i> {{ __('portal.copyright.create.after_submission_book_meeting') }}
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.ip.submit_request') }}</button>
        </form>
    </div>
</div>
@endsection

