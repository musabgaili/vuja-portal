@extends('layouts.internal-dashboard')
@section('title', 'Scope Change Requests')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">Projects</a></li>
<li class="breadcrumb-item active">Scope Changes</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Pending Scope Change Requests</h3>
    </div>
    <div class="card-content">
        @forelse($scopeChanges as $change)
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5>{{ $change->title }}</h5>
                        <small class="text-muted">
                            Project: <a href="{{ route('projects.manager.show', $change->project) }}">{{ $change->project->title }}</a>
                        </small>
                    </div>
                    <span class="status-badge {{ $change->getStatusBadgeColor() }}">{{ ucfirst($change->status) }}</span>
                </div>
            </div>
            <div class="card-content">
                <p><strong>Description:</strong></p>
                <p>{{ $change->description }}</p>
                
                @if($change->justification)
                <p><strong>Justification:</strong></p>
                <p class="text-muted">{{ $change->justification }}</p>
                @endif
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small>
                        Requested by: <strong>{{ $change->requestedBy->name }}</strong> 
                        on {{ $change->created_at->format('M d, Y') }}
                    </small>
                    
                    @if($change->isPending())
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="showApproveModal({{ $change->id }})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="showRejectModal({{ $change->id }})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal-{{ $change->id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Approve Scope Change</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('projects.scope-changes.approve', $change) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Review Notes (Optional)</label>
                                <textarea name="review_notes" rows="3" class="form-control" placeholder="Add any notes about this approval..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal-{{ $change->id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Reject Scope Change</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('projects.scope-changes.reject', $change) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Reason for Rejection *</label>
                                <textarea name="review_notes" rows="4" class="form-control" required placeholder="Explain why this change cannot be approved..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-4">No pending scope change requests.</p>
        @endforelse
        
        {{ $scopeChanges->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
@push('scripts')
<script>
function showApproveModal(id){new bootstrap.Modal(document.getElementById('approveModal-'+id)).show();}
function showRejectModal(id){new bootstrap.Modal(document.getElementById('rejectModal-'+id)).show();}
</script>
@endpush

