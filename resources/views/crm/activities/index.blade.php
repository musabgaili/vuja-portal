@extends('layouts.internal-dashboard')
@section('title', __('portal.crm_act.my_activities'))

@php
    $linkFor = function ($a) {
        return match ($a->subject_type) {
            \App\Models\Opportunity::class => $a->subject ? route('crm.show', $a->subject) : '#',
            \App\Models\Company::class => $a->subject ? route('companies.show', $a->subject) : '#',
            \App\Models\Contact::class => $a->subject ? route('contacts.edit', $a->subject) : '#',
            default => '#',
        };
    };
@endphp

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-list-check"></i> {{ __('portal.crm_act.my_activities') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.crm_act.inbox_sub') }}</p>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@foreach([['overdue', $overdue, 'danger'], ['upcoming', $upcoming, 'primary']] as [$key, $list, $color])
<div class="card mb-3">
    <div class="card-header"><span class="card-title">{{ __('portal.crm_act.'.$key) }} ({{ $list->count() }})</span></div>
    <div class="card-content p-0">
        <table class="table mb-0">
            <tbody>
            @forelse($list as $a)
                <tr>
                    <td style="width:36px;"><i class="fas {{ $a->typeIcon() }}" style="color:var(--{{ $color === 'danger' ? 'error' : 'primary' }}-color);"></i></td>
                    <td>
                        <div style="font-weight:600;">{{ $a->summary }}</div>
                        <a href="{{ $linkFor($a) }}" class="text-muted small">{{ optional($a->subject)->name ?? optional($a->subject)->title ?? '—' }}</a>
                    </td>
                    <td class="{{ $key === 'overdue' ? 'text-danger' : 'text-muted' }}" style="white-space:nowrap;">
                        @if($a->due_at)<i class="fas fa-calendar"></i> {{ $a->due_at->format('M j, g:i A') }}@endif
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('crm-activities.complete', $a) }}">@csrf
                            <button class="btn btn-outline-primary btn-sm"><i class="fas fa-check"></i> {{ __('portal.crm_act.complete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-3">{{ __('portal.crm_act.nothing') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection
