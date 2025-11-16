@extends('layouts.internal-dashboard')
@section('title', 'Idea Requests')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('ideas.manager.index') }}">Idea Generation</a></li>
<li class="breadcrumb-item active">All Requests</li>
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
            <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700;"><i class="fas fa-lightbulb"></i> Idea Requests</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">Manage and track all idea generation requests</p>
        </div>
        <div class="text-end">
            <h2 style="margin: 0; font-size: 2.5rem; font-weight: 700;">{{ $ideas->total() }}</h2>
            <small style="opacity: 0.9;">Total Requests</small>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="row align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold">Filter by Status</label>
            <select class="form-control" onchange="filterByStatus(this.value)">
                <option value="">All Status</option>
                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="negotiation" {{ request('status') == 'negotiation' ? 'selected' : '' }}>In Negotiation</option>
                <option value="quoted" {{ request('status') == 'quoted' ? 'selected' : '' }}>Quoted</option>
                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="payment_pending" {{ request('status') == 'payment_pending' ? 'selected' : '' }}>Payment Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        @if(request('status'))
        <div class="col-md-2">
            <a href="{{ route('ideas.manager.index') }}" class="btn btn-secondary w-100">
                <i class="fas fa-times"></i> Clear Filter
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
                <th>ID</th>
                <th>Title</th>
                <th>Client</th>
                <th>Status</th>
                <th>Quote</th>
                <th>Assigned To</th>
                <th>Submitted</th>
                <th>Actions</th>
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
                        {{ ucfirst($idea->client_type) }}
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
                        <span class="text-muted">Not assigned</span>
                    @endif
                </td>
                <td>{{ $idea->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="action-btn-group">
                        <a href="{{ route('ideas.manager.show', $idea) }}" class="btn btn-sm btn-secondary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($idea->isInNegotiation() || $idea->isSubmitted())
                        <button class="btn btn-sm btn-primary" onclick="showQuoteModal({{ $idea->id }})" title="Send Quote">
                            <i class="fas fa-dollar-sign"></i>
                        </button>
                        @endif
                        @if($idea->isPaymentPending())
                        <button class="btn btn-sm btn-success" onclick="verifyPayment({{ $idea->id }}, 'approve')" title="Verify Payment">
                            <i class="fas fa-check"></i>
                        </button>
                        @endif
                        @if($idea->isApproved() && !$idea->assignedTo)
                        <button class="btn btn-sm btn-info" onclick="showAssignModal({{ $idea->id }})" title="Assign">
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
    <h4 style="color: #1e293b; font-weight: 600;">No Idea Requests</h4>
    <p class="text-muted">No idea requests have been submitted yet.</p>
</div>
@endif

<!-- Quote Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-dollar-sign"></i> Send Quote</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="quoteForm" action="">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Final Quote Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="final_quote" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label fw-bold">Agreement Terms *</label>
                        <textarea name="agreement_terms" rows="4" class="form-control" required placeholder="Enter agreement terms and conditions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Quote</button>
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
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Assign to Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-bold">Select Employee *</label>
                        <select name="assigned_to" class="form-control" required>
                            <option value="">Choose an employee...</option>
                            @foreach(\App\Models\User::where('role', 'employee')->get() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-check"></i> Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showQuoteModal(ideaId) {
    document.getElementById('quoteForm').action = `/internal/ideas/${ideaId}/send-quote`;
    new bootstrap.Modal(document.getElementById('quoteModal')).show();
}

function showAssignModal(ideaId) {
    document.getElementById('assignForm').action = `/internal/ideas/${ideaId}/assign`;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function verifyPayment(ideaId, action) {
    if (confirm('Are you sure you want to ' + action + ' this payment?')) {
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
