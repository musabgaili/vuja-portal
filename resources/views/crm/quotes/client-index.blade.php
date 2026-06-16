@extends('layouts.dashboard')
@section('title', __('portal.quote.my_quotes'))

@section('content')
<div class="page-hero"><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-file-invoice"></i> {{ __('portal.quote.my_quotes') }}</h1></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="row g-3">
@forelse($quotes as $q)
    <div class="col-md-6">
        <div class="card"><div class="card-content">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <h3 style="font-size:1.05rem; font-weight:600; margin:0;">{{ $q->title }}</h3>
                <span class="badge bg-{{ $q->statusColor() }}">{{ ucfirst($q->status) }}</span>
            </div>
            <div style="font-size:1.5rem; font-weight:800; color:var(--primary-color); margin:.5rem 0;">{{ number_format((float) $q->total_client, 2) }} {{ config('scope.currency','SAR') }}</div>
            <a href="{{ route('quotes.client.show', $q) }}" class="btn btn-primary btn-sm">{{ __('portal.quote.review') }} <i class="fas fa-arrow-right"></i></a>
        </div></div>
    </div>
@empty
    <div class="col-12"><div class="card"><div class="card-content text-muted text-center py-4">{{ __('portal.quote.none_client') }}</div></div></div>
@endforelse
</div>
@endsection
