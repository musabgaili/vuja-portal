@extends('layouts.dashboard')
@section('title', $quote->title)

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-file-invoice"></i> {{ $quote->title }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.quote.proposal_for_you') }}</p>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="row g-3">
    <div class="col-lg-7">
        @if($quote->scope)
            <div class="card mb-3"><div class="card-header"><span class="card-title">{{ __('portal.quote.scope') }}</span></div>
            <div class="card-content"><div style="white-space:pre-line;">{{ $quote->scope }}</div></div></div>
        @endif

        <div class="card"><div class="card-header"><span class="card-title">{{ __('portal.quote.investment') }}</span></div>
        <div class="card-content p-0" style="overflow-x:auto;">
            <table class="table mb-0">
                <thead><tr><th>{{ __('portal.quote.category') }}</th><th class="text-end">{{ __('portal.quote.amount') }}</th></tr></thead>
                <tbody>
                @foreach($quote->clientGrouped() as $cat => $amount)
                    <tr><td>{{ $cat }}</td><td class="text-end">{{ number_format($amount, 2) }} {{ config('scope.currency','SAR') }}</td></tr>
                @endforeach
                </tbody>
                <tfoot><tr><td><strong>{{ __('portal.quote.total') }}</strong></td><td class="text-end"><strong>{{ number_format((float) $quote->total_client, 2) }} {{ config('scope.currency','SAR') }}</strong></td></tr></tfoot>
            </table>
        </div></div>
    </div>

    <div class="col-lg-5">
        <div class="card"><div class="card-content">
            @if($quote->isAccepted())
                <div class="alert alert-success mb-0" data-persist><i class="fas fa-circle-check"></i> {{ __('portal.quote.you_accepted') }} {{ optional($quote->accepted_at)->format('M j, Y') }}.<br><small>{{ __('portal.quote.signed_by') }}: {{ $quote->accepted_signature }}</small></div>
            @elseif($quote->status === 'sent')
                <h3 class="card-title mb-2">{{ __('portal.quote.accept_title') }}</h3>
                <p class="text-muted" style="font-size:.85rem;">{{ __('portal.quote.accept_help') }}</p>
                <form method="POST" action="{{ route('quotes.client.accept', $quote) }}">@csrf
                    <input type="text" name="signature" class="form-control mb-2" maxlength="120" placeholder="{{ __('portal.quote.type_name') }}" required>
                    <button class="btn btn-primary w-100"><i class="fas fa-signature"></i> {{ __('portal.quote.accept_btn') }}</button>
                </form>
                <form method="POST" action="{{ route('quotes.client.reject', $quote) }}" class="mt-2">@csrf
                    <input type="text" name="reject_reason" class="form-control mb-2" placeholder="{{ __('portal.quote.decline_reason') }}">
                    <button class="btn btn-outline-primary w-100">{{ __('portal.quote.decline_btn') }}</button>
                </form>
            @else
                <div class="alert alert-secondary mb-0">{{ __('portal.quote.not_available') }}</div>
            @endif
        </div></div>
    </div>
</div>
@endsection
