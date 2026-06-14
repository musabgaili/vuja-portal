@extends('layouts.internal-dashboard')
@section('title', 'Weekly Planner')

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-calendar-week"></i> Weekly Strategic Plan</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">Week of {{ $weekStart->format('D, M j') }} — {{ $weekStart->copy()->addDays(4)->format('D, M j, Y') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <span class="badge bg-{{ $plan->statusColor() }}" style="font-size:.8rem;">{{ ucfirst($plan->status ?? 'draft') }}</span>
    <small class="text-muted"><i class="fas fa-clock"></i> Deadline: {{ $deadline->format('l, M j \a\t g:i A') }}</small>
</div>

<form method="POST" action="{{ route('weekly-planner.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><span class="card-title">40-Hour Allocation</span></div>
                <div class="card-content">
                    @foreach($buckets as $key => $label)
                        <div class="mb-3">
                            <label class="form-label">{{ $label }}</label>
                            <input type="number" min="0" max="80" name="hours_{{ $key }}" value="{{ old('hours_'.$key, $plan->{'hours_'.$key} ?? 0) }}"
                                   class="form-control planner-hours" data-bucket="{{ $key }}">
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center mt-2" style="padding-top:.75rem; border-top:1px solid var(--gray-200);">
                        <strong>Total</strong>
                        <strong id="hoursTotal" style="font-size:1.25rem; color:var(--primary-color);">0</strong> / {{ $requiredHours }} h
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><span class="card-title">Working Location (Sun–Thu)</span></div>
                <div class="card-content">
                    @foreach($days as $day)
                        <div class="mb-2 d-flex align-items-center gap-2">
                            <label class="form-label mb-0" style="width:110px; text-transform:capitalize;">{{ $day }}</label>
                            <select name="locations[{{ $day }}]" class="form-select">
                                <option value="">—</option>
                                @foreach($locations as $lk => $ll)
                                    <option value="{{ $lk }}" @selected(($plan->locations[$day] ?? '') === $lk)>{{ $ll }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" name="action" value="draft" class="btn btn-outline-primary"><i class="fas fa-save"></i> Save Draft</button>
        <button type="submit" name="action" value="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit for Approval</button>
    </div>
</form>

@if($plan->status === 'rejected' && $plan->review_notes)
    <div class="alert alert-warning mt-3"><strong>Changes requested:</strong> {{ $plan->review_notes }}</div>
@endif

<div class="card mt-4">
    <div class="card-header"><span class="card-title">My Recent Plans</span></div>
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr><th>Week</th><th>Projects</th><th>Dev</th><th>Pre-sale</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($history as $h)
                <tr>
                    <td>{{ $h->week_start->format('M j, Y') }}</td>
                    <td>{{ $h->hours_projects }}h</td><td>{{ $h->hours_development }}h</td><td>{{ $h->hours_presale }}h</td>
                    <td><span class="badge bg-{{ $h->statusColor() }}">{{ ucfirst($h->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-3">No plans yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (function () {
        var inputs = document.querySelectorAll('.planner-hours'), total = document.getElementById('hoursTotal');
        function recalc() {
            var t = 0; inputs.forEach(function (i) { t += parseInt(i.value || 0, 10); });
            total.textContent = t;
            total.style.color = (t === {{ $requiredHours }}) ? 'var(--success-color)' : (t > {{ $requiredHours }} ? 'var(--error-color)' : 'var(--primary-color)');
        }
        inputs.forEach(function (i) { i.addEventListener('input', recalc); });
        recalc();
    })();
</script>
@endsection
