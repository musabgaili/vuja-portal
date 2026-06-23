{{--
    Shared work panel for assignable service requests.
    Required vars: $model (the service request), $type (route slug: prototype|threed|ip|copyright|research|consultation|idea)
    Renders only for the assigned worker, managers, and PMs.
--}}
@php
    $me = auth()->user();
    $canWork = $me && $me->isInternal()
        && ((int) $model->assigned_to === (int) $me->id || $me->isManager() || $me->isProjectManager());
    $canManageItems = $me && ($me->isManager() || $me->isProjectManager());
    $ws = $model->workerStatusValue();
@endphp

@if($canWork)
<div class="card mt-3" id="serviceWorkPanel">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="card-title mb-0"><i class="fas fa-briefcase"></i> {{ __('portal.service_work.panel_title') }}</span>
        <span class="badge bg-{{ $model->workerStatusBadge() }}">{{ $model->workerStatusLabel() }}</span>
    </div>
    <div class="card-content">
        @if(! $model->assigned_to)
            <div class="alert alert-warning py-2"><i class="fas fa-info-circle"></i> {{ __('portal.service_work.unassigned_hint') }}</div>
        @endif

        {{-- ── A. Progress (worker_status) ───────────────────────────── --}}
        <h6 class="text-muted mb-2">{{ __('portal.service_work.progress_title') }}</h6>
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach(['not_started' => 'secondary', 'in_progress' => 'warning', 'submitted_for_review' => 'success'] as $state => $color)
                <form method="POST" action="{{ route('service-work.status', ['type' => $type, 'id' => $model->id]) }}">
                    @csrf
                    <input type="hidden" name="worker_status" value="{{ $state }}">
                    <button type="submit" class="btn btn-sm {{ $ws === $state ? 'btn-'.$color : 'btn-outline-'.$color }}" @disabled($ws === $state)>
                        @if($ws === $state)<i class="fas fa-check"></i> @endif
                        {{ __('portal.service_work.worker_status.'.$state) }}
                    </button>
                </form>
            @endforeach
        </div>

        {{-- ── P2. Service-specific outcome ──────────────────────────── --}}
        @switch($type)
            @case('ip')
                <h6 class="text-muted mb-2">{{ __('portal.service_work.outcome_title') }}</h6>
                <form method="POST" action="{{ route('ip.update-status', $model) }}" class="row g-2 align-items-end mb-4">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small">{{ __('portal.ip.update_status') }}</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="documentation" @selected($model->status === 'documentation')>{{ __('portal.ip.status_documentation') }}</option>
                            <option value="filing" @selected($model->status === 'filing')>{{ __('portal.ip.status_filing') }}</option>
                            <option value="registered" @selected($model->status === 'registered')>{{ __('portal.ip.status_registered') }}</option>
                            <option value="completed" @selected($model->status === 'completed')>{{ __('portal.ip.status_completed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">{{ __('portal.ip.registration_number') }}</label>
                        <input type="text" name="registration_number" class="form-control form-control-sm" value="{{ $model->registration_number }}" placeholder="{{ __('portal.ip.registration_number_if_registered') }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm w-100"><i class="fas fa-save"></i> {{ __('portal.service_work.record_outcome') }}</button>
                    </div>
                </form>
                @break

            @case('copyright')
                <h6 class="text-muted mb-2">{{ __('portal.service_work.outcome_title') }}</h6>
                <form method="POST" action="{{ route('copyright.update-status', $model) }}" class="row g-2 align-items-end mb-4">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small">{{ __('portal.ip.update_status') }}</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="filing" @selected($model->status === 'filing')>{{ __('portal.ip.status_filing') }}</option>
                            <option value="registered" @selected($model->status === 'registered')>{{ __('portal.ip.status_registered') }}</option>
                            <option value="completed" @selected($model->status === 'completed')>{{ __('portal.ip.status_completed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">{{ __('portal.copyright.number_label') }}</label>
                        <input type="text" name="copyright_number" class="form-control form-control-sm" value="{{ $model->copyright_number }}" placeholder="{{ __('portal.ip.registration_number_if_registered') }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm w-100"><i class="fas fa-save"></i> {{ __('portal.service_work.record_outcome') }}</button>
                    </div>
                </form>
                @break

            @case('research')
                <h6 class="text-muted mb-2">{{ __('portal.service_work.outcome_title') }}</h6>
                <form method="POST" action="{{ route('research.complete', $model) }}" class="mb-4">
                    @csrf
                    <label class="form-label small">{{ __('portal.research.manager.show.findings_label') }}</label>
                    <textarea name="research_findings" rows="4" class="form-control mb-2" placeholder="{{ __('portal.research.manager.show.findings_placeholder') }}">{{ $model->research_findings }}</textarea>
                    <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> {{ __('portal.service_work.submit_findings') }}</button>
                </form>
                @break
        @endswitch

        {{-- ── C. Deliverables ───────────────────────────────────────── --}}
        <h6 class="text-muted mb-2">{{ __('portal.service_work.deliverables_title') }}</h6>
        @forelse($model->deliverables as $d)
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                <div style="min-width:0;">
                    <a href="{{ route('service-work.download', $d) }}" class="text-decoration-none text-truncate d-inline-block mw-100">
                        <i class="fas fa-download"></i> {{ $d->content ?: $d->file_name }}
                    </a>
                    <small class="text-muted">({{ $d->size_label }})</small>
                    @if($d->is_client_visible)<span class="badge bg-info ms-1"><i class="fas fa-eye"></i> {{ __('portal.service_work.shared_with_client') }}</span>@endif
                    <small class="text-muted d-block">{{ $d->user->name ?? '—' }} · {{ $d->created_at->translatedFormat('M d, Y H:i') }}</small>
                </div>
                @if((int) $d->user_id === (int) $me->id || $canManageItems)
                <form method="POST" action="{{ route('service-work.delete-item', $d) }}" onsubmit="return confirm(@js(__('portal.service_work.confirm_delete')))">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
        @empty
            <p class="text-muted small">{{ __('portal.service_work.no_deliverables') }}</p>
        @endforelse

        <form method="POST" action="{{ route('service-work.deliverable', ['type' => $type, 'id' => $model->id]) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-4">
            @csrf
            <div class="col-md-5">
                <label class="form-label small">{{ __('portal.service_work.deliverable_label') }}</label>
                <input type="text" name="label" class="form-control form-control-sm" maxlength="255" placeholder="{{ __('portal.service_work.deliverable_label_ph') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">{{ __('portal.service_work.file') }}</label>
                <input type="file" name="file" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="dlvShare_{{ $model->id }}" name="is_client_visible" value="1">
                    <label class="form-check-label small" for="dlvShare_{{ $model->id }}">{{ __('portal.service_work.share_with_client') }}</label>
                </div>
                <button class="btn btn-primary btn-sm w-100"><i class="fas fa-upload"></i> {{ __('portal.service_work.upload') }}</button>
            </div>
        </form>

        {{-- ── B. Internal notes ─────────────────────────────────────── --}}
        <h6 class="text-muted mb-2">{{ __('portal.service_work.notes_title') }} <small class="text-muted">({{ __('portal.service_work.internal_only') }})</small></h6>
        @forelse($model->workNotes as $n)
            <div class="border-start border-3 border-secondary ps-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <small class="text-muted">{{ $n->user->name ?? '—' }} · {{ $n->created_at->translatedFormat('M d, Y H:i') }}</small>
                    @if((int) $n->user_id === (int) $me->id || $canManageItems)
                    <form method="POST" action="{{ route('service-work.delete-item', $n) }}" onsubmit="return confirm(@js(__('portal.service_work.confirm_delete')))">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-link text-danger p-0"><i class="fas fa-times"></i></button>
                    </form>
                    @endif
                </div>
                <div style="white-space:pre-line;">{{ $n->content }}</div>
            </div>
        @empty
            <p class="text-muted small">{{ __('portal.service_work.no_notes') }}</p>
        @endforelse

        <form method="POST" action="{{ route('service-work.note', ['type' => $type, 'id' => $model->id]) }}" class="mt-2">
            @csrf
            <textarea name="content" rows="2" class="form-control mb-2" maxlength="5000" required placeholder="{{ __('portal.service_work.note_ph') }}"></textarea>
            <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-plus"></i> {{ __('portal.service_work.add_note') }}</button>
        </form>
    </div>
</div>
@endif
