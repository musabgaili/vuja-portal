@extends('layouts.internal-dashboard')
@section('title', __('engagement.admin.accounts'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-users"></i> {{ __('engagement.admin.accounts') }}</h1>
    </div>
    <a href="{{ route('engagement.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('engagement.admin.dashboard') }}</a>
</div>

<div class="card">
    <div class="card-content">
        <form method="GET" class="mb-3" action="{{ route('engagement.admin.accounts') }}">
            <div class="input-group" style="max-width:420px;">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="{{ __('engagement.admin.search_clients') }}">
                <button class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <div style="overflow-x:auto;">
        <table class="table align-middle mb-0">
            <thead><tr>
                <th>{{ __('engagement.admin.client') }}</th>
                <th>{{ __('engagement.admin.balance') }}</th>
                <th>{{ __('engagement.admin.lifetime') }}</th>
                <th>{{ __('engagement.current_tier') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse($accounts as $a)
                <tr>
                    <td>{{ $a->client?->name ?? '—' }}<div class="text-muted" style="font-size:.78rem;">{{ $a->client?->email }}</div></td>
                    <td>{{ number_format($a->balance) }}</td>
                    <td>{{ number_format($a->lifetime_points) }}</td>
                    <td><span class="badge bg-primary">{{ $a->tier?->localizedName() ?? '—' }}</span></td>
                    <td class="text-end"><a href="{{ route('engagement.admin.account', $a) }}" class="btn btn-sm btn-light">{{ __('engagement.admin.view') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-4">{{ __('engagement.admin.no_accounts') }}</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        @if($accounts->hasPages())<div class="mt-3">{{ $accounts->withQueryString()->links() }}</div>@endif
    </div>
</div>
@endsection
