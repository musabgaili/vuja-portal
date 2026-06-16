@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.claims_queue'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-bullhorn"></i> {{ __('engagement.admin.claims_queue') }}</h1>
    </div>
    <a href="{{ route('engagement.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.dashboard') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="mb-3 d-flex gap-2 flex-wrap">
    @foreach(['submitted','approved','rejected','all'] as $s)
        <a href="{{ route('engagement.admin.claims.index', ['status' => $s]) }}"
           class="btn btn-sm {{ $status === $s ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $s === 'all' ? __('engagement.admin.all') : __('engagement.claim_status.'.$s) }}
        </a>
    @endforeach
</div>

<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>{{ __('engagement.admin.client') }}</th>
            <th>{{ __('engagement.admin.action') }}</th>
            <th>{{ __('engagement.admin.platform') }}</th>
            <th>{{ __('engagement.admin.posted') }}</th>
            <th>{{ __('engagement.admin.submitted_at') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @forelse($claims as $claim)
            <tr>
                <td>{{ $claim->account->client?->name ?? '—' }}</td>
                <td>{{ optional($claim->rule())->localizedName() ?? $claim->earning_rule_key }}</td>
                <td>@if($claim->platform){{ __('engagement.platforms.'.$claim->platform) }}@else—@endif</td>
                <td style="white-space:nowrap;">{{ optional($claim->posted_at)->format('Y-m-d') ?? '—' }}</td>
                <td style="white-space:nowrap;">{{ $claim->created_at->format('Y-m-d') }}</td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                        @if($claim->post_url)<a href="{{ $claim->post_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-light"><i class="fas fa-link"></i></a>@endif
                        @if($claim->screenshot_path)<a href="{{ route('engagement.admin.claims.screenshot', $claim) }}" class="btn btn-sm btn-light"><i class="fas fa-image"></i></a>@endif
                        @if($claim->isSubmitted())
                            <button class="btn btn-sm btn-success" type="button" onclick="document.getElementById('rev-{{ $claim->id }}').classList.toggle('d-none')"><i class="fas fa-gavel"></i> {{ __('engagement.admin.approve') }}/{{ __('engagement.admin.reject') }}</button>
                        @else
                            <span class="badge bg-{{ $claim->statusColor() }}">{{ __('engagement.claim_status.'.$claim->status) }}</span>
                        @endif
                    </div>
                    @if($claim->isSubmitted())
                        <div id="rev-{{ $claim->id }}" class="d-none mt-2 text-start" style="max-width:360px; margin-inline-start:auto;">
                            <form method="POST" action="{{ route('engagement.admin.claims.approve', $claim) }}">@csrf
                                <textarea name="review_notes" class="form-control form-control-sm mb-1" rows="2" placeholder="{{ __('engagement.admin.review_notes') }}"></textarea>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-success flex-fill"><i class="fas fa-check"></i> {{ __('engagement.admin.approve') }}</button>
                                    <button class="btn btn-sm btn-outline-danger flex-fill" formaction="{{ route('engagement.admin.claims.reject', $claim) }}"><i class="fas fa-xmark"></i> {{ __('engagement.admin.reject') }}</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted text-center py-4">{{ __('engagement.admin.no_pending_claims') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@if($claims->hasPages())<div class="mt-3">{{ $claims->withQueryString()->links() }}</div>@endif
@endsection
