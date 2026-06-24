@extends('layouts.internal-dashboard')
@section('title', __('portal.spend.manage_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.spend.manage_title') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-folder-open"></i> {{ __('portal.spend.manage_title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.spend.manage_sub') }}</p>
    </div>
    <a href="{{ route('spend.approvals') }}" class="btn btn-light"><i class="fas fa-inbox"></i> {{ __('portal.spend.approvals') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

{{-- Summary totals (respect the current filter) --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card"><div class="card-content"><div class="text-muted small">{{ __('portal.spend.summary_count') }}</div><div style="font-size:1.4rem;font-weight:800;">{{ $summary['count'] }}</div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-content"><div class="text-muted small">{{ __('portal.spend.summary_pending') }}</div><div style="font-size:1.4rem;font-weight:800;color:#b8860b;">{{ number_format($summary['pending'], 2) }} <small>{{ $currency }}</small></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-content"><div class="text-muted small">{{ __('portal.spend.summary_committed') }}</div><div style="font-size:1.4rem;font-weight:800;color:#0d6efd;">{{ number_format($summary['approved'], 2) }} <small>{{ $currency }}</small></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-content"><div class="text-muted small">{{ __('portal.spend.summary_spent') }}</div><div style="font-size:1.4rem;font-weight:800;color:#198754;">{{ number_format($summary['completed'], 2) }} <small>{{ $currency }}</small></div></div></div></div>
</div>

{{-- Filters --}}
<div class="card mb-3"><div class="card-content">
    <form method="GET" action="{{ route('spend.manage') }}" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-0">{{ __('portal.spend.col_status') }}</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">{{ __('portal.spend.filter_all') }}</option>
                @foreach(['pending','approved','completed','rejected'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '')===$s)>{{ __('portal.spend.status.'.$s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-0">{{ __('portal.spend.type_label') }}</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">{{ __('portal.spend.filter_all') }}</option>
                <option value="reimbursement" @selected(($filters['type'] ?? '')==='reimbursement')>{{ __('portal.spend.type.reimbursement') }}</option>
                <option value="purchase" @selected(($filters['type'] ?? '')==='purchase')>{{ __('portal.spend.type.purchase') }}</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-0">{{ __('portal.spend.scope_label') }}</label>
            <select name="scope" class="form-select form-select-sm">
                <option value="">{{ __('portal.spend.filter_all') }}</option>
                <option value="project" @selected(($filters['scope'] ?? '')==='project')>{{ __('portal.spend.scope.project') }}</option>
                <option value="general" @selected(($filters['scope'] ?? '')==='general')>{{ __('portal.spend.scope.general') }}</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-0">{{ __('portal.spend.project') }}</label>
            <select name="project_id" class="form-select form-select-sm">
                <option value="">{{ __('portal.spend.filter_all') }}</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" @selected((string)($filters['project_id'] ?? '')===(string)$p->id)>{{ $p->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-0">{{ __('portal.spend.requester') }}</label>
            <select name="requester_id" class="form-select form-select-sm">
                <option value="">{{ __('portal.spend.filter_all') }}</option>
                @foreach($requesters as $u)
                    <option value="{{ $u->id }}" @selected((string)($filters['requester_id'] ?? '')===(string)$u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-0">{{ __('portal.spend.search') }}</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('portal.spend.search_ph') }}">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-0">{{ __('portal.spend.from') }}</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-0">{{ __('portal.spend.to') }}</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> {{ __('portal.spend.apply') }}</button>
            <a href="{{ route('spend.manage') }}" class="btn btn-sm btn-outline-secondary">{{ __('portal.spend.clear') }}</a>
        </div>
    </form>
</div></div>

<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table mb-0">
        <thead><tr>
            <th>{{ __('portal.spend.col_title') }}</th>
            <th>{{ __('portal.spend.requester') }}</th>
            <th>{{ __('portal.spend.col_kind') }}</th>
            <th class="text-end">{{ __('portal.spend.col_amount') }}</th>
            <th>{{ __('portal.spend.col_for') }}</th>
            <th>{{ __('portal.spend.col_status') }}</th>
            <th>{{ __('portal.spend.col_date') }}</th>
        </tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr style="cursor:pointer;" onclick="window.location='{{ route('spend.show', $r) }}'">
                <td><a href="{{ route('spend.show', $r) }}" class="fw-bold text-decoration-none">{{ $r->title }}</a></td>
                <td>{{ $r->requester->name ?? '—' }}</td>
                <td>
                    <span class="badge bg-{{ $r->isPurchase() ? 'primary' : 'secondary' }}">{{ __('portal.spend.type.'.$r->type) }}</span>
                    <span class="badge bg-light text-dark">{{ __('portal.spend.scope.'.$r->scope) }}</span>
                </td>
                <td class="text-end">{{ number_format($r->effectiveAmount(), 2) }} {{ $r->currency }}</td>
                <td>{{ $r->isProject() ? ($r->project->title ?? '—') : ($r->category ?: __('portal.spend.general')) }}</td>
                <td><span class="badge bg-{{ $r->statusColor() }}">{{ $r->statusLabel() }}</span></td>
                <td><span class="text-muted small">{{ $r->created_at->translatedFormat('M j, Y') }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('portal.spend.none_match') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-2">{{ $requests->links() }}</div>
@endsection
