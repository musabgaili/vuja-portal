@extends('layouts.dashboard')

@section('title', 'Payment Upload')
@section('page-title', 'Upload Payment Confirmation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">💳 Payment Confirmation - {{ $idea->title }}</h3>
            </div>
            <div class="card-content">
                <!-- Quote Summary -->
                <div class="quote-summary mb-4">
                    <h4>Quote Summary</h4>
                    <div class="summary-item">
                        <span>Service:</span>
                        <strong>{{ $idea->title }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Amount:</span>
                        <strong class="amount">${{ number_format($idea->final_quote, 2) }}</strong>
                    </div>
                    @if($idea->agreement_terms)
                    <div class="summary-item">
                        <span>Terms:</span>
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
                        <strong>Payment Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Make payment of ${{ number_format($idea->final_quote, 2) }} to our account</li>
                            <li>Upload payment confirmation (receipt, screenshot, or bank statement)</li>
                            <li>Accepted formats: PDF, JPG, JPEG, PNG (max 10MB)</li>
                            <li>Our team will verify your payment within 24-48 hours</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Payment Confirmation *</label>
                        <input type="file" name="payment_file" class="form-control @error('payment_file') is-invalid @enderror" 
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('payment_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Payment Confirmation
                        </button>
                        <a href="{{ route('ideas.show', $idea) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Payment Confirmation Uploaded!</strong>
                    <p class="mb-0 mt-2">Your payment confirmation has been uploaded and is awaiting verification from our team.</p>
                </div>

                <div class="payment-status">
                    <div class="status-item">
                        <i class="fas fa-file-invoice"></i>
                        <div>
                            <strong>Payment File:</strong>
                            <span>Uploaded</span>
                        </div>
                    </div>
                    <div class="status-item">
                        <i class="fas {{ $idea->payment_verified_at ? 'fa-check-circle text-success' : 'fa-clock text-warning' }}"></i>
                        <div>
                            <strong>Verification Status:</strong>
                            <span>{{ $idea->payment_verified_at ? 'Verified ✓' : 'Pending Verification' }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('ideas.show', $idea) }}" class="btn btn-secondary mt-4">
                    <i class="fas fa-arrow-left"></i> Back to Request
                </a>
                @endif
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Payment Methods</h3>
            </div>
            <div class="card-content">
                <div class="payment-methods">
                    <div class="payment-method">
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>Bank Transfer</strong>
                            <p>Transfer directly to our bank account</p>
                        </div>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-credit-card"></i>
                        <div>
                            <strong>Credit/Debit Card</strong>
                            <p>Pay securely online (Coming Soon)</p>
                        </div>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-wallet"></i>
                        <div>
                            <strong>Digital Wallet</strong>
                            <p>PayPal, Stripe, etc. (Coming Soon)</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Online payment gateway integration is coming soon. Currently, please make bank transfer and upload confirmation.
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

