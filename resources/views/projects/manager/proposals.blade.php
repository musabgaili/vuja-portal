@extends('layouts.internal-dashboard')
@section('title', __('portal.projects_propose.queue_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">{{ __('portal.projects_manager.projects') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.projects_propose.queue_breadcrumb') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:linear-gradient(135deg,#0F969C 0%,#294D61 100%);color:#fff;padding:1.5rem;border-radius:12px;margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-lightbulb"></i> {{ __('portal.projects_propose.queue_title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ $isReviewer ? __('portal.projects_propose.queue_subtitle_reviewer') : __('portal.projects_propose.queue_subtitle_employee') }}</p>
    </div>
    <a href="{{ route('projects.propose.create') }}" class="btn btn-warning"><i class="fas fa-plus"></i> {{ __('portal.projects_propose.new') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

{{-- Pending proposals --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-hourglass-half"></i> {{ __('portal.projects_propose.pending') }} ({{ $proposals->count() }})</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:760px;">
            <thead>
                <tr>
                    <th>{{ __('portal.projects_propose.col_project') }}</th>
                    <th>{{ __('portal.projects_propose.col_proposed_by') }}</th>
                    <th>{{ __('portal.projects_propose.col_client') }}</th>
                    <th>{{ __('portal.projects_propose.col_budget') }}</th>
                    <th class="text-end">{{ __('portal.team.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($proposals as $p)
                <tr>
                    <td>
                        <strong>{{ $p->title }}</strong>
                        <div class="text-muted" style="font-size:.82rem;max-width:340px;">{{ \Illuminate\Support\Str::limit(strip_tags($p->description), 120) }}</div>
                        @if($p->proposal_notes)
                            <div class="mt-1" style="font-size:.8rem;"><i class="fas fa-quote-left text-muted"></i> {{ \Illuminate\Support\Str::limit($p->proposal_notes, 100) }}</div>
                        @endif
                    </td>
                    <td>{{ $p->proposedBy?->name ?? '—' }}<br><small class="text-muted">{{ $p->created_at?->translatedFormat('M j, Y') }}</small></td>
                    <td>
                        {{ $p->clientDisplayName() ?? __('portal.projects_propose.no_client') }}
                        @if(! $p->client_id && $p->prospect_name)
                        <br><small class="text-muted"><i class="fas fa-user-clock"></i> {{ __('portal.projects_propose.prospect_badge') }}</small>
                        @endif
                    </td>
                    <td>{{ $p->budget ? number_format($p->budget, 2).' '.config('scope.currency','SAR') : '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('projects.manager.show', $p) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
                        @if($isReviewer)
                            <form method="POST" action="{{ route('projects.proposal.approve', $p) }}" class="d-inline" onsubmit="return confirm(@js(__('portal.projects_propose.approve_confirm')))">@csrf
                                <button class="btn btn-sm btn-primary" title="{{ __('portal.projects_propose.approve') }}"><i class="fas fa-check"></i> {{ __('portal.projects_propose.approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('projects.proposal.reject', $p) }}" class="d-inline" onsubmit="return askRejectReason(this)">@csrf
                                <input type="hidden" name="review_notes">
                                <button class="btn btn-sm btn-outline-danger" title="{{ __('portal.projects_propose.reject') }}"><i class="fas fa-xmark"></i></button>
                            </form>
                        @else
                            <span class="badge bg-warning text-dark">{{ __('portal.projects.status.proposed') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-3">{{ __('portal.projects_propose.none_pending') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recently reviewed --}}
@if($reviewed->count())
<div class="card">
    <div class="card-header"><span class="card-title"><i class="fas fa-clock-rotate-left"></i> {{ __('portal.projects_propose.recently_reviewed') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:720px;">
            <thead>
                <tr>
                    <th>{{ __('portal.projects_propose.col_project') }}</th>
                    <th>{{ __('portal.projects_propose.col_proposed_by') }}</th>
                    <th>{{ __('portal.projects_propose.col_outcome') }}</th>
                    <th>{{ __('portal.projects_propose.col_reviewer') }}</th>
                    <th>{{ __('portal.projects_propose.col_comment') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($reviewed as $p)
                <tr>
                    <td><a href="{{ route('projects.manager.show', $p) }}" style="color:var(--primary-color);text-decoration:none;"><strong>{{ $p->title }}</strong></a></td>
                    <td>{{ $p->proposedBy?->name ?? '—' }}</td>
                    <td><span class="status-badge {{ $p->getStatusBadgeColor() }}">{{ $p->getStatusLabel() }}</span></td>
                    <td>{{ $p->proposalReviewedBy?->name ?? '—' }}<br><small class="text-muted">{{ $p->proposal_reviewed_at?->translatedFormat('M j, Y') }}</small></td>
                    <td style="max-width:280px;font-size:.85rem;">{{ $p->proposal_review_notes ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@push('scripts')
<script>
function askRejectReason(form) {
    var note = window.prompt(@js(__('portal.projects_propose.reject_prompt')));
    if (note === null) return false;                 // cancelled
    if (note.trim() === '') { alert(@js(__('portal.projects_propose.reject_required'))); return false; }
    form.querySelector('input[name="review_notes"]').value = note;
    return true;
}
</script>
@endpush
@endsection
