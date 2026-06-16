@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.assignments'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-diagram-project"></i> {{ __('targets.cap.assignments') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.assignments_subtitle') }}</p></div>
    <a href="{{ route('capacity.admin.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.cap.admin_title') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="card mb-3"><div class="card-content">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div><label class="form-label">{{ __('targets.cap.project') }}</label>
            <select name="project_id" class="form-select" onchange="this.form.submit()" style="min-width:300px;">
                <option value="">{{ __('targets.cap.choose_project') }}</option>
                @foreach($projects as $p)<option value="{{ $p->id }}" @selected($selected && $selected->id===$p->id)>#{{ $p->id }} — {{ \Illuminate\Support\Str::limit($p->title, 50) }}</option>@endforeach
            </select></div>
    </form>
</div></div>

@if($selected)
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card mb-3"><div class="card-header"><span class="card-title">{{ __('targets.cap.assignable_lines') }}</span></div>
        <div class="card-content p-0">
            <table class="table table-sm mb-0">
                <thead><tr><th>{{ __('targets.cap.line') }}</th><th class="text-end">{{ __('targets.cap.amount') }}</th></tr></thead>
                <tbody>
                @forelse($lines as $l)
                    <tr><td style="font-size:.82rem;">{{ $l->name }} <span class="text-muted">({{ $l->category }})</span></td><td class="text-end">{{ number_format($l->amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="2" class="text-muted text-center py-3">{{ __('targets.cap.no_lines') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>

        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-plus"></i> {{ __('targets.cap.assign') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('capacity.admin.assignments.store') }}">@csrf
                <input type="hidden" name="project_id" value="{{ $selected->id }}">
                <label class="form-label">{{ __('targets.cap.engineer') }}</label>
                <select name="team_member_id" class="form-select mb-2" required>
                    <option value="">—</option>
                    @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->display_name }}</option>@endforeach
                </select>
                <label class="form-label">{{ __('targets.cap.line_optional') }}</label>
                <select name="project_line_id" class="form-select mb-2" id="asg-line">
                    <option value="">—</option>
                    @foreach($lines as $l)<option value="{{ $l->line_id }}" data-amount="{{ $l->amount }}">{{ \Illuminate\Support\Str::limit($l->name, 40) }}</option>@endforeach
                </select>
                <label class="form-label">{{ __('targets.cap.category') }}</label>
                <select name="activity_category_id" class="form-select mb-2" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->localizedName() }}</option>@endforeach
                </select>
                <label class="form-label">{{ __('targets.cap.value') }} ({{ config('targets.currency','SAR') }})</label>
                <input type="number" step="0.01" min="0" name="value" class="form-control mb-2" id="asg-value" required>
                <button class="btn btn-primary w-100">{{ __('targets.cap.assign') }}</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-7"><div class="card"><div class="card-header"><span class="card-title">{{ __('targets.cap.current_assignments') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('targets.cap.engineer') }}</th><th>{{ __('targets.cap.category') }}</th><th class="text-end">{{ __('targets.cap.value') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($existing as $a)
                <tr>
                    <td>{{ $a->member?->display_name ?? '—' }}</td>
                    <td>{{ $a->category?->localizedName() ?? '—' }}</td>
                    <td class="text-end">{{ number_format($a->value, 2) }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('capacity.admin.assignments.delete', $a) }}" onsubmit="return confirm('{{ __('targets.cap.delete_confirm') }}')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-4">{{ __('targets.cap.no_assignments') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <small class="text-muted d-block mt-2">{{ __('targets.cap.recognition_hint') }}</small>
    </div>
</div>
@endif

@push('scripts')
<script>
(function(){ var line=document.getElementById('asg-line'), val=document.getElementById('asg-value');
  if(line){ line.addEventListener('change', function(){ var o=line.options[line.selectedIndex]; if(o && o.dataset.amount && val && !val.value){ val.value=o.dataset.amount; } }); } })();
</script>
@endpush
@endsection
