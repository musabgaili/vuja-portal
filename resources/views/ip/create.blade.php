@extends('layouts.dashboard')
@section('title', __('portal.ip.registration'))
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📄 {{ __('portal.ip.registration_request') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.ip.back') }}</a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('ip.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('portal.ip.ip_type') }} *</label>
                <select name="ip_type" class="form-control" required>
                    <option value="">{{ __('portal.ip.select_type') }}</option>
                    @foreach($ipTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.ip.title') }} *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.ip.description') }} *</label>
                <textarea name="ip_description" rows="6" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('portal.ip.supporting_documents') }}</label>
                <input type="file" name="documents[]" class="form-control" multiple>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-calendar me-2"></i> {{ __('portal.ip.after_submission_book_meeting') }}
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.ip.submit_request') }}</button>
        </form>
    </div>
</div>
@endsection

