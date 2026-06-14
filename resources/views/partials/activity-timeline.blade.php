{{-- CRM activity timeline + chatter. Expects $subject (model) and $subjectKey (string). --}}
@php
    $acts = $subject->activities()->with(['user', 'creator'])->get();
    $planned = $acts->where('status', 'planned')->sortBy('due_at');
    $done = $acts->where('status', 'done');
@endphp
<div class="card">
    <div class="card-header"><span class="card-title"><i class="fas fa-clock-rotate-left"></i> {{ __('portal.crm_act.title') }}</span></div>
    <div class="card-content">
        @error('summary')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <form method="POST" action="{{ route('crm-activities.store') }}" class="mb-3">
            @csrf
            <input type="hidden" name="subject" value="{{ $subjectKey }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="action" class="form-select">
                        <option value="log">{{ __('portal.crm_act.log_now') }}</option>
                        <option value="schedule">{{ __('portal.crm_act.schedule') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="note">{{ __('portal.crm_act.note') }}</option>
                        <option value="call">{{ __('portal.crm_act.call') }}</option>
                        <option value="email">{{ __('portal.crm_act.email') }}</option>
                        <option value="meeting">{{ __('portal.crm_act.meeting') }}</option>
                        <option value="todo">{{ __('portal.crm_act.todo') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="datetime-local" name="due_at" class="form-control" title="{{ __('portal.crm_act.due') }}">
                </div>
                <div class="col-12">
                    <input type="text" name="summary" class="form-control" required placeholder="{{ __('portal.crm_act.summary_ph') }}">
                </div>
                <div class="col-12">
                    <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('portal.crm_act.notes_ph') }}"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('portal.crm_act.add') }}</button>
                </div>
            </div>
        </form>

        {{-- Planned next-actions --}}
        @if($planned->count())
            <div class="text-muted small mb-1" style="text-transform:uppercase; letter-spacing:.05em;">{{ __('portal.crm_act.next_actions') }}</div>
            @foreach($planned as $a)
                <div class="d-flex align-items-start gap-2 py-2" style="border-bottom:1px solid var(--gray-200);">
                    <i class="fas {{ $a->typeIcon() }}" style="margin-top:3px; color:{{ $a->isOverdue() ? 'var(--error-color)' : 'var(--primary-color)' }};"></i>
                    <div style="flex:1;">
                        <div style="font-weight:600;">{{ $a->summary }}</div>
                        @if($a->notes)<div class="text-muted small">{{ $a->notes }}</div>@endif
                        <div class="small {{ $a->isOverdue() ? 'text-danger' : 'text-muted' }}">
                            @if($a->due_at)<i class="fas fa-calendar"></i> {{ $a->due_at->format('M j, g:i A') }} @if($a->isOverdue())· {{ __('portal.crm_act.overdue') }}@endif @endif
                            · {{ $a->user->name ?? '' }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('crm-activities.complete', $a) }}">@csrf
                        <button class="btn btn-outline-primary btn-sm" title="{{ __('portal.crm_act.complete') }}"><i class="fas fa-check"></i></button>
                    </form>
                </div>
            @endforeach
        @endif

        {{-- Logged history --}}
        @if($done->count())
            <div class="text-muted small mt-3 mb-1" style="text-transform:uppercase; letter-spacing:.05em;">{{ __('portal.crm_act.history') }}</div>
            @foreach($done as $a)
                <div class="d-flex align-items-start gap-2 py-2" style="border-bottom:1px solid var(--gray-200);">
                    <i class="fas {{ $a->typeIcon() }}" style="margin-top:3px; color:var(--gray-400);"></i>
                    <div style="flex:1;">
                        <div>{{ $a->summary }}</div>
                        @if($a->notes)<div class="text-muted small">{{ $a->notes }}</div>@endif
                        <div class="text-muted small">{{ optional($a->done_at)->format('M j, Y g:i A') }} · {{ $a->creator->name ?? '' }}</div>
                    </div>
                </div>
            @endforeach
        @endif

        @if(! $planned->count() && ! $done->count())
            <p class="text-muted mb-0">{{ __('portal.crm_act.empty') }}</p>
        @endif
    </div>
</div>
