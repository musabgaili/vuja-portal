@extends('layouts.internal-dashboard')
@section('title', __('portal.planner.title'))

@php
    // sunday..thursday → calendar dates for this week.
    $dayDates = [];
    foreach ($days as $i => $d) { $dayDates[$d] = $weekStart->copy()->addDays($i); }
    $readonly = $plan->status === 'approved';
@endphp

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-calendar-week"></i> {{ __('portal.planner.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ $weekStart->translatedFormat('D, M j') }} — {{ $weekStart->copy()->addDays(4)->translatedFormat('D, M j, Y') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

{{-- Week navigation + status --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="btn-group">
        <a href="{{ route('weekly-planner.index', ['week' => $prevWeek]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-chevron-left"></i> {{ __('portal.planner.prev_week') }}</a>
        <a href="{{ route('weekly-planner.index') }}" class="btn btn-outline-primary btn-sm">{{ __('portal.planner.this_week') }}</a>
        <a href="{{ route('weekly-planner.index', ['week' => $nextWeek]) }}" class="btn btn-outline-primary btn-sm">{{ __('portal.planner.next_week') }} <i class="fas fa-chevron-right"></i></a>
    </div>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="badge bg-{{ $plan->statusColor() }}" style="font-size:.8rem;">{{ __('portal.planner.status.'.($plan->status ?? 'draft')) }}</span>
        <small class="text-muted"><i class="fas fa-clock"></i> {{ __('portal.planner.deadline') }}: {{ $deadline->translatedFormat('l, M j \a\t g:i A') }}</small>
    </div>
</div>

@if($readonly)
    <div class="alert alert-info"><i class="fas fa-lock"></i> {{ __('portal.planner.readonly_note') }}</div>
@endif

<form method="POST" action="{{ route('weekly-planner.store', ['week' => $weekStart->toDateString()]) }}" id="timesheetForm">
    @csrf

    {{-- Working location + availability window per day --}}
    <div class="card mb-3">
        <div class="card-header"><span class="card-title"><i class="fas fa-map-marker-alt"></i> {{ __('portal.planner.working_location') }}</span></div>
        <div class="card-content">
            <p class="text-muted mb-2" style="font-size:.85rem;">{{ __('portal.planner.availability_hint') }}</p>
            <div class="row g-2">
                @foreach($days as $day)
                    <div class="col">
                        <label class="form-label mb-1" style="font-size:.8rem; text-transform:capitalize;">{{ __('portal.planner.day.'.$day) }}<br><small class="text-muted">{{ $dayDates[$day]->translatedFormat('M j') }}</small></label>
                        <select name="locations[{{ $day }}]" class="form-select form-select-sm mb-1" @disabled($readonly)>
                            <option value="">—</option>
                            @foreach($locations as $lk => $ll)
                                <option value="{{ $lk }}" @selected(($plan->locations[$day] ?? '') === $lk)>{{ $ll }}</option>
                            @endforeach
                        </select>
                        <div class="d-flex gap-1">
                            <input type="time" name="availability[{{ $day }}][start]" value="{{ $plan->availability[$day]['start'] ?? '' }}" class="form-control form-control-sm" title="{{ __('portal.planner.from') }}" @disabled($readonly)>
                            <input type="time" name="availability[{{ $day }}][end]" value="{{ $plan->availability[$day]['end'] ?? '' }}" class="form-control form-control-sm" title="{{ __('portal.planner.to') }}" @disabled($readonly)>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Projects & Tasks --}}
    <div class="card mb-3">
        <div class="card-header"><span class="card-title"><i class="fas fa-diagram-project"></i> {{ __('portal.planner.projects_tasks') }}</span></div>
        <div class="card-content p-0">
            <p class="text-muted px-3 pt-3 mb-2" style="font-size:.85rem;">{{ __('portal.planner.projects_tasks_hint') }}</p>
            <div class="table-responsive">
                <table class="table ts-table mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:240px;">{{ __('portal.planner.col_item') }}</th>
                            @foreach($days as $day)
                                <th class="text-center">{{ __('portal.planner.day_short.'.$day) }}<br><small class="text-muted">{{ $dayDates[$day]->translatedFormat('M j') }}</small></th>
                            @endforeach
                            <th class="text-center">{{ __('portal.planner.row_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td><span class="badge bg-primary">{{ __('portal.planner.kind.project') }}</span> {{ $project->title }}</td>
                                @foreach($days as $day)
                                    <td class="text-center">
                                        <input type="number" min="0" max="{{ $maxHoursPerDay }}" name="hours[project][{{ $project->id }}][{{ $day }}]"
                                               value="{{ old('hours.project.'.$project->id.'.'.$day, $cells['project'][$project->id][$day] ?? '') }}"
                                               class="form-control form-control-sm ts-hours" data-day="{{ $day }}" @disabled($readonly)>
                                    </td>
                                @endforeach
                                <td class="text-center ts-row-total">0</td>
                            </tr>
                        @empty
                            {{-- no projects --}}
                        @endforelse

                        @foreach($tasks as $task)
                            <tr>
                                <td><span class="badge bg-info">{{ __('portal.planner.kind.task') }}</span> {{ $task->title }}
                                    <small class="text-muted">({{ __('portal.staff_tasks.category.'.$task->category) }})</small></td>
                                @foreach($days as $day)
                                    <td class="text-center">
                                        <input type="number" min="0" max="{{ $maxHoursPerDay }}" name="hours[task][{{ $task->id }}][{{ $day }}]"
                                               value="{{ old('hours.task.'.$task->id.'.'.$day, $cells['task'][$task->id][$day] ?? '') }}"
                                               class="form-control form-control-sm ts-hours" data-day="{{ $day }}" @disabled($readonly)>
                                    </td>
                                @endforeach
                                <td class="text-center ts-row-total">0</td>
                            </tr>
                        @endforeach

                        @if($projects->isEmpty() && $tasks->isEmpty())
                            <tr><td colspan="{{ count($days) + 2 }}" class="text-muted text-center py-3">{{ __('portal.planner.no_projects') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Activities --}}
    <div class="card mb-3">
        <div class="card-header"><span class="card-title"><i class="fas fa-list-check"></i> {{ __('portal.planner.activities') }}</span></div>
        <div class="card-content p-0">
            <div class="table-responsive">
                <table class="table ts-table mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:240px;">{{ __('portal.planner.col_activity') }}</th>
                            @foreach($days as $day)
                                <th class="text-center">{{ __('portal.planner.day_short.'.$day) }}<br><small class="text-muted">{{ $dayDates[$day]->translatedFormat('M j') }}</small></th>
                            @endforeach
                            <th class="text-center">{{ __('portal.planner.row_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $key => $label)
                            <tr>
                                <td>{{ __('portal.planner.activity.'.$key) }}</td>
                                @foreach($days as $day)
                                    <td class="text-center">
                                        <input type="number" min="0" max="{{ $maxHoursPerDay }}" name="hours[activity][{{ $key }}][{{ $day }}]"
                                               value="{{ old('hours.activity.'.$key.'.'.$day, $cells['activity'][$key][$day] ?? '') }}"
                                               class="form-control form-control-sm ts-hours" data-day="{{ $day }}" @disabled($readonly)>
                                    </td>
                                @endforeach
                                <td class="text-center ts-row-total">0</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--gray-300);">
                            <th class="text-end">{{ __('portal.planner.daily_total') }}</th>
                            @foreach($days as $day)
                                <th class="text-center ts-col-total" data-day="{{ $day }}">0</th>
                            @endforeach
                            <th class="text-center"><span id="tsGrandTotal" style="color:var(--primary-color);">0</span> / {{ $requiredHours }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @unless($readonly)
    <div class="d-flex gap-2 mb-3">
        <button type="submit" name="action" value="draft" class="btn btn-outline-primary"><i class="fas fa-save"></i> {{ __('portal.planner.save_draft') }}</button>
        <button type="submit" name="action" value="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.planner.submit') }}</button>
    </div>
    @endunless
</form>

@if($plan->status === 'rejected' && $plan->review_notes)
    <div class="alert alert-warning"><strong>{{ __('portal.planner.changes_requested') }}:</strong> {{ $plan->review_notes }}</div>
@endif

{{-- History --}}
<div class="card mt-4">
    <div class="card-header"><span class="card-title">{{ __('portal.planner.recent_plans') }}</span></div>
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr><th>{{ __('portal.planner.week') }}</th><th>{{ __('portal.planner.total') }}</th><th>{{ __('portal.planner.field_status') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($history as $h)
                <tr>
                    <td>{{ $h->week_start->translatedFormat('M j, Y') }}</td>
                    <td>{{ $h->totalHours() }}h</td>
                    <td><span class="badge bg-{{ $h->statusColor() }}">{{ __('portal.planner.status.'.$h->status) }}</span></td>
                    <td class="text-end"><a href="{{ route('weekly-planner.index', ['week' => $h->week_start->toDateString()]) }}" class="btn btn-sm btn-link">{{ __('portal.planner.open') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted text-center py-3">{{ __('portal.planner.no_plans') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .ts-table th, .ts-table td { vertical-align: middle; }
    .ts-table .ts-hours { width: 64px; margin: 0 auto; text-align: center; padding: .25rem .35rem; }
    .ts-table thead th { font-size: .8rem; }
</style>
<script>
    (function () {
        var form = document.getElementById('timesheetForm');
        if (!form) return;
        var inputs = form.querySelectorAll('.ts-hours');
        var grand = document.getElementById('tsGrandTotal');
        var required = {{ $requiredHours }};

        function recalc() {
            var colTotals = {}, total = 0;
            form.querySelectorAll('.ts-col-total').forEach(function (c) { colTotals[c.dataset.day] = 0; });
            // row totals
            form.querySelectorAll('tbody tr').forEach(function (tr) {
                var rowTotal = 0;
                tr.querySelectorAll('.ts-hours').forEach(function (i) {
                    var v = parseInt(i.value || 0, 10) || 0;
                    rowTotal += v;
                    if (colTotals[i.dataset.day] !== undefined) colTotals[i.dataset.day] += v;
                    total += v;
                });
                var rt = tr.querySelector('.ts-row-total');
                if (rt) rt.textContent = rowTotal;
            });
            form.querySelectorAll('.ts-col-total').forEach(function (c) {
                c.textContent = colTotals[c.dataset.day];
                c.style.color = (colTotals[c.dataset.day] === 8) ? 'var(--success-color)' : '';
            });
            grand.textContent = total;
            grand.style.color = (total === required) ? 'var(--success-color)' : (total > required ? 'var(--error-color)' : 'var(--primary-color)');
        }
        inputs.forEach(function (i) { i.addEventListener('input', recalc); });
        recalc();
    })();
</script>
@endsection
