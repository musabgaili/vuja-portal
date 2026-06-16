@extends('layouts.internal-dashboard')
@section('title', $quote->title)

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-file-invoice"></i> #{{ $quote->id }} · {{ $quote->title }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9;">{{ $quote->client->name ?? __('portal.quote.no_client') }}@if($quote->opportunity) · {{ $quote->opportunity->name }}@endif</p>
        </div>
        <span class="badge bg-{{ $quote->statusColor() }}" style="font-size:.85rem;">{{ $quote->statusLabel() }}</span>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-lock"></i> {{ __('portal.quote.internal_view') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0">
                <thead><tr><th>{{ __('portal.quote.item') }}</th><th>{{ __('portal.quote.cost') }}</th><th>{{ __('portal.quote.markup') }}</th><th class="text-end">{{ __('portal.quote.qty') }}</th><th class="text-end">{{ __('portal.quote.price') }}</th></tr></thead>
                <tbody>
                @foreach($quote->items as $it)
                    <tr>
                        <td>{{ $it->name }} <span class="badge bg-secondary">{{ $it->category }}</span></td>
                        <td>{{ number_format((float) $it->internal_cost, 2) }} {{ config('scope.currency','SAR') }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $it->markup_percentage, 2), '0'), '.') }}%</td>
                        <td class="text-end">{{ $it->qty }}</td>
                        <td class="text-end">{{ number_format((float) $it->line_client, 2) }} {{ config('scope.currency','SAR') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="text-end">{{ __('portal.quote.internal_cost') }}</td><td class="text-end">{{ number_format((float) $quote->total_internal, 2) }} {{ config('scope.currency','SAR') }}</td></tr>
                    <tr><td colspan="4" class="text-end"><strong>{{ __('portal.quote.client_total') }}</strong></td><td class="text-end"><strong>{{ number_format((float) $quote->total_client, 2) }} {{ config('scope.currency','SAR') }}</strong></td></tr>
                    <tr><td colspan="4" class="text-end">{{ __('portal.quote.margin') }}</td><td class="text-end" style="color:var(--success-color);">{{ number_format($quote->margin(), 2) }} {{ config('scope.currency','SAR') }}</td></tr>
                </tfoot>
            </table>
        </div></div>

        @if($quote->scope)
            <div class="card mt-3"><div class="card-header"><span class="card-title">{{ __('portal.quote.scope') }}</span></div>
            <div class="card-content"><div style="white-space:pre-line;">{{ $quote->scope }}</div></div></div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card"><div class="card-content">
            <h3 class="card-title mb-3">{{ __('portal.quote.actions') }}</h3>
            @if($quote->isAccepted())
                <div class="alert alert-success" data-persist><i class="fas fa-circle-check"></i> {{ __('portal.quote.accepted_on') }} {{ optional($quote->accepted_at)->translatedFormat('M j, Y') }}<br><small>{{ __('portal.quote.signed_by') }}: {{ $quote->accepted_signature }}</small></div>
                @if(auth()->user()->isManager() || auth()->user()->isProjectManager())
                <a href="{{ route('invoices.create', ['quote' => $quote->id]) }}" class="btn btn-success w-100 mb-2"><i class="fas fa-file-invoice-dollar"></i> {{ __('portal.invoices.from_quote') }}</a>
                @endif
                @if($quote->project)<a href="{{ route('projects.manager.show', $quote->project) }}" class="btn btn-primary w-100 mb-2"><i class="fas fa-folder-open"></i> {{ __('portal.quote.open_order') }}</a>@endif
            @else
                {{-- Step 1: creator submits a draft (or a sent-back quote) for approval --}}
                @if($quote->isEditable())
                    <form method="POST" action="{{ route('quotes.submit', $quote) }}" class="mb-2">@csrf
                        <button class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> {{ __('portal.quote.submit_for_approval') }}</button>
                    </form>
                    @if($quote->status === 'changes_requested')
                        <div class="alert alert-warning" style="font-size:.85rem;">{{ __('portal.quote.changes_requested_note') }}</div>
                    @endif
                @endif

                {{-- Step 2: a manager / project manager reviews --}}
                @if($quote->status === 'pending_approval')
                    @if($canApprove)
                        <form method="POST" action="{{ route('quotes.approve', $quote) }}" class="mb-2">@csrf
                            <button class="btn btn-success w-100"><i class="fas fa-check"></i> {{ __('portal.quote.approve') }}</button>
                        </form>
                        <form method="POST" action="{{ route('quotes.request-changes', $quote) }}" class="mb-2">@csrf
                            <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" required placeholder="{{ __('portal.quote.changes_ph') }}"></textarea>
                            <button class="btn btn-outline-warning w-100"><i class="fas fa-rotate-left"></i> {{ __('portal.quote.request_changes') }}</button>
                        </form>
                        <form method="POST" action="{{ route('quotes.reject', $quote) }}" class="mb-2">@csrf
                            <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" required placeholder="{{ __('portal.quote.reject_ph') }}"></textarea>
                            <button class="btn btn-outline-danger w-100"><i class="fas fa-ban"></i> {{ __('portal.quote.reject') }}</button>
                        </form>
                    @else
                        <div class="alert alert-warning" style="font-size:.85rem;"><i class="fas fa-clock"></i> {{ __('portal.quote.awaiting_approval') }}</div>
                    @endif
                @endif

                {{-- Step 3: approved -> send to client --}}
                @if($quote->status === 'approved')
                    @if($quote->approver)<div class="text-muted small mb-2">{{ __('portal.quote.approved_by') }}: {{ $quote->approver->name }}</div>@endif
                    <form method="POST" action="{{ route('quotes.send', $quote) }}" class="mb-2">@csrf
                        <button class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> {{ __('portal.quote.send_to_client') }}</button>
                    </form>
                @endif

                @if($quote->status === 'sent')
                    <div class="alert alert-info" style="font-size:.85rem;"><i class="fas fa-hourglass-half"></i> {{ __('portal.quote.awaiting_client') }}</div>
                @endif

                @if($quote->status === 'rejected')
                    <div class="alert alert-danger" style="font-size:.85rem;">{{ __('portal.quote.was_rejected') }}@if($quote->reject_reason): {{ $quote->reject_reason }}@endif</div>
                @endif

                {{-- Accept on behalf only once the quote has cleared approval --}}
                @if($quote->isApproved())
                    <form method="POST" action="{{ route('quotes.accept-internal', $quote) }}" class="mb-2" onsubmit="return confirm('{{ __('portal.quote.confirm_accept') }}')">@csrf
                        <button class="btn btn-outline-primary w-100"><i class="fas fa-handshake"></i> {{ __('portal.quote.accept_onbehalf') }}</button>
                    </form>
                @endif
            @endif
            @if($quote->client)
                <div class="text-muted small mt-2">{{ __('portal.quote.client_link') }}:</div>
                <div style="word-break:break-all; font-size:.8rem;">{{ route('quotes.client.show', $quote) }}</div>
            @endif
            <a href="{{ route('quotes.index') }}" class="btn btn-secondary w-100 mt-2">{{ __('portal.quote.back') }}</a>
        </div></div>

        {{-- Client-facing preview --}}
        <div class="card mt-3"><div class="card-header"><span class="card-title"><i class="fas fa-user"></i> {{ __('portal.quote.client_view') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0">
                @foreach($quote->clientGrouped() as $cat => $amount)
                    <tr><td>{{ $cat }}</td><td class="text-end">{{ number_format($amount, 2) }} {{ config('scope.currency','SAR') }}</td></tr>
                @endforeach
                <tr><td><strong>{{ __('portal.quote.total') }}</strong></td><td class="text-end"><strong>{{ number_format((float) $quote->total_client, 2) }} {{ config('scope.currency','SAR') }}</strong></td></tr>
            </table>
        </div></div>

        {{-- Internal discussion / approval trail --}}
        <div class="card mt-3"><div class="card-header"><span class="card-title"><i class="fas fa-comments"></i> {{ __('portal.quote.comments') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('quotes.comments.store', $quote) }}" class="mb-3">@csrf
                <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" required placeholder="{{ __('portal.quote.comment_ph') }}"></textarea>
                <button class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> {{ __('portal.quote.add_comment') }}</button>
            </form>
            @forelse($quote->comments as $c)
                <div style="border-bottom:1px solid var(--gray-200); padding:.5rem 0;">
                    <div class="d-flex justify-content-between">
                        <strong style="font-size:.85rem;">{{ $c->user->name ?? '—' }}
                            @if($c->type !== 'note')<span class="badge bg-{{ $c->type === 'approval' ? 'success' : ($c->type === 'rejection' ? 'danger' : 'warning') }}">{{ __('portal.quote.ctype.'.$c->type) }}</span>@endif
                        </strong>
                        <small class="text-muted">{{ $c->created_at->diffForHumans() }}</small>
                    </div>
                    <div style="font-size:.9rem;">{{ $c->comment }}</div>
                </div>
            @empty
                <p class="text-muted" style="font-size:.85rem;">{{ __('portal.quote.no_comments') }}</p>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
