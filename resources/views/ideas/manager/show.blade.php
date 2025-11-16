@extends('layouts.internal-dashboard')
@section('title', 'Idea Request Details')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('ideas.manager.index') }}">Idea Generation</a>
    </li>
    <li class="breadcrumb-item active">#{{ $idea->id }} - {{ Str::limit($idea->title, 30) }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>{{ $idea->title }}</h3>
                    <span class="status-badge {{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span>
                </div>
                <div class="card-content">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><i class="fas fa-{{ $idea->client_type === 'company' ? 'building' : 'user' }}"></i> Client Type:</strong>
                                <span class="badge {{ $idea->client_type === 'company' ? 'bg-primary' : 'bg-secondary' }} ms-2">
                                    {{ ucfirst($idea->client_type) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><i class="fas fa-info-circle"></i> Idea Status:</strong>
                                <span class="badge bg-info ms-2">
                                    {{ match($idea->idea_status) {
                                        'seeking_around' => 'Seeking Around',
                                        'ready' => 'Ready',
                                        'running_project' => 'Running Project',
                                        'concept_only' => 'Concept Only',
                                        default => ucfirst($idea->idea_status)
                                    } }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3"><strong>Description:</strong>
                        <p>{{ $idea->description }}</p>
                    </div>
                    <div class="mb-3"><strong>Target Market:</strong>
                        <p>{{ $idea->target_market }}</p>
                    </div>
                    <div class="mb-3"><strong>Problem Solving:</strong>
                        <p>{{ $idea->problem_solving }}</p>
                    </div>
                    <div class="mb-3"><strong>Unique Value:</strong>
                        <p>{{ $idea->unique_value }}</p>
                    </div>
                    @if ($idea->final_quote)
                        <div class="mb-3"><strong>Final Quote:</strong>
                            <p class="text-success">${{ number_format($idea->final_quote, 2) }}</p>
                        </div>
                    @endif
                    @if ($idea->quote_terms)
                        <div class="mb-3"><strong>Quote Terms:</strong>
                            <p>{{ $idea->quote_terms }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @if ($idea->comments->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3>Negotiation History</h3>
                    </div>
                    <div class="card-content">
                        @foreach ($idea->comments as $comment)
                            <div class="comment-item mb-3 p-3" style="background: var(--bg-tertiary); border-radius: 8px;">
                                <strong>{{ $comment->user->name }}</strong> <small
                                    class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                @if ($comment->is_internal)
                                    <span class="badge bg-warning">Internal</span>
                                @endif
                                <p class="mt-2">{{ $comment->comment }}</p>
                                @if ($comment->suggested_price)
                                    <p class="text-success"><strong>Suggested Price:
                                            ${{ number_format($comment->suggested_price, 2) }}</strong></p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Client Information</h3>
                </div>
                <div class="card-content">
                    <p><strong>Name:</strong> {{ $idea->user->name }}</p>
                    <p><strong>Email:</strong> <a href="mailto:{{ $idea->user->email }}">{{ $idea->user->email }}</a></p>
                    @if ($idea->user->phone)
                        <p><strong>Phone:</strong> <a href="tel:{{ $idea->user->phone }}">{{ $idea->user->phone }}</a></p>
                    @endif
                    <p><strong>Submitted:</strong> {{ $idea->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-content">
                    @if($idea->quote_status === 'pending_approval' && auth()->user()->isManager())
                        <button class="btn btn-warning btn-block mb-2" onclick="approveQuote()"><i class="fas fa-check-circle"></i> Approve Quote</button>
                        <a href="{{ asset('storage/' . $idea->quote_file_path) }}" target="_blank" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-file-pdf"></i> View Quote File
                        </a>
                    @endif
                    
                    @if ($idea->isSubmitted() || $idea->isInNegotiation())
                        <button class="btn btn-primary btn-block mb-2" onclick="showQuoteModal()"><i class="fas fa-dollar-sign"></i> Upload Quote</button>
                    @endif
                    
                    @if ($idea->isPaymentPending() && $idea->payment_file_path)
                        <button class="btn btn-success btn-block mb-2" onclick="verifyPayment()"><i class="fas fa-check"></i> Verify Payment</button>
                    @endif
                    
                    @if (!$idea->assigned_to)
                        <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> Assign Employee</button>
                    @elseif($idea->assignedTo)
                        <p><strong>Assigned to:</strong> {{ $idea->assignedTo->name }}</p>
                    @endif
                    
                    @if($idea->isCompleted())
                        <form method="POST" action="{{ route('ideas.convert-to-project', $idea) }}" style="display:inline-block;width:100%;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-project-diagram"></i> Convert to Project
                            </button>
                        </form>
                    @endif
                    
                    @if($idea->isInNegotiation() || $idea->isQuoted() || $idea->isSubmitted())
                        <button class="btn btn-danger btn-block mb-2" onclick="showCloseModal()"><i class="fas fa-times-circle"></i> Close/Lost</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="quoteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Upload Quote</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('ideas.send-quote', $idea) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Quote Amount ($) *</label>
                            <input type="number" name="final_quote" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Quote Document (PDF/DOC) *</label>
                            <input type="file" name="quote_file" class="form-control" accept=".pdf,.doc,.docx" required>
                            <small class="text-muted">Upload complete quote with all terms</small>
                        </div>
                        @if(!auth()->user()->isManager())
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> Quote will be sent to manager for approval first.
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> {{ auth()->user()->isManager() ? 'Send to Client' : 'Submit for Approval' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assignModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Assign Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('ideas.assign', $idea) }}">@csrf<div class="modal-body"><select
                            name="assigned_to" class="form-control" required>
                            <option value="">Select employee...</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select></div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Assign</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="closeModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Close Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('ideas.close', $idea) }}">@csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="rejected">Rejected/Lost</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Why is this being closed?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-danger">Close Request</button></div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function showQuoteModal() {
            new bootstrap.Modal(document.getElementById('quoteModal')).show();
        }

        function showAssignModal() {
            new bootstrap.Modal(document.getElementById('assignModal')).show();
        }

        function showCloseModal() {
            new bootstrap.Modal(document.getElementById('closeModal')).show();
        }

        function approveQuote() {
            if (confirm('Approve this quote and send to client?')) {
                fetch('{{ route('ideas.approve-quote', $idea) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(() => location.reload());
            }
        }

        function verifyPayment() {
            if (confirm('Verify payment received?')) {
                fetch('{{ route('ideas.verify-payment', $idea) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(() => location.reload());
            }
        }
    </script>
@endpush
