@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.leave'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-plane-departure"></i> {{ __('targets.cap.leave') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.leave_subtitle') }}</p></div>
    <a href="{{ route('capacity.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.cap.admin_title') }}</a>
</div>


<div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-plus"></i> {{ __('targets.cap.add_leave') }}</span></div>
    <div class="card-content">
        <form method="POST" action="{{ route('capacity.admin.leave.store') }}">@csrf
            <label class="form-label">{{ __('targets.cap.engineer') }}</label>
            <select name="team_member_id" class="form-select mb-2" required>
                <option value="">—</option>
                @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->display_name }}</option>@endforeach
            </select>
            <label class="form-label">{{ __('targets.cap.date') }}</label>
            <input type="date" name="date" class="form-control mb-2" required>
            <label class="form-label">{{ __('targets.cap.hours') }}</label>
            <input type="number" step="0.5" min="0" max="24" name="hours" value="8" class="form-control mb-2" required>
            <label class="form-label">{{ __('targets.cap.type') }}</label>
            <select name="type" class="form-select mb-2"><option value="leave">{{ __('targets.cap.leave_type') }}</option><option value="holiday">{{ __('targets.cap.holiday_type') }}</option></select>
            <button class="btn btn-primary w-100">{{ __('targets.cap.add_leave') }}</button>
        </form>
    </div></div></div>

    <div class="col-lg-8"><div class="card"><div class="card-content p-0" style="overflow-x:auto;">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('targets.cap.engineer') }}</th><th>{{ __('targets.cap.date') }}</th><th>{{ __('targets.cap.hours') }}</th><th>{{ __('targets.cap.type') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($entries as $e)
                <tr>
                    <td>{{ $e->member?->display_name ?? '—' }}</td>
                    <td>{{ $e->date?->format('Y-m-d') }}</td>
                    <td>{{ rtrim(rtrim(number_format($e->hours,1),'0'),'.') }}h</td>
                    <td><span class="badge bg-secondary">{{ __('targets.cap.'.$e->type.'_type') }}</span></td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('capacity.admin.leave.delete', $e) }}" onsubmit="return confirm('{{ __('targets.cap.delete_confirm') }}')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-4">{{ __('targets.cap.no_leave') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    @if($entries->hasPages())<div class="mt-3">{{ $entries->links() }}</div>@endif
    </div>
</div>
@endsection
