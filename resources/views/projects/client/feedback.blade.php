@extends('layouts.dashboard')
@section('title', 'Project Feedback')
@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>Rate Your Experience</h3>
                <p class="text-muted mb-0">Project: {{ $project->title }}</p>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('projects.client.feedback.store', $project) }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>Overall Rating *</label>
                        <div class="star-rating">
                            @for($i = 5; $i >= 1; $i--)
                            <input type="radio" name="rating" value="{{ $i }}" id="rating-{{ $i }}" required>
                            <label for="rating-{{ $i }}"><i class="fas fa-star"></i></label>
                            @endfor
                        </div>
                        @error('rating')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Your Feedback</label>
                        <textarea name="feedback" rows="5" class="form-control" placeholder="Tell us about your experience..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Communication</label>
                                <select name="communication_rating" class="form-control">
                                    <option value="">Rate...</option>
                                    @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Quality</label>
                                <select name="quality_rating" class="form-control">
                                    <option value="">Rate...</option>
                                    @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Timeline</label>
                                <select name="timeline_rating" class="form-control">
                                    <option value="">Rate...</option>
                                    @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="would_recommend" name="would_recommend" value="1">
                            <label class="form-check-label" for="would_recommend">
                                I would recommend VujaDe to others
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Feedback
                        </button>
                        <a href="{{ route('projects.client.show', $project) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.star-rating{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:5px;}
.star-rating input{display:none;}
.star-rating label{cursor:pointer;font-size:2rem;color:var(--gray-300);transition:color 0.2s;}
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label{color:var(--warning-color);}
</style>
@endpush

