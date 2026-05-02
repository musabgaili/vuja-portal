@extends('layouts.dashboard')

@section('title', __('portal.ideas.payment.page_title'))
@section('page-title', __('portal.ideas.payment.page_heading'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">💳 {{ __('portal.ideas.payment.title', ['title' => $idea->title]) }}</h3>
            </div>
            <div class="card-content">
                <!-- Quote Summary -->
                <div class="quote-summary mb-4">
                    <h4>{{ __('portal.ideas.payment.quote_summary') }}</h4>
                    <div class="summary-item">
                        <span>{{ __('portal.ideas.payment.service') }}</span>
                        <strong>{{ $idea->title }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>{{ __('portal.ideas.payment.amount') }}</span>
                        <strong class="amount">${{ number_format($idea->final_quote, 2) }}</strong>
                    </div>
                    @if($idea->agreement_terms)
                    <div class="summary-item">
                        <span>{{ __('portal.ideas.payment.terms') }}</span>
                        <p class="terms">{{ $idea->agreement_terms }}</p>
                    </div>
                    @endif
                </div>

                <!-- Payment Upload Form -->
                @if(!$idea->payment_file)
                <form method="POST" action="{{ route('ideas.payment.upload', $idea) }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('portal.ideas.payment.instructions_title') }}</strong>
                        <ul class="mb-0 mt-2">
                            <li>{{ __('portal.ideas.payment.instruction_pay', ['amount' => '$'.number_format($idea->final_quote, 2)]) }}</li>
                            <li>{{ __('portal.ideas.payment.instruction_upload') }}</li>
                            <li>{{ __('portal.ideas.payment.instruction_formats') }}</li>
                            <li>{{ __('portal.ideas.payment.instruction_verify') }}</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('portal.ideas.payment.upload_label') }}</label>
                        <input type="file" name="payment_file" class="form-control @error('payment_file') is-invalid @enderror" 
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('payment_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> {{ __('portal.ideas.payment.upload_confirm') }}
                        </button>
                        <a href="{{ route('ideas.show', $idea) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('portal.ideas.payment.back') }}
                        </a>
                    </div>
                </form>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>{{ __('portal.ideas.payment.uploaded_title') }}</strong>
                    <p class="mb-0 mt-2">{{ __('portal.ideas.payment.uploaded_body') }}</p>
                </div>

                <div class="payment-status">
                    <div class="status-item">
                        <i class="fas fa-file-invoice"></i>
                        <div>
                            <strong>{{ __('portal.ideas.payment.payment_file') }}</strong>
                            <span>{{ __('portal.ideas.payment.file_uploaded') }}</span>
                        </div>
                    </div>
                    <div class="status-item">
                        <i class="fas {{ $idea->payment_verified_at ? 'fa-check-circle text-success' : 'fa-clock text-warning' }}"></i>
                        <div>
                            <strong>{{ __('portal.ideas.payment.verification_label') }}</strong>
                            <span>{{ $idea->payment_verified_at ? __('portal.ideas.payment.verified') : __('portal.ideas.payment.pending_verification') }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('ideas.show', $idea) }}" class="btn btn-secondary mt-4">
                    <i class="fas fa-arrow-left"></i> {{ __('portal.ideas.payment.back_request') }}
                </a>
                @endif
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.ideas.payment.methods_title') }}</h3>
            </div>
            <div class="card-content">
                <div class="payment-methods">
                    <div class="payment-method">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>{{ __('portal.ideas.payment.bank_title') }}</strong>
                            <p>{{ __('portal.ideas.payment.bank_desc') }}</p>
                        </div>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <strong>{{ __('portal.ideas.payment.card_title') }}</strong>
                            <p>{{ __('portal.ideas.payment.card_desc') }}</p>
                        </div>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-wallet"></i>
                        <div>
                            <strong>{{ __('portal.ideas.payment.wallet_title') }}</strong>
                            <p>{{ __('portal.ideas.payment.wallet_desc') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ __('portal.service_requests_page.create.integration_note_title') }}</strong> {{ __('portal.ideas.payment.gateway_note') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.quote-summary {
    background: var(--bg-tertiary);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
}

.quote-summary h4 {
    margin-bottom: var(--space-md);
    color: var(--text-color);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--gray-200);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .amount {
    font-size: var(--font-size-xl);
    color: var(--primary-color);
}

.summary-item .terms {
    margin: var(--space-sm) 0 0 0;
    color: var(--gray-600);
    text-align: right;
}

.payment-status {
    background: var(--bg-tertiary);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
}

.status-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md) 0;
    border-bottom: 1px solid var(--gray-200);
}

.status-item:last-child {
    border-bottom: none;
}

.status-item i {
    font-size: var(--font-size-xl);
    color: var(--primary-color);
}

.payment-methods {
    display: grid;
    gap: var(--space-md);
}

.payment-method {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
}

.payment-method i {
    font-size: var(--font-size-2xl);
    color: var(--primary-color);
}

.payment-method p {
    margin: 0;
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}
</style>
@endpush
