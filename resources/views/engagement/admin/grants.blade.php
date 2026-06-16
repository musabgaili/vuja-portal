@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.grants'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-hand-holding-dollar"></i> {{ __('engagement.admin.grants') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('engagement.admin.two_person_note') }}</p>
    </div>
    <a href="{{ route('engagement.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.dashboard') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="row g-3">
    @if($canSuggest)
    <div class="col-lg-4">
        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-plus"></i> {{ __('engagement.admin.suggest_grant') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('engagement.admin.grants.store') }}">@csrf
                <label class="form-label">{{ __('engagement.admin.client') }}</label>
                <select name="client_id" class="form-select mb-2" required>
                    <option value="">—</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach
                </select>
                <label class="form-label">{{ __('engagement.admin.grant_points') }}</label>
                <input type="number" name="points" class="form-control mb-2" min="1" required>
                <label class="form-label">{{ __('engagement.admin.reason') }}</label>
                <textarea name="reason" class="form-control mb-2" rows="2" required maxlength="500"></textarea>
                <button class="btn btn-primary w-100">{{ __('engagement.admin.suggest_grant') }}</button>
            </form>
        </div></div>
    </div>
    @endif

    <div class="{{ $canSuggest ? 'col-lg-8' : 'col-12' }}">
        <div class="card"><div class="card-content p-0" style="overflow-x:auto;">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>{{ __('engagement.admin.client') }}</th>
                    <th>{{ __('engagement.admin.grant_points') }}</th>
                    <th>{{ __('engagement.admin.reason') }}</th>
                    <th>{{ __('engagement.admin.suggested_by') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($requests as $g)
                    <tr>
                        <td>{{ $g->client?->name ?? '—' }}</td>
                        <td class="fw-bold">+{{ number_format($g->points) }}</td>
                        <td style="max-width:280px;"><div style="font-size:.85rem;">{{ $g->reason }}</div></td>
                        <td style="font-size:.82rem;">{{ $g->suggestedBy?->name ?? '—' }}</td>
                        <td class="text-end">
                            @if($g->isSuggested())
                                @if($canApprove)
                                    <div class="d-flex gap-1 justify-content-end">
                                        <form method="POST" action="{{ route('engagement.admin.grants.approve', $g) }}">@csrf
                                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> {{ __('engagement.admin.approve_grant') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('engagement.admin.grants.reject', $g) }}">@csrf
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-xmark"></i></button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-warning">{{ __('engagement.admin.awaiting_manager') }}</span>
                                @endif
                            @else
                                <span class="badge bg-{{ $g->statusColor() }}">{{ $g->status }}</span>
                                @if($g->approvedBy)<div class="text-muted" style="font-size:.72rem;">{{ __('engagement.admin.approved_by') }}: {{ $g->approvedBy->name }}</div>@endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">{{ __('engagement.admin.no_grants') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
        @if($requests->hasPages())<div class="mt-3">{{ $requests->links() }}</div>@endif
    </div>
</div>
@endsection
