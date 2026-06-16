@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.submissions'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-clipboard-check"></i> {{ __('targets.cap.submissions') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.week_of', ['from' => $week->translatedFormat('M d'), 'to' => $week->copy()->addDays(6)->translatedFormat('M d')]) }}</p></div>
    <a href="{{ route('capacity.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.cap.admin_title') }}</a>
</div>

<div class="card"><div class="card-content p-0">
    <table class="table align-middle mb-0">
        <thead><tr><th>{{ __('targets.cap.engineer') }}</th><th>{{ __('targets.cap.status') }}</th><th>{{ __('targets.cap.available') }}</th></tr></thead>
        <tbody>
        @forelse($members as $m)
            @php $a = $submitted->get($m->id); @endphp
            <tr>
                <td><strong>{{ $m->display_name }}</strong></td>
                <td>
                    @if($a && $a->status === 'submitted')<span class="badge bg-success"><i class="fas fa-check"></i> {{ __('targets.cap.submitted_badge') }}</span>
                    @elseif($a)<span class="badge bg-warning">{{ __('targets.cap.draft_badge') }}</span>
                    @else<span class="badge bg-danger">{{ __('targets.cap.not_submitted') }}</span>@endif
                </td>
                <td>{{ $a ? rtrim(rtrim(number_format($a->available_hours,1),'0'),'.').'h' : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted text-center py-4">{{ __('targets.cap.no_engineers') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
