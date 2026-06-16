@extends('layouts.internal-dashboard')
@section('title', __('portal.quote.title'))

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-file-invoice"></i> {{ __('portal.quote.title') }}</h1>
        <a href="{{ route('scope-planner.index') }}" class="btn btn-light" style="font-weight:600;"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.quote.new_from_planner') }}</a>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table mb-0">
        <thead><tr><th>{{ __('portal.quote.quote') }}</th><th>{{ __('portal.quote.client') }}</th><th>{{ __('portal.quote.deal') }}</th><th class="text-end">{{ __('portal.quote.total') }}</th><th>{{ __('portal.quote.status') }}</th></tr></thead>
        <tbody>
        @forelse($quotes as $q)
            <tr>
                <td><a href="{{ route('quotes.show', $q) }}" style="font-weight:600;">#{{ $q->id }} · {{ $q->title }}</a></td>
                <td>{{ $q->client->name ?? '—' }}</td>
                <td>{{ $q->opportunity?->name ?? '—' }}</td>
                <td class="text-end">{{ number_format((float) $q->total_client, 0) }} {{ config('scope.currency','SAR') }}</td>
                <td><span class="badge bg-{{ $q->statusColor() }}">{{ $q->statusLabel() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="p-0">
                <x-empty-state icon="fa-file-invoice" :title="__('portal.quote.none')" :text="__('portal.quote.none_hint')">
                    <a href="{{ route('scope-planner.index') }}" class="btn btn-sm btn-primary mt-3"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.quote.new_from_planner') }}</a>
                </x-empty-state>
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-2">{{ $quotes->links() }}</div>
@endsection
