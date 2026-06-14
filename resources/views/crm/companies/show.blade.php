@extends('layouts.internal-dashboard')
@section('title', $company->name)

@section('content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-building"></i> {{ $company->name }}</h1>
            <p style="margin:.25rem 0 0; opacity:.9;">{{ $company->industry ?: '' }}</p>
        </div>
        <a href="{{ route('companies.edit', $company) }}" class="btn" style="background:#fff; color:var(--primary-color); font-weight:600;"><i class="fas fa-pen"></i> {{ __('portal.crm.edit') }}</a>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-content">
            <div class="mb-2">@foreach($company->tags as $t)<span class="badge" style="background:{{ $t->color }}22; color:{{ $t->color }};">{{ $t->name }}</span> @endforeach</div>
            <div class="text-muted small">{{ __('portal.crm.website') }}</div><div dir="ltr">{{ $company->website ?: '—' }}</div>
            <div class="text-muted small mt-2">{{ __('portal.crm.email') }}</div><div dir="ltr">{{ $company->email ?: '—' }}</div>
            <div class="text-muted small mt-2">{{ __('portal.crm.phone') }}</div><div dir="ltr">{{ $company->phone ?: '—' }}</div>
            <div class="text-muted small mt-2">{{ __('portal.crm.owner') }}</div><div>{{ $company->owner->name ?? '—' }}</div>
            @if($company->address)<div class="text-muted small mt-2">{{ __('portal.crm.address') }}</div><div style="white-space:pre-line;">{{ $company->address }}</div>@endif
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">{{ __('portal.crm.contacts') }} ({{ $company->contacts->count() }})</span>
                <a href="{{ route('contacts.create', ['company_id' => $company->id]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus"></i> {{ __('portal.crm.add_contact') }}</a>
            </div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <tbody>
                    @forelse($company->contacts as $ct)
                        <tr>
                            <td>{{ $ct->name }} @if($ct->is_primary)<span class="badge bg-primary">{{ __('portal.crm.primary') }}</span>@endif<div class="text-muted small">{{ $ct->job_title }}</div></td>
                            <td dir="ltr">{{ $ct->email ?: '—' }}</td>
                            <td class="text-end"><a href="{{ route('contacts.edit', $ct) }}" class="text-muted"><i class="fas fa-pen"></i></a></td>
                        </tr>
                    @empty
                        <tr><td class="text-muted text-center py-3">{{ __('portal.crm.no_contacts') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.crm.opportunities') }} ({{ $company->opportunities->count() }})</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <tbody>
                    @forelse($company->opportunities as $op)
                        <tr>
                            <td><a href="{{ route('crm.show', $op) }}">{{ $op->name }}</a></td>
                            <td><span class="badge bg-{{ $op->stageColor() }}">{{ ucfirst($op->stage) }}</span></td>
                            <td class="text-end">${{ number_format((float) $op->expected_value, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-muted text-center py-3">{{ __('portal.crm.no_deals') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    @include('partials.activity-timeline', ['subject' => $company, 'subjectKey' => 'company'])
</div>
@endsection
