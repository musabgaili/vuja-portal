@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.engineers'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-user-gear"></i> {{ __('targets.cap.engineers') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.engineers_subtitle') }}</p></div>
    <a href="{{ route('capacity.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.cap.admin_title') }}</a>
</div>


@if($candidates->isNotEmpty())
<div class="card mb-3"><div class="card-content">
    <form method="POST" action="{{ route('capacity.admin.engineers.store') }}" class="d-flex gap-2 align-items-end flex-wrap">@csrf
        <div><label class="form-label">{{ __('targets.cap.add_engineer') }}</label>
            <select name="user_id" class="form-select" required style="min-width:240px;">
                <option value="">—</option>
                @foreach($candidates as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>@endforeach
            </select></div>
        <button class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('targets.cap.add') }}</button>
    </form>
</div></div>
@endif

@foreach($members as $m)<form id="eng-{{ $m->id }}" method="POST" action="{{ route('capacity.admin.engineers.update', $m) }}" class="d-none">@csrf @method('PUT')</form>@endforeach
<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table align-middle mb-0">
        <thead><tr>
            <th>{{ __('targets.cap.engineer') }}</th>
            <th style="width:120px;">{{ __('targets.cap.capacity_h') }}</th>
            <th style="width:150px;">{{ __('targets.cap.specialization') }}</th>
            <th>{{ __('targets.cap.skills') }}</th>
            <th style="width:70px;">{{ __('targets.admin.active') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @forelse($members as $m)
            @php $f = 'eng-'.$m->id; $skillIds = $m->categories->pluck('id')->all(); @endphp
            <tr>
                <td><strong>{{ $m->display_name }}</strong><div class="text-muted" style="font-size:.72rem;">{{ $m->user?->email }}</div></td>
                <td><input form="{{ $f }}" type="number" step="0.5" min="0" max="168" name="weekly_capacity_hours" value="{{ rtrim(rtrim(number_format($m->weekly_capacity_hours,1),'0'),'.') }}" class="form-control form-control-sm" required></td>
                <td>
                    <select form="{{ $f }}" name="specialization" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="design" @selected($m->specialization==='design')>{{ __('targets.cap.design') }}</option>
                        <option value="electronics" @selected($m->specialization==='electronics')>{{ __('targets.cap.electronics') }}</option>
                    </select>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-2" style="font-size:.8rem;">
                        @foreach($deliveryCategories as $c)
                            <label><input form="{{ $f }}" type="checkbox" name="skills[]" value="{{ $c->id }}" @checked(in_array($c->id,$skillIds))> {{ $c->localizedName() }}</label>
                        @endforeach
                    </div>
                </td>
                <td><input form="{{ $f }}" type="checkbox" name="is_active" value="1" @checked($m->is_active)></td>
                <td class="text-end"><button form="{{ $f }}" class="btn btn-sm btn-primary">{{ __('targets.admin.save') }}</button></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted text-center py-4">{{ __('targets.cap.no_engineers') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
