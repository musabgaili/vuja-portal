@extends('layouts.dashboard')
@section('title', 'Change Requests')

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-file-signature"></i> {{ __('portal.change_req.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ $project->title }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

@forelse($changeRequests as $cr)
    <div class="card mb-3">
        <div class="card-content">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div>
                    <h3 style="font-size:1.05rem; font-weight:600; margin:0;">{{ $cr->title }}</h3>
                    <p class="text-muted mb-1" style="font-size:.85rem;">{{ $cr->description }}</p>
                </div>
                <span class="badge bg-{{ $cr->getStatusBadgeColor() }}">{{ ucfirst($cr->status) }}</span>
            </div>

            @if((float) $cr->budget_delta !== 0.0)
                <div class="my-2" style="font-size:.9rem;">
                    <strong>{{ __('portal.change_req.budget_impact') }}:</strong>
                    <span style="color: {{ (float) $cr->budget_delta > 0 ? 'var(--error-color)' : 'var(--success-color)' }};">
                        {{ (float) $cr->budget_delta > 0 ? '+' : '' }}${{ number_format((float) $cr->budget_delta, 2) }}
                    </span>
                </div>
            @endif

            @if($cr->isApplied())
                <div class="alert alert-success mb-0 mt-2" style="font-size:.85rem;">
                    <i class="fas fa-circle-check"></i> {{ __('portal.change_req.signed_on') }} {{ $cr->client_signed_at->format('M j, Y g:i A') }} — <em>{{ $cr->client_signature }}</em>
                </div>
            @elseif($cr->needsClientSignature())
                <div class="mt-3" style="border-top:1px solid var(--gray-200); padding-top:.75rem;">
                    <p style="font-size:.85rem; margin-bottom:.5rem;">{{ __('portal.change_req.sign_prompt') }}</p>
                    <form method="POST" action="{{ route('projects.client.change-requests.sign', $cr) }}" class="d-flex gap-2 flex-wrap">
                        @csrf
                        <input type="text" name="client_signature" class="form-control" style="max-width:280px;"
                               placeholder="{{ __('portal.change_req.type_full_name') }}" maxlength="120" required>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-signature"></i> {{ __('portal.change_req.sign_apply') }}</button>
                    </form>
                </div>
            @elseif($cr->isPending())
                <p class="text-muted mb-0 mt-2" style="font-size:.85rem;"><i class="fas fa-clock"></i> {{ __('portal.change_req.awaiting_review') }}</p>
            @endif
        </div>
    </div>
@empty
    <div class="card"><div class="card-content text-muted text-center py-4">{{ __('portal.change_req.none') }}</div></div>
@endforelse

<a href="{{ route('projects.client.scope-change.create', $project) }}" class="btn btn-outline-primary"><i class="fas fa-plus"></i> {{ __('portal.change_req.new') }}</a>
<a href="{{ route('projects.client.show', $project) }}" class="btn btn-secondary">{{ __('portal.change_req.back') }}</a>
@endsection
