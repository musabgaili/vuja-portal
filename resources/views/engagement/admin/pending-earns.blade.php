@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.pending_earns'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-hourglass-half"></i> {{ __('engagement.admin.pending_earns') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('engagement.admin.pending_earns_sub') }}</p>
    </div>
    <a href="{{ route('engagement.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.dashboard') }}</a>
</div>


<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>{{ __('engagement.admin.client') }}</th>
            <th>{{ __('engagement.description') }}</th>
            <th>{{ __('engagement.date') }}</th>
            <th class="text-end">{{ __('engagement.points') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @forelse($earns as $t)
            <tr>
                <td>{{ $t->account->client?->name ?? '—' }}<div class="text-muted" style="font-size:.78rem;">{{ $t->account->client?->email }}</div></td>
                <td>{{ $t->description }}</td>
                <td style="white-space:nowrap;">{{ $t->created_at->format('Y-m-d') }}</td>
                <td class="text-end fw-bold" style="color:var(--primary-color);">+{{ number_format($t->points) }}</td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <form method="POST" action="{{ route('engagement.admin.earns.approve', $t) }}">@csrf
                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> {{ __('engagement.admin.approve') }}</button>
                        </form>
                        <form method="POST" action="{{ route('engagement.admin.earns.reject', $t) }}">@csrf
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-xmark"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted text-center py-4">{{ __('engagement.admin.no_pending_earns') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@if($earns->hasPages())<div class="mt-3">{{ $earns->links() }}</div>@endif
@endsection
