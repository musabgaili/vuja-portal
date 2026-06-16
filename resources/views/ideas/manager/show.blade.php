@extends('layouts.internal-dashboard')
@section('title', __('portal.ideas.manager.show.page_title'))

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('ideas.manager.index') }}">{{ __('portal.ideas.manager.show.breadcrumb_parent') }}</a>
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
                                <strong><i class="fas fa-{{ $idea->client_type === 'company' ? 'building' : 'user' }}"></i> {{ __('portal.ideas.manager.show.client_type_label') }}</strong>
                                <span class="badge {{ $idea->client_type === 'company' ? 'bg-primary' : 'bg-secondary' }} ms-2">
                                    {{ __('portal.ideas.client_type.'.$idea->client_type) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong><i class="fas fa-info-circle"></i> {{ __('portal.ideas.manager.show.idea_status_label') }}</strong>
                                <span class="badge bg-info ms-2">
                                    {{ __('portal.ideas.idea_status.'.$idea->idea_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3"><strong>{{ __('portal.ideas.manager.show.description') }}</strong>
                        <p>{{ $idea->description }}</p>
                    </div>
                    <div class="mb-3"><strong>{{ __('portal.ideas.manager.show.target_market') }}</strong>
                        <p>{{ $idea->target_market }}</p>
                    </div>
                    <div class="mb-3"><strong>{{ __('portal.ideas.manager.show.problem_solving') }}</strong>
                        <p>{{ $idea->problem_solving }}</p>
                    </div>
                    <div class="mb-3"><strong>{{ __('portal.ideas.manager.show.unique_value') }}</strong>
                        <p>{{ $idea->unique_value }}</p>
                    </div>
                    @if ($idea->final_quote)
                        <div class="mb-3"><strong>{{ __('portal.ideas.manager.show.final_quote') }}</strong>
                            <p class="text-success">${{ number_format($idea->final_quote, 2) }}</p>
                        </div>
                    @endif
                    @if ($idea->quote_terms)
                        <div class="mb-3"><strong>{{ __('portal.ideas.manager.show.quote_terms') }}</strong>
                            <p>{{ $idea->quote_terms }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @if ($idea->comments->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('portal.ideas.manager.show.negotiation_history') }}</h3>
                    </div>
                    <div class="card-content">
                        @foreach ($idea->comments as $comment)
                            <div class="comment-item mb-3 p-3" style="background: var(--bg-tertiary); border-radius: 8px;">
                                <strong>{{ $comment->user->name }}</strong> <small
                                    class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                @if ($comment->is_internal)
                                    <span class="badge bg-warning">{{ __('portal.ideas.manager.show.internal_badge') }}</span>
                                @endif
                                <p class="mt-2">{{ $comment->comment }}</p>
                                @if ($comment->suggested_price)
                                    <p class="text-success"><strong>{{ __('portal.ideas.manager.show.suggested_price') }}
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
                    <h3>{{ __('portal.ideas.manager.show.client_info') }}</h3>
                </div>
                <div class="card-content">
                    <p><strong>{{ __('portal.ideas.manager.show.name') }}</strong> {{ $idea->user->name }}</p>
                    <p><strong>{{ __('portal.ideas.manager.show.email') }}</strong> <a href="mailto:{{ $idea->user->email }}">{{ $idea->user->email }}</a></p>
                    @if ($idea->user->phone)
                        <p><strong>{{ __('portal.ideas.manager.show.phone') }}</strong> <a href="tel:{{ $idea->user->phone }}">{{ $idea->user->phone }}</a></p>
                    @endif
                    <p><strong>{{ __('portal.ideas.manager.show.submitted') }}</strong> {{ $idea->created_at->translatedFormat('M d, Y H:i') }}</p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <h3>{{ __('portal.ideas.manager.show.actions') }}</h3>
                </div>
                <div class="card-content">
                    @if($idea->quote_status === 'pending_approval' && auth()->user()->isManager())
                        <button id="approveQuoteButton" class="btn btn-warning btn-block mb-2" onclick="approveQuote()"><i class="fas fa-check-circle"></i> {{ __('portal.ideas.manager.show.approve_quote') }}</button>
                        <a href="{{ route('ideas.quote.download', $idea) }}" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-file-pdf"></i> {{ __('portal.ideas.manager.show.view_quote_file') }}
                        </a>
                    @endif
                    
                    @if ($idea->isSubmitted() || $idea->isInNegotiation())
                        <button class="btn btn-primary btn-block mb-2" onclick="showQuoteModal()"><i class="fas fa-dollar-sign"></i> {{ __('portal.ideas.manager.show.upload_quote') }}</button>
                    @endif
                    
                    @if ($idea->isPaymentPending() && $idea->payment_file_path)
                        <button id="verifyPaymentButton" class="btn btn-success btn-block mb-2" onclick="verifyPayment()"><i class="fas fa-check"></i> {{ __('portal.ideas.manager.show.verify_payment') }}</button>
                    @endif
                    
                    @if (!$idea->assigned_to)
                        <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> {{ __('portal.ideas.manager.show.assign_employee') }}</button>
                    @elseif($idea->assignedTo)
                        <p><strong>{{ __('portal.ideas.manager.show.assigned_to') }}</strong> {{ $idea->assignedTo->name }}</p>
                    @endif
                    
                    @if($idea->isCompleted())
                        <form method="POST" action="{{ route('ideas.convert-to-project', $idea) }}" style="display:inline-block;width:100%;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-project-diagram"></i> {{ __('portal.ideas.manager.show.convert_project') }}
                            </button>
                        </form>
                    @endif
                    
                    @if($idea->isInNegotiation() || $idea->isQuoted() || $idea->isSubmitted())
                        <button class="btn btn-danger btn-block mb-2" onclick="showCloseModal()"><i class="fas fa-times-circle"></i> {{ __('portal.ideas.manager.show.close_lost') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="quoteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ __('portal.ideas.manager.show.upload_quote_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('ideas.send-quote', $idea) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('portal.ideas.manager.show.quote_amount_label') }}</label>
                            <input type="number" name="final_quote" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('portal.ideas.manager.show.quote_doc_label') }}</label>
                            <input type="file" name="quote_file" class="form-control" accept=".pdf,.doc,.docx" required>
                            <small class="text-muted">{{ __('portal.ideas.manager.show.quote_doc_help') }}</small>
                        </div>
                        @if(!auth()->user()->isManager())
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> {{ __('portal.ideas.manager.show.quote_approval_note') }}
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.ideas.manager.index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> {{ auth()->user()->isManager() ? __('portal.ideas.manager.show.submit_send_client') : __('portal.ideas.manager.show.submit_for_approval') }}
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
                    <h5>{{ __('portal.ideas.manager.show.assign_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('ideas.assign', $idea) }}">@csrf<div class="modal-body"><select
                            name="assigned_to" class="form-control" required>
                            <option value="">{{ __('portal.ideas.manager.show.select_employee') }}</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.ideas.manager.index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('portal.ideas.manager.show.assign') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="closeModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ __('portal.ideas.manager.show.close_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('ideas.close', $idea) }}">@csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('portal.ideas.manager.show.choose_status') }}</label>
                            <select name="status" class="form-control" required>
                                <option value="rejected">{{ __('portal.ideas.manager.show.close_status_rejected') }}</option>
                                <option value="cancelled">{{ __('portal.ideas.manager.show.close_status_cancelled') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __('portal.ideas.manager.show.close_reason_label') }}</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="{{ __('portal.ideas.manager.show.close_reason_placeholder') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.ideas.manager.index.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('portal.ideas.manager.show.close_request_submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const approveQuoteConfirm = @json(__('portal.ideas.manager.show.approve_quote_confirm'));
        const verifyPaymentConfirm = @json(__('portal.ideas.manager.show.verify_payment_confirm'));
        const managerActionError = @json(__('portal.projects_manager.show.error_marking_milestone'));

        function showQuoteModal() {
            new bootstrap.Modal(document.getElementById('quoteModal')).show();
        }

        function showAssignModal() {
            new bootstrap.Modal(document.getElementById('assignModal')).show();
        }

        function showCloseModal() {
            new bootstrap.Modal(document.getElementById('closeModal')).show();
        }

        async function submitManagerAction(url, confirmMessage, buttonId, payload = {}) {
            if (!confirm(confirmMessage)) {
                return;
            }

            const button = document.getElementById(buttonId);
            const originalHtml = button ? button.innerHTML : '';

            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' + originalHtml;
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || data.success === false) {
                    throw new Error(data.message || managerActionError);
                }

                if (buttonId === 'approveQuoteButton' && button) {
                    button.classList.remove('btn-warning');
                    button.classList.add('btn-success');
                }

                window.showAppToast(data.message, 'success');
                window.setTimeout(() => location.reload(), 600);
            } catch (error) {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }

                window.showAppToast(error.message || managerActionError, 'error');
            }
        }

        function approveQuote() {
            submitManagerAction('{{ route('ideas.approve-quote', $idea) }}', approveQuoteConfirm, 'approveQuoteButton');
        }

        function verifyPayment() {
            submitManagerAction('{{ route('ideas.verify-payment', $idea) }}', verifyPaymentConfirm, 'verifyPaymentButton', {
                action: 'approve'
            });
        }
    </script>
@endpush
