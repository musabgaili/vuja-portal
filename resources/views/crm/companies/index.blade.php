@extends('layouts.internal-dashboard')
@section('title', __('portal.nav.companies'))

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-building"></i> {{ __('portal.nav.companies') }}</h1>
        <div class="d-flex gap-2">
            @if(auth()->user()->isManager())
            <a href="{{ route('imports.form', 'companies') }}" class="btn btn-light"><i class="fas fa-file-excel"></i> {{ __('portal.import.import') }}</a>
            @endif
            <a href="{{ route('companies.create') }}" class="btn" style="background:#fff; color:var(--primary-color); font-weight:600;"><i class="fas fa-plus"></i> {{ __('portal.crm.new_company') }}</a>
        </div>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<form method="GET" class="mb-3" style="max-width:360px;">
    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('portal.crm.search') }}">
</form>

<div class="card"><div class="card-content p-0">
    <table class="table mb-0">
        <thead><tr><th>{{ __('portal.crm.company') }}</th><th>{{ __('portal.crm.industry') }}</th><th>{{ __('portal.crm.contacts') }}</th><th>{{ __('portal.crm.deals') }}</th><th>{{ __('portal.crm.tags') }}</th></tr></thead>
        <tbody>
        @forelse($companies as $co)
            <tr>
                <td><a href="{{ route('companies.show', $co) }}" style="font-weight:600;">{{ $co->name }}</a></td>
                <td>{{ $co->industry ?: '—' }}</td>
                <td>{{ $co->contacts_count }}</td>
                <td>{{ $co->opportunities_count }}</td>
                <td>@foreach($co->tags as $t)<span class="badge" style="background:{{ $t->color }}22; color:{{ $t->color }};">{{ $t->name }}</span> @endforeach</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted text-center py-3">{{ __('portal.crm.no_companies') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-2">{{ $companies->links() }}</div>
@endsection
