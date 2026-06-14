@extends('layouts.internal-dashboard')
@section('title', $quote->title)

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-file-invoice"></i> #{{ $quote->id }} · {{ $quote->title }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9;">{{ $quote->client->name ?? __('portal.quote.no_client') }}@if($quote->opportunity) · {{ $quote->opportunity->name }}@endif</p>
        </div>
        <span class="badge bg-{{ $quote->statusColor() }}" style="font-size:.85rem;">{{ ucfirst($quote->status) }}</span>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

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
                        <td>${{ number_format((float) $it->internal_cost, 2) }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $it->markup_percentage, 2), '0'), '.') }}%</td>
                        <td class="text-end">{{ $it->qty }}</td>
                        <td class="text-end">${{ number_format((float) $it->line_client, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="text-end">{{ __('portal.quote.internal_cost') }}</td><td class="text-end">${{ number_format((float) $quote->total_internal, 2) }}</td></tr>
                    <tr><td colspan="4" class="text-end"><strong>{{ __('portal.quote.client_total') }}</strong></td><td class="text-end"><strong>${{ number_format((float) $quote->total_client, 2) }}</strong></td></tr>
                    <tr><td colspan="4" class="text-end">{{ __('portal.quote.margin') }}</td><td class="text-end" style="color:var(--success-color);">${{ number_format($quote->margin(), 2) }}</td></tr>
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
                <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ __('portal.quote.accepted_on') }} {{ optional($quote->accepted_at)->format('M j, Y') }}<br><small>{{ __('portal.quote.signed_by') }}: {{ $quote->accepted_signature }}</small></div>
                @if($quote->project)<a href="{{ route('projects.manager.show', $quote->project) }}" class="btn btn-primary w-100 mb-2"><i class="fas fa-folder-open"></i> {{ __('portal.quote.open_order') }}</a>@endif
            @else
                @if($quote->status === 'draft')
                    <form method="POST" action="{{ route('quotes.send', $quote) }}" class="mb-2">@csrf
                        <button class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> {{ __('portal.quote.mark_sent') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('quotes.accept-internal', $quote) }}" class="mb-2" onsubmit="return confirm('{{ __('portal.quote.confirm_accept') }}')">@csrf
                    <button class="btn btn-outline-primary w-100"><i class="fas fa-handshake"></i> {{ __('portal.quote.accept_onbehalf') }}</button>
                </form>
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
                    <tr><td>{{ $cat }}</td><td class="text-end">${{ number_format($amount, 2) }}</td></tr>
                @endforeach
                <tr><td><strong>{{ __('portal.quote.total') }}</strong></td><td class="text-end"><strong>${{ number_format((float) $quote->total_client, 2) }}</strong></td></tr>
            </table>
        </div></div>
    </div>
</div>
@endsection
