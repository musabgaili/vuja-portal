@extends('layouts.internal-dashboard')
@section('title', __('portal.meetings.invitations_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('meetings.internal.my-meetings') }}">{{ __('portal.meetings.my_meetings') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.meetings.invitations_title') }}</li>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card mb-3">
    <div class="card-header"><h3 class="mb-0"><i class="fas fa-calendar-plus"></i> {{ __('portal.meetings.pending_invitations') }}</h3></div>
    <div class="card-content">
        @forelse($pending as $att)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <strong>{{ $att->meeting->title }}</strong>
                        <div class="text-muted" style="font-size:.9rem;">
                            <i class="fas fa-user"></i> {{ __('portal.meetings.from_organizer', ['name' => $att->meeting->client?->name ?? '—']) }}
                        </div>
                        <div class="text-muted" style="font-size:.9rem;">
                            <i class="fas fa-clock"></i> {{ $att->meeting->scheduled_at->translatedFormat('l, M j, Y · g:i A') }}
                            · {{ $att->meeting->duration_minutes }} {{ __('portal.meetings.min') }}
                        </div>
                        @if($att->meeting->description)
                            <div class="mt-1" style="font-size:.85rem;">{{ $att->meeting->description }}</div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('meetings.invitation.accept', $att) }}">
                            @csrf
                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> {{ __('portal.meetings.accept') }}</button>
                        </form>
                        <form method="POST" action="{{ route('meetings.invitation.decline', $att) }}" onsubmit="return confirm(@json(__('portal.meetings.decline_confirm')))">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-xmark"></i> {{ __('portal.meetings.decline') }}</button>
                        </form>
                    </div>
                </div>
                <div class="alert alert-light border mt-2 mb-0" style="font-size:.8rem;">
                    <i class="fas fa-circle-info"></i> {{ __('portal.meetings.accept_explainer') }}
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p class="mb-0">{{ __('portal.meetings.no_pending_invitations') }}</p>
            </div>
        @endforelse
    </div>
</div>

@if($answered->count())
<div class="card">
    <div class="card-header"><h3 class="mb-0">{{ __('portal.meetings.invitation_history') }}</h3></div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr>
                    <th>{{ __('portal.meetings.subject') }}</th>
                    <th>{{ __('portal.time_slots.date') }}</th>
                    <th>{{ __('portal.meetings.status_label') }}</th>
                </tr></thead>
                <tbody>
                    @foreach($answered as $att)
                    <tr>
                        <td>{{ $att->meeting?->title ?? '—' }}</td>
                        <td>{{ $att->meeting?->scheduled_at?->translatedFormat('M j, Y g:i A') ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $att->isAccepted() ? 'success' : 'secondary' }}">
                                {{ __('portal.meetings.attendee_status.'.$att->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
