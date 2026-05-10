@extends('layouts.internal-dashboard')
@section('title', __('portal.ideas.manager.index.heading'))
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('ideas.manager.index') }}">{{ __('portal.ideas.manager.index.breadcrumb_parent') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.ideas.manager.index.all_requests_breadcrumb') }}</li>
@endsection

@section('content')
<style>
.ideas-header {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(251, 191, 36, 0.3);
}
.filter-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}
.table-modern {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.table-modern thead {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}
.table-modern th {
    padding: 1rem;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table-modern td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.table-modern tbody tr {
    transition: all 0.2s;
}
.table-modern tbody tr:hover {
    background: #f8fafc;
}
.action-btn-group {
    display: flex;
    gap: 0.375rem;
}
.action-btn-group .btn {
    padding: 0.375rem 0.625rem;
    font-size: 0.875rem;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}
</style>

<div class="ideas-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700;"><i class="fas fa-lightbulb"></i> {{ __('portal.ideas.manager.index.heading') }}</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">{{ __('portal.ideas.manager.index.subtitle') }}</p>
        </div>
        <div class="text-end">
            <h2 style="margin: 0; font-size: 2.5rem; font-weight: 700;">{{ $ideas->total() }}</h2>
            <small style="opacity: 0.9;">{{ __('portal.ideas.manager.index.total_label') }}</small>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="row align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold">{{ __('portal.ideas.manager.index.filter_label') }}</label>
            <select class="form-control" onchange="filterByStatus(this.value)">
                <option value="">{{ __('portal.ideas.manager.index.filter_all') }}</option>
                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_submitted') }}</option>
                <option value="negotiation" {{ request('status') == 'negotiation' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_negotiation') }}</option>
                <option value="quoted" {{ request('status') == 'quoted' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_quoted') }}</option>
                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_accepted') }}</option>
                <option value="payment_pending" {{ request('status') == 'payment_pending' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_payment_pending') }}</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_approved') }}</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_in_progress') }}</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('portal.ideas.manager.index.filter_completed') }}</option>
            </select>
        </div>
        @if(request('status'))
        <div class="col-md-2">
            <a href="{{ route('ideas.manager.index') }}" class="btn btn-secondary w-100">
                <i class="fas fa-times"></i> {{ __('portal.ideas.manager.index.clear_filter') }}
            </a>
        </div>
        @endif
    </div>
</div>

@if($ideas->count() > 0)
<div class="table-modern">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>{{ __('portal.ideas.manager.index.col_id') }}</th>
                <th>{{ __('portal.ideas.manager.index.col_title') }}</th>
                <th>{{ __('portal.ideas.manager.index.col_client') }}</th>
                <th>{{ __('portal.ideas.manager.index.col_status') }}</th>
                <th>{{ __('portal.ideas.manager.index.col_quote') }}</th>
                <th>{{ __('portal.ideas.manager.index.col_assigned') }}</th>
                <th>{{ __('portal.ideas.manager.index.col_submitted') }}</th>
                <th>{{ __('portal.ideas.manager.index.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ideas as $idea)
            <tr>
                <td><strong style="color: #3b82f6;">#{{ $idea->id }}</strong></td>
                <td>
                    <strong style="color: #1e293b;">{{ $idea->title }}</strong>
                    <br>
                    <small class="text-muted">{{ Str::limit($idea->description, 60) }}</small>
                </td>
                <td>
                    <strong>{{ $idea->user->name }}</strong>
                    <span class="badge {{ $idea->client_type === 'company' ? 'bg-primary' : 'bg-secondary' }} ms-2">
                        <i class="fas fa-{{ $idea->client_type === 'company' ? 'building' : 'user' }}"></i>
                        {{ __('portal.ideas.client_type.'.$idea->client_type) }}
                    </span>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-envelope"></i> {{ $idea->user->email }}
                        @if($idea->user->phone)
                        <br><i class="fas fa-phone"></i> {{ $idea->user->phone }}
                        @endif
                    </small>
                </td>
                <td>
                    <span class="status-badge {{ $idea->getStatusBadgeColor() }}">
                        {{ $idea->getStatusLabel() }}
                    </span>
                </td>
                <td>
                    @if($idea->final_quote)
                        <strong style="color: #10b981;">${{ number_format($idea->final_quote, 2) }}</strong>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($idea->assignedTo)
                        <span class="badge bg-info">{{ $idea->assignedTo->name }}</span>
                    @else
                        <span class="text-muted">{{ __('portal.ideas.manager.index.not_assigned') }}</span>
                    @endif
                </td>
                <td>{{ $idea->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="action-btn-group">
                        <a href="{{ route('ideas.manager.show', $idea) }}" class="btn btn-sm btn-secondary" title="{{ __('portal.ideas.manager.index.title_view') }}">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($idea->isInNegotiation() || $idea->isSubmitted())
                        <button class="btn btn-sm btn-primary" onclick="showQuoteModal(@js($idea->getRouteKey()))" title="{{ __('portal.ideas.manager.index.title_send_quote') }}">
                            <i class="fas fa-dollar-sign"></i>
                        </button>
                        @endif
                        @if($idea->isPaymentPending())
                        <button class="btn btn-sm btn-success" onclick="verifyPayment(@js($idea->getRouteKey()), 'approve')" title="{{ __('portal.ideas.manager.index.title_verify_payment') }}">
                            <i class="fas fa-check"></i>
                        </button>
                        @endif
                        @if($idea->isApproved() && !$idea->assignedTo)
                        <button class="btn btn-sm btn-info" onclick="showAssignModal(@js($idea->getRouteKey()))" title="{{ __('portal.ideas.manager.index.title_assign') }}">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $ideas->links('pagination::bootstrap-5') }}
</div>
@else
<div class="empty-state">
    <i class="fas fa-lightbulb"></i>
    <h4 style="color: #1e293b; font-weight: 600;">{{ __('portal.ideas.manager.index.empty_title') }}</h4>
    <p class="text-muted">{{ __('portal.ideas.manager.index.empty_body') }}</p>
</div>
@endif

<!-- Quote Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-dollar-sign"></i> {{ __('portal.ideas.manager.index.quote_modal_title') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="quoteForm" action="">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.ideas.manager.index.final_quote_label') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="final_quote" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label fw-bold">{{ __('portal.ideas.manager.index.agreement_terms_label') }}</label>
                        <textarea name="agreement_terms" rows="4" class="form-control" required placeholder="{{ __('portal.ideas.manager.index.agreement_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.ideas.manager.index.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.ideas.manager.index.send_quote') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> {{ __('portal.ideas.manager.index.assign_modal_title') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-bold">{{ __('portal.ideas.manager.index.select_employee') }}</label>
                        <select name="assigned_to" class="form-control" required>
                            <option value="">{{ __('portal.ideas.manager.index.choose_employee') }}</option>
                            @foreach(\App\Models\User::where('role', 'employee')->get() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.ideas.manager.index.cancel') }}</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-check"></i> {{ __('portal.ideas.manager.index.assign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const verifyPaymentConfirm = @json(__('portal.ideas.manager.index.verify_payment_confirm'));

function showQuoteModal(ideaId) {
    document.getElementById('quoteForm').action = `/internal/ideas/${ideaId}/send-quote`;
    new bootstrap.Modal(document.getElementById('quoteModal')).show();
}

function showAssignModal(ideaId) {
    document.getElementById('assignForm').action = `/internal/ideas/${ideaId}/assign`;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function verifyPayment(ideaId, action) {
    if (confirm(verifyPaymentConfirm)) {
        fetch(`/internal/ideas/${ideaId}/verify-payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ action: action })
        }).then(() => location.reload());
    }
}

function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}
</script>
@endpush
