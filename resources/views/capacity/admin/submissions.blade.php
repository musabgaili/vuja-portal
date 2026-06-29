@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.submissions'))

@section('content')
@php
    $submittedStatuses = ['pending', 'approved'];
    $submittedCount = $members->filter(fn ($m) => ($p = $plans->get($m->user_id)) && in_array($p->status, $submittedStatuses, true))->count();
    $lateCount = $members->filter(function ($m) use ($plans, $submittedStatuses) {
        $p = $plans->get($m->user_id);
        return $p && in_array($p->status, $submittedStatuses, true) && $p->submitted_at && $p->isLate();
    })->count();
@endphp
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-clipboard-check"></i> {{ __('targets.cap.submissions') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.week_of', ['from' => $week->translatedFormat('M d'), 'to' => $week->copy()->addDays(6)->translatedFormat('M d')]) }}
            · {{ __('portal.planner.deadline') }}: {{ $deadline->translatedFormat('l, M j \a\t g:i A') }}</p></div>
    <a href="{{ route('capacity.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.cap.admin_title') }}</a>
</div>

{{-- Week nav + summary + jump to plans review --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="btn-group btn-group-sm">
        <a href="{{ route('capacity.admin.submissions', ['week' => $week->copy()->subWeek()->toDateString()]) }}" class="btn btn-outline-primary"><i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ __('portal.planner.prev_week') }}</a>
        <a href="{{ route('capacity.admin.submissions') }}" class="btn btn-outline-primary">{{ __('portal.planner.this_week') }}</a>
        <a href="{{ route('capacity.admin.submissions', ['week' => $week->copy()->addWeek()->toDateString()]) }}" class="btn btn-outline-primary">{{ __('portal.planner.next_week') }} <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-info">{{ __('targets.cap.submitted_count', ['done' => $submittedCount, 'total' => $members->count()]) }}</span>
        @if($lateCount > 0)<span class="badge bg-danger">{{ __('targets.cap.late_count', ['count' => $lateCount]) }}</span>@endif
        <a href="{{ route('weekly-planner.review', ['week' => $week->toDateString()]) }}" class="btn btn-sm btn-primary"><i class="fas fa-clipboard-list"></i> {{ __('targets.cap.open_in_review') }}</a>
    </div>
</div>

<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>{{ __('targets.cap.engineer') }}</th>
            <th>{{ __('targets.cap.status') }}</th>
            <th>{{ __('targets.cap.on_time_col') }}</th>
            <th>{{ __('targets.cap.submitted_at') }}</th>
        </tr></thead>
        <tbody>
        @forelse($members as $m)
            @php $p = $plans->get($m->user_id); $submitted = $p && in_array($p->status, $submittedStatuses, true); @endphp
            <tr>
                <td><strong>{{ $m->display_name }}</strong><div class="text-muted" style="font-size:.72rem;">{{ $m->user?->email }}</div></td>
                <td>
                    @if($p)
                        <span class="badge bg-{{ $p->statusColor() }}">{{ __('portal.planner.status.'.$p->status) }}</span>
                    @else
                        <span class="badge bg-danger">{{ __('targets.cap.not_submitted') }}</span>
                    @endif
                </td>
                <td>
                    @if($submitted && $p->submitted_at)
                        @if($p->isLate())
                            <span class="badge bg-danger"><i class="fas fa-triangle-exclamation"></i> {{ __('targets.cap.late') }}</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-check"></i> {{ __('targets.cap.on_time') }}</span>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-muted" style="font-size:.85rem;">{{ $p && $p->submitted_at ? $p->submitted_at->translatedFormat('M j, g:i A') : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted text-center py-4">{{ __('targets.cap.no_engineers') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
