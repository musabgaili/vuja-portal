@extends('layouts.internal-dashboard')
@section('title', __('portal.nav.contacts'))

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-address-book"></i> {{ __('portal.nav.contacts') }}</h1>
        <a href="{{ route('contacts.create') }}" class="btn" style="background:#fff; color:var(--primary-color); font-weight:600;"><i class="fas fa-plus"></i> {{ __('portal.crm.new_contact') }}</a>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<form method="GET" class="mb-3" style="max-width:360px;">
    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('portal.crm.search') }}">
</form>

<div class="card"><div class="card-content p-0">
    <table class="table mb-0">
        <thead><tr><th>{{ __('portal.crm.contact') }}</th><th>{{ __('portal.crm.company') }}</th><th>{{ __('portal.crm.email') }}</th><th>{{ __('portal.crm.phone') }}</th><th>{{ __('portal.crm.tags') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($contacts as $ct)
            <tr>
                <td style="font-weight:600;">{{ $ct->name }} @if($ct->is_primary)<span class="badge bg-primary">{{ __('portal.crm.primary') }}</span>@endif<div class="text-muted small">{{ $ct->job_title }}</div></td>
                <td>{{ $ct->company->name ?? '—' }}</td>
                <td dir="ltr">{{ $ct->email ?: '—' }}</td>
                <td dir="ltr">{{ $ct->phone ?: '—' }}</td>
                <td>@foreach($ct->tags as $t)<span class="badge" style="background:{{ $t->color }}22; color:{{ $t->color }};">{{ $t->name }}</span> @endforeach</td>
                <td class="text-end"><a href="{{ route('contacts.edit', $ct) }}" class="text-muted"><i class="fas fa-pen"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted text-center py-3">{{ __('portal.crm.no_contacts') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-2">{{ $contacts->links() }}</div>
@endsection
