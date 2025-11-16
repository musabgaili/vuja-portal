@extends('layouts.dashboard')

@section('title', 'Price Negotiation')
@section('page-title', 'Negotiate Terms')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">💬 Price Negotiation - {{ $idea->title }}</h3>
                <span class="status-badge {{ $idea->getStatusBadgeColor() }}">
                    {{ $idea->getStatusLabel() }}
                </span>
            </div>
            <div class="card-content">
                <!-- Conversation Thread -->
                <div class="conversation-thread">
                    @forelse($idea->comments()->orderBy('created_at')->get() as $comment)
                    <div class="message {{ $comment->user_id === auth()->id() ? 'message-sent' : 'message-received' }}">
                        <div class="message-avatar">
                            {{ strtoupper(substr($comment->user->name, 0, 2)) }}
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <strong>{{ $comment->user->name }}</strong>
                                <span class="message-role">{{ ucfirst($comment->user->role->value) }}</span>
                                <span class="message-time">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="message-body">
                                {{ $comment->comment }}
                            </div>
                            @if($comment->suggested_price)
                            <div class="message-price">
                                <i class="fas fa-tag"></i> Suggested Price: <strong>${{ number_format($comment->suggested_price, 2) }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                    @endforelse
                </div>

                <!-- New Message Form -->
                <div class="message-form">
                    <form method="POST" action="{{ route('ideas.comments.store', $idea) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Your Message</label>
                            <textarea name="comment" rows="3" class="form-control @error('comment') is-invalid @enderror" 
                                      placeholder="Type your message or counter-offer..." required></textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Suggested Price (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="suggested_price" class="form-control @error('suggested_price') is-invalid @enderror" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                            @error('suggested_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Idea Summary</h3>
            </div>
            <div class="card-content">
                <h5>{{ $idea->title }}</h5>
                <p class="text-muted">{{ Str::limit($idea->description, 150) }}</p>
                
                @if($idea->final_quote)
                <div class="current-quote">
                    <strong>Current Quote:</strong>
                    <div class="quote-amount">${{ number_format($idea->final_quote, 2) }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tips for Negotiation</h3>
            </div>
            <div class="card-content">
                <ul class="tips-list">
                    <li><i class="fas fa-check"></i> Be clear about your budget and expectations</li>
                    <li><i class="fas fa-check"></i> Ask questions if anything is unclear</li>
                    <li><i class="fas fa-check"></i> Suggest realistic counter-offers</li>
                    <li><i class="fas fa-check"></i> Response time: typically 24-48 hours</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.conversation-thread {
    max-height: 500px;
    overflow-y: auto;
    padding: var(--space-md);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-lg);
}

.message {
    display: flex;
    margin-bottom: var(--space-md);
}

.message-sent {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.message-sent .message-avatar {
    background: var(--success-color);
    margin-left: var(--space-sm);
}

.message-received .message-avatar {
    margin-right: var(--space-sm);
}

.message-content {
    max-width: 70%;
    background: white;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-light);
}

.message-sent .message-content {
    background: #e3f2fd;
}

.message-header {
    display: flex;
    gap: var(--space-sm);
    align-items: center;
    margin-bottom: var(--space-xs);
    flex-wrap: wrap;
}

.message-role {
    font-size: var(--font-size-xs);
    padding: 2px 8px;
    background: var(--gray-200);
    border-radius: var(--radius-sm);
    color: var(--gray-700);
}

.message-time {
    font-size: var(--font-size-xs);
    color: var(--gray-500);
    margin-left: auto;
}

.message-body {
    color: var(--text-color);
    line-height: 1.5;
}

.message-price {
    margin-top: var(--space-sm);
    padding: var(--space-sm);
    background: #d1fae5;
    border-radius: var(--radius-sm);
    color: #065f46;
}

.message-form {
    background: white;
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.empty-state {
    text-align: center;
    padding: var(--space-2xl);
}

.current-quote {
    margin-top: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
}

.quote-amount {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--primary-color);
    margin-top: var(--space-xs);
}

.tips-list {
    list-style: none;
    padding: 0;
}

.tips-list li {
    padding: var(--space-xs) 0;
    color: var(--gray-700);
}

.tips-list i {
    color: var(--success-color);
    margin-right: var(--space-xs);
}
</style>
@endpush

